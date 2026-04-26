import { useEffect, useState } from 'react';
import { TransportPageShell } from '../components/TransportPageShell';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createFuelLog, deleteFuelLog, fetchFuelLogs, fetchTransportReferenceData, updateFuelLog } from '../store/transportSlice';

const initialForm = {
  id: null,
  vehicle_id: '',
  fuel_date: '',
  quantity_liters: '',
  cost_per_unit: '',
  total_cost: '',
  odometer_reading: '',
  fuel_station: '',
  reference_no: '',
};

export function FuelLogsPage() {
  const dispatch = useAppDispatch();
  const { fuelLogs, fuelLogsPagination, vehicles, saving, error } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchFuelLogs({ page, per_page: 12, search }));
  }, [dispatch, page, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      quantity_liters: form.quantity_liters || null,
      cost_per_unit: form.cost_per_unit || null,
      total_cost: form.total_cost || null,
      odometer_reading: form.odometer_reading || null,
    };
    const action = form.id
      ? updateFuelLog({ id: form.id, payload: { ...payload, id: undefined } })
      : createFuelLog(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TransportPageShell
      title="Fuel Logs"
      description="Capture refueling patterns and operating cost trends for each vehicle in the fleet."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Fuel Log' : 'Create Fuel Log'}
      saving={saving}
      error={error}
      fields={[
        { key: 'vehicle_id', label: 'Vehicle', type: 'select', options: vehicles.map((item) => ({ value: item.id, label: item.vehicle_no })) },
        { key: 'fuel_date', label: 'Fuel Date', type: 'date' },
        { key: 'quantity_liters', label: 'Quantity (L)', type: 'number' },
        { key: 'cost_per_unit', label: 'Cost per Unit', type: 'number' },
        { key: 'total_cost', label: 'Total Cost', type: 'number' },
        { key: 'odometer_reading', label: 'Odometer', type: 'number' },
        { key: 'fuel_station', label: 'Fuel Station' },
        { key: 'reference_no', label: 'Reference No' },
      ]}
      columns={[
        { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
        { key: 'fuel_date', header: 'Date' },
        { key: 'quantity_liters', header: 'Liters' },
        { key: 'total_cost', header: 'Total Cost' },
        { key: 'fuel_station', header: 'Station' },
      ]}
      rows={fuelLogs}
      search={search}
      setSearch={(value) => {
        setSearch(value);
        setPage(1);
      }}
      pagination={{ page, totalPages: fuelLogsPagination.totalPages, onPageChange: setPage }}
      onEdit={(row) => setForm({ ...initialForm, ...row, vehicle_id: row.vehicle_id || '' })}
      onDelete={(id) => dispatch(deleteFuelLog(id))}
      emptyState="No fuel logs found."
    />
  );
}
