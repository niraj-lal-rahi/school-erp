import { Alert, Grid, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalResults } from '../store/portalSlice';

export function ResultsPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, results, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalResults(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const latest = results?.latest_result;

  return (
    <PortalPageShell
      title="Results and Report Cards"
      description="Show the latest academic outcome first, then let students and guardians review subject-wise performance without switching tools."
      modeLabel="Results"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <PortalSectionCard title="Latest Result" subtitle="The most recent published result for the active student.">
            {latest ? (
              <>
                <Typography><strong>Exam:</strong> {latest.exam?.name || '-'}</Typography>
                <Typography><strong>Percentage:</strong> {Number(latest.percentage || 0).toFixed(2)}%</Typography>
                <Typography><strong>Grade:</strong> {latest.grade || '-'}</Typography>
                <Typography><strong>GPA:</strong> {latest.gpa ?? '-'}</Typography>
                <Typography><strong>Rank:</strong> {latest.rank || '-'}</Typography>
              </>
            ) : (
              <Typography color="text.secondary">No published result has been found yet.</Typography>
            )}
          </PortalSectionCard>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <PortalSectionCard title="All Results" subtitle="Track exam-by-exam outcomes and keep parents aligned with the same student view.">
            <AppDataTable
              title="Published Results"
              columns={[
                { key: 'exam', header: 'Exam', render: (row) => row.exam?.name || '-' },
                { key: 'exam_type', header: 'Type', render: (row) => row.exam?.exam_type || '-' },
                { key: 'percentage', header: 'Percentage', render: (row) => `${Number(row.percentage || 0).toFixed(2)}%` },
                { key: 'grade', header: 'Grade' },
                { key: 'gpa', header: 'GPA', render: (row) => row.gpa ?? '-' },
                { key: 'rank', header: 'Rank', render: (row) => row.rank || '-' },
                { key: 'result_status', header: 'Status' },
              ]}
              rows={results?.results || []}
              loading={sectionLoading}
              searchValue=""
              onSearchChange={() => {}}
              emptyState="No results are available for the active student."
            />
          </PortalSectionCard>
        </Grid>
      </Grid>

      {latest?.subjects?.length ? (
        <PortalSectionCard title="Latest Subject Breakdown" subtitle="A subject-level look at the latest exam result.">
          <AppDataTable
            title="Subject Details"
            columns={[
              { key: 'subject', header: 'Subject' },
              { key: 'max_marks', header: 'Max Marks' },
              { key: 'obtained_marks', header: 'Obtained' },
              { key: 'grade', header: 'Grade' },
              { key: 'is_pass', header: 'Outcome', render: (row) => row.is_pass ? 'Pass' : 'Fail' },
            ]}
            rows={latest.subjects}
            loading={sectionLoading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No subject details are available."
          />
        </PortalSectionCard>
      ) : null}
    </PortalPageShell>
  );
}
