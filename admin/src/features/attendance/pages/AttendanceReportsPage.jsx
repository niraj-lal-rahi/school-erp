import { Grid, LinearProgress, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { fetchAttendanceOptions, fetchAttendanceReports } from '../store/attendanceSlice';

function MetricCard({ title, value, subtitle }) {
  return (
    <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={0.5}>
        <Typography variant="body2" color="text.secondary">{title}</Typography>
        <Typography variant="h5">{value}</Typography>
        {subtitle ? <Typography variant="caption" color="text.secondary">{subtitle}</Typography> : null}
      </Stack>
    </Paper>
  );
}

export function AttendanceReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, options, loading } = useAppSelector((state) => state.attendance);
  const [filters, setFilters] = useState({
    academic_year_id: '',
    class_id: '',
    section_id: '',
    student_id: '',
    staff_id: '',
    date_from: '',
    date_to: '',
  });

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchAttendanceReports({
      ...Object.fromEntries(Object.entries(filters).filter(([, value]) => value)),
    }));
  }, [dispatch, filters]);

  const studentSummary = reports.studentSummary?.summary || {};
  const staffSummary = reports.staffSummary?.summary || {};

  return (
    <AttendancePageShell
      title="Attendance Reports Dashboard"
      description="Track overall student and staff attendance, class-level attendance performance, and defaulting students from one reporting workspace."
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
          <TextField select label="Academic Year" value={filters.academic_year_id} onChange={(event) => setFilters((current) => ({ ...current, academic_year_id: event.target.value }))} sx={{ minWidth: 220 }}>
            <MenuItem value="">All</MenuItem>
            {options.academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
          </TextField>
          <TextField select label="Class" value={filters.class_id} onChange={(event) => setFilters((current) => ({ ...current, class_id: event.target.value }))} sx={{ minWidth: 180 }}>
            <MenuItem value="">All</MenuItem>
            {options.schoolClasses.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
          </TextField>
          <TextField select label="Section" value={filters.section_id} onChange={(event) => setFilters((current) => ({ ...current, section_id: event.target.value }))} sx={{ minWidth: 180 }}>
            <MenuItem value="">All</MenuItem>
            {options.sections.filter((item) => !filters.class_id || String(item.school_class_id) === String(filters.class_id)).map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
          </TextField>
          <TextField label="From Date" type="date" InputLabelProps={{ shrink: true }} value={filters.date_from} onChange={(event) => setFilters((current) => ({ ...current, date_from: event.target.value }))} sx={{ minWidth: 180 }} />
          <TextField label="To Date" type="date" InputLabelProps={{ shrink: true }} value={filters.date_to} onChange={(event) => setFilters((current) => ({ ...current, date_to: event.target.value }))} sx={{ minWidth: 180 }} />
        </Stack>
      </Paper>

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard title="Student Records" value={studentSummary.records_count || 0} subtitle={`Present ${studentSummary.present_count || 0} / Absent ${studentSummary.absent_count || 0}`} />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard title="Staff Records" value={staffSummary.records_count || 0} subtitle={`Present ${staffSummary.present_count || 0} / Leave ${staffSummary.leave_count || 0}`} />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard title="Classes Tracked" value={reports.classAttendance?.rows?.length || 0} subtitle="Active class attendance groups" />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard title="Defaulters" value={reports.defaulters?.rows?.length || 0} subtitle={`Threshold ${reports.defaulters?.threshold || 75}%`} />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Student Summary"
            columns={[
              { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
              { key: 'admission_no', header: 'Admission No', render: (row) => row.student?.admission_no || 'N/A' },
              { key: 'total_records', header: 'Records' },
            ]}
            rows={reports.studentSummary?.by_student || []}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No student summary records available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Staff Summary"
            columns={[
              { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || 'N/A' },
              { key: 'employee_code', header: 'Employee Code', render: (row) => row.staff?.employee_code || 'N/A' },
              { key: 'total_records', header: 'Records' },
            ]}
            rows={reports.staffSummary?.by_staff || []}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No staff summary records available."
          />
        </Grid>
      </Grid>

      <AppDataTable
        title="Class Attendance"
        columns={[
          { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'N/A' },
          { key: 'section', header: 'Section', render: (row) => row.section?.name || 'N/A' },
          { key: 'total_records', header: 'Records' },
          { key: 'present_count', header: 'Present' },
          { key: 'absent_count', header: 'Absent' },
        ]}
        rows={reports.classAttendance?.rows || []}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No class attendance report rows available."
      />

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h6">Attendance Defaulters</Typography>
          {(reports.defaulters?.rows || []).length ? (
            reports.defaulters.rows.map((row) => (
              <Stack key={row.user_id} spacing={0.75}>
                <Stack direction="row" justifyContent="space-between">
                  <Typography variant="body2">Student ID {row.user_id}</Typography>
                  <Typography variant="body2">{Number(row.percentage || 0).toFixed(2)}%</Typography>
                </Stack>
                <LinearProgress variant="determinate" value={Number(row.percentage || 0)} sx={{ height: 10, borderRadius: 999 }} />
                <Typography variant="caption" color="text.secondary">
                  Present {row.present_days} of {row.total_days} tracked days
                </Typography>
              </Stack>
            ))
          ) : (
            <Typography variant="body2" color="text.secondary">
              No students are currently below the configured attendance threshold.
            </Typography>
          )}
        </Stack>
      </Paper>
    </AttendancePageShell>
  );
}
