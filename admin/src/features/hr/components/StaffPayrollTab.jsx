import { Paper, Stack, Typography } from '@mui/material';
import { AppDataTable } from '../../../components/common/AppDataTable';

export function StaffPayrollTab({ salaryStructures, payslips }) {
  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="h6" gutterBottom>Salary Structures</Typography>
        <AppDataTable
          title="Assigned Salary Structures"
          columns={[
            { key: 'effective_from', header: 'Effective From' },
            { key: 'effective_to', header: 'Effective To' },
            { key: 'basic_salary', header: 'Basic Salary' },
            { key: 'gross_salary', header: 'Gross' },
            { key: 'net_salary', header: 'Net' },
            { key: 'status', header: 'Status' },
          ]}
          rows={salaryStructures || []}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No salary structures linked yet."
        />
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="h6" gutterBottom>Payslips</Typography>
        <AppDataTable
          title="Generated Payslips"
          columns={[
            { key: 'gross_salary', header: 'Gross' },
            { key: 'total_deductions', header: 'Deductions' },
            { key: 'net_salary', header: 'Net' },
            { key: 'payment_status', header: 'Payment Status' },
            { key: 'paid_at', header: 'Paid At' },
          ]}
          rows={payslips || []}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No payslips generated for this staff member yet."
        />
      </Paper>
    </Stack>
  );
}
