import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { IconButton, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createGuardian, deleteGuardian, fetchMasterData, updateGuardian } from '../store/masterDataSlice';

export function GuardiansPage() {
  const dispatch = useAppDispatch();
  const { guardians, saving, error } = useAppSelector((state) => state.masterData);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState({
    id: null,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    relationship_type: '',
    occupation: '',
  });

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const action = form.id
      ? updateGuardian({ guardianId: form.id, payload: { ...form, id: undefined } })
      : createGuardian(form);

    const result = await dispatch(action);

    if (!result.error) {
      setForm({
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        relationship_type: '',
        occupation: '',
      });
    }
  }

  const filteredGuardians = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return guardians;

    return guardians.filter((guardian) =>
      `${guardian.first_name} ${guardian.last_name} ${guardian.email || ''} ${guardian.phone || ''}`
        .toLowerCase()
        .includes(query)
    );
  }, [guardians, search]);

  return (
    <Stack spacing={2}>
      <TextField
        size="small"
        label="Search Guardians"
        value={search}
        onChange={(event) => setSearch(event.target.value)}
      />
      <MasterDataPageShell
        title="Guardian Directory"
        description="Create, search, edit, and maintain guardian records so student-parent mapping can be done from dropdowns instead of manual ids."
        fields={[
          { key: 'first_name', label: 'First Name' },
          { key: 'last_name', label: 'Last Name' },
          { key: 'email', label: 'Email' },
          { key: 'phone', label: 'Phone' },
          { key: 'relationship_type', label: 'Relationship Type' },
          { key: 'occupation', label: 'Occupation' },
        ]}
        formState={form}
        onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Guardian' : 'Add Guardian'}
        saving={saving}
        error={error}
        columns={[
          { key: 'name', header: 'Guardian', render: (row) => `${row.first_name} ${row.last_name}` },
          { key: 'relationship_type', header: 'Relationship' },
          { key: 'email', header: 'Email' },
          { key: 'phone', header: 'Phone' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="primary" onClick={() => setForm({ ...row })}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton color="error" onClick={() => dispatch(deleteGuardian(row.id))}>
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              </Stack>
            ),
          },
        ]}
        rows={filteredGuardians}
      />
    </Stack>
  );
}
