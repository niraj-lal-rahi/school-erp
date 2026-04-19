import FamilyRestroomOutlinedIcon from '@mui/icons-material/FamilyRestroomOutlined';
import PhoneOutlinedIcon from '@mui/icons-material/PhoneOutlined';
import {
  Alert,
  Chip,
  Divider,
  Paper,
  Stack,
  Typography,
} from '@mui/material';

export function StudentGuardiansPanel({ guardians = [] }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Guardian Mapping</Typography>
          <Typography variant="body2" color="text.secondary">
            Review parent and guardian relationships, emergency contact roles, and pickup authorization.
          </Typography>
        </Stack>

        {guardians.length === 0 ? <Alert severity="info">No guardians are mapped to this student yet.</Alert> : null}

        {guardians.map((guardian, index) => (
          <Stack key={guardian.id} spacing={1.5}>
            {index > 0 ? <Divider /> : null}
            <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
              <Stack spacing={0.75}>
                <Stack direction="row" spacing={1} alignItems="center">
                  <FamilyRestroomOutlinedIcon sx={{ color: 'text.secondary' }} />
                  <Typography variant="subtitle1">
                    {guardian.full_name || `${guardian.first_name || ''} ${guardian.last_name || ''}`.trim()}
                  </Typography>
                </Stack>
                <Typography variant="body2" color="text.secondary">
                  {guardian.email || 'No email provided'}
                </Typography>
                <Stack direction="row" spacing={1} alignItems="center">
                  <PhoneOutlinedIcon sx={{ fontSize: 18, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    {guardian.phone || 'No phone provided'}
                  </Typography>
                </Stack>
              </Stack>

              <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
                <Chip size="small" label={guardian.pivot?.relationship_label || guardian.relationship_type || 'Guardian'} />
                {guardian.pivot?.is_primary ? <Chip size="small" color="primary" label="Primary" /> : null}
                {guardian.pivot?.is_emergency_contact ? <Chip size="small" color="warning" label="Emergency" /> : null}
                {guardian.pivot?.pickup_authorized ? <Chip size="small" color="success" label="Pickup Authorized" /> : null}
              </Stack>
            </Stack>
          </Stack>
        ))}
      </Stack>
    </Paper>
  );
}
