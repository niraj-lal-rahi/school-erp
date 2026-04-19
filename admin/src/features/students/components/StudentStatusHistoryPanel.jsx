import HistoryOutlinedIcon from '@mui/icons-material/HistoryOutlined';
import { Alert, Chip, Divider, Paper, Stack, Typography } from '@mui/material';

export function StudentStatusHistoryPanel({ items = [] }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Status History</Typography>
          <Typography variant="body2" color="text.secondary">
            Review the audited lifecycle trail for this student.
          </Typography>
        </Stack>

        {items.length === 0 ? <Alert severity="info">No lifecycle actions recorded yet.</Alert> : null}

        {items.map((item, index) => (
          <Stack key={item.id} spacing={1.25}>
            {index > 0 ? <Divider /> : null}
            <Stack direction="row" spacing={1.5} alignItems="flex-start">
              <HistoryOutlinedIcon sx={{ mt: 0.2, color: 'text.secondary' }} />
              <Stack spacing={0.75}>
                <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
                  <Chip size="small" label={item.action_type} />
                  <Chip size="small" variant="outlined" label={`${item.previous_status || 'n/a'} -> ${item.new_status}`} />
                  <Chip size="small" variant="outlined" label={item.effective_date} />
                </Stack>
                <Typography variant="body2" color="text.secondary">
                  {item.reason || 'No reason provided'}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  {item.performed_by_name || 'System'}
                </Typography>
              </Stack>
            </Stack>
          </Stack>
        ))}
      </Stack>
    </Paper>
  );
}
