import { useEffect, useState } from 'react';
import { Chip } from '@mui/material';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createAcademicYear, fetchMasterData } from '../store/masterDataSlice';

export function AcademicYearsPage() {
  const dispatch = useAppDispatch();
  const { academicYears, saving, error } = useAppSelector((state) => state.masterData);
  const [form, setForm] = useState({
    name: '',
    start_date: '',
    end_date: '',
    is_current: '0',
  });

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(createAcademicYear({ ...form, is_current: form.is_current === '1' }));

    if (!result.error) {
      setForm({
        name: '',
        start_date: '',
        end_date: '',
        is_current: '0',
      });
    }
  }

  return (
    <MasterDataPageShell
      title="Academic Years"
      description="Manage year boundaries and mark the active session used by admission and enrollment forms."
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'start_date', label: 'Start Date', type: 'date' },
        { key: 'end_date', label: 'End Date', type: 'date' },
        {
          key: 'is_current',
          label: 'Current Year',
          select: true,
          options: [
            { value: '0', label: 'No' },
            { value: '1', label: 'Yes' },
          ],
        },
      ]}
      formState={form}
      onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
      onSubmit={handleSubmit}
      submitLabel="Add Academic Year"
      saving={saving}
      error={error}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'dates', header: 'Dates', render: (row) => `${row.start_date} to ${row.end_date}` },
        { key: 'is_current', header: 'Current', render: (row) => row.is_current ? <Chip label="Current" color="success" size="small" /> : <Chip label="Archived" size="small" /> },
      ]}
      rows={academicYears}
    />
  );
}
