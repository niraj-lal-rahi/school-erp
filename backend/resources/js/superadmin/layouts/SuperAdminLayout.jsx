import React from 'react';
import SuperAdminNav from '../components/SuperAdminNav';

export default function SuperAdminLayout({ title, description, actions, children, currentUser, onLogout }) {
  return (
    <div className="sa-layout">
      <header className="sa-layout__header">
        <div>
          <p className="sa-layout__eyebrow">SuperAdmin Control Plane</p>
          <h1>{title}</h1>
          {description ? <p className="sa-layout__description">{description}</p> : null}
        </div>
        <div className="sa-layout__actions">
          {currentUser ? <span className="sa-user-chip">{currentUser.name || currentUser.email}</span> : null}
          {actions}
          {onLogout ? <button type="button" className="sa-button sa-button--secondary" onClick={onLogout}>Sign out</button> : null}
        </div>
      </header>
      <SuperAdminNav />
      <main className="sa-layout__content">{children}</main>
    </div>
  );
}
