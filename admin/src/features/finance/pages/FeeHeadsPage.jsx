import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createFeeHead,
  deleteFeeHead,
  fetchFeeCategories,
  fetchFeeHeads,
  fetchFinanceMasterData,
  updateFeeHead,
} from '../store/financeSlice';
import { FinancePageShell } from '../components/FinancePageShell';

const initialForm = {
  id: null,
  fee_category_id: '',
  name: '',
  code: '',
  amount_type: 'fixed',
  default_amount: '',
  is_refundable: false,
  is_optional: false,
  status: 'active',
};

export function FeeHeadsPage() {
  const dispatch = useAppDispatch();
  const { feeHeads, feeCategories, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchFeeHeads());
    dispatch(fetchFeeCategories());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return feeHeads.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.fee_category?.name || ''}`.toLowerCase().includes(query);
      const matchesCategory = !categoryFilter || String(item.fee_category_id) === String(categoryFilter);

      return matchesSearch && matchesCategory;
    });
  }, [feeHeads, search, categoryFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      fee_category_id: Number(form.fee_category_id),
      default_amount: form.default_amount === '' ? null : Number(form.default_amount),
    };

    const action = form.id
      ? updateFeeHead({ id: form.id, payload: { ...payload, id: undefined } })
      : createFeeHead(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <FinancePageShell
      title="Fee Heads"
      description="Create the actual charge lines that later feed structures, installments, invoices, and collections."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Fee Head' : 'Create Fee Head'}
      saving={saving}
      error={error}
      fields={[
        {
          key: 'fee_category_id',
          label: 'Fee Category',
          type: 'select',
          options: [{ value: '', label: 'Select' }, ...feeCategories.map((item) => ({ value: item.id, label: item.name }))],
        },
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'amount_type', label: 'Amount Type', type: 'select', options: [{ value: 'fixed', label: 'Fixed' }, { value: 'variable', label: 'Variable' }] },
        { key: 'default_amount', label: 'Default Amount', type: 'number' },
        { key: 'is_refundable', label: 'Refundable', type: 'select-boolean', options: [{ value: true, label: 'Yes' }, { value: false, label: 'No' }] },
        { key: 'is_optional', label: 'Optional', type: 'select-boolean', options: [{ value: true, label: 'Yes' }, { value: false, label: 'No' }] },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'fee_category', header: 'Category', render: (row) => row.fee_category?.name || 'Unassigned' },
        { key: 'amount_type', header: 'Amount Type' },
        { key: 'default_amount', header: 'Default Amount' },
        { key: 'is_optional', header: 'Optional', render: (row) => row.is_optional ? 'Yes' : 'No' },
        { key: 'status', header: 'Status' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      filters={[
        {
          key: 'fee_category_id',
          label: 'Category',
          value: categoryFilter,
          onChange: setCategoryFilter,
          options: [{ value: '', label: 'All' }, ...feeCategories.map((item) => ({ value: item.id, label: item.name }))],
        },
      ]}
      onEdit={(row) => setForm({
        id: row.id,
        fee_category_id: row.fee_category_id || '',
        name: row.name || '',
        code: row.code || '',
        amount_type: row.amount_type || 'fixed',
        default_amount: row.default_amount || '',
        is_refundable: Boolean(row.is_refundable),
        is_optional: Boolean(row.is_optional),
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteFeeHead(id))}
      emptyState="No fee heads created yet."
    />
  );
}
