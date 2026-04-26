import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Button, Grid, IconButton, MenuItem, Paper, Stack, Tab, Tabs, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createStaffAllocation,
  createStudentAllocation,
  deleteStaffAllocation,
  deleteStudentAllocation,
  fetchStaffAllocations,
  fetchStudentAllocations,
  fetchTransportReferenceData,
  updateStaffAllocation,
  updateStudentAllocation,
} from '../store/transportSlice';

const initialStudentForm = {
  id: null,
  student_id: '',
  academic_year_id: '',
  route_id: '',
  route_vehicle_assignment_id: '',
  pickup_stop_id: '',
  drop_stop_id: '',
  allocated_from: '',
  allocated_to: '',
  fare_amount: '',
  status: 'active',
};

const initialStaffForm = {
  id: null,
  staff_id: '',
  route_id: '',
  route_vehicle_assignment_id: '',
  pickup_stop_id: '',
  drop_stop_id: '',
  allocated_from: '',
  allocated_to: '',
  fare_amount: '',
  status: 'active',
};

export function AllocationsPage() {
  const dispatch = useAppDispatch();
  const {
    studentAllocations,
    staffAllocations,
    studentAllocationsPagination,
    staffAllocationsPagination,
    students,
    staffMembers,
    academicYears,
    routes,
    routeStops,
    routeAssignments,
    saving,
  } = useAppSelector((state) => state.transport);
  const [tab, setTab] = useState('student');
  const [studentForm, setStudentForm] = useState(initialStudentForm);
  const [staffForm, setStaffForm] = useState(initialStaffForm);
  const [studentPage, setStudentPage] = useState(1);
  const [staffPage, setStaffPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTransportReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchStudentAllocations({ page: studentPage, per_page: 10 }));
  }, [dispatch, studentPage]);

  useEffect(() => {
    dispatch(fetchStaffAllocations({ page: staffPage, per_page: 10 }));
  }, [dispatch, staffPage]);

  async function submitStudent(event) {
    event.preventDefault();
    const payload = {
      ...studentForm,
      route_vehicle_assignment_id: studentForm.route_vehicle_assignment_id || null,
      pickup_stop_id: studentForm.pickup_stop_id || null,
      drop_stop_id: studentForm.drop_stop_id || null,
      allocated_to: studentForm.allocated_to || null,
      fare_amount: studentForm.fare_amount || null,
    };
    const action = studentForm.id
      ? updateStudentAllocation({ id: studentForm.id, payload: { ...payload, id: undefined } })
      : createStudentAllocation(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setStudentForm(initialStudentForm);
    }
  }

  async function submitStaff(event) {
    event.preventDefault();
    const payload = {
      ...staffForm,
      route_vehicle_assignment_id: staffForm.route_vehicle_assignment_id || null,
      pickup_stop_id: staffForm.pickup_stop_id || null,
      drop_stop_id: staffForm.drop_stop_id || null,
      allocated_to: staffForm.allocated_to || null,
      fare_amount: staffForm.fare_amount || null,
    };
    const action = staffForm.id
      ? updateStaffAllocation({ id: staffForm.id, payload: { ...payload, id: undefined } })
      : createStaffAllocation(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setStaffForm(initialStaffForm);
    }
  }

  const filteredStops = routeStops.filter((item) => item.route_id === Number((tab === 'student' ? studentForm.route_id : staffForm.route_id) || 0));
  const filteredAssignments = routeAssignments.filter((item) => item.route_id === Number((tab === 'student' ? studentForm.route_id : staffForm.route_id) || 0));

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 1.5, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Tabs value={tab} onChange={(_, value) => setTab(value)}>
          <Tab value="student" label="Student Allocations" />
          <Tab value="staff" label="Staff Allocations" />
        </Tabs>
      </Paper>

      {tab === 'student' ? (
        <Grid container spacing={3}>
          <Grid size={{ xs: 12, lg: 4 }}>
            <PermissionGate permission="transport.manage">
              <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
                <Stack component="form" spacing={2} onSubmit={submitStudent}>
                  <Typography variant="h5">Student Allocation</Typography>
                  <TextField select label="Student" value={studentForm.student_id} onChange={(e) => setStudentForm((current) => ({ ...current, student_id: e.target.value }))}>
                    {students.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Academic Year" value={studentForm.academic_year_id} onChange={(e) => setStudentForm((current) => ({ ...current, academic_year_id: e.target.value }))}>
                    {academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Route" value={studentForm.route_id} onChange={(e) => setStudentForm((current) => ({ ...current, route_id: e.target.value, route_vehicle_assignment_id: '', pickup_stop_id: '', drop_stop_id: '' }))}>
                    {routes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Route Assignment" value={studentForm.route_vehicle_assignment_id} onChange={(e) => setStudentForm((current) => ({ ...current, route_vehicle_assignment_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredAssignments.map((item) => <MenuItem key={item.id} value={item.id}>{item.vehicle?.vehicle_no || `Assignment #${item.id}`}</MenuItem>)}
                  </TextField>
                  <TextField select label="Pickup Stop" value={studentForm.pickup_stop_id} onChange={(e) => setStudentForm((current) => ({ ...current, pickup_stop_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredStops.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Drop Stop" value={studentForm.drop_stop_id} onChange={(e) => setStudentForm((current) => ({ ...current, drop_stop_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredStops.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField label="Allocated From" type="date" InputLabelProps={{ shrink: true }} value={studentForm.allocated_from} onChange={(e) => setStudentForm((current) => ({ ...current, allocated_from: e.target.value }))} />
                  <TextField label="Allocated To" type="date" InputLabelProps={{ shrink: true }} value={studentForm.allocated_to} onChange={(e) => setStudentForm((current) => ({ ...current, allocated_to: e.target.value }))} />
                  <TextField label="Fare Amount" value={studentForm.fare_amount} onChange={(e) => setStudentForm((current) => ({ ...current, fare_amount: e.target.value }))} />
                  <TextField select label="Status" value={studentForm.status} onChange={(e) => setStudentForm((current) => ({ ...current, status: e.target.value }))}>
                    <MenuItem value="active">Active</MenuItem>
                    <MenuItem value="inactive">Inactive</MenuItem>
                    <MenuItem value="cancelled">Cancelled</MenuItem>
                    <MenuItem value="completed">Completed</MenuItem>
                  </TextField>
                  <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : studentForm.id ? 'Update Allocation' : 'Create Allocation'}</Button>
                </Stack>
              </Paper>
            </PermissionGate>
          </Grid>
          <Grid size={{ xs: 12, lg: 8 }}>
            <AppDataTable
              title="Student Transport Allocations"
              columns={[
                { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
                { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
                { key: 'pickupStop', header: 'Pickup', render: (row) => row.pickup_stop?.name || row.pickupStop?.name || 'N/A' },
                { key: 'dropStop', header: 'Drop', render: (row) => row.drop_stop?.name || row.dropStop?.name || 'N/A' },
                { key: 'status', header: 'Status' },
                {
                  key: 'actions',
                  header: 'Actions',
                  render: (row) => (
                    <PermissionGate permission="transport.manage" fallback={null}>
                      <Stack direction="row" spacing={1}>
                        <IconButton color="primary" onClick={() => setStudentForm({ ...initialStudentForm, ...row, pickup_stop_id: row.pickup_stop_id || '', drop_stop_id: row.drop_stop_id || '', route_vehicle_assignment_id: row.route_vehicle_assignment_id || '' })}>
                          <EditOutlinedIcon />
                        </IconButton>
                        <IconButton color="error" onClick={() => dispatch(deleteStudentAllocation(row.id))}>
                          <DeleteOutlineOutlinedIcon />
                        </IconButton>
                      </Stack>
                    </PermissionGate>
                  ),
                },
              ]}
              rows={studentAllocations}
              loading={false}
              searchValue=""
              onSearchChange={() => {}}
              pagination={{ page: studentPage, totalPages: studentAllocationsPagination.totalPages, onPageChange: setStudentPage }}
              emptyState="No student allocations found."
            />
          </Grid>
        </Grid>
      ) : (
        <Grid container spacing={3}>
          <Grid size={{ xs: 12, lg: 4 }}>
            <PermissionGate permission="transport.manage">
              <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
                <Stack component="form" spacing={2} onSubmit={submitStaff}>
                  <Typography variant="h5">Staff Allocation</Typography>
                  <TextField select label="Staff" value={staffForm.staff_id} onChange={(e) => setStaffForm((current) => ({ ...current, staff_id: e.target.value }))}>
                    {staffMembers.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name || item.employee_code}</MenuItem>)}
                  </TextField>
                  <TextField select label="Route" value={staffForm.route_id} onChange={(e) => setStaffForm((current) => ({ ...current, route_id: e.target.value, route_vehicle_assignment_id: '', pickup_stop_id: '', drop_stop_id: '' }))}>
                    {routes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Route Assignment" value={staffForm.route_vehicle_assignment_id} onChange={(e) => setStaffForm((current) => ({ ...current, route_vehicle_assignment_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredAssignments.map((item) => <MenuItem key={item.id} value={item.id}>{item.vehicle?.vehicle_no || `Assignment #${item.id}`}</MenuItem>)}
                  </TextField>
                  <TextField select label="Pickup Stop" value={staffForm.pickup_stop_id} onChange={(e) => setStaffForm((current) => ({ ...current, pickup_stop_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredStops.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField select label="Drop Stop" value={staffForm.drop_stop_id} onChange={(e) => setStaffForm((current) => ({ ...current, drop_stop_id: e.target.value }))}>
                    <MenuItem value="">None</MenuItem>
                    {filteredStops.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
                  </TextField>
                  <TextField label="Allocated From" type="date" InputLabelProps={{ shrink: true }} value={staffForm.allocated_from} onChange={(e) => setStaffForm((current) => ({ ...current, allocated_from: e.target.value }))} />
                  <TextField label="Allocated To" type="date" InputLabelProps={{ shrink: true }} value={staffForm.allocated_to} onChange={(e) => setStaffForm((current) => ({ ...current, allocated_to: e.target.value }))} />
                  <TextField label="Fare Amount" value={staffForm.fare_amount} onChange={(e) => setStaffForm((current) => ({ ...current, fare_amount: e.target.value }))} />
                  <TextField select label="Status" value={staffForm.status} onChange={(e) => setStaffForm((current) => ({ ...current, status: e.target.value }))}>
                    <MenuItem value="active">Active</MenuItem>
                    <MenuItem value="inactive">Inactive</MenuItem>
                    <MenuItem value="cancelled">Cancelled</MenuItem>
                    <MenuItem value="completed">Completed</MenuItem>
                  </TextField>
                  <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : staffForm.id ? 'Update Allocation' : 'Create Allocation'}</Button>
                </Stack>
              </Paper>
            </PermissionGate>
          </Grid>
          <Grid size={{ xs: 12, lg: 8 }}>
            <AppDataTable
              title="Staff Transport Allocations"
              columns={[
                { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || 'N/A' },
                { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
                { key: 'pickupStop', header: 'Pickup', render: (row) => row.pickup_stop?.name || row.pickupStop?.name || 'N/A' },
                { key: 'dropStop', header: 'Drop', render: (row) => row.drop_stop?.name || row.dropStop?.name || 'N/A' },
                { key: 'status', header: 'Status' },
                {
                  key: 'actions',
                  header: 'Actions',
                  render: (row) => (
                    <PermissionGate permission="transport.manage" fallback={null}>
                      <Stack direction="row" spacing={1}>
                        <IconButton color="primary" onClick={() => setStaffForm({ ...initialStaffForm, ...row, pickup_stop_id: row.pickup_stop_id || '', drop_stop_id: row.drop_stop_id || '', route_vehicle_assignment_id: row.route_vehicle_assignment_id || '' })}>
                          <EditOutlinedIcon />
                        </IconButton>
                        <IconButton color="error" onClick={() => dispatch(deleteStaffAllocation(row.id))}>
                          <DeleteOutlineOutlinedIcon />
                        </IconButton>
                      </Stack>
                    </PermissionGate>
                  ),
                },
              ]}
              rows={staffAllocations}
              loading={false}
              searchValue=""
              onSearchChange={() => {}}
              pagination={{ page: staffPage, totalPages: staffAllocationsPagination.totalPages, onPageChange: setStaffPage }}
              emptyState="No staff allocations found."
            />
          </Grid>
        </Grid>
      )}
    </Stack>
  );
}
