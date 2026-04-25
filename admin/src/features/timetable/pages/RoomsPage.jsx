import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createTimetableRoom,
  deleteTimetableRoom,
  fetchTimetableRooms,
  updateTimetableRoom,
} from '../store/timetableSlice';
import { TimetablePageShell } from '../components/TimetablePageShell';

const initialForm = {
  id: null,
  name: '',
  code: '',
  room_type: 'classroom',
  capacity: '',
  building: '',
  floor: '',
  status: 'active',
};

export function RoomsPage() {
  const dispatch = useAppDispatch();
  const { rooms, loading, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableRooms());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return rooms.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.room_type} ${item.building || ''}`.toLowerCase().includes(query);
      const matchesType = !typeFilter || item.room_type === typeFilter;

      return matchesSearch && matchesType;
    });
  }, [rooms, search, typeFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      capacity: form.capacity ? Number(form.capacity) : null,
    };

    const action = form.id
      ? updateTimetableRoom({ id: form.id, payload: { ...payload, id: undefined } })
      : createTimetableRoom(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TimetablePageShell
      title="Room Management"
      description="Register classrooms, labs, libraries, and shared spaces before we start scheduling weekly slots."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Room' : 'Create Room'}
      saving={saving}
      error={error}
      loading={loading}
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        {
          key: 'room_type',
          label: 'Room Type',
          type: 'select',
          options: [
            { value: 'classroom', label: 'Classroom' },
            { value: 'lab', label: 'Lab' },
            { value: 'library', label: 'Library' },
            { value: 'auditorium', label: 'Auditorium' },
            { value: 'sports', label: 'Sports' },
            { value: 'other', label: 'Other' },
          ],
        },
        { key: 'capacity', label: 'Capacity', type: 'number' },
        { key: 'building', label: 'Building' },
        { key: 'floor', label: 'Floor' },
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
        { key: 'room_type', header: 'Type' },
        { key: 'capacity', header: 'Capacity' },
        { key: 'location', header: 'Location', render: (row) => [row.building, row.floor].filter(Boolean).join(' / ') || '—' },
        { key: 'status', header: 'Status' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      filters={[
        {
          key: 'room_type',
          label: 'Room Type',
          value: typeFilter,
          onChange: setTypeFilter,
          options: [
            { value: '', label: 'All Types' },
            { value: 'classroom', label: 'Classroom' },
            { value: 'lab', label: 'Lab' },
            { value: 'library', label: 'Library' },
            { value: 'auditorium', label: 'Auditorium' },
            { value: 'sports', label: 'Sports' },
            { value: 'other', label: 'Other' },
          ],
        },
      ]}
      onEdit={(row) => setForm({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        room_type: row.room_type || 'classroom',
        capacity: row.capacity || '',
        building: row.building || '',
        floor: row.floor || '',
        status: row.status || 'active',
      })}
      onDelete={(id) => dispatch(deleteTimetableRoom(id))}
      emptyState="No rooms have been configured for timetable scheduling yet."
    />
  );
}
