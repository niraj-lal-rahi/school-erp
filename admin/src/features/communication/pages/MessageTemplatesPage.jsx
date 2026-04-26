import { Alert, Button, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationFormDrawer } from '../components/CommunicationFormDrawer';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import { createTemplate, deleteTemplate, fetchTemplates, updateTemplate } from '../store/communicationSlice';
import { statusOptions, templateTypeOptions } from '../types/options';

const initialForm = {
  id: null,
  name: '',
  code: '',
  template_type: 'email',
  subject: '',
  body: '',
  variables: '[]',
  status: 'active',
};

export function MessageTemplatesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const { templates, templatesPagination, loading, saving, error } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchTemplates({ page, per_page: 10, search, status: statusFilter || undefined }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      variables: form.variables ? JSON.parse(form.variables) : [],
    };
    const action = form.id ? updateTemplate({ id: form.id, payload }) : createTemplate(payload);
    const result = await dispatch(action);
    if (!result.error) {
      setDrawerOpen(false);
      setForm(initialForm);
    }
  }

  return (
    <CommunicationPageShell
      title="Message Templates"
      description="Standardize reminders, notices, and operational alerts so teams send faster with consistent wording."
      actions={canManage ? <Button variant="contained" onClick={() => { setForm(initialForm); setDrawerOpen(true); }}>New Template</Button> : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Templates"
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'template_type', header: 'Type' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              <Stack direction="row" spacing={1}>
                <Button size="small" onClick={() => {
                  setForm({
                    ...initialForm,
                    ...row,
                    variables: JSON.stringify(row.variables || [], null, 2),
                  });
                  setDrawerOpen(true);
                }}>
                  Edit
                </Button>
                <Button size="small" color="error" onClick={() => dispatch(deleteTemplate(row.id))}>Delete</Button>
              </Stack>
            ) : null,
          },
        ]}
        rows={templates}
        loading={loading}
        searchValue={search}
        onSearchChange={(value) => {
          setSearch(value);
          setPage(1);
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: statusFilter,
            onChange: (value) => {
              setStatusFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...statusOptions.template],
          },
        ]}
        pagination={{ page, totalPages: templatesPagination.totalPages, onPageChange: setPage }}
        emptyState="No communication templates found."
      />

      <CommunicationFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={form.id ? 'Edit Template' : 'Create Template'}
        description="Build reusable content blocks for email, SMS, push, and in-app delivery."
        form={form}
        setForm={setForm}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Template' : 'Save Template'}
        saving={saving}
        fields={[
          { key: 'name', label: 'Name' },
          { key: 'code', label: 'Code' },
          { key: 'template_type', label: 'Template Type', type: 'select', options: templateTypeOptions },
          { key: 'subject', label: 'Subject' },
          { key: 'body', label: 'Body', type: 'multiline', rows: 6 },
          { key: 'variables', label: 'Variables JSON', type: 'multiline', rows: 4 },
          { key: 'status', label: 'Status', type: 'select', options: statusOptions.template },
        ]}
      />
    </CommunicationPageShell>
  );
}
