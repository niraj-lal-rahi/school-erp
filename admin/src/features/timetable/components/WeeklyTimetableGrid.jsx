import { Chip, Paper, Stack, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Typography } from '@mui/material';

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

function formatDayLabel(day) {
  return day.charAt(0).toUpperCase() + day.slice(1);
}

function renderCell(entry) {
  if (!entry) {
    return <Typography variant="body2" color="text.secondary">-</Typography>;
  }

  const approvedSubstitutions = (entry.substitutions || []).filter((item) => item.status === 'approved' || item.status === 'completed');

  return (
    <Stack spacing={0.5}>
      <Typography variant="body2" fontWeight={700}>
        {entry.subject?.name || entry.entry_type}
      </Typography>
      {entry.staff?.full_name ? (
        <Typography variant="caption" color="text.secondary">
          {entry.staff.full_name}
        </Typography>
      ) : null}
      {entry.room?.name ? (
        <Typography variant="caption" color="text.secondary">
          {entry.room.name}
        </Typography>
      ) : null}
      {approvedSubstitutions.length ? (
        <Stack direction="row" spacing={0.5} flexWrap="wrap">
          {approvedSubstitutions.map((item) => (
            <Chip
              key={item.id}
              size="small"
              color="warning"
              label={`Sub: ${item.substitute_staff?.full_name || 'Assigned'}`}
            />
          ))}
        </Stack>
      ) : null}
    </Stack>
  );
}

export function WeeklyTimetableGrid({ title, subtitle = '', periods, entries }) {
  const periodMap = periods
    .slice()
    .sort((left, right) => (left.sequence || 0) - (right.sequence || 0));

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={0.5} mb={2}>
        <Typography variant="h5">{title}</Typography>
        {subtitle ? (
          <Typography variant="body2" color="text.secondary">
            {subtitle}
          </Typography>
        ) : null}
      </Stack>

      <TableContainer>
        <Table size="small">
          <TableHead>
            <TableRow>
              <TableCell>Period</TableCell>
              {days.map((day) => (
                <TableCell key={day}>{formatDayLabel(day)}</TableCell>
              ))}
            </TableRow>
          </TableHead>
          <TableBody>
            {periodMap.map((period) => (
              <TableRow key={period.id}>
                <TableCell sx={{ minWidth: 170 }}>
                  <Stack spacing={0.25}>
                    <Typography fontWeight={700}>{period.name}</Typography>
                    <Typography variant="caption" color="text.secondary">
                      {period.start_time} - {period.end_time}
                    </Typography>
                  </Stack>
                </TableCell>
                {days.map((day) => {
                  const entry = entries.find((item) => item.day_of_week === day && item.attendance_period_id === period.id);
                  return (
                    <TableCell key={`${period.id}-${day}`} sx={{ verticalAlign: 'top', minWidth: 180 }}>
                      {renderCell(entry)}
                    </TableCell>
                  );
                })}
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
    </Paper>
  );
}
