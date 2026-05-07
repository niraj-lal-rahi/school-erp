import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function PlansPage() {
  return (
    <SuperAdminLayout
      title="Plans"
      description="Create and manage subscription plans, pricing, usage limits, and feature entitlements."
      actions={<button type="button">Create Plan</button>}
    >
      <section className="sa-card">
        <h3>Plan Matrix</h3>
        <p>Use this page for price points, limits, and plan feature toggles.</p>
      </section>
    </SuperAdminLayout>
  );
}
