import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function SystemHealthPage() {
  return (
    <SuperAdminLayout
      title="System Health"
      description="Monitor platform services, tenant database connectivity, queue health, storage posture, and scheduler state."
    >
      <section className="sa-card">
        <h3>Health Dashboard</h3>
        <p>Pull from the platform health and tenant health endpoints.</p>
      </section>
    </SuperAdminLayout>
  );
}
