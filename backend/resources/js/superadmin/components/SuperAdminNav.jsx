import React from 'react';

export const superAdminNavItems = [
  { label: 'Dashboard', path: '/superadmin' },
  { label: 'Tenants', path: '/superadmin/tenants' },
  { label: 'Plans', path: '/superadmin/plans' },
  { label: 'Subscriptions', path: '/superadmin/subscriptions' },
  { label: 'Billing', path: '/superadmin/billing' },
  { label: 'Feature Flags', path: '/superadmin/feature-flags' },
  { label: 'Platform Settings', path: '/superadmin/platform-settings' },
  { label: 'Security Settings', path: '/superadmin/security-settings' },
  { label: 'System Health', path: '/superadmin/system-health' },
  { label: 'Audit Logs', path: '/superadmin/audit-logs' },
  { label: 'Impersonation Logs', path: '/superadmin/impersonation-logs' },
  { label: 'Emergency Access', path: '/superadmin/emergency-access' },
  { label: 'Backups', path: '/superadmin/backups' },
];

export default function SuperAdminNav() {
  return (
    <nav className="sa-nav">
      {superAdminNavItems.map((item) => (
        <a key={item.path} href={item.path} className="sa-nav__link">
          {item.label}
        </a>
      ))}
    </nav>
  );
}
