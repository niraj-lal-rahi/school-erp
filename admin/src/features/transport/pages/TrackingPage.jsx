import MyLocationOutlinedIcon from '@mui/icons-material/MyLocationOutlined';
import { Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createGpsLog, fetchGpsLogs, fetchTransportReferenceData, fetchVehicleLocation } from '../store/transportSlice';

const initialForm = {
  vehicle_id: '',
  driver_id: '',
  transport_trip_id: '',
  latitude: '',
  longitude: '',
  recorded_at: new Date().toISOString().slice(0, 16),
  engine_status: 'on',
};

export function TrackingPage() {
  const dispatch = useAppDispatch();
  const { gpsLogs, gpsLogsPagination, vehicles, drivers, trips, vehicleLocation, saving, loading } = useAppSelector((state) => state.transport);
  const [form, setForm] = useState(initialForm);
  const [page, setPage] = useState(1);
  const [vehicleLookup, setVehicleLookup] = useState('');

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
    dispatch(fetchTrips({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchGpsLogs({ page, per_page: 12 }));
  }, [dispatch, page]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(createGpsLog({
      ...form,
      vehicle_id: form.vehicle_id || null,
      driver_id: form.driver_id || null,
      transport_trip_id: form.transport_trip_id || null,
      latitude: Number(form.latitude),
      longitude: Number(form.longitude),
      recorded_at: form.recorded_at,
    }));
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Stack spacing={3}>
          <PermissionGate permission="transport.manage">
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack component="form" spacing={2} onSubmit={handleSubmit}>
                <Typography variant="h5">GPS Tracking</Typography>
                <Typography variant="body2" color="text.secondary">Capture location pings for live movement visibility and playback-ready logs.</Typography>
                <TextField select label="Vehicle" value={form.vehicle_id} onChange={(e) => setForm((current) => ({ ...current, vehicle_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {vehicles.map((item) => <MenuItem key={item.id} value={item.id}>{item.vehicle_no}</MenuItem>)}
                </TextField>
                <TextField select label="Driver" value={form.driver_id} onChange={(e) => setForm((current) => ({ ...current, driver_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {drivers.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                </TextField>
                <TextField select label="Trip" value={form.transport_trip_id} onChange={(e) => setForm((current) => ({ ...current, transport_trip_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {trips.map((item) => <MenuItem key={item.id} value={item.id}>{`${item.route?.name || 'Route'} - ${item.trip_date}`}</MenuItem>)}
                </TextField>
                <TextField label="Latitude" value={form.latitude} onChange={(e) => setForm((current) => ({ ...current, latitude: e.target.value }))} />
                <TextField label="Longitude" value={form.longitude} onChange={(e) => setForm((current) => ({ ...current, longitude: e.target.value }))} />
                <TextField label="Recorded At" type="datetime-local" InputLabelProps={{ shrink: true }} value={form.recorded_at} onChange={(e) => setForm((current) => ({ ...current, recorded_at: e.target.value }))} />
                <TextField select label="Engine Status" value={form.engine_status} onChange={(e) => setForm((current) => ({ ...current, engine_status: e.target.value }))}>
                  <MenuItem value="on">On</MenuItem>
                  <MenuItem value="off">Off</MenuItem>
                  <MenuItem value="idle">Idle</MenuItem>
                </TextField>
                <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : 'Store GPS Log'}</Button>
              </Stack>
            </Paper>
          </PermissionGate>

          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Vehicle Location Lookup</Typography>
              <TextField select label="Vehicle" value={vehicleLookup} onChange={(e) => setVehicleLookup(e.target.value)}>
                <MenuItem value="">Select vehicle</MenuItem>
                {vehicles.map((item) => <MenuItem key={item.id} value={item.id}>{item.vehicle_no}</MenuItem>)}
              </TextField>
              <Button
                variant="outlined"
                startIcon={<MyLocationOutlinedIcon />}
                disabled={!vehicleLookup}
                onClick={() => dispatch(fetchVehicleLocation(vehicleLookup))}
              >
                Fetch Latest Location
              </Button>
              {vehicleLocation ? (
                <Stack spacing={0.5}>
                  <Typography variant="body2"><strong>Latitude:</strong> {vehicleLocation.latitude}</Typography>
                  <Typography variant="body2"><strong>Longitude:</strong> {vehicleLocation.longitude}</Typography>
                  <Typography variant="body2"><strong>Logged At:</strong> {vehicleLocation.log_datetime || vehicleLocation.created_at}</Typography>
                </Stack>
              ) : (
                <Typography variant="body2" color="text.secondary">Select a vehicle to see its latest known location.</Typography>
              )}
            </Stack>
          </Paper>
        </Stack>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="GPS Logs"
          columns={[
            { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
            { key: 'driver', header: 'Driver', render: (row) => row.driver?.full_name || 'N/A' },
            { key: 'latitude', header: 'Latitude' },
            { key: 'longitude', header: 'Longitude' },
            { key: 'recorded_at', header: 'Logged At' },
          ]}
          rows={gpsLogs}
          loading={loading}
          searchValue=""
          onSearchChange={() => {}}
          pagination={{ page, totalPages: gpsLogsPagination.totalPages, onPageChange: setPage }}
          emptyState="No GPS logs found."
        />
      </Grid>
    </Grid>
  );
}
