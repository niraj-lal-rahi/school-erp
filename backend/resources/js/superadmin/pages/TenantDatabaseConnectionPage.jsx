import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';
import MaskedCredentialField from '../components/MaskedCredentialField';

export default function TenantDatabaseConnectionPage() {
  return (
    <SuperAdminLayout
      title="Tenant Database Connection"
      description="Inspect tenant database status safely without exposing raw credentials."
      actions={(
        <>
          <button type="button">Test Database</button>
          <button type="button">Provision Database</button>
        </>
      )}
    >
      <section className="sa-card">
        <h3>Masked Credentials</h3>
        <MaskedCredentialField label="Host" value="db******.local" helperText="Always masked in UI." />
        <MaskedCredentialField label="Port" value="****" />
        <MaskedCredentialField label="Username" value="er******in" />
        <MaskedCredentialField label="Password" value="********" helperText="Password is never displayed." />
      </section>
    </SuperAdminLayout>
  );
}
