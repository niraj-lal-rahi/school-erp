import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createTimetableException,
  deleteTimetableException,
  fetchTimetableExceptions,
  fetchTimetableOptions,
  updateTimetableException,
} from '../store/timetableSlice';
import { TimetablePageShell } from '../components/TimetablePageShell';

const initialForm = {
  id: null,
  academic_year_id: '',
  school_class_id: '',
  section_id: '',
  exception_date: '',
  title: '',
  description: '',
  exception_type: 'event',
  affects_attendance: false,
};

export function ScheduleExceptionsPage() {
  const dispatch = useAppDispatch();
  const { exceptions, options, loading, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableExceptions());
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  const sectionOptions = useMemo(
    () => (options.sections || [])
      .filter((section) => !form.school_class_id || `${section.school_class_id}` === `${form.school_class_id}`)
      .map((section) => ({ value: section.id, label: section.name })),
    [options.sections, form.school_class_id],
  );

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return exceptions.filter((item) => {
      const matchesSearch = !query || `${item.title} ${item.description || ''} ${item.school_class?.name || ''} ${item.section?.name || ''}`.toLowerCase().includes(query);
      const matchesType = !typeFilter || item.exception_type === typeFilter;

      return matchesSearch && matchesType;
    });
  }, [exceptions, search, typeFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      academic_year_id: Number(form.academic_year_id),
      school_class_id: form.school_class_id ? Number(form.school_class_id) : null,
      section_id: form.section_id ? Number(form.section_id) : null,
      exception_date: form.exception_date,
      title: form.title,
      description: form.description || null,
      exception_type: form.exception_type,
      affects_attendance: Boolean(form.affects_attendance),
    };

    const action = form.id
      ? updateTimetableException({ id: form.id, payload })
      : createTimetableException(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <TimetablePageShell
      title="Schedule Exceptions"
      description="Capture holidays, exams, cancelled classes, and one-off special schedules that affect the weekly plan."
      form={form}
      setForm={setForm}
      onSubmit={handleSubmit}
      submitLabel={form.id ? 'Update Exception' : 'Create Exception'}
      saving={saving}
      error={error}
      loading={loading}
      fields={[
        {
          key: 'academic_year_id',
          label: 'Academic Year',
          type: 'select',
          options: (options.academicYears || []).map((item) => ({ value: item.id, label: item.name })),
        },
        {
          key: 'school_class_id',
          label: 'Class',
          type: 'select',
          options: [{ value: '', label: 'All Classes' }, ...(options.classes || []).map((item) => ({ value: item.id, label: item.name }))],
        },
        {
          key: 'section_id',
          label: 'Section',
          type: 'select',
          options: [{ value: '', label: 'All Sections' }, ...sectionOptions],
        },
        { key: 'exception_date', label: 'Exception Date', type: 'date' },
        { key: 'title', label: 'Title' },
        { key: 'description', label: 'Description', type: 'textarea' },
        {
          key: 'exception_type',
          label: 'Exception Type',
          type: 'select',
          options: [
            { value: 'holiday', label: 'Holiday' },
            { value: 'exam', label: 'Exam' },
            { value: 'event', label: 'Event' },
            { value: 'cancelled_class', label: 'Cancelled Class' },
            { value: 'special_schedule', label: 'Special Schedule' },
          ],
        },
        { key: 'affects_attendance', label: 'Affects attendance', type: 'checkbox' },
      ]}
      columns={[
        { key: 'exception_date', header: 'Date' },
        { key: 'title', header: 'Title' },
        { key: 'exception_type', header: 'Type' },
        { key: 'scope', header: 'Scope', render: (row) => `${row.school_class?.name || 'All Classes'}${row.section?.name ? ` / ${row.section.name}` : ''}` },
        { key: 'affects_attendance', header: 'Attendance', render: (row) => row.affects_attendance ? 'Yes' : 'No' },
      ]}
      rows={filteredRows}
      search={search}
      setSearch={setSearch}
      filters={[
        {
          key: 'exception_type',
          label: 'Type',
          value: typeFilter,
          onChange: setTypeFilter,
          options: [
            { value: '', label: 'All Types' },
            { value: 'holiday', label: 'Holiday' },
            { value: 'exam', label: 'Exam' },
            { value: 'event', label: 'Event' },
            { value: 'cancelled_class', label: 'Cancelled Class' },
            { value: 'special_schedule', label: 'Special Schedule' },
          ],
        },
      ]}
      onEdit={(row) => setForm({
        id: row.id,
        academic_year_id: row.academic_year_id || '',
        school_class_id: row.school_class_id || '',
        section_id: row.section_id || '',
        exception_date: row.exception_date || '',
        title: row.title || '',
        description: row.description || '',
        exception_type: row.exception_type || 'event',
        affects_attendance: Boolean(row.affects_attendance),
      })}
      onDelete={(id) => dispatch(deleteTimetableException(id))}
      emptyState="No schedule exceptions have been captured yet."
    />
  );
}
