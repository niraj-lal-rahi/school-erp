import {
  Button,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchFinanceMasterData, fetchFinanceReports } from '../store/financeSlice';

function SummaryCard({ title, value, subtitle }) {
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

export function FinanceReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, students, academicYears, loading } = useAppSelector((state) => state.finance);
  const [filters, setFilters] = useState({
    academic_year_id: '',
    student_id: '',
    date_from: '',
    date_to: '',
  });

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchFinanceReports({
      academic_year_id: filters.academic_year_id || undefined,
      student_id: filters.student_id || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
    }));
  }, [dispatch, filters]);

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h5">Finance Reports Dashboard</Typography>
          <Typography variant="body2" color="text.secondary">
            Review fee collection, dues, student ledger movement, expenses, and overall income versus expense in one place.
          </Typography>

          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField
                select
                fullWidth
                label="Academic Year"
                value={filters.academic_year_id}
                onChange={(event) => setFilters((current) => ({ ...current, academic_year_id: event.target.value }))}
              >
                <MenuItem value="">All</MenuItem>
                {academicYears.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField
                select
                fullWidth
                label="Student"
                value={filters.student_id}
                onChange={(event) => setFilters((current) => ({ ...current, student_id: event.target.value }))}
              >
                <MenuItem value="">All</MenuItem>
                {students.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField
                fullWidth
                label="From Date"
                type="date"
                InputLabelProps={{ shrink: true }}
                value={filters.date_from}
                onChange={(event) => setFilters((current) => ({ ...current, date_from: event.target.value }))}
              />
            </Grid>
            <Grid size={{ xs: 12, md: 3 }}>
              <TextField
                fullWidth
                label="To Date"
                type="date"
                InputLabelProps={{ shrink: true }}
                value={filters.date_to}
                onChange={(event) => setFilters((current) => ({ ...current, date_to: event.target.value }))}
              />
            </Grid>
          </Grid>

          <Stack direction="row" justifyContent="flex-end">
            <Button variant="outlined" onClick={() => setFilters({ academic_year_id: '', student_id: '', date_from: '', date_to: '' })}>
              Reset Filters
            </Button>
          </Stack>
        </Stack>
      </Paper>

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 4 }}>
          <SummaryCard
            title="Total Collected"
            value={reports.feeCollection?.summary?.total_collected ?? '0.00'}
            subtitle={`Payments: ${reports.feeCollection?.summary?.payments_count ?? 0}`}
          />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <SummaryCard
            title="Outstanding Fees"
            value={reports.outstandingFees?.summary?.outstanding_total ?? '0.00'}
            subtitle={`Students with dues: ${reports.outstandingFees?.summary?.students_with_dues ?? 0}`}
          />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <SummaryCard
            title="Net Surplus"
            value={reports.incomeVsExpense?.summary?.net_surplus ?? '0.00'}
            subtitle={`Income ${reports.incomeVsExpense?.summary?.income_total ?? '0.00'} / Expense ${reports.incomeVsExpense?.summary?.expense_total ?? '0.00'}`}
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Collection By Method"
            columns={[
              { key: 'payment_method', header: 'Method' },
              { key: 'total_amount', header: 'Total Amount' },
              { key: 'payments_count', header: 'Payments' },
            ]}
            rows={reports.feeCollection?.by_method || []}
            loading={loading}
            emptyState="No collection data available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Outstanding Students"
            columns={[
              { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
              { key: 'admission', header: 'Admission No', render: (row) => row.student?.admission_no || 'N/A' },
              { key: 'outstanding_total', header: 'Outstanding' },
            ]}
            rows={reports.outstandingFees?.top_outstanding_students || []}
            loading={loading}
            emptyState="No outstanding fee data available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Daily Collection"
            columns={[
              { key: 'payment_date', header: 'Date' },
              { key: 'total_amount', header: 'Total Amount' },
              { key: 'payments_count', header: 'Payments' },
            ]}
            rows={reports.dailyCollection?.rows || []}
            loading={loading}
            emptyState="No daily collection rows available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Expense Summary"
            columns={[
              { key: 'category', header: 'Category', render: (row) => row.category?.name || 'N/A' },
              { key: 'total_amount', header: 'Total Amount' },
              { key: 'expenses_count', header: 'Expenses' },
            ]}
            rows={reports.expenseSummary?.by_category || []}
            loading={loading}
            emptyState="No expense summary data available."
          />
        </Grid>
      </Grid>

      <AppDataTable
        title="Student Ledger"
        columns={[
          { key: 'type', header: 'Type' },
          { key: 'reference_no', header: 'Reference' },
          { key: 'date', header: 'Date' },
          { key: 'debit', header: 'Debit' },
          { key: 'credit', header: 'Credit' },
          { key: 'running_balance', header: 'Running Balance' },
          { key: 'status', header: 'Status' },
        ]}
        rows={reports.studentLedger?.entries || []}
        loading={loading}
        emptyState="No student ledger entries available."
      />
    </Stack>
  );
}
