import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Box, IconButton, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createStudentHouse, deleteStudentHouse, fetchMasterData, updateStudentHouse } from '../store/masterDataSlice';

const defaultForm = {
  id: null,
  name: '',
  code: '',
  color: '',
  description: '',
  status: 'active',
};

export function StudentHousesPage() {
  const dispatch = useAppDispatch();
  const { studentHouses, saving, error } = useAppSelector((state) => state.masterData);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState(defaultForm);

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateStudentHouse({ houseId: form.id, payload: { ...form, id: undefined } })
      : createStudentHouse(form);

    const result = await dispatch(action);

    if (!result.error) {
      setForm(defaultForm);
    }
  }

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return studentHouses;

    return studentHouses.filter((house) =>
      `${house.name || ''} ${house.code || ''} ${house.description || ''} ${house.color || ''} ${house.status || ''}`
        .toLowerCase()
        .includes(query)
    );
  }, [studentHouses, search]);

  return (
    <Stack spacing={2}>
      <TextField size="small" label="Search Houses" value={search} onChange={(event) => setSearch(event.target.value)} />
      <MasterDataPageShell
        title="Student Houses"
        description="Maintain the house system used for inter-house competitions, pastoral grouping, and activity reporting."
        fields={[
          { key: 'name', label: 'Name' },
          { key: 'code', label: 'Code' },
          { key: 'color', label: 'Color' },
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
        submitLabel={form.id ? 'Update House' : 'Add House'}
        saving={saving}
        error={error}
        columns={[
          { key: 'name', header: 'House' },
          { key: 'code', header: 'Code' },
          {
            key: 'color',
            header: 'Color',
            render: (row) => (
              <Stack direction="row" spacing={1} alignItems="center">
                <Box sx={{ width: 14, height: 14, borderRadius: '50%', bgcolor: row.color || '#cbd5e1', border: '1px solid rgba(15,23,42,0.12)' }} />
                <span>{row.color || 'Not set'}</span>
              </Stack>
            ),
          },
          { key: 'students_count', header: 'Students' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="primary" onClick={() => setForm({ ...row })}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton color="error" onClick={() => dispatch(deleteStudentHouse(row.id))}>
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
