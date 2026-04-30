import { Alert } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalAssignments, fetchPortalContext } from '../store/portalSlice';

export function AssignmentsPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, assignments, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalAssignments(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  return (
    <PortalPageShell
      title="Assignments and Homework"
      description="Give students and guardians one shared view of published homework, due dates, teachers, and any attachment references."
      modeLabel="Assignments"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Published Assignments"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'subject', header: 'Subject' },
          { key: 'teacher', header: 'Teacher' },
          { key: 'assigned_date', header: 'Assigned' },
          { key: 'due_date', header: 'Due' },
          { key: 'attachment_path', header: 'Attachment', render: (row) => row.attachment_path || '-' },
        ]}
        rows={assignments?.assignments || []}
        loading={sectionLoading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No assignments are published for the active student right now."
      />
    </PortalPageShell>
  );
}
