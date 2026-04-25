import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createTimetablePeriod,
  deleteTimetablePeriod,
  fetchTimetablePeriods,
  updateTimetablePeriod,
} from '../store/timetableSlice';
import { TimetablePageShell } from '../components/TimetablePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  start_time: '',
  end_time: '',
  sequence: '',
  is_break: false,
  break_type: '',
  status: 'active',
};

export function PeriodsPage() {
  const dispatch = useAppDispatch();
  const { periods, loading, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchTimetablePeriods());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return periods.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.break_type || ''}`.toLowerCase().includes(query);
      const matchesStatus = !statusFilter || item.status === statusFilter;

      return matchesSearch && matchesStatus;
    });
  }, [periods, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      sequence: Number(form.sequence),
      break_type: form.is_break ? (form.break_type || null) : null,
    };

    const action = form.id
      ? updateTimetablePeriod({ id: form.id, payload: { ...payload, id: undefined } })
      : createTimetablePeriod(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TimetablePageShell
      title="Period Management"
      description="Maintain shared school periods for timetable planning and attendance alignment."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Period' : 'Create Period'}
      saving={saving}
      error={error}
      loading={loading}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'start_time', label: 'Start Time', type: 'time' },
        { key: 'end_time', label: 'End Time', type: 'time' },
        { key: 'sequence', label: 'Sequence', type: 'number' },
        { key: 'is_break', label: 'This is a break slot', type: 'checkbox' },
        {
          key: 'break_type',
          label: 'Break Type',
          type: 'select',
          options: [
            { value: '', label: 'None' },
            { value: 'short_break', label: 'Short Break' },
            { value: 'lunch', label: 'Lunch' },
            { value: 'assembly', label: 'Assembly' },
            { value: 'activity', label: 'Activity' },
          ],
          helperText: form.is_break ? 'Pick the break classification for printable timetables.' : 'Optional unless this slot is a break.',
        },
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
        { key: 'time', header: 'Time', render: (row) => `${row.start_time} - ${row.end_time}` },
        { key: 'sequence', header: 'Sequence' },
        { key: 'slot_type', header: 'Type', render: (row) => row.is_break ? (row.break_type || 'Break') : 'Teaching' },
        { key: 'status', header: 'Status' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      filters={[
        {
          key: 'status',
          label: 'Status',
          value: statusFilter,
          onChange: setStatusFilter,
          options: [
            { value: '', label: 'All Statuses' },
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
          ],
        },
      ]}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        start_time: row.start_time || '',
        end_time: row.end_time || '',
        sequence: row.sequence || '',
        is_break: Boolean(row.is_break),
        break_type: row.break_type || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteTimetablePeriod(id))}
      emptyState="No timetable periods have been created yet."
    />
  );
}
