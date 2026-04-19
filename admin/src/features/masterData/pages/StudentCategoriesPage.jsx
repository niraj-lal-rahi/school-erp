import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { IconButton, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createStudentCategory, deleteStudentCategory, fetchMasterData, updateStudentCategory } from '../store/masterDataSlice';

const defaultForm = {
  id: null,
  name: '',
  code: '',
  description: '',
  status: 'active',
};

export function StudentCategoriesPage() {
  const dispatch = useAppDispatch();
  const { studentCategories, saving, error } = useAppSelector((state) => state.masterData);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState(defaultForm);

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateStudentCategory({ categoryId: form.id, payload: { ...form, id: undefined } })
      : createStudentCategory(form);

    const result = await dispatch(action);

    if (!result.error) {
      setForm(defaultForm);
    }
  }

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return studentCategories;

    return studentCategories.filter((category) =>
      `${category.name || ''} ${category.code || ''} ${category.description || ''} ${category.status || ''}`
        .toLowerCase()
        .includes(query)
    );
  }, [studentCategories, search]);

  return (
    <Stack spacing={2}>
      <TextField size="small" label="Search Categories" value={search} onChange={(event) => setSearch(event.target.value)} />
      <MasterDataPageShell
        title="Student Categories"
        description="Manage category labels such as General, Scholarship, EWS, or International for student grouping and reporting."
        fields={[
          { key: 'name', label: 'Name' },
          { key: 'code', label: 'Code' },
          { key: 'description', label: 'Description' },
          {
            key: 'status',
            label: 'Status',
            select: true,
            options: [
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
            ],
          },
        ]}
        formState={form}
        onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Category' : 'Add Category'}
        saving={saving}
        error={error}
        columns={[
          { key: 'name', header: 'Category' },
          { key: 'code', header: 'Code' },
          { key: 'status', header: 'Status' },
          { key: 'students_count', header: 'Students' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="primary" onClick={() => setForm({ ...row })}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton color="error" onClick={() => dispatch(deleteStudentCategory(row.id))}>
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              </Stack>
            ),
          },
        ]}
        rows={filteredRows}
      />
    </Stack>
  );
}
