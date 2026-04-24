import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  id: null,
  bank_name: '',
  account_holder_name: '',
  account_number: '',
  ifsc_code: '',
  branch_name: '',
  account_type: '',
  is_primary: true,
};

export function StaffBankDetailsTab({ items, saving, error, onCreate, onUpdate, onDelete }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? onUpdate(form.id, form)
      : onCreate(form);
    const result = await action;

    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Bank Details"
          columns={[
            { key: 'bank_name', header: 'Bank' },
            { key: 'account_holder_name', header: 'Account Holder' },
            { key: 'account_number', header: 'Account Number' },
            { key: 'ifsc_code', header: 'IFSC' },
            { key: 'account_type', header: 'Account Type' },
            { key: 'is_primary', header: 'Primary', render: (row) => row.is_primary ? 'Yes' : 'No' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm(row)}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => onDelete(row.id)}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={items}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No bank details recorded."
        />
      </Grid>

      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h6">{form.id ? 'Edit Bank Detail' : 'Add Bank Detail'}</Typography>
            {error ? <Alert severity="error">{error}</Alert> : null}
            <TextField label="Bank Name" value={form.bank_name} onChange={(event) => setForm((current) => ({ ...current, bank_name: event.target.value }))} />
            <TextField label="Account Holder Name" value={form.account_holder_name} onChange={(event) => setForm((current) => ({ ...current, account_holder_name: event.target.value }))} />
            <TextField label="Account Number" value={form.account_number} onChange={(event) => setForm((current) => ({ ...current, account_number: event.target.value }))} />
            <TextField label="IFSC Code" value={form.ifsc_code} onChange={(event) => setForm((current) => ({ ...current, ifsc_code: event.target.value }))} />
            <TextField label="Branch Name" value={form.branch_name} onChange={(event) => setForm((current) => ({ ...current, branch_name: event.target.value }))} />
            <TextField label="Account Type" value={form.account_type} onChange={(event) => setForm((current) => ({ ...current, account_type: event.target.value }))} />
            <Stack direction="row" alignItems="center" justifyContent="space-between">
              <Typography variant="body2">Primary Account</Typography>
              <Switch checked={Boolean(form.is_primary)} onChange={(event) => setForm((current) => ({ ...current, is_primary: event.target.checked }))} />
            </Stack>
            <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : form.id ? 'Update' : 'Save'}</Button>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
