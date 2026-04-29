import { Chip } from '@mui/material';

const statusMap = {
  draft: { label: 'Draft', color: 'default' },
  processing: { label: 'Processing', color: 'warning' },
  published: { label: 'Published', color: 'success' },
  archived: { label: 'Archived', color: 'default' },
  pass: { label: 'Pass', color: 'success' },
  fail: { label: 'Fail', color: 'error' },
  absent: { label: 'Absent', color: 'warning' },
  withheld: { label: 'Withheld', color: 'default' },
};

export function ResultStatusChip({ status }) {
  const meta = statusMap[status] || { label: status || 'Unknown', color: 'default' };

  return <Chip size="small" label={meta.label} color={meta.color} variant={meta.color === 'default' ? 'outlined' : 'filled'} />;
}
