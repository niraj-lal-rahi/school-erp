import CancelOutlinedIcon from '@mui/icons-material/CancelOutlined';
import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import DirectionsBusFilledOutlinedIcon from '@mui/icons-material/DirectionsBusFilledOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  cancelTrip,
  completeTrip,
  createTrip,
  deleteTrip,
  fetchTransportReferenceData,
  fetchTripLogs,
  fetchTrips,
  markBoarded,
  markDropped,
  startTrip,
  updateTrip,
} from '../store/transportSlice';

const initialTripForm = {
  id: null,
  route_vehicle_assignment_id: '',
  route_id: '',
  vehicle_id: '',
  driver_id: '',
  trip_date: '',
  trip_type: 'pickup',
  scheduled_start_time: '',
  scheduled_end_time: '',
  status: 'scheduled',
  notes: '',
};

const initialLogForm = {
  transport_trip_id: '',
  user_type: 'student',
  student_id: '',
  staff_id: '',
  route_stop_id: '',
  event_type: 'boarded',
  event_time: new Date().toISOString().slice(0, 16),
  remarks: '',
};

export function TripsPage() {
  const dispatch = useAppDispatch();
  const {
    trips,
    tripLogs,
    tripsPagination,
    tripLogsPagination,
    routeAssignments,
    routeStops,
    students,
    staffMembers,
    saving,
    error,
  } = useAppSelector((state) => state.transport);
  const [tripForm, setTripForm] = useState(initialTripForm);
  const [logForm, setLogForm] = useState(initialLogForm);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [logsPage, setLogsPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchTrips({ page, per_page: 10, status: search || undefined }));
  }, [dispatch, page, search]);

  useEffect(() => {
    dispatch(fetchTripLogs({ page: logsPage, per_page: 10 }));
  }, [dispatch, logsPage]);

  const selectedAssignment = useMemo(
    () => routeAssignments.find((item) => item.id === Number(tripForm.route_vehicle_assignment_id)),
    [routeAssignments, tripForm.route_vehicle_assignment_id],
  );

  useEffect(() => {
    if (!selectedAssignment) return;
    setTripForm((current) => ({
      ...current,
      route_id: selectedAssignment.route_id || '',
      vehicle_id: selectedAssignment.vehicle_id || '',
      driver_id: selectedAssignment.driver_id || '',
    }));
  }, [selectedAssignment]);

  async function handleTripSubmit(event) {
    event.preventDefault();
    const payload = {
      ...tripForm,
      driver_id: tripForm.driver_id || null,
      scheduled_start_time: tripForm.scheduled_start_time || null,
      scheduled_end_time: tripForm.scheduled_end_time || null,
    };
    const action = tripForm.id
      ? updateTrip({ id: tripForm.id, payload: { ...payload, id: undefined } })
      : createTrip(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setTripForm(initialTripForm);
    }
  }

  async function handleLog(eventType) {
    const payload = {
      ...logForm,
      event_type: eventType,
      student_id: logForm.user_type === 'student' ? logForm.student_id : null,
      staff_id: logForm.user_type === 'staff' ? logForm.staff_id : null,
      event_time: logForm.event_time,
    };
    const action = eventType === 'boarded'
      ? markBoarded({ id: payload.transport_trip_id, payload })
      : markDropped({ id: payload.transport_trip_id, payload });
    const result = await dispatch(action);
    if (!result.error) {
      setLogForm(initialLogForm);
      dispatch(fetchTripLogs({ page: 1, per_page: 10 }));
    }
  }

  const tripStopOptions = routeStops.filter((item) => item.route_id === Number(tripForm.route_id || logForm.route_id || 0));

  return (
    <Stack spacing={3}>
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PermissionGate permission="transport.manage">
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack component="form" spacing={2} onSubmit={handleTripSubmit}>
                <Typography variant="h5">Trips</Typography>
                <Typography variant="body2" color="text.secondary">Create daily pickup and drop movements from approved route assignments.</Typography>
                {error ? <Alert severity="error">{error}</Alert> : null}
                <TextField select label="Route Assignment" value={tripForm.route_vehicle_assignment_id} onChange={(e) => setTripForm((current) => ({ ...current, route_vehicle_assignment_id: e.target.value }))}>
                  {routeAssignments.map((item) => <MenuItem key={item.id} value={item.id}>{item.route?.name || `Assignment #${item.id}`}</MenuItem>)}
                </TextField>
                <TextField label="Trip Date" type="date" InputLabelProps={{ shrink: true }} value={tripForm.trip_date} onChange={(e) => setTripForm((current) => ({ ...current, trip_date: e.target.value }))} />
                <TextField select label="Trip Type" value={tripForm.trip_type} onChange={(e) => setTripForm((current) => ({ ...current, trip_type: e.target.value }))}>
                  <MenuItem value="pickup">Pickup</MenuItem>
                  <MenuItem value="drop">Drop</MenuItem>
                  <MenuItem value="round_trip">Round Trip</MenuItem>
                  <MenuItem value="special">Special</MenuItem>
                </TextField>
                <TextField label="Scheduled Start" value={tripForm.scheduled_start_time} onChange={(e) => setTripForm((current) => ({ ...current, scheduled_start_time: e.target.value }))} />
                <TextField label="Scheduled End" value={tripForm.scheduled_end_time} onChange={(e) => setTripForm((current) => ({ ...current, scheduled_end_time: e.target.value }))} />
                <TextField select label="Status" value={tripForm.status} onChange={(e) => setTripForm((current) => ({ ...current, status: e.target.value }))}>
                  <MenuItem value="scheduled">Scheduled</MenuItem>
                  <MenuItem value="in_progress">In Progress</MenuItem>
                  <MenuItem value="completed">Completed</MenuItem>
                  <MenuItem value="cancelled">Cancelled</MenuItem>
                </TextField>
                <TextField label="Notes" multiline minRows={3} value={tripForm.notes} onChange={(e) => setTripForm((current) => ({ ...current, notes: e.target.value }))} />
                <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : tripForm.id ? 'Update Trip' : 'Create Trip'}</Button>
              </Stack>
            </Paper>
          </PermissionGate>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Trip Schedule"
            columns={[
              { key: 'trip_date', header: 'Date' },
              { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
              { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
              { key: 'trip_type', header: 'Type' },
              { key: 'status', header: 'Status' },
              { key: 'boarded', header: 'Boarded', render: (row) => row.total_boarded || 0 },
              { key: 'dropped', header: 'Dropped', render: (row) => row.total_dropped || 0 },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <PermissionGate permission="transport.manage" fallback={null}>
                    <Stack direction="row" spacing={1}>
                      <IconButton color="primary" onClick={() => setTripForm({
                        ...initialTripForm,
                        ...row,
                        route_vehicle_assignment_id: row.route_vehicle_assignment_id || '',
                        route_id: row.route_id || '',
                        vehicle_id: row.vehicle_id || '',
                        driver_id: row.driver_id || '',
                      })}>
                        <DirectionsBusFilledOutlinedIcon />
                      </IconButton>
                      <IconButton color="success" disabled={row.status !== 'scheduled'} onClick={() => dispatch(startTrip({ id: row.id, payload: { started_at: new Date().toISOString() } }))}>
                        <EditOutlinedIcon />
                      </IconButton>
                      <IconButton color="info" disabled={row.status !== 'in_progress'} onClick={() => dispatch(completeTrip({ id: row.id, payload: { completed_at: new Date().toISOString() } }))}>
                        <CheckCircleOutlineOutlinedIcon />
                      </IconButton>
                      <IconButton color="warning" disabled={row.status === 'completed' || row.status === 'cancelled'} onClick={() => dispatch(cancelTrip({ id: row.id, payload: { reason: 'Cancelled from admin panel' } }))}>
                        <CancelOutlinedIcon />
                      </IconButton>
                      <IconButton color="error" onClick={() => dispatch(deleteTrip(row.id))}>
                        <DeleteOutlineOutlinedIcon />
                      </IconButton>
                    </Stack>
                  </PermissionGate>
                ),
              },
            ]}
            rows={trips}
            loading={false}
            searchValue={search}
            onSearchChange={(value) => {
              setSearch(value);
              setPage(1);
            }}
            filters={[
              {
                key: 'status',
                label: 'Status',
                value: search,
                onChange: (value) => {
                  setSearch(value);
                  setPage(1);
                },
                options: [{ value: '', label: 'All' }, { value: 'scheduled', label: 'Scheduled' }, { value: 'in_progress', label: 'In Progress' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }],
              },
            ]}
            pagination={{ page, totalPages: tripsPagination.totalPages, onPageChange: setPage }}
            emptyState="No trips found."
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PermissionGate permission="transport.manage">
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Typography variant="h5">Trip Event Logger</Typography>
                <Typography variant="body2" color="text.secondary">Mark boarded and dropped events without leaving the trip operations screen.</Typography>
                <TextField select label="Trip" value={logForm.transport_trip_id} onChange={(e) => setLogForm((current) => ({ ...current, transport_trip_id: e.target.value }))}>
                  {trips.map((item) => <MenuItem key={item.id} value={item.id}>{`${item.route?.name || 'Route'} - ${item.trip_date}`}</MenuItem>)}
                </TextField>
                <TextField select label="User Type" value={logForm.user_type} onChange={(e) => setLogForm((current) => ({ ...current, user_type: e.target.value, student_id: '', staff_id: '' }))}>
                  <MenuItem value="student">Student</MenuItem>
                  <MenuItem value="staff">Staff</MenuItem>
                </TextField>
                {logForm.user_type === 'student' ? (
                  <TextField select label="Student" value={logForm.student_id} onChange={(e) => setLogForm((current) => ({ ...current, student_id: e.target.value }))}>
                    {students.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                  </TextField>
                ) : (
                  <TextField select label="Staff" value={logForm.staff_id} onChange={(e) => setLogForm((current) => ({ ...current, staff_id: e.target.value }))}>
                    {staffMembers.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name || item.employee_code}</MenuItem>)}
                  </TextField>
                )}
                <TextField select label="Route Stop" value={logForm.route_stop_id} onChange={(e) => setLogForm((current) => ({ ...current, route_stop_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {tripStopOptions.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                </TextField>
                <TextField label="Event Time" type="datetime-local" InputLabelProps={{ shrink: true }} value={logForm.event_time} onChange={(e) => setLogForm((current) => ({ ...current, event_time: e.target.value }))} />
                <TextField label="Remarks" multiline minRows={2} value={logForm.remarks} onChange={(e) => setLogForm((current) => ({ ...current, remarks: e.target.value }))} />
                <Stack direction="row" spacing={2}>
                  <Button variant="contained" onClick={() => handleLog('boarded')}>Mark Boarded</Button>
                  <Button variant="outlined" onClick={() => handleLog('dropped')}>Mark Dropped</Button>
                </Stack>
              </Stack>
            </Paper>
          </PermissionGate>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Trip Logs"
            columns={[
              { key: 'event_time', header: 'Event Time' },
              { key: 'trip', header: 'Trip', render: (row) => row.trip?.trip_date || row.transport_trip_id },
              { key: 'passenger', header: 'Passenger', render: (row) => row.student?.full_name || row.staff?.full_name || 'N/A' },
              { key: 'event_type', header: 'Event' },
              { key: 'user_type', header: 'Type' },
            ]}
            rows={tripLogs}
            loading={false}
            searchValue=""
            onSearchChange={() => {}}
            pagination={{ page: logsPage, totalPages: tripLogsPagination.totalPages, onPageChange: setLogsPage }}
            emptyState="No trip logs recorded yet."
          />
        </Grid>
      </Grid>
    </Stack>
  );
}
