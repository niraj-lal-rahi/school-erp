import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function PlatformSettingsPage() {
  return (
    <SuperAdminLayout
      title="Platform Settings"
      description="Configure email, SMS, payment, storage, branding, and localization defaults for the platform."
    >
      <section className="sa-card">
        <h3>Settings Groups</h3>
        <p>Bind this page to grouped platform settings while keeping secrets masked.</p>
      </section>
    </SuperAdminLayout>
  );
}
