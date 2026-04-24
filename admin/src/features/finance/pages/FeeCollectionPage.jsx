import {
  Alert,
  Button,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { collectPayment, fetchFinanceMasterData, fetchInvoices } from '../store/financeSlice';

const initialForm = {
  student_id: '',
  fee_invoice_id: '',
  payment_date: '',
  payment_method: 'cash',
  gateway_provider: '',
  reference_no: '',
  amount: '',
  remarks: '',
};

export function FeeCollectionPage() {
  const dispatch = useAppDispatch();
  const { students, invoices, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchInvoices({ per_page: 100 }));
  }, [dispatch]);

  const selectedInvoice = useMemo(
    () => invoices.find((item) => String(item.id) === String(form.fee_invoice_id)),
    [invoices, form.fee_invoice_id],
  );

  useEffect(() => {
    if (selectedInvoice) {
      setForm((current) => ({
        ...current,
        student_id: String(selectedInvoice.student_id),
        amount: String(selectedInvoice.balance_amount || ''),
      }));
    }
  }, [selectedInvoice]);

  async function handleSubmit(event) {
    event.preventDefault();
    setSuccessMessage('');

    const payload = {
      student_id: Number(form.student_id),
      fee_invoice_id: form.fee_invoice_id ? Number(form.fee_invoice_id) : null,
      payment_date: form.payment_date,
      payment_method: form.payment_method,
      gateway_provider: form.gateway_provider || null,
      reference_no: form.reference_no || null,
      amount: Number(form.amount),
      remarks: form.remarks || null,
      allocations: form.fee_invoice_id ? [{
        fee_invoice_id: Number(form.fee_invoice_id),
        allocated_amount: Number(form.amount),
      }] : [],
    };

    const result = await dispatch(collectPayment(payload));
    if (!result.error) {
      setSuccessMessage(`Payment ${result.payload.payment_no} collected successfully.`);
      setForm(initialForm);
      dispatch(fetchInvoices({ per_page: 100 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 5 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">Fee Collection</Typography>
            <Typography variant="body2" color="text.secondary">
              Collect student fee payments quickly against outstanding invoices and create receipts automatically.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}
            {successMessage ? <Alert severity="success">{successMessage}</Alert> : null}

            <TextField
              select
              label="Invoice"
              value={form.fee_invoice_id}
              onChange={(event) => setForm((current) => ({ ...current, fee_invoice_id: event.target.value }))}
            >
              <MenuItem value="">Select</MenuItem>
              {invoices
                .filter((invoice) => Number(invoice.balance_amount) > 0 && invoice.status !== 'cancelled')
                .map((invoice) => (
                  <MenuItem key={invoice.id} value={invoice.id}>
                    {invoice.invoice_no} - {invoice.student?.full_name} - Balance {invoice.balance_amount}
                  </MenuItem>
                ))}
            </TextField>

            <TextField
              select
              label="Student"
              value={form.student_id}
              onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value }))}
            >
              <MenuItem value="">Select</MenuItem>
              {students.map((student) => (
                <MenuItem key={student.id} value={student.id}>
                  {student.full_name} ({student.admission_no})
                </MenuItem>
              ))}
            </TextField>

            <TextField
              label="Payment Date"
              type="date"
              InputLabelProps={{ shrink: true }}
              value={form.payment_date}
              onChange={(event) => setForm((current) => ({ ...current, payment_date: event.target.value }))}
            />
            <TextField
              select
              label="Payment Method"
              value={form.payment_method}
              onChange={(event) => setForm((current) => ({ ...current, payment_method: event.target.value }))}
            >
              <MenuItem value="cash">Cash</MenuItem>
              <MenuItem value="bank_transfer">Bank Transfer</MenuItem>
              <MenuItem value="cheque">Cheque</MenuItem>
              <MenuItem value="upi">UPI</MenuItem>
              <MenuItem value="card">Card</MenuItem>
              <MenuItem value="online_gateway">Online Gateway</MenuItem>
            </TextField>
            <TextField
              label="Gateway Provider"
              value={form.gateway_provider}
              onChange={(event) => setForm((current) => ({ ...current, gateway_provider: event.target.value }))}
            />
            <TextField
              label="Reference No"
              value={form.reference_no}
              onChange={(event) => setForm((current) => ({ ...current, reference_no: event.target.value }))}
            />
            <TextField
              label="Amount"
              type="number"
              value={form.amount}
              onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))}
            />
            <TextField
              label="Remarks"
              multiline
              minRows={3}
              value={form.remarks}
              onChange={(event) => setForm((current) => ({ ...current, remarks: event.target.value }))}
            />

            <Button type="submit" variant="contained" disabled={saving || !form.fee_invoice_id}>
              {saving ? 'Collecting...' : 'Collect Payment'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 7 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack spacing={1}>
            <Typography variant="h5">Collection Notes</Typography>
            <Typography variant="body2" color="text.secondary">
              Offline methods are marked successful immediately and generate receipts automatically.
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Online gateway payments are created in pending status and can be confirmed or failed from Payment History.
            </Typography>
            <Typography variant="body2" color="text.secondary">
              For this fast collection screen, payment is allocated against the selected invoice as a single allocation.
            </Typography>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
