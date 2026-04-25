import PrintOutlinedIcon from '@mui/icons-material/PrintOutlined';
import { Button } from '@mui/material';

export function TimetablePrintButton({ label = 'Print timetable' }) {
  return (
    <Button
      variant="outlined"
      startIcon={<PrintOutlinedIcon />}
      onClick={() => window.print()}
    >
      {label}
    </Button>
  );
}
