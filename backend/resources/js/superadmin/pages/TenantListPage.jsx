import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';
import StatusBadge from '../components/StatusBadge';

export default function TenantListPage() {
  return (
    <SuperAdminLayout
      title="Tenant List"
      description="Manage tenant lifecycle, provisioning, connectivity, and operational status from one place."
      actions={(
        <>
          <button type="button">Create Tenant</button>
          <button type="button">Export List</button>
        </>
      )}
    >
      <section className="sa-card">
        <div className="sa-row sa-row--spread">
          <div>
            <h3>Tenants</h3>
            <p>Include suspend, activate, provision database, and test database actions in each row.</p>
          </div>
          <StatusBadge status="active" />
        </div>
        <div className="sa-table-placeholder">
          Tenant table placeholder with masked DB posture, plan, health, and action buttons.
        </div>
      </section>
    </SuperAdminLayout>
  );
}
