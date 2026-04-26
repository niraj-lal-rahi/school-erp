import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createRoute,
  createRouteAssignment,
  deleteRoute,
  deleteRouteAssignment,
  fetchRouteAssignments,
  fetchRoutes,
  fetchTransportReferenceData,
  updateRoute,
  updateRouteAssignment,
} from '../store/transportSlice';

const initialRouteForm = {
  id: null,
  name: '',
  code: '',
  route_type: 'regular',
  start_location: '',
  end_location: '',
  distance_km: '',
  estimated_duration_minutes: '',
  status: 'active',
};

const initialAssignmentForm = {
  id: null,
  route_id: '',
  vehicle_id: '',
  driver_id: '',
  academic_year_id: '',
  assigned_from: '',
  assigned_to: '',
  shift_type: 'both',
  status: 'active',
};

export function RoutesPage() {
  const dispatch = useAppDispatch();
  const {
    routes,
    routeAssignments,
    routesPagination,
    routeAssignmentsPagination,
    vehicles,
    drivers,
    academicYears,
    saving,
    error,
  } = useAppSelector((state) => state.transport);
  const [routeForm, setRouteForm] = useState(initialRouteForm);
  const [assignmentForm, setAssignmentForm] = useState(initialAssignmentForm);
  const [routeSearch, setRouteSearch] = useState('');
  const [assignmentPage, setAssignmentPage] = useState(1);
  const [routePage, setRoutePage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchRoutes({ page: routePage, per_page: 10, search: routeSearch }));
  }, [dispatch, routePage, routeSearch]);

  useEffect(() => {
    dispatch(fetchRouteAssignments({ page: assignmentPage, per_page: 10 }));
  }, [dispatch, assignmentPage]);

  async function handleRouteSubmit(event) {
    event.preventDefault();
    const payload = {
      ...routeForm,
      distance_km: routeForm.distance_km || null,
      estimated_duration_minutes: routeForm.estimated_duration_minutes || null,
    };
    const action = routeForm.id
      ? updateRoute({ id: routeForm.id, payload: { ...payload, id: undefined } })
      : createRoute(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setRouteForm(initialRouteForm);
    }
  }

  async function handleAssignmentSubmit(event) {
    event.preventDefault();
    const payload = {
      ...assignmentForm,
      driver_id: assignmentForm.driver_id || null,
      academic_year_id: assignmentForm.academic_year_id || null,
      assigned_to: assignmentForm.assigned_to || null,
    };
    const action = assignmentForm.id
      ? updateRouteAssignment({ id: assignmentForm.id, payload: { ...payload, id: undefined } })
      : createRouteAssignment(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setAssignmentForm(initialAssignmentForm);
    }
  }

  return (
    <Stack spacing={3}>
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PermissionGate permission="transport.manage">
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack component="form" spacing={2} onSubmit={handleRouteSubmit}>
                <Typography variant="h5">Routes</Typography>
                <Typography variant="body2" color="text.secondary">Define transport corridors before creating stops, allocations, and trips.</Typography>
                {error ? <Alert severity="error">{error}</Alert> : null}
                <TextField label="Name" value={routeForm.name} onChange={(e) => setRouteForm((current) => ({ ...current, name: e.target.value }))} />
                <TextField label="Code" value={routeForm.code} onChange={(e) => setRouteForm((current) => ({ ...current, code: e.target.value }))} />
                <TextField select label="Route Type" value={routeForm.route_type} onChange={(e) => setRouteForm((current) => ({ ...current, route_type: e.target.value }))}>
                  <MenuItem value="regular">Regular</MenuItem>
                  <MenuItem value="special">Special</MenuItem>
                  <MenuItem value="event">Event</MenuItem>
                  <MenuItem value="exam">Exam</MenuItem>
                </TextField>
                <TextField label="Start Location" value={routeForm.start_location} onChange={(e) => setRouteForm((current) => ({ ...current, start_location: e.target.value }))} />
                <TextField label="End Location" value={routeForm.end_location} onChange={(e) => setRouteForm((current) => ({ ...current, end_location: e.target.value }))} />
                <TextField label="Distance (km)" value={routeForm.distance_km} onChange={(e) => setRouteForm((current) => ({ ...current, distance_km: e.target.value }))} />
                <TextField label="Duration (minutes)" value={routeForm.estimated_duration_minutes} onChange={(e) => setRouteForm((current) => ({ ...current, estimated_duration_minutes: e.target.value }))} />
                <TextField select label="Status" value={routeForm.status} onChange={(e) => setRouteForm((current) => ({ ...current, status: e.target.value }))}>
                  <MenuItem value="active">Active</MenuItem>
                  <MenuItem value="inactive">Inactive</MenuItem>
                </TextField>
                <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : routeForm.id ? 'Update Route' : 'Create Route'}</Button>
              </Stack>
            </Paper>
          </PermissionGate>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Routes List"
            columns={[
              { key: 'name', header: 'Name' },
              { key: 'code', header: 'Code' },
              { key: 'route_type', header: 'Type' },
              { key: 'start_location', header: 'Start' },
              { key: 'end_location', header: 'End' },
              { key: 'status', header: 'Status' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <PermissionGate permission="transport.manage" fallback={null}>
                    <Stack direction="row" spacing={1}>
                      <IconButton color="primary" onClick={() => setRouteForm({ ...initialRouteForm, ...row })}>
                        <EditOutlinedIcon />
                      </IconButton>
                      <IconButton color="error" onClick={() => dispatch(deleteRoute(row.id))}>
                        <DeleteOutlineOutlinedIcon />
                      </IconButton>
                    </Stack>
                  </PermissionGate>
                ),
              },
            ]}
            rows={routes}
            loading={false}
            searchValue={routeSearch}
            onSearchChange={(value) => {
              setRouteSearch(value);
              setRoutePage(1);
            }}
            pagination={{ page: routePage, totalPages: routesPagination.totalPages, onPageChange: setRoutePage }}
            emptyState="No routes found."
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PermissionGate permission="transport.manage">
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack component="form" spacing={2} onSubmit={handleAssignmentSubmit}>
                <Typography variant="h5">Vehicle Assignments</Typography>
                <Typography variant="body2" color="text.secondary">Map routes to vehicles and drivers for the active transport season.</Typography>
                <TextField select label="Route" value={assignmentForm.route_id} onChange={(e) => setAssignmentForm((current) => ({ ...current, route_id: e.target.value }))}>
                  {routes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                </TextField>
                <TextField select label="Vehicle" value={assignmentForm.vehicle_id} onChange={(e) => setAssignmentForm((current) => ({ ...current, vehicle_id: e.target.value }))}>
                  {vehicles.map((item) => <MenuItem key={item.id} value={item.id}>{item.vehicle_no}</MenuItem>)}
                </TextField>
                <TextField select label="Driver" value={assignmentForm.driver_id} onChange={(e) => setAssignmentForm((current) => ({ ...current, driver_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {drivers.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                </TextField>
                <TextField select label="Academic Year" value={assignmentForm.academic_year_id} onChange={(e) => setAssignmentForm((current) => ({ ...current, academic_year_id: e.target.value }))}>
                  <MenuItem value="">None</MenuItem>
                  {academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                </TextField>
                <TextField label="Assigned From" type="date" InputLabelProps={{ shrink: true }} value={assignmentForm.assigned_from} onChange={(e) => setAssignmentForm((current) => ({ ...current, assigned_from: e.target.value }))} />
                <TextField label="Assigned To" type="date" InputLabelProps={{ shrink: true }} value={assignmentForm.assigned_to} onChange={(e) => setAssignmentForm((current) => ({ ...current, assigned_to: e.target.value }))} />
                <TextField select label="Shift Type" value={assignmentForm.shift_type} onChange={(e) => setAssignmentForm((current) => ({ ...current, shift_type: e.target.value }))}>
                  <MenuItem value="pickup">Pickup</MenuItem>
                  <MenuItem value="drop">Drop</MenuItem>
                  <MenuItem value="both">Both</MenuItem>
                </TextField>
                <TextField select label="Status" value={assignmentForm.status} onChange={(e) => setAssignmentForm((current) => ({ ...current, status: e.target.value }))}>
                  <MenuItem value="active">Active</MenuItem>
                  <MenuItem value="inactive">Inactive</MenuItem>
                  <MenuItem value="completed">Completed</MenuItem>
                  <MenuItem value="cancelled">Cancelled</MenuItem>
                </TextField>
                <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : assignmentForm.id ? 'Update Assignment' : 'Create Assignment'}</Button>
              </Stack>
            </Paper>
          </PermissionGate>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Route Vehicle Assignments"
            columns={[
              { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
              { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
              { key: 'driver', header: 'Driver', render: (row) => row.driver?.full_name || 'Unassigned' },
              { key: 'shift_type', header: 'Shift' },
              { key: 'status', header: 'Status' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <PermissionGate permission="transport.manage" fallback={null}>
                    <Stack direction="row" spacing={1}>
                      <IconButton color="primary" onClick={() => setAssignmentForm({
                        ...initialAssignmentForm,
                        ...row,
                        route_id: row.route_id || '',
                        vehicle_id: row.vehicle_id || '',
                        driver_id: row.driver_id || '',
                        academic_year_id: row.academic_year_id || '',
                      })}>
                        <EditOutlinedIcon />
                      </IconButton>
                      <IconButton color="error" onClick={() => dispatch(deleteRouteAssignment(row.id))}>
                        <DeleteOutlineOutlinedIcon />
                      </IconButton>
                    </Stack>
                  </PermissionGate>
                ),
              },
            ]}
            rows={routeAssignments}
            loading={false}
            searchValue=""
            onSearchChange={() => {}}
            pagination={{ page: assignmentPage, totalPages: routeAssignmentsPagination.totalPages, onPageChange: setAssignmentPage }}
            emptyState="No route assignments created yet."
          />
        </Grid>
      </Grid>
    </Stack>
  );
}
