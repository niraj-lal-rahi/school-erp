import React, { useState } from 'react';

export default function LoginPage({ error, loading, onSubmit }) {
  const [form, setForm] = useState({
    email: 'superadmin@system.local',
    password: 'password123',
  });

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    await onSubmit(form);
  }

  return (
    <div className="sa-auth">
      <div className="sa-auth__panel">
        <p className="sa-layout__eyebrow">SuperAdmin Control Plane</p>
        <h1>Platform Login</h1>
        <p className="sa-layout__description">
          Sign in with your platform administrator account. This login is separate from school tenant access.
        </p>

        <form className="sa-auth__form" onSubmit={handleSubmit}>
          <label className="sa-auth__field">
            <span>Email</span>
            <input
              type="email"
              name="email"
              value={form.email}
              onChange={handleChange}
              autoComplete="username"
              required
            />
          </label>

          <label className="sa-auth__field">
            <span>Password</span>
            <input
              type="password"
              name="password"
              value={form.password}
              onChange={handleChange}
              autoComplete="current-password"
              required
            />
          </label>

          {error ? <div className="sa-auth__error">{error}</div> : null}

          <button type="submit" disabled={loading}>
            {loading ? 'Signing in...' : 'Sign in to platform'}
          </button>
        </form>
      </div>
    </div>
  );
}
