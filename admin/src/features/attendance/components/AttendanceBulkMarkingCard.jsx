import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import SendOutlinedIcon from '@mui/icons-material/SendOutlined';
import LockOutlinedIcon from '@mui/icons-material/LockOutlined';
import {
  Alert,
  Button,
  MenuItem,
  Paper,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Typography,
} from '@mui/material';
import { AttendanceStatusChip } from './AttendanceStatusChip';

export function AttendanceBulkMarkingCard({
  session,
  students,
  records,
  statusTypes,
  saving,
  error,
  onRecordChange,
  onSave,
  onSubmitSession,
  onLockSession,
}) {
  if (!session) {
    return (
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="body2" color="text.secondary">
          Select an attendance session to start marking records.
        </Typography>
      </Paper>
    );
  }

  const isLocked = session.status === 'locked';

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
          <Stack spacing={0.5}>
            <Typography variant="h6">Bulk Attendance Grid</Typography>
            <Typography variant="body2" color="text.secondary">
              {session.school_class?.name || 'Class'} {session.section?.name ? `• ${session.section.name}` : ''} on {session.attendance_date}
            </Typography>
            <Stack direction="row" spacing={1}>
              <AttendanceStatusChip status={{ code: session.status?.toUpperCase(), name: session.status }} />
              {session.period ? <AttendanceStatusChip status={{ code: session.period.code, name: session.period.name, color_code: '#6d4c41' }} /> : null}
            </Stack>
          </Stack>

          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1}>
            <Button
              variant="outlined"
              startIcon={<SaveOutlinedIcon />}
              onClick={onSave}
              disabled={saving || isLocked}
            >
              Save Records
            </Button>
            <Button
              variant="outlined"
              startIcon={<SendOutlinedIcon />}
              onClick={onSubmitSession}
              disabled={saving || isLocked}
            >
              Submit Session
            </Button>
            <Button
              variant="contained"
              startIcon={<LockOutlinedIcon />}
              onClick={onLockSession}
              disabled={saving || isLocked}
            >
              Lock Session
            </Button>
          </Stack>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}
        {isLocked ? <Alert severity="info">This session is locked. Records are now read-only.</Alert> : null}

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Student</TableCell>
                <TableCell>Admission No</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Remarks</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {students.map((student) => {
                const record = records[student.id] || {};

                return (
                  <TableRow key={student.id} hover>
                    <TableCell>{student.full_name}</TableCell>
                    <TableCell>{student.admission_no}</TableCell>
                    <TableCell sx={{ minWidth: 220 }}>
                      <TextField
                        select
                        fullWidth
                        size="small"
                        value={record.attendance_status_type_id || ''}
                        onChange={(event) => onRecordChange(student.id, 'attendance_status_type_id', event.target.value)}
                        disabled={isLocked}
                      >
                        <MenuItem value="">Select status</MenuItem>
                        {statusTypes.map((status) => (
                          <MenuItem key={status.id} value={status.id}>
                            {status.name}
                          </MenuItem>
                        ))}
                      </TextField>
                    </TableCell>
                    <TableCell sx={{ minWidth: 280 }}>
                      <TextField
                        fullWidth
                        size="small"
                        value={record.remarks || ''}
                        onChange={(event) => onRecordChange(student.id, 'remarks', event.target.value)}
                        disabled={isLocked}
                      />
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </TableContainer>
      </Stack>
    </Paper>
  );
}
