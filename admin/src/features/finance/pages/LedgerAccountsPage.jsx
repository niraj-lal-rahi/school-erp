import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createLedgerAccount, deleteLedgerAccount, fetchLedgerAccounts, updateLedgerAccount } from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  account_type: 'expense',
  parent_id: '',
  status: 'active',
};

export function LedgerAccountsPage() {
  const dispatch = useAppDispatch();
  const { ledgerAccounts, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchLedgerAccounts());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return ledgerAccounts;

    return ledgerAccounts.filter((item) => `${item.name} ${item.code} ${item.account_type}`.toLowerCase().includes(query));
  }, [ledgerAccounts, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      name: form.name,
      code: form.code,
      account_type: form.account_type,
      parent_id: form.parent_id ? Number(form.parent_id) : null,
      status: form.status,
    };

    const action = form.id
      ? updateLedgerAccount({ id: form.id, payload })
      : createLedgerAccount(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Ledger Accounts"
      description="Build the reusable chart of accounts that later finance reports and postings can rely on."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Ledger Account' : 'Create Ledger Account'}
      saving={saving}
      error={error}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        {
          key: 'account_type',
          label: 'Account Type',
          type: 'select',
          options: [
            { value: 'asset', label: 'Asset' },
            { value: 'liability', label: 'Liability' },
            { value: 'income', label: 'Income' },
            { value: 'expense', label: 'Expense' },
            { value: 'equity', label: 'Equity' },
          ],
        },
        {
          key: 'parent_id',
          label: 'Parent Account',
          type: 'select',
          options: [{ value: '', label: 'None' }, ...ledgerAccounts.map((item) => ({ value: item.id, label: item.name }))],
        },
        {
          key: 'status',
          label: 'Status',
          type: 'select',
          options: [
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
          ],
        },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'account_type', header: 'Type' },
        { key: 'parent', header: 'Parent', render: (row) => row.parent?.name || 'None' },
        { key: 'entries_count', header: 'Entries' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        account_type: row.account_type || 'expense',
        parent_id: row.parent_id || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteLedgerAccount(id))}
      emptyState="No ledger accounts created yet."
    />
  );
}
