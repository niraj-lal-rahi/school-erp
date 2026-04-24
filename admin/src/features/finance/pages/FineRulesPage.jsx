import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createFineRule, deleteFineRule, fetchFinanceMasterData, fetchFineRules, updateFineRule } from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  fee_head_id: '',
  fine_type: 'fixed',
  amount: '',
  grace_days: 0,
  max_fine_amount: '',
  status: 'active',
};

export function FineRulesPage() {
  const dispatch = useAppDispatch();
  const { fineRules, feeHeads, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchFineRules());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return fineRules;

    return fineRules.filter((item) =>
      `${item.name} ${item.code} ${item.fee_head?.name || ''}`.toLowerCase().includes(query));
  }, [fineRules, search]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      name: form.name,
      code: form.code,
      fee_head_id: form.fee_head_id ? Number(form.fee_head_id) : null,
      fine_type: form.fine_type,
      amount: Number(form.amount),
      grace_days: Number(form.grace_days || 0),
      max_fine_amount: form.max_fine_amount ? Number(form.max_fine_amount) : null,
      status: form.status,
    };

    const action = form.id
      ? updateFineRule({ id: form.id, payload })
      : createFineRule(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Fine Rules"
      description="Configure reusable late fee and overdue fine rules that can be applied to invoices."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Fine Rule' : 'Create Fine Rule'}
      saving={saving}
      error={error}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        {
          key: 'fee_head_id',
          label: 'Fee Head',
          type: 'select',
          options: [{ value: '', label: 'All Heads' }, ...feeHeads.map((item) => ({ value: item.id, label: item.name }))],
        },
        {
          key: 'fine_type',
          label: 'Fine Type',
          type: 'select',
          options: [
            { value: 'fixed', label: 'Fixed' },
            { value: 'daily', label: 'Daily' },
            { value: 'percentage', label: 'Percentage' },
          ],
        },
        { key: 'amount', label: 'Amount', type: 'number' },
        { key: 'grace_days', label: 'Grace Days', type: 'number' },
        { key: 'max_fine_amount', label: 'Max Fine Amount', type: 'number' },
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
        { key: 'fee_head', header: 'Fee Head', render: (row) => row.fee_head?.name || 'All Heads' },
        { key: 'fine_type', header: 'Type' },
        { key: 'amount', header: 'Amount' },
        { key: 'grace_days', header: 'Grace Days' },
        { key: 'status', header: 'Status' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        fee_head_id: row.fee_head_id || '',
        fine_type: row.fine_type || 'fixed',
        amount: row.amount || '',
        grace_days: row.grace_days || 0,
        max_fine_amount: row.max_fine_amount || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteFineRule(id))}
      emptyState="No fine rules configured yet."
    />
  );
}
