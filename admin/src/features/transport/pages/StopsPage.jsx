import { useEffect, useState } from 'react';
import { TransportPageShell } from '../components/TransportPageShell';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createRouteStop, deleteRouteStop, fetchRouteStops, fetchTransportReferenceData, updateRouteStop } from '../store/transportSlice';

const initialForm = {
  id: null,
  route_id: '',
  name: '',
  code: '',
  stop_order: '',
  pickup_time: '',
  drop_time: '',
  address: '',
  status: 'active',
};

export function StopsPage() {
  const dispatch = useAppDispatch();
  const { routeStops, routeStopsPagination, routes, saving, error } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [routeFilter, setRouteFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchRouteStops({ page, per_page: 12, search, route_id: routeFilter || undefined }));
  }, [dispatch, page, search, routeFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      stop_order: Number(form.stop_order),
      pickup_time: form.pickup_time || null,
      drop_time: form.drop_time || null,
    };
    const action = form.id
      ? updateRouteStop({ id: form.id, payload: { ...payload, id: undefined } })
      : createRouteStop(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TransportPageShell
      title="Route Stops"
      description="Build pickup and drop sequencing for every route so allocations and trip logs stay accurate."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Stop' : 'Create Stop'}
      saving={saving}
      error={error}
      fields={[
        { key: 'route_id', label: 'Route', type: 'select', options: routes.map((item) => ({ value: item.id, label: item.name })) },
        { key: 'name', label: 'Stop Name' },
        { key: 'code', label: 'Stop Code' },
        { key: 'stop_order', label: 'Stop Order', type: 'number' },
        { key: 'pickup_time', label: 'Pickup Time' },
        { key: 'drop_time', label: 'Drop Time' },
        { key: 'address', label: 'Address', type: 'textarea' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Stop' },
        { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
        { key: 'stop_order', header: 'Order' },
        { key: 'pickup_time', header: 'Pickup' },
        { key: 'drop_time', header: 'Drop' },
        { key: 'status', header: 'Status' },
      ]}
      rows={routeStops}
      search={search}
      setSearch={(value) => {
        setSearch(value);
        setPage(1);
      }}
      filters={[
        {
          key: 'route',
          label: 'Route',
          value: routeFilter,
          onChange: (value) => {
            setRouteFilter(value);
            setPage(1);
          },
          options: [{ value: '', label: 'All' }, ...routes.map((item) => ({ value: item.id, label: item.name }))],
        },
      ]}
      pagination={{ page, totalPages: routeStopsPagination.totalPages, onPageChange: setPage }}
      onEdit={(row) => setForm({ ...initialForm, ...row, route_id: row.route_id || '' })}
      onDelete={(id) => dispatch(deleteRouteStop(id))}
      emptyState="No route stops found."
    />
  );
}
