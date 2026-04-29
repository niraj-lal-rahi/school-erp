import DownloadOutlinedIcon from '@mui/icons-material/DownloadOutlined';
import { Alert, Button } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { reportsApi } from '../services/reportsApi';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { fetchReportRuns } from '../store/reportsSlice';
import { fileTypeOptions } from '../types/options';

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

export function ExportsPage() {
  const dispatch = useAppDispatch();
  const { runs, loading, error } = useAppSelector((state) => state.reports);
  const [search, setSearch] = useState('');
  const [fileType, setFileType] = useState('');

  useEffect(() => {
    dispatch(fetchReportRuns({ per_page: 100 }));
  }, [dispatch]);

  const exports = useMemo(() => runs.flatMap((run) => (run.exports || []).map((exportItem) => ({
    ...exportItem,
    report_name: run.report_definition?.name || 'N/A',
    report_code: run.report_definition?.code || 'N/A',
    run_status: run.status,
    file_type: exportItem.file_name?.split('.').pop() || run.file_type,
  }))), [runs]);

  const filteredExports = exports.filter((item) => {
    const matchesSearch = !search || [item.file_name, item.report_name, item.report_code]
      .filter(Boolean)
      .some((value) => String(value).toLowerCase().includes(search.toLowerCase()));
    const matchesType = !fileType || item.file_type === fileType;
    return matchesSearch && matchesType;
  });

  return (
    <ReportsPageShell
      title="Exports"
      description="Track generated output files and download the exact exported artifacts your users rely on."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Generated Exports"
        columns={[
          { key: 'file_name', header: 'File Name' },
          { key: 'report_name', header: 'Report' },
          { key: 'file_type', header: 'Type' },
          { key: 'file_size', header: 'Size' },
          { key: 'downloaded_count', header: 'Downloads' },
          { key: 'run_status', header: 'Run Status' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Button size="small" startIcon={<DownloadOutlinedIcon />} onClick={() => downloadExportFile(row)}>
                Download
              </Button>
            ),
          },
        ]}
        rows={filteredExports}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'file_type',
            label: 'File Type',
            value: fileType,
            onChange: setFileType,
            options: [{ value: '', label: 'All File Types' }, ...fileTypeOptions],
          },
        ]}
        emptyState="No exported files available."
      />
    </ReportsPageShell>
  );
}
