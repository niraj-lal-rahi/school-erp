import { Chip } from '@mui/material';

const fallbackColors = {
  PRESENT: '#0b6e4f',
  ABSENT: '#c62828',
  LATE: '#ef6c00',
  LEAVE: '#1565c0',
  HOLIDAY: '#6a1b9a',
  HALF_DAY: '#6d4c41',
  ON_DUTY: '#00838f',
};

export function AttendanceStatusChip({ status }) {
  if (!status) {
    return <Chip size="small" label="N/A" variant="outlined" />;
  }

  const code = status.code || status;
  const label = status.name || String(code).replace(/_/g, ' ');
  const background = status.color_code || fallbackColors[String(code).toUpperCase()] || '#455a64';

  return (
    <Chip
      size="small"
      label={label}
      sx={{
        backgroundColor: `${background}18`,
        color: background,
        border: `1px solid ${background}40`,
        fontWeight: 600,
      }}
    />
  );
}
