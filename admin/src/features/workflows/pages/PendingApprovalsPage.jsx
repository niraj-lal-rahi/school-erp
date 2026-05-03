import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import HighlightOffOutlinedIcon from '@mui/icons-material/HighlightOffOutlined';
import { Alert, Button, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { useWorkflowAccess } from '../hooks/useWorkflowAccess';
import {
  approveWorkflowRequest,
  fetchApprovalRequests,
  rejectWorkflowRequest,
} from '../store/workflowsSlice';

export function PendingApprovalsPage() {
  const dispatch = useAppDispatch();
  const { canApprove } = useWorkflowAccess();
  const { approvals, approvalsPagination, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('pending');

  useEffect(() => {
    dispatch(fetchApprovalRequests({ status, per_page: 20, page: approvalsPagination.page }));
  }, [approvalsPagination.page, dispatch, status]);

  const rows = approvals.filter((item) => `${item.module} ${item.reference_type} ${item.reference_id}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <WorkflowPageShell
      title="Pending Approvals"
      description="Review approval queues, keep self-approval blocked, and turn around decision-heavy workflows from one focused inbox."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Approval Queue"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...approvalsPagination,
          onPageChange: (page) => dispatch(fetchApprovalRequests({ status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [
              { label: 'Pending', value: 'pending' },
              { label: 'Approved', value: 'approved' },
              { label: 'Rejected', value: 'rejected' },
              { label: 'Cancelled', value: 'cancelled' },
            ],
          },
        ]}
        columns={[
          { key: 'id', header: 'Approval #' },
          { key: 'module', header: 'Module' },
          { key: 'reference_type', header: 'Reference Type' },
          { key: 'reference_id', header: 'Reference ID' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          { key: 'requested_at', header: 'Requested At', render: (row) => row.requested_at || 'N/A' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              canApprove ? (
                <Stack direction="row" spacing={1}>
                  <Button
                    size="small"
                    startIcon={<CheckCircleOutlineOutlinedIcon />}
                    disabled={saving || row.status !== 'pending'}
                    onClick={() => dispatch(approveWorkflowRequest({ id: row.id, payload: { remarks: 'Approved from workflow inbox.' } }))}
                  >
                    Approve
                  </Button>
                  <Button
                    size="small"
                    color="error"
                    startIcon={<HighlightOffOutlinedIcon />}
                    disabled={saving || row.status !== 'pending'}
                    onClick={() => dispatch(rejectWorkflowRequest({ id: row.id, payload: { remarks: 'Rejected from workflow inbox.' } }))}
                  >
                    Reject
                  </Button>
                </Stack>
              ) : null
            ),
          },
        ]}
      />
    </WorkflowPageShell>
  );
}
