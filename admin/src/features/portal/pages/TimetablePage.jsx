import { Alert } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalTimetable } from '../store/portalSlice';

export function TimetablePage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, timetable, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalTimetable(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const weeklyRows = Object.entries(timetable?.weekly || {}).flatMap(([day, rows]) => (
    rows.map((row) => ({ ...row, day }))
  ));

  return (
    <PortalPageShell
      title="Timetable"
      description="Keep the weekly schedule easy to scan on both parent and student profiles, with today’s lessons and the broader week in one place."
      modeLabel="Timetable"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <PortalSectionCard title="Today" subtitle="The active student’s timetable entries for the current day.">
        <AppDataTable
          title="Today"
          columns={[
            { key: 'period', header: 'Period', render: (row) => row.period?.name || '-' },
            { key: 'subject', header: 'Subject', render: (row) => row.subject || row.entry_type || '-' },
            { key: 'teacher', header: 'Teacher', render: (row) => row.teacher || '-' },
            { key: 'room', header: 'Room', render: (row) => row.room || '-' },
          ]}
          rows={timetable?.upcoming || []}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No timetable rows are scheduled for today."
        />
      </PortalSectionCard>

      <PortalSectionCard title="Weekly Timetable" subtitle="A full-week view generated from the unified timetable module.">
        <AppDataTable
          title="Weekly Schedule"
          columns={[
            { key: 'day', header: 'Day' },
            { key: 'period', header: 'Period', render: (row) => row.period?.name || '-' },
            { key: 'subject', header: 'Subject', render: (row) => row.subject || row.entry_type || '-' },
            { key: 'teacher', header: 'Teacher', render: (row) => row.teacher || '-' },
            { key: 'room', header: 'Room', render: (row) => row.room || '-' },
          ]}
          rows={weeklyRows}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No weekly timetable is available for this student."
        />
      </PortalSectionCard>
    </PortalPageShell>
  );
}
