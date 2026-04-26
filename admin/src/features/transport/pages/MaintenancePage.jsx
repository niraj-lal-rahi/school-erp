import { useEffect, useState } from 'react';
import { TransportPageShell } from '../components/TransportPageShell';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createMaintenanceLog, deleteMaintenanceLog, fetchMaintenanceLogs, fetchTransportReferenceData, updateMaintenanceLog } from '../store/transportSlice';

const initialForm = {
  id: null,
  vehicle_id: '',
  maintenance_type: 'service',
  title: '',
  maintenance_date: '',
  next_due_date: '',
  odometer_reading: '',
  cost: '',
  vendor_name: '',
  status: 'scheduled',
};

export function MaintenancePage() {
  const dispatch = useAppDispatch();
  const { maintenanceLogs, maintenanceLogsPagination, vehicles, saving, error } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchMaintenanceLogs({ page, per_page: 12, search }));
  }, [dispatch, page, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      next_due_date: form.next_due_date || null,
      odometer_reading: form.odometer_reading || null,
      cost: form.cost || null,
    };
    const action = form.id
      ? updateMaintenanceLog({ id: form.id, payload: { ...payload, id: undefined } })
      : createMaintenanceLog(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TransportPageShell
      title="Maintenance Logs"
      description="Track servicing, repairs, and compliance work so the fleet stays safe and audit-ready."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Maintenance Log' : 'Create Maintenance Log'}
      saving={saving}
      error={error}
      fields={[
        { key: 'vehicle_id', label: 'Vehicle', type: 'select', options: vehicles.map((item) => ({ value: item.id, label: item.vehicle_no })) },
        { key: 'maintenance_type', label: 'Type', type: 'select', options: [{ value: 'service', label: 'Service' }, { value: 'repair', label: 'Repair' }, { value: 'inspection', label: 'Inspection' }, { value: 'insurance', label: 'Insurance' }, { value: 'permit', label: 'Permit' }, { value: 'other', label: 'Other' }] },
        { key: 'title', label: 'Title' },
        { key: 'maintenance_date', label: 'Maintenance Date', type: 'date' },
        { key: 'next_due_date', label: 'Next Due Date', type: 'date' },
        { key: 'odometer_reading', label: 'Odometer', type: 'number' },
        { key: 'cost', label: 'Cost', type: 'number' },
        { key: 'vendor_name', label: 'Vendor' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'scheduled', label: 'Scheduled' }, { value: 'in_progress', label: 'In Progress' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }] },
      ]}
      columns={[
        { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
        { key: 'title', header: 'Title' },
        { key: 'maintenance_date', header: 'Date' },
        { key: 'cost', header: 'Cost' },
        { key: 'status', header: 'Status' },
      ]}
      rows={maintenanceLogs}
      search={search}
      setSearch={(value) => {
        setSearch(value);
        setPage(1);
      }}
      pagination={{ page, totalPages: maintenanceLogsPagination.totalPages, onPageChange: setPage }}
      onEdit={(row) => setForm({ ...initialForm, ...row, vehicle_id: row.vehicle_id || '' })}
      onDelete={(id) => dispatch(deleteMaintenanceLog(id))}
      emptyState="No maintenance records found."
    />
  );
}
