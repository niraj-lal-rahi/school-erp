import { Chip } from '@mui/material';

const statusMap = {
  active: 'success',
  approved: 'success',
  completed: 'success',
  successful: 'success',
  sent: 'success',
  pending: 'warning',
  in_progress: 'info',
  processing: 'info',
  draft: 'default',
  inactive: 'default',
  rejected: 'error',
  failed: 'error',
  cancelled: 'default',
  skipped: 'default',
};

function labelize(value) {
  return String(value || 'unknown')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (match) => match.toUpperCase());
}

export function WorkflowStatusChip({ value }) {
  return <Chip size="small" color={statusMap[value] || 'default'} label={labelize(value)} />;
}
