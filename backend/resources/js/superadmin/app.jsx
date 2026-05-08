import React from 'react';
import { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import routes from './routes/index';
import LoginPage from './pages/LoginPage';
import { superAdminAuth } from './services/authService';
import './styles.css';

function normalizePath(pathname) {
  const value = pathname || '/superadmin';
  return value.endsWith('/') && value.length > 1 ? value.slice(0, -1) : value;
}

function routePatternToRegex(path) {
  const pattern = path.replace(/:[^/]+/g, '([^/]+)');
  return new RegExp(`^${pattern}$`);
}

function resolveRoute(pathname) {
  const currentPath = normalizePath(pathname);

  for (const route of routes) {
    if (route.path === currentPath) {
      return route;
    }

    if (route.path.includes(':')) {
      const regex = routePatternToRegex(route.path);

      if (regex.test(currentPath)) {
        return route;
      }
    }
  }

  return routes[0];
}

function App() {
  const route = useMemo(() => resolveRoute(window.location.pathname), []);
  const [authState, setAuthState] = useState({
    ready: false,
    loading: false,
    user: null,
    error: null,
  });

  useEffect(() => {
    let active = true;

    async function bootstrap() {
      if (!superAdminAuth.isAuthenticated()) {
        if (active) {
          setAuthState({ ready: true, loading: false, user: null, error: null });
        }

        if (!route.public && window.location.pathname !== '/superadmin/login') {
          window.location.replace('/superadmin/login');
        }

        return;
      }

      try {
        const payload = await superAdminAuth.me();

        if (!active) {
          return;
        }

        setAuthState({
          ready: true,
          loading: false,
          user: payload?.data ?? null,
          error: null,
        });

        if (route.public) {
          window.location.replace('/superadmin');
        }
      } catch (error) {
        superAdminAuth.clearToken();

        if (!active) {
          return;
        }

        setAuthState({
          ready: true,
          loading: false,
          user: null,
          error: error.message,
        });

        if (!route.public) {
          window.location.replace('/superadmin/login');
        }
      }
    }

    bootstrap();

    return () => {
      active = false;
    };
  }, [route.public]);

  async function handleLogin(credentials) {
    setAuthState((current) => ({ ...current, loading: true, error: null }));

    try {
      await superAdminAuth.login(credentials);
      const payload = await superAdminAuth.me();

      setAuthState({
        ready: true,
        loading: false,
        user: payload?.data ?? null,
        error: null,
      });

      window.location.assign('/superadmin');
    } catch (error) {
      superAdminAuth.clearToken();
      setAuthState({
        ready: true,
        loading: false,
        user: null,
        error: error.message,
      });
    }
  }

  async function handleLogout() {
    await superAdminAuth.logout();
    setAuthState({
      ready: true,
      loading: false,
      user: null,
      error: null,
    });
    window.location.assign('/superadmin/login');
  }

  if (!authState.ready && !route.public) {
    return (
      <div className="sa-auth">
        <div className="sa-auth__panel">
          <p className="sa-layout__eyebrow">SuperAdmin Control Plane</p>
          <h1>Checking session</h1>
          <p className="sa-layout__description">We are validating your platform access.</p>
        </div>
      </div>
    );
  }

  if (route.public) {
    return <LoginPage error={authState.error} loading={authState.loading} onSubmit={handleLogin} />;
  }

  const Page = route.component;

  return <Page currentUser={authState.user} onLogout={handleLogout} />;
}

const container = document.getElementById('superadmin-root');

if (container) {
  createRoot(container).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
  );
}
