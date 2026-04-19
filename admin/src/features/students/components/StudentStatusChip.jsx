import { Chip } from '@mui/material';

const statusMap = {
  active: 'success',
  inactive: 'warning',
  alumni: 'default',
};

export function StudentStatusChip({ status }) {
  return <Chip size="small" label={status || 'unknown'} color={statusMap[status] || 'default'} />;
}
