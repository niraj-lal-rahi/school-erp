import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import {
  Alert,
  Button,
  Grid,
  IconButton,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createLedgerEntry,
  deleteLedgerEntry,
  fetchFinanceMasterData,
  fetchLedgerEntries,
  updateLedgerEntry,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  ledger_account_id: '',
  source_type: 'manual',
  source_id: 1,
  entry_date: '',
  debit: '',
  credit: '',
  description: '',
};

export function LedgerEntriesPage() {
  const dispatch = useAppDispatch();
  const { ledgerEntries, ledgerEntriesPagination, ledgerAccounts, loading, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchLedgerEntries({
      page,
      search,
      per_page: 12,
    }));
  }, [dispatch, page, search]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      ledger_account_id: Number(form.ledger_account_id),
      source_type: form.source_type,
      source_id: Number(form.source_id),
      entry_date: form.entry_date,
      debit: form.debit ? Number(form.debit) : 0,
      credit: form.credit ? Number(form.credit) : 0,
      description: form.description || null,
    };

    const action = form.id
      ? updateLedgerEntry({ id: form.id, payload })
      : createLedgerEntry(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchLedgerEntries({ page, search, per_page: 12 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">{form.id ? 'Edit Ledger Entry' : 'Create Ledger Entry'}</Typography>
            <Typography variant="body2" color="text.secondary">
              Record base financial postings with source references so later reports have durable accounting data.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField select label="Ledger Account" value={form.ledger_account_id} onChange={(event) => setForm((current) => ({ ...current, ledger_account_id: event.target.value }))}>
              <MenuItem value="">Select</MenuItem>
              {ledgerAccounts.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField label="Source Type" value={form.source_type} onChange={(event) => setForm((current) => ({ ...current, source_type: event.target.value }))} />
            <TextField label="Source ID" type="number" value={form.source_id} onChange={(event) => setForm((current) => ({ ...current, source_id: event.target.value }))} />
            <TextField label="Entry Date" type="date" InputLabelProps={{ shrink: true }} value={form.entry_date} onChange={(event) => setForm((current) => ({ ...current, entry_date: event.target.value }))} />
            <TextField label="Debit" type="number" value={form.debit} onChange={(event) => setForm((current) => ({ ...current, debit: event.target.value, credit: '' }))} />
            <TextField label="Credit" type="number" value={form.credit} onChange={(event) => setForm((current) => ({ ...current, credit: event.target.value, debit: '' }))} />
            <TextField label="Description" multiline minRows={3} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Ledger Entry' : 'Create Ledger Entry'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Ledger Entries"
          columns={[
            { key: 'ledger_account', header: 'Account', render: (row) => row.ledger_account?.name || 'N/A' },
            { key: 'source_type', header: 'Source Type' },
            { key: 'source_id', header: 'Source ID' },
            { key: 'entry_date', header: 'Entry Date' },
            { key: 'debit', header: 'Debit' },
            { key: 'credit', header: 'Credit' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="error" onClick={() => dispatch(deleteLedgerEntry(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={ledgerEntries}
          loading={loading}
          searchValue={search}
          onSearchChange={(value) => {
            setSearch(value);
            setPage(1);
          }}
          pagination={{
            page,
            totalPages: ledgerEntriesPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No ledger entries recorded yet."
        />
      </Grid>
    </Grid>
  );
}
