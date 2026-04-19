import { Chip } from '@mui/material';

const statusMap = {
  applicant: 'info',
  active: 'success',
  inactive: 'warning',
  withdrawn: 'warning',
  transferred: 'info',
  alumni: 'default',
  graduated: 'success',
  suspended: 'error',
};

export function StudentStatusChip({ status }) {
  return <Chip size="small" label={status || 'unknown'} color={statusMap[status] || 'default'} />;
}
