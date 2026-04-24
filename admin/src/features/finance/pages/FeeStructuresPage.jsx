import AddCircleOutlineOutlinedIcon from '@mui/icons-material/AddCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
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
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createFeeStructure,
  deleteFeeStructure,
  fetchFinanceMasterData,
  fetchFeeStructures,
  updateFeeStructure,
} from '../store/financeSlice';

const blankItem = {
  fee_head_id: '',
  amount: '',
  due_frequency: 'monthly',
  due_day: '',
  sort_order: '',
};

const initialForm = {
  id: null,
  academic_year_id: '',
  school_class_id: '',
  section_id: '',
  name: '',
  code: '',
  description: '',
  effective_from: '',
  effective_to: '',
  status: 'active',
  items: [{ ...blankItem }],
};

export function FeeStructuresPage() {
  const dispatch = useAppDispatch();
  const {
    feeStructures,
    feeHeads,
    academicYears,
    schoolClasses,
    sections,
    loading,
    saving,
    error,
  } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [academicYearFilter, setAcademicYearFilter] = useState('');
  const [classFilter, setClassFilter] = useState('');

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchFeeStructures());
  }, [dispatch]);

  const sectionOptions = useMemo(
    () => sections.filter((section) => !form.school_class_id || String(section.school_class_id) === String(form.school_class_id)),
    [sections, form.school_class_id],
  );

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return feeStructures.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.description || ''}`.toLowerCase().includes(query);
      const matchesAcademicYear = !academicYearFilter || String(item.academic_year_id) === String(academicYearFilter);
      const matchesClass = !classFilter || String(item.school_class_id || '') === String(classFilter);

      return matchesSearch && matchesAcademicYear && matchesClass;
    });
  }, [feeStructures, search, academicYearFilter, classFilter]);

  function updateItem(index, key, value) {
    setForm((current) => ({
      ...current,
      items: current.items.map((item, itemIndex) => (itemIndex === index ? { ...item, [key]: value } : item)),
    }));
  }

  function addItem() {
    setForm((current) => ({
      ...current,
      items: [...current.items, { ...blankItem, sort_order: current.items.length + 1 }],
    }));
  }

  function removeItem(index) {
    setForm((current) => ({
      ...current,
      items: current.items.filter((_, itemIndex) => itemIndex !== index),
    }));
  }

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      ...form,
      academic_year_id: Number(form.academic_year_id),
      school_class_id: form.school_class_id ? Number(form.school_class_id) : null,
      section_id: form.section_id ? Number(form.section_id) : null,
      effective_to: form.effective_to || null,
      items: form.items.map((item, index) => ({
        fee_head_id: Number(item.fee_head_id),
        amount: Number(item.amount),
        due_frequency: item.due_frequency,
        due_day: item.due_day === '' ? null : Number(item.due_day),
        sort_order: item.sort_order === '' ? index + 1 : Number(item.sort_order),
      })),
    };

    const action = form.id
      ? updateFeeStructure({ id: form.id, payload: { ...payload, id: undefined } })
      : createFeeStructure(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchFeeStructures());
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 5 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Fee Structures</Typography>
              <Typography variant="body2" color="text.secondary">
                Build reusable fee blueprints per year, class, and section before assigning them to students.
              </Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Academic Year"
              value={form.academic_year_id}
              onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}
            >
              <MenuItem value="">Select</MenuItem>
              {academicYears.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>

            <TextField
              select
              label="Class"
              value={form.school_class_id}
              onChange={(event) => setForm((current) => ({ ...current, school_class_id: event.target.value, section_id: '' }))}
            >
              <MenuItem value="">All Classes</MenuItem>
              {schoolClasses.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>

            <TextField
              select
              label="Section"
              value={form.section_id}
              onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}
            >
              <MenuItem value="">All Sections</MenuItem>
              {sectionOptions.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>

            <TextField label="Structure Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
            <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
            <TextField label="Description" multiline minRows={3} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
            <TextField label="Effective From" type="date" InputLabelProps={{ shrink: true }} value={form.effective_from} onChange={(event) => setForm((current) => ({ ...current, effective_from: event.target.value }))} />
            <TextField label="Effective To" type="date" InputLabelProps={{ shrink: true }} value={form.effective_to} onChange={(event) => setForm((current) => ({ ...current, effective_to: event.target.value }))} />
            <TextField select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
              <MenuItem value="active">Active</MenuItem>
              <MenuItem value="inactive">Inactive</MenuItem>
            </TextField>

            <Stack spacing={1.5}>
              <Stack direction="row" justifyContent="space-between" alignItems="center">
                <Typography variant="subtitle1">Structure Items</Typography>
                <Button startIcon={<AddCircleOutlineOutlinedIcon />} onClick={addItem}>
                  Add Item
                </Button>
              </Stack>

              {form.items.map((item, index) => (
                <Paper key={`fee-structure-item-${index}`} variant="outlined" sx={{ p: 2 }}>
                  <Stack spacing={1.5}>
                    <TextField
                      select
                      label="Fee Head"
                      value={item.fee_head_id}
                      onChange={(event) => updateItem(index, 'fee_head_id', event.target.value)}
                    >
                      <MenuItem value="">Select</MenuItem>
                      {feeHeads.map((feeHead) => (
                        <MenuItem key={feeHead.id} value={feeHead.id}>
                          {feeHead.name}
                        </MenuItem>
                      ))}
                    </TextField>

                    <Grid container spacing={2}>
                      <Grid size={{ xs: 12, md: 6 }}>
                        <TextField
                          fullWidth
                          label="Amount"
                          type="number"
                          value={item.amount}
                          onChange={(event) => updateItem(index, 'amount', event.target.value)}
                        />
                      </Grid>
                      <Grid size={{ xs: 12, md: 6 }}>
                        <TextField
                          select
                          fullWidth
                          label="Due Frequency"
                          value={item.due_frequency}
                          onChange={(event) => updateItem(index, 'due_frequency', event.target.value)}
                        >
                          <MenuItem value="one_time">One Time</MenuItem>
                          <MenuItem value="monthly">Monthly</MenuItem>
                          <MenuItem value="quarterly">Quarterly</MenuItem>
                          <MenuItem value="half_yearly">Half Yearly</MenuItem>
                          <MenuItem value="yearly">Yearly</MenuItem>
                          <MenuItem value="custom">Custom</MenuItem>
                        </TextField>
                      </Grid>
                      <Grid size={{ xs: 12, md: 6 }}>
                        <TextField
                          fullWidth
                          label="Due Day"
                          type="number"
                          value={item.due_day}
                          onChange={(event) => updateItem(index, 'due_day', event.target.value)}
                        />
                      </Grid>
                      <Grid size={{ xs: 12, md: 6 }}>
                        <TextField
                          fullWidth
                          label="Sort Order"
                          type="number"
                          value={item.sort_order}
                          onChange={(event) => updateItem(index, 'sort_order', event.target.value)}
                        />
                      </Grid>
                    </Grid>

                    <Stack direction="row" justifyContent="flex-end">
                      <Button
                        color="error"
                        disabled={form.items.length === 1}
                        onClick={() => removeItem(index)}
                      >
                        Remove Item
                      </Button>
                    </Stack>
                  </Stack>
                </Paper>
              ))}
            </Stack>

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Fee Structure' : 'Create Fee Structure'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 7 }}>
        <AppDataTable
          title="Fee Structures List"
          columns={[
            { key: 'name', header: 'Name' },
            { key: 'code', header: 'Code' },
            { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || 'N/A' },
            { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'All Classes' },
            { key: 'items_count', header: 'Items', render: (row) => row.items?.length || 0 },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    academic_year_id: row.academic_year_id || '',
                    school_class_id: row.school_class_id || '',
                    section_id: row.section_id || '',
                    name: row.name || '',
                    code: row.code || '',
                    description: row.description || '',
                    effective_from: row.effective_from || '',
                    effective_to: row.effective_to || '',
                    status: row.status || 'active',
                    items: (row.items || []).length
                      ? row.items.map((item) => ({
                        fee_head_id: item.fee_head_id || '',
                        amount: item.amount || '',
                        due_frequency: item.due_frequency || 'monthly',
                        due_day: item.due_day ?? '',
                        sort_order: item.sort_order ?? '',
                      }))
                      : [{ ...blankItem }],
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteFeeStructure(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={filteredRows}
          loading={loading}
          searchValue={search}
          onSearchChange={setSearch}
          filters={[
            {
              key: 'academic_year_id',
              label: 'Academic Year',
              value: academicYearFilter,
              onChange: setAcademicYearFilter,
              options: [{ value: '', label: 'All' }, ...academicYears.map((item) => ({ value: item.id, label: item.name }))],
            },
            {
              key: 'school_class_id',
              label: 'Class',
              value: classFilter,
              onChange: setClassFilter,
              options: [{ value: '', label: 'All' }, ...schoolClasses.map((item) => ({ value: item.id, label: item.name }))],
            },
          ]}
          emptyState="No fee structures created yet."
        />
      </Grid>
    </Grid>
  );
}
