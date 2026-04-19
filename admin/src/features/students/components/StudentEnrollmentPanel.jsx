import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import {
  Alert,
  Chip,
  Divider,
  Paper,
  Stack,
  Typography,
} from '@mui/material';

export function StudentEnrollmentPanel({ enrollments = [] }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Enrollment Timeline</Typography>
          <Typography variant="body2" color="text.secondary">
            Review current and historical academic placement for this student.
          </Typography>
        </Stack>

        {enrollments.length === 0 ? <Alert severity="info">No enrollment records found for this student.</Alert> : null}

        {enrollments.map((enrollment, index) => (
          <Stack key={enrollment.id} spacing={1.25}>
            {index > 0 ? <Divider /> : null}
            <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
              <Stack spacing={0.75}>
                <Stack direction="row" spacing={1} alignItems="center">
                  <SchoolOutlinedIcon sx={{ color: 'text.secondary' }} />
                  <Typography variant="subtitle1">
                    {enrollment.school_class?.name || 'Class not set'}
                    {enrollment.section?.name ? ` • Section ${enrollment.section.name}` : ''}
                  </Typography>
                </Stack>
                <Typography variant="body2" color="text.secondary">
                  Academic year: {enrollment.academic_year?.name || enrollment.academic_year_id || 'Not set'}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Roll number: {enrollment.roll_number || 'Not assigned'} • Enrolled on {enrollment.enrollment_date || enrollment.joined_on || 'n/a'}
                </Typography>
              </Stack>

              <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
                <Chip size="small" label={enrollment.status || 'unknown'} />
                {enrollment.is_current ? <Chip size="small" color="success" label="Current" /> : <Chip size="small" variant="outlined" label="History" />}
              </Stack>
            </Stack>

            {enrollment.remarks ? (
              <Typography variant="body2" color="text.secondary">
                {enrollment.remarks}
              </Typography>
            ) : null}
          </Stack>
        ))}
      </Stack>
    </Paper>
  );
}
