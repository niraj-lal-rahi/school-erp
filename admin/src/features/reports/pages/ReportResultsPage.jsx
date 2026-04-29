import DownloadOutlinedIcon from '@mui/icons-material/DownloadOutlined';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, MenuItem, Stack } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { reportsApi } from '../services/reportsApi';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { fetchReportRun, fetchReportRuns } from '../store/reportsSlice';
import { fileTypeOptions, runStatusOptions } from '../types/options';

async function downloadExportFile(exportItem) {
  const response = await reportsApi.downloadExport(exportItem.id);
  const url = window.URL.createObjectURL(new Blob([response.data], { type: exportItem.mime_type || 'application/octet-stream' }));
  const link = document.createElement('a');
  link.href = url;
  link.download = exportItem.file_name || `report-export-${exportItem.id}`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
}

export function ReportResultsPage() {
  const dispatch = useAppDispatch();
  const { runs, runsPagination, selectedRun, loading, error } = useAppSelector((state) => state.reports);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({ status: '', file_type: '' });

  useEffect(() => {
    dispatch(fetchReportRuns({
      status: filters.status || undefined,
      per_page: 20,
      page: runsPagination.page,
    }));
  }, [dispatch, filters.status, runsPagination.page]);

  const visibleRuns = useMemo(
    () => runs.filter((item) => {
      if (!search) {
        return true;
      }

      const needle = search.toLowerCase();
      return [item.report_definition?.name, item.report_definition?.code, item.status, item.file_type]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(needle));
    }).filter((item) => (filters.file_type ? item.file_type === filters.file_type : true)),
    [runs, search, filters.file_type],
  );

  const exports = selectedRun?.exports || [];

  return (
    <ReportsPageShell
      title="Report Results Table"
      description="Review run history, inspect the most recent output metadata, and download generated exports."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Report Runs"
        columns={[
          { key: 'report', header: 'Report', render: (row) => row.report_definition?.name || 'N/A' },
          { key: 'run_type', header: 'Run Type' },
          { key: 'status', header: 'Status' },
          { key: 'file_type', header: 'File Type' },
          { key: 'started_at', header: 'Started', render: (row) => row.started_at ? new Date(row.started_at).toLocaleString() : 'N/A' },
          { key: 'completed_at', header: 'Completed', render: (row) => row.completed_at ? new Date(row.completed_at).toLocaleString() : 'N/A' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Button size="small" startIcon={<VisibilityOutlinedIcon />} onClick={() => dispatch(fetchReportRun(row.id))}>
                Inspect
              </Button>
            ),
          },
        ]}
        rows={visibleRuns}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters((current) => ({ ...current, status: value })),
            options: runStatusOptions,
          },
          {
            key: 'file_type',
            label: 'File Type',
            value: filters.file_type,
            onChange: (value) => setFilters((current) => ({ ...current, file_type: value })),
            options: [{ value: '', label: 'All File Types' }, ...fileTypeOptions],
          },
        ]}
        pagination={{
          ...runsPagination,
          onPageChange: (page) => dispatch(fetchReportRuns({
            status: filters.status || undefined,
            per_page: 20,
            page,
          })),
        }}
        emptyState="No report runs available."
      />

      <AppDataTable
        title="Selected Run Exports"
        columns={[
          { key: 'file_name', header: 'File Name' },
          { key: 'mime_type', header: 'MIME Type' },
          { key: 'file_size', header: 'Size' },
          { key: 'downloaded_count', header: 'Downloads' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<DownloadOutlinedIcon />} onClick={() => downloadExportFile(row)}>
                  Download
                </Button>
              </Stack>
            ),
          },
        ]}
        rows={exports}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="Inspect a run to see its generated exports here."
      />
    </ReportsPageShell>
  );
}
