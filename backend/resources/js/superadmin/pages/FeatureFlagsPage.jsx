import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function FeatureFlagsPage() {
  return (
    <SuperAdminLayout
      title="Feature Flags"
      description="Manage global features, tenant-specific overrides, and plan-based access for each module."
    >
      <section className="sa-card">
        <h3>Feature Access Matrix</h3>
        <p>Show global state, plan inheritance, and tenant override controls in one table.</p>
      </section>
    </SuperAdminLayout>
  );
}
