import { useEffect, useState } from 'react';
import { TransportPageShell } from '../components/TransportPageShell';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createVehicle, deleteVehicle, fetchVehicles, updateVehicle } from '../store/transportSlice';

const initialForm = {
  id: null,
  vehicle_no: '',
  registration_no: '',
  name: '',
  vehicle_type: 'bus',
  seat_capacity: '',
  fuel_type: 'diesel',
  ownership_type: 'owned',
  status: 'active',
};

export function VehiclesPage() {
  const dispatch = useAppDispatch();
  const { vehicles, vehiclesPagination, saving, error } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchVehicles({ page, per_page: 12, search, status: statusFilter || undefined }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      seat_capacity: form.seat_capacity ? Number(form.seat_capacity) : null,
    };
    const action = form.id
      ? updateVehicle({ id: form.id, payload: { ...payload, id: undefined } })
      : createVehicle(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TransportPageShell
      title="Vehicles"
      description="Maintain fleet details, status, and operating capacity for school transport operations."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Vehicle' : 'Create Vehicle'}
      saving={saving}
      error={error}
      fields={[
        { key: 'vehicle_no', label: 'Vehicle No' },
        { key: 'registration_no', label: 'Registration No' },
        { key: 'name', label: 'Display Name' },
        { key: 'vehicle_type', label: 'Vehicle Type', type: 'select', options: [{ value: 'bus', label: 'Bus' }, { value: 'van', label: 'Van' }, { value: 'car', label: 'Car' }, { value: 'auto', label: 'Auto' }, { value: 'other', label: 'Other' }] },
        { key: 'seat_capacity', label: 'Seat Capacity', type: 'number' },
        { key: 'fuel_type', label: 'Fuel Type', type: 'select', options: [{ value: 'diesel', label: 'Diesel' }, { value: 'petrol', label: 'Petrol' }, { value: 'cng', label: 'CNG' }, { value: 'electric', label: 'Electric' }, { value: 'hybrid', label: 'Hybrid' }, { value: 'other', label: 'Other' }] },
        { key: 'ownership_type', label: 'Ownership', type: 'select', options: [{ value: 'owned', label: 'Owned' }, { value: 'leased', label: 'Leased' }, { value: 'contract', label: 'Contract' }] },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'maintenance', label: 'Maintenance' }, { value: 'retired', label: 'Retired' }] },
      ]}
      columns={[
        { key: 'vehicle_no', header: 'Vehicle No' },
        { key: 'registration_no', header: 'Registration No' },
        { key: 'vehicle_type', header: 'Type' },
        { key: 'seat_capacity', header: 'Seats' },
        { key: 'status', header: 'Status' },
      ]}
      rows={vehicles}
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
          options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'maintenance', label: 'Maintenance' }, { value: 'retired', label: 'Retired' }],
        },
      ]}
      pagination={{ page, totalPages: vehiclesPagination.totalPages, onPageChange: setPage }}
      onEdit={(row) => setForm({ ...initialForm, ...row, seat_capacity: row.seat_capacity ?? '' })}
      onDelete={(id) => dispatch(deleteVehicle(id))}
      emptyState="No vehicles found."
    />
  );
}
