import React from 'react';
import SuperAdminNav from '../components/SuperAdminNav';

export default function SuperAdminLayout({ title, description, actions, children }) {
  return (
    <div className="sa-layout">
      <header className="sa-layout__header">
        <div>
          <p className="sa-layout__eyebrow">SuperAdmin Control Plane</p>
          <h1>{title}</h1>
          {description ? <p className="sa-layout__description">{description}</p> : null}
        </div>
        {actions ? <div className="sa-layout__actions">{actions}</div> : null}
      </header>
      <SuperAdminNav />
      <main className="sa-layout__content">{children}</main>
    </div>
  );
}
