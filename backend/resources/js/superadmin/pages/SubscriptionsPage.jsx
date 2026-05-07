import React from 'react';
import SuperAdminLayout from '../layouts/SuperAdminLayout';

export default function SubscriptionsPage() {
  return (
    <SuperAdminLayout
      title="Subscriptions"
      description="Assign plans, manage trials, upgrade or downgrade tenants, and monitor active subscription state."
    >
      <section className="sa-card">
        <h3>Subscription Operations</h3>
        <p>Wire to tenant subscribe, renew, cancel, and plan-change endpoints.</p>
      </section>
    </SuperAdminLayout>
  );
}
