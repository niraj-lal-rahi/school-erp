import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createFeeCategory, deleteFeeCategory, fetchFeeCategories, updateFeeCategory } from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  description: '',
  status: 'active',
};

export function FeeCategoriesPage() {
  const dispatch = useAppDispatch();
  const { feeCategories, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchFeeCategories());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return feeCategories;

    return feeCategories.filter((item) => `${item.name} ${item.code} ${item.description || ''}`.toLowerCase().includes(query));
  }, [feeCategories, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateFeeCategory({ id: form.id, payload: { ...form, id: undefined } })
      : createFeeCategory(form);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Fee Categories"
      description="Organize tuition, transport, hostel, and other financial buckets before building structures and invoices."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Fee Category' : 'Create Fee Category'}
      saving={saving}
      error={error}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'description', label: 'Description', type: 'textarea' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'status', header: 'Status' },
        { key: 'fee_heads_count', header: 'Fee Heads' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        description: row.description || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteFeeCategory(id))}
      emptyState="No fee categories created yet."
    />
  );
}
