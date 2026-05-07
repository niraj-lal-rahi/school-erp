import React from 'react';
import { createRoot } from 'react-dom/client';
import routes from './routes/index';
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
  const route = resolveRoute(window.location.pathname);
  const Page = route.component;

  return <Page />;
}

const container = document.getElementById('superadmin-root');

if (container) {
  createRoot(container).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
  );
}
