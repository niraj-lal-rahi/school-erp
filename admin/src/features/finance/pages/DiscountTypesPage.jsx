import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createDiscountType, deleteDiscountType, fetchDiscountTypes, updateDiscountType } from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  discount_type: 'fixed',
  value: '',
  max_amount: '',
  description: '',
  status: 'active',
};

export function DiscountTypesPage() {
  const dispatch = useAppDispatch();
  const { discountTypes, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchDiscountTypes());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return discountTypes;

    return discountTypes.filter((item) =>
      `${item.name} ${item.code} ${item.description || ''}`.toLowerCase().includes(query));
  }, [discountTypes, search]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      name: form.name,
      code: form.code,
      discount_type: form.discount_type,
      value: Number(form.value),
      max_amount: form.max_amount ? Number(form.max_amount) : null,
      description: form.description || null,
      status: form.status,
    };

    const action = form.id
      ? updateDiscountType({ id: form.id, payload })
      : createDiscountType(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Discount Types"
      description="Define scholarship and concession templates that can later be assigned to individual students."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Discount Type' : 'Create Discount Type'}
      saving={saving}
      error={error}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        {
          key: 'discount_type',
          label: 'Discount Type',
          type: 'select',
          options: [
            { value: 'fixed', label: 'Fixed' },
            { value: 'percentage', label: 'Percentage' },
          ],
        },
        { key: 'value', label: 'Value', type: 'number' },
        { key: 'max_amount', label: 'Max Amount', type: 'number' },
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
        { key: 'discount_type', header: 'Type' },
        { key: 'value', header: 'Value' },
        { key: 'max_amount', header: 'Max Amount' },
        { key: 'status', header: 'Status' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        discount_type: row.discount_type || 'fixed',
        value: row.value || '',
        max_amount: row.max_amount || '',
        description: row.description || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteDiscountType(id))}
      emptyState="No discount types configured yet."
    />
  );
}
