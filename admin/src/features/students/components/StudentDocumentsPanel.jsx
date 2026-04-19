import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import {
  Alert,
  Chip,
  Divider,
  IconButton,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { PermissionGate } from '../../../components/common/PermissionGate';

export function StudentDocumentsPanel({ documents = [], onDelete, saving, error }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Student Documents</Typography>
          <Typography variant="body2" color="text.secondary">
            Review uploaded certificates, proofs, and supporting records linked to this student.
          </Typography>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        {documents.length === 0 ? (
          <Alert severity="info">No documents have been uploaded for this student yet.</Alert>
        ) : null}

        {documents.map((document, index) => (
          <Stack key={document.id} spacing={1.5}>
            {index > 0 ? <Divider /> : null}
            <Stack direction="row" justifyContent="space-between" spacing={2} alignItems="flex-start">
              <Stack direction="row" spacing={1.5} alignItems="flex-start">
                <DescriptionOutlinedIcon sx={{ mt: 0.3, color: 'text.secondary' }} />
                <Stack spacing={0.75}>
                  <Typography variant="subtitle1">{document.title}</Typography>
                  <Typography variant="body2" color="text.secondary">
                    {document.file_name || document.file_path}
                  </Typography>
                  <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
                    <Chip size="small" label={document.document_type} />
                    {document.verification_status ? <Chip size="small" color="success" label={document.verification_status} /> : null}
                    {document.issued_date ? <Chip size="small" variant="outlined" label={`Issued ${document.issued_date}`} /> : null}
                  </Stack>
                  {document.remarks ? (
                    <Typography variant="body2" color="text.secondary">
                      {document.remarks}
                    </Typography>
                  ) : null}
                </Stack>
              </Stack>

              <PermissionGate permission="students.documents.upload" fallback={null}>
                <IconButton
                  color="error"
                  onClick={() => onDelete(document.id)}
                  disabled={saving}
                  aria-label={`Delete ${document.title}`}
                >
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              </PermissionGate>
            </Stack>
          </Stack>
        ))}
      </Stack>
    </Paper>
  );
}
