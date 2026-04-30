import { Alert } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalFees } from '../store/portalSlice';

export function FeesPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, fees, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalFees(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const summary = fees?.summary || {};

  return (
    <PortalPageShell
      title="Fees and Payment Summary"
      description="Review invoice history, dues, and whether the current portal access is allowed to pay fees for the selected student."
      modeLabel="Fees"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <PortalSectionCard title="Fee Summary" subtitle="The important numbers first, without making parents or students dig into raw invoices.">
        <AppDataTable
          title="Summary"
          columns={[
            { key: 'metric', header: 'Metric' },
            { key: 'value', header: 'Value' },
          ]}
          rows={[
            { id: 1, metric: 'Total Invoiced', value: `₹${Number(summary.total_invoiced || 0).toFixed(2)}` },
            { id: 2, metric: 'Total Paid', value: `₹${Number(summary.total_paid || 0).toFixed(2)}` },
            { id: 3, metric: 'Total Due', value: `₹${Number(summary.total_due || 0).toFixed(2)}` },
            { id: 4, metric: 'Payment Access', value: summary.can_pay_fees ? 'Enabled' : 'Disabled' },
          ]}
          rowsPerPage={4}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="Fee summary is not available."
        />
      </PortalSectionCard>

      <PortalSectionCard title="Invoices" subtitle="Every issued invoice for the active student, including dues and payment progress.">
        <AppDataTable
          title="Fee Invoices"
          columns={[
            { key: 'invoice_no', header: 'Invoice No' },
            { key: 'issue_date', header: 'Issue Date' },
            { key: 'due_date', header: 'Due Date' },
            { key: 'grand_total', header: 'Total', render: (row) => `₹${Number(row.grand_total || 0).toFixed(2)}` },
            { key: 'paid_amount', header: 'Paid', render: (row) => `₹${Number(row.paid_amount || 0).toFixed(2)}` },
            { key: 'balance_amount', header: 'Due', render: (row) => `₹${Number(row.balance_amount || 0).toFixed(2)}` },
            { key: 'status', header: 'Status' },
          ]}
          rows={fees?.invoices || []}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No invoices are available for the selected student."
        />
      </PortalSectionCard>
    </PortalPageShell>
  );
}
