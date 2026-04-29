import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Grid, Stack } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsMetricCard } from '../components/ReportsMetricCard';
import { ReportsMiniBarChart } from '../components/ReportsMiniBarChart';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { useReportsAccess } from '../hooks/useReportsAccess';
import { fetchReportWidgets, fetchReportsDashboard, updateDashboardLayout } from '../store/reportsSlice';

export function DashboardPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useReportsAccess();
  const { dashboard, widgets, layout, loading, saving, error } = useAppSelector((state) => state.reports);

  useEffect(() => {
    dispatch(fetchReportsDashboard());
    dispatch(fetchReportWidgets());
  }, [dispatch]);

  const kpis = dashboard?.kpis || {};
  const attendanceSummary = dashboard?.attendance?.student_summary || {};
  const financeMethods = dashboard?.finance?.by_method || [];
  const examTrends = dashboard?.exam_overview?.trends?.rows || [];
  const communicationChannels = dashboard?.communication_overview?.channel_performance?.rows || [];

  return (
    <ReportsPageShell
      title="Reports & Analytics Dashboard"
      description="Track the school’s headline numbers across attendance, collections, examinations, transport, and communication from a single analytics workspace."
      actions={canManage ? (
        <Button
          variant="outlined"
          startIcon={<SaveOutlinedIcon />}
          disabled={saving || !widgets.length}
          onClick={() => dispatch(updateDashboardLayout({
            user_type: 'admin',
            user_id: 0,
            layout: (layout.length ? layout : widgets.map((widget, index) => ({
              widget_id: widget.id,
              x: widget.position?.x ?? (index * 3),
              y: widget.position?.y ?? 0,
              w: widget.position?.w ?? 3,
              h: widget.position?.h ?? 2,
            }))),
          }))}
        >
          Save Layout Snapshot
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportsMetricCard label="Total Students" value={kpis.total_students || 0} helper="Current student base across the tenant." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportsMetricCard label="Present Today" value={kpis.today_present_students || 0} helper="Students marked present today." progress={(Number(kpis.total_students) ? (Number(kpis.today_present_students || 0) / Number(kpis.total_students)) * 100 : 0)} />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportsMetricCard label="Today Collection" value={kpis.today_collection || 0} helper="Collections recorded today." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportsMetricCard label="Notifications Sent" value={kpis.notifications_sent_today || 0} helper="Outbound communication activity today." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <ReportsMiniBarChart
            title="Attendance Mix"
            subtitle="Present, absent, and leave movement in the current window."
            items={[
              { label: 'Present', value: attendanceSummary.present_count || 0 },
              { label: 'Absent', value: attendanceSummary.absent_count || 0 },
              { label: 'Leave', value: attendanceSummary.leave_count || 0 },
              { label: 'Late', value: attendanceSummary.late_count || 0 },
            ]}
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 4 }}>
          <ReportsMiniBarChart
            title="Collection By Method"
            subtitle="Compare the collection contribution of each payment channel."
            items={financeMethods.map((item) => ({
              label: item.payment_method || 'Unknown',
              value: Number(item.total_amount || 0),
            }))}
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 4 }}>
          <ReportsMiniBarChart
            title="Communication Performance"
            subtitle="Read the strength of each outbound channel."
            items={communicationChannels.map((item) => ({
              label: item.channel || 'Unknown',
              value: Number(item.total || 0),
            }))}
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Exam Trend Snapshot"
            columns={[
              { key: 'exam', header: 'Exam', render: (row) => row.exam_name || row.exam || 'N/A' },
              { key: 'average_percentage', header: 'Average %', render: (row) => `${Number(row.average_percentage || 0).toFixed(2)}%` },
              { key: 'pass_rate', header: 'Pass Rate', render: (row) => `${Number(row.pass_rate || 0).toFixed(2)}%` },
            ]}
            rows={examTrends}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No exam trend rows available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Active Widgets"
            columns={[
              { key: 'name', header: 'Widget' },
              { key: 'module', header: 'Module' },
              { key: 'widget_type', header: 'Type' },
              { key: 'status', header: 'Status' },
            ]}
            rows={widgets}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No dashboard widgets available."
          />
        </Grid>
      </Grid>
    </ReportsPageShell>
  );
}
