import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import FamilyRestroomOutlinedIcon from '@mui/icons-material/FamilyRestroomOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import {
  Divider,
  Grid,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { StudentStatusChip } from './StudentStatusChip';

export function StudentProfileCard({ student }) {
  if (!student) {
    return null;
  }

  const latestAdmission = student.admissions?.[0];
  const latestEnrollment = student.enrollments?.[0];

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={3}>
        <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
          <div>
            <Typography variant="h5">{student.full_name || `${student.first_name} ${student.last_name}`}</Typography>
            <Typography variant="body2" color="text.secondary">
              Admission No: {student.admission_no}{student.roll_no ? ` • Roll No: ${student.roll_no}` : ''}
            </Typography>
          </div>
          <StudentStatusChip status={student.current_status || student.status} />
        </Stack>

        <Grid container spacing={3}>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="subtitle2" color="text.secondary">Contact</Typography>
            <Typography>{student.email || 'No email'}</Typography>
            <Typography>{student.phone || 'No phone'}</Typography>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="subtitle2" color="text.secondary">Enrollment</Typography>
            <Typography>{latestEnrollment?.school_class?.name || 'Not assigned'}</Typography>
            <Typography>{latestEnrollment?.section?.name ? `Section ${latestEnrollment.section.name}` : 'No section'}</Typography>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="subtitle2" color="text.secondary">Admission Process</Typography>
            <Typography>{latestAdmission?.status || 'No admission record'}</Typography>
            <Typography>{latestAdmission?.remarks || 'No remarks'}</Typography>
          </Grid>
        </Grid>

        <Divider />

        <Grid container spacing={3}>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="h6" gutterBottom>Guardians</Typography>
            <List dense disablePadding>
              {(student.guardians || []).map((guardian) => (
                <ListItem key={guardian.id} disableGutters>
                  <ListItemIcon><FamilyRestroomOutlinedIcon /></ListItemIcon>
                  <ListItemText
                    primary={guardian.full_name || `${guardian.first_name} ${guardian.last_name}`}
                    secondary={`${guardian.pivot?.relationship_label || guardian.pivot?.relationship || guardian.relationship_type || 'Guardian'} • ${guardian.phone || 'No phone'}`}
                  />
                </ListItem>
              ))}
            </List>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="h6" gutterBottom>Academic Snapshot</Typography>
            <List dense disablePadding>
              <ListItem disableGutters>
                <ListItemIcon><SchoolOutlinedIcon /></ListItemIcon>
                <ListItemText primary="Gender" secondary={student.gender} />
              </ListItem>
              <ListItem disableGutters>
                <ListItemIcon><SchoolOutlinedIcon /></ListItemIcon>
                <ListItemText primary="Date of Birth" secondary={student.date_of_birth} />
              </ListItem>
              <ListItem disableGutters>
                <ListItemIcon><SchoolOutlinedIcon /></ListItemIcon>
                <ListItemText primary="Blood Group" secondary={student.blood_group || 'Not available'} />
              </ListItem>
              <ListItem disableGutters>
                <ListItemIcon><SchoolOutlinedIcon /></ListItemIcon>
                <ListItemText primary="Religion" secondary={student.religion || 'Not available'} />
              </ListItem>
            </List>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Typography variant="h6" gutterBottom>Documents</Typography>
            <List dense disablePadding>
              {(student.documents || []).map((document) => (
                <ListItem key={document.id} disableGutters>
                  <ListItemIcon><DescriptionOutlinedIcon /></ListItemIcon>
                  <ListItemText primary={document.title} secondary={document.document_type} />
                </ListItem>
              ))}
            </List>
          </Grid>
        </Grid>
      </Stack>
    </Paper>
  );
}
