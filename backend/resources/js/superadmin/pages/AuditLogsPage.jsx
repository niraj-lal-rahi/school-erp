import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';
import AuditTimeline from '../components/AuditTimeline';

export default function AuditLogsPage() {
  return (
    <SuperAdminLayout
      title="Audit Logs"
      description="Search and filter platform audit events across tenants, billing, provisioning, security, and settings changes."
    >
      <AuditTimeline items={[]} />
    </SuperAdminLayout>
  );
}
