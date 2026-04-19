import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createGuardian, fetchMasterData } from '../store/masterDataSlice';

export function GuardiansPage() {
  const dispatch = useAppDispatch();
  const { guardians, saving, error } = useAppSelector((state) => state.masterData);
  const [form, setForm] = useState({
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
    const result = await dispatch(createGuardian(form));

    if (!result.error) {
      setForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        relationship_type: '',
        occupation: '',
      });
    }
  }

  return (
    <MasterDataPageShell
      title="Guardian Directory"
      description="Create and maintain guardian records so student-parent mapping can be done from dropdowns instead of manual ids."
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
      submitLabel="Add Guardian"
      saving={saving}
      error={error}
      columns={[
        { key: 'name', header: 'Guardian', render: (row) => `${row.first_name} ${row.last_name}` },
        { key: 'relationship_type', header: 'Relationship' },
        { key: 'email', header: 'Email' },
        { key: 'phone', header: 'Phone' },
      ]}
      rows={guardians}
    />
  );
}
