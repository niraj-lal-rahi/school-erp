import AddTaskOutlinedIcon from '@mui/icons-material/AddTaskOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { ResultStatusChip } from '../components/ResultStatusChip';
import { createExam, deleteExam, enrollStudentsForExam, fetchExaminationMasterData, fetchExams, updateExam } from '../store/examinationSlice';

const initialForm = {
  id: null,
  academic_year_id: '',
  exam_type_id: '',
  term_id: '',
  class_id: '',
  section_id: '',
  name: '',
  code: '',
  start_date: '',
  end_date: '',
  total_marks: '',
  passing_marks: '',
  result_status: 'draft',
};

export function ExamSetupPage() {
  const dispatch = useAppDispatch();
  const {
    exams,
    academicYears,
    examTypes,
    terms,
    schoolClasses,
    sections,
    loading,
    saving,
    error,
  } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [classFilter, setClassFilter] = useState('');
  const [resultStatusFilter, setResultStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchExaminationMasterData());
    dispatch(fetchExams());
  }, [dispatch]);

  const sectionOptions = useMemo(
    () => sections.filter((section) => !form.class_id || String(section.school_class_id) === String(form.class_id)),
    [sections, form.class_id],
  );

  const rows = useMemo(() => {
    const query = search.trim().toLowerCase();
    return exams.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code}`.toLowerCase().includes(query);
      const matchesClass = !classFilter || String(item.class_id || '') === String(classFilter);
      const matchesStatus = !resultStatusFilter || item.result_status === resultStatusFilter;
      return matchesSearch && matchesClass && matchesStatus;
    });
  }, [exams, search, classFilter, resultStatusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    if (!canManage) return;

    const payload = {
      ...form,
      academic_year_id: Number(form.academic_year_id),
      exam_type_id: Number(form.exam_type_id),
      term_id: form.term_id ? Number(form.term_id) : null,
      class_id: form.class_id ? Number(form.class_id) : null,
      section_id: form.section_id ? Number(form.section_id) : null,
      total_marks: form.total_marks === '' ? null : Number(form.total_marks),
      passing_marks: form.passing_marks === '' ? null : Number(form.passing_marks),
    };

    const action = form.id
      ? updateExam({ id: form.id, payload: { ...payload, id: undefined } })
      : createExam(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchExams());
    }
  }

  return (
    <ExaminationPageShell
      title="Exam Setup"
      description="Create exam windows by year, type, class, and section, then move them into marks entry and result processing."
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h6">{form.id ? 'Edit Exam' : 'Create Exam'}</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField select label="Academic Year" value={form.academic_year_id} onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}>
                <MenuItem value="">Select</MenuItem>
                {academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Exam Type" value={form.exam_type_id} onChange={(event) => setForm((current) => ({ ...current, exam_type_id: event.target.value }))}>
                <MenuItem value="">Select</MenuItem>
                {examTypes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Term" value={form.term_id} onChange={(event) => setForm((current) => ({ ...current, term_id: event.target.value }))}>
                <MenuItem value="">Optional</MenuItem>
                {terms.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Class" value={form.class_id} onChange={(event) => setForm((current) => ({ ...current, class_id: event.target.value, section_id: '' }))}>
                <MenuItem value="">All Classes</MenuItem>
                {schoolClasses.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Section" value={form.section_id} onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}>
                <MenuItem value="">All Sections</MenuItem>
                {sectionOptions.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Exam Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
              <TextField type="date" label="Start Date" InputLabelProps={{ shrink: true }} value={form.start_date} onChange={(event) => setForm((current) => ({ ...current, start_date: event.target.value }))} />
              <TextField type="date" label="End Date" InputLabelProps={{ shrink: true }} value={form.end_date} onChange={(event) => setForm((current) => ({ ...current, end_date: event.target.value }))} />
              <TextField type="number" label="Total Marks" value={form.total_marks} onChange={(event) => setForm((current) => ({ ...current, total_marks: event.target.value }))} />
              <TextField type="number" label="Passing Marks" value={form.passing_marks} onChange={(event) => setForm((current) => ({ ...current, passing_marks: event.target.value }))} />
              <TextField select label="Result Status" value={form.result_status} onChange={(event) => setForm((current) => ({ ...current, result_status: event.target.value }))}>
                <MenuItem value="draft">Draft</MenuItem>
                <MenuItem value="processing">Processing</MenuItem>
                <MenuItem value="published">Published</MenuItem>
                <MenuItem value="archived">Archived</MenuItem>
              </TextField>
              <Button type="submit" variant="contained" disabled={saving || !canManage}>
                {saving ? 'Saving...' : form.id ? 'Update Exam' : 'Create Exam'}
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Exam Setup List"
            columns={[
              { key: 'name', header: 'Name' },
              { key: 'code', header: 'Code' },
              { key: 'exam_type', header: 'Exam Type', render: (row) => row.exam_type?.name || 'N/A' },
              { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'All Classes' },
              { key: 'date_range', header: 'Dates', render: (row) => `${row.start_date || '-'} to ${row.end_date || '-'}` },
              { key: 'result_status', header: 'Status', render: (row) => <ResultStatusChip status={row.result_status} /> },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Stack direction="row" spacing={1}>
                    {canManage ? (
                      <>
                        <IconButton color="primary" onClick={() => setForm({
                          id: row.id,
                          academic_year_id: row.academic_year_id || '',
                          exam_type_id: row.exam_type_id || '',
                          term_id: row.term_id || '',
                          class_id: row.class_id || '',
                          section_id: row.section_id || '',
                          name: row.name || '',
                          code: row.code || '',
                          start_date: row.start_date || '',
                          end_date: row.end_date || '',
                          total_marks: row.total_marks ?? '',
                          passing_marks: row.passing_marks ?? '',
                          result_status: row.result_status || 'draft',
                        })}>
                          <EditOutlinedIcon />
                        </IconButton>
                        <IconButton color="success" onClick={() => dispatch(enrollStudentsForExam(row.id))}>
                          <AddTaskOutlinedIcon />
                        </IconButton>
                        <IconButton color="error" onClick={() => dispatch(deleteExam(row.id))}>
                          <DeleteOutlineOutlinedIcon />
                        </IconButton>
                      </>
                    ) : 'View only'}
                  </Stack>
                ),
              },
            ]}
            rows={rows}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            filters={[
              {
                key: 'class_id',
                label: 'Class',
                value: classFilter,
                onChange: setClassFilter,
                options: [{ value: '', label: 'All' }, ...schoolClasses.map((item) => ({ value: item.id, label: item.name }))],
              },
              {
                key: 'result_status',
                label: 'Result Status',
                value: resultStatusFilter,
                onChange: setResultStatusFilter,
                options: [
                  { value: '', label: 'All' },
                  { value: 'draft', label: 'Draft' },
                  { value: 'processing', label: 'Processing' },
                  { value: 'published', label: 'Published' },
                  { value: 'archived', label: 'Archived' },
                ],
              },
            ]}
            emptyState="No exams configured yet."
          />
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
