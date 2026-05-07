import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';
import AuditTimeline from '../components/AuditTimeline';

const sampleTimeline = [
  { id: 1, action: 'tenant_created', description: 'Tenant record created.', created_at: new Date().toISOString(), module: 'platform_tenant' },
  { id: 2, action: 'tenant_db_provisioned', description: 'Database provisioned and connected.', created_at: new Date().toISOString(), module: 'tenant_database' },
];

export default function TenantDetailPage() {
  return (
    <SuperAdminLayout
      title="Tenant Detail"
      description="View tenant metadata, current subscription, feature posture, database status, and audit history."
      actions={(
        <>
          <button type="button">Activate</button>
          <button type="button">Suspend</button>
          <button type="button">Open Provisioning</button>
        </>
      )}
    >
      <div className="sa-grid sa-grid--two">
        <section className="sa-card"><h3>Tenant Overview</h3><p>Show tenant code, slug, status, trial expiry, plan, and support controls here.</p></section>
        <section className="sa-card"><h3>Operational Summary</h3><p>Link health, feature access, billing risk, and current admin contacts here.</p></section>
      </div>
      <AuditTimeline items={sampleTimeline} />
    </SuperAdminLayout>
  );
}
