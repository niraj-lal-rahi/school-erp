import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createExpenseCategory, deleteExpenseCategory, fetchExpenseCategories, updateExpenseCategory } from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  description: '',
  status: 'active',
};

export function ExpenseCategoriesPage() {
  const dispatch = useAppDispatch();
  const { expenseCategories, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchExpenseCategories());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return expenseCategories;

    return expenseCategories.filter((item) => `${item.name} ${item.code} ${item.description || ''}`.toLowerCase().includes(query));
  }, [expenseCategories, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateExpenseCategory({ id: form.id, payload: { ...form, id: undefined } })
      : createExpenseCategory(form);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Expense Categories"
      description="Organize operational spending into clean buckets before approval and ledger posting."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Expense Category' : 'Create Expense Category'}
      saving={saving}
      error={error}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'description', label: 'Description', type: 'textarea' },
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
        { key: 'status', header: 'Status' },
        { key: 'expenses_count', header: 'Expenses' },
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
      onDelete={(id) => dispatch(deleteExpenseCategory(id))}
      emptyState="No expense categories created yet."
    />
  );
}
