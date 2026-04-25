import RefreshOutlinedIcon from '@mui/icons-material/RefreshOutlined';
import { Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { fetchAttendanceOptions, fetchAttendanceSummary, refreshAttendanceSummary } from '../store/attendanceSlice';

export function AttendanceSummaryPage() {
  const dispatch = useAppDispatch();
  const { summary, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [filters, setFilters] = useState({
    user_type: 'student',
    academic_year_id: '',
  });

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchAttendanceSummary({
      user_type: filters.user_type || undefined,
      academic_year_id: filters.academic_year_id || undefined,
    }));
  }, [dispatch, filters]);

  const rows = useMemo(() => summary.filter((item) => !filters.user_type || item.user_type === filters.user_type), [filters.user_type, summary]);

  return (
    <AttendancePageShell
      title="Attendance Summary"
      description="Review attendance totals and percentages for both students and staff, and refresh the computed summary cache when new records land."
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="space-between">
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField select label="User Type" value={filters.user_type} onChange={(event) => setFilters((current) => ({ ...current, user_type: event.target.value }))} sx={{ minWidth: 180 }}>
              <MenuItem value="student">student</MenuItem>
              <MenuItem value="staff">staff</MenuItem>
            </TextField>
            <TextField select label="Academic Year" value={filters.academic_year_id} onChange={(event) => setFilters((current) => ({ ...current, academic_year_id: event.target.value }))} sx={{ minWidth: 220 }}>
              <MenuItem value="">All</MenuItem>
              {options.academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
            </TextField>
          </Stack>

          <Button variant="contained" startIcon={<RefreshOutlinedIcon />} onClick={() => dispatch(refreshAttendanceSummary(filters))} disabled={saving}>
            Refresh Summary
          </Button>
        </Stack>
      </Paper>

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="body2" color="text.secondary">Summary Rows</Typography>
            <Typography variant="h5">{rows.length}</Typography>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="body2" color="text.secondary">Average Percentage</Typography>
            <Typography variant="h5">
              {rows.length ? (rows.reduce((carry, item) => carry + Number(item.percentage || 0), 0) / rows.length).toFixed(2) : '0.00'}%
            </Typography>
          </Paper>
        </Grid>
      </Grid>

      <AppDataTable
        title="Attendance Summary Table"
        columns={[
          { key: 'user_type', header: 'User Type' },
          { key: 'user_id', header: 'User ID' },
          { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || 'N/A' },
          { key: 'total_days', header: 'Total Days' },
          { key: 'present_days', header: 'Present' },
          { key: 'absent_days', header: 'Absent' },
          { key: 'leave_days', header: 'Leave' },
          { key: 'late_days', header: 'Late' },
          { key: 'percentage', header: 'Percentage', render: (row) => `${Number(row.percentage || 0).toFixed(2)}%` },
        ]}
        rows={rows}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState={error || 'No attendance summary rows available.'}
      />
    </AttendancePageShell>
  );
}
