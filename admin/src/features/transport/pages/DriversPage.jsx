import { useEffect, useState } from 'react';
import { TransportPageShell } from '../components/TransportPageShell';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createDriver, deleteDriver, fetchDrivers, fetchTransportReferenceData, updateDriver } from '../store/transportSlice';

const initialForm = {
  id: null,
  staff_id: '',
  driver_code: '',
  first_name: '',
  middle_name: '',
  last_name: '',
  phone: '',
  license_no: '',
  joining_date: '',
  status: 'active',
};

export function DriversPage() {
  const dispatch = useAppDispatch();
  const { drivers, driversPagination, staffMembers, saving, error } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchDrivers({ page, per_page: 12, search, status: statusFilter || undefined }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      staff_id: form.staff_id || null,
      joining_date: form.joining_date || null,
    };
    const action = form.id
      ? updateDriver({ id: form.id, payload: { ...payload, id: undefined } })
      : createDriver(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TransportPageShell
      title="Drivers"
      description="Manage dedicated transport drivers and connect them to HR staff profiles when needed."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Driver' : 'Create Driver'}
      saving={saving}
      error={error}
      fields={[
        { key: 'staff_id', label: 'Linked Staff', type: 'select', options: [{ value: '', label: 'None' }, ...staffMembers.map((item) => ({ value: item.id, label: item.full_name || item.employee_code }))] },
        { key: 'driver_code', label: 'Driver Code' },
        { key: 'first_name', label: 'First Name' },
        { key: 'middle_name', label: 'Middle Name' },
        { key: 'last_name', label: 'Last Name' },
        { key: 'phone', label: 'Phone' },
        { key: 'license_no', label: 'License No' },
        { key: 'joining_date', label: 'Joining Date', type: 'date' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'suspended', label: 'Suspended' }, { value: 'retired', label: 'Retired' }] },
      ]}
      columns={[
        { key: 'full_name', header: 'Driver' },
        { key: 'driver_code', header: 'Code' },
        { key: 'phone', header: 'Phone' },
        { key: 'license_no', header: 'License' },
        { key: 'status', header: 'Status' },
      ]}
      rows={drivers}
      search={search}
      setSearch={(value) => {
        setSearch(value);
        setPage(1);
      }}
      filters={[
        {
          key: 'status',
          label: 'Status',
          value: statusFilter,
          onChange: (value) => {
            setStatusFilter(value);
            setPage(1);
          },
          options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'suspended', label: 'Suspended' }, { value: 'retired', label: 'Retired' }],
        },
      ]}
      pagination={{ page, totalPages: driversPagination.totalPages, onPageChange: setPage }}
      onEdit={(row) => setForm({
        ...initialForm,
        ...row,
        staff_id: row.staff_id || '',
        joining_date: row.joining_date || '',
      })}
      onDelete={(id) => dispatch(deleteDriver(id))}
      emptyState="No drivers found."
    />
  );
}
