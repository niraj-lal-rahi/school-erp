import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function DashboardPage() {
  return (
    <SuperAdminLayout
      title="Dashboard"
      description="Platform overview across tenant health, billing posture, queue state, and operational alerts."
    >
      <div className="sa-grid sa-grid--two">
        <section className="sa-card"><h3>Platform KPIs</h3><p>Hook this page to `/api/v1/platform/health` for live uptime and queue metrics.</p></section>
        <section className="sa-card"><h3>Action Center</h3><p>Use this page for fast links to onboarding, failed jobs, emergency access, and recent audit events.</p></section>
      </div>
    </SuperAdminLayout>
  );
}
