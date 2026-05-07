import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function BillingPage() {
  return (
    <SuperAdminLayout
      title="Billing"
      description="Inspect tenant billing records, reconcile manual collections, and mark invoices paid or failed."
    >
      <section className="sa-card">
        <h3>Billing Records</h3>
        <p>Include mark-paid and mark-failed actions with audit-backed confirmation.</p>
      </section>
    </SuperAdminLayout>
  );
}
