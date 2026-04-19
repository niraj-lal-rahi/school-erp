import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { IconButton, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createSection, deleteSection, fetchMasterData, updateSection } from '../store/masterDataSlice';

export function SectionsPage() {
  const dispatch = useAppDispatch();
  const { sections, classes, saving, error } = useAppSelector((state) => state.masterData);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState({
    id: null,
    school_class_id: '',
    name: '',
    capacity: '40',
  });

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  const classOptions = useMemo(
    () => classes.map((schoolClass) => ({ value: schoolClass.id, label: `${schoolClass.name} (${schoolClass.code})` })),
    [classes]
  );

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      school_class_id: Number(form.school_class_id),
      name: form.name,
      capacity: Number(form.capacity || 40),
    };

    const result = await dispatch(form.id
      ? updateSection({ sectionId: form.id, payload })
      : createSection(payload));

    if (!result.error) {
      setForm({
        id: null,
        school_class_id: '',
        name: '',
        capacity: '40',
      });
    }
  }

  const filteredSections = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return sections;

    return sections.filter((section) =>
      `${section.name} ${section.school_class?.name || ''} ${section.school_class?.academic_year?.name || ''}`
        .toLowerCase()
        .includes(query)
    );
  }, [sections, search]);

  return (
    <Stack spacing={2}>
      <TextField
        size="small"
        label="Search Sections"
        value={search}
        onChange={(event) => setSearch(event.target.value)}
      />
      <MasterDataPageShell
        title="Sections"
        description="Create, search, edit, and manage sections under each class so enrollments can assign students to actual class divisions."
        fields={[
          { key: 'school_class_id', label: 'Class', select: true, options: classOptions },
          { key: 'name', label: 'Section Name' },
          { key: 'capacity', label: 'Capacity' },
        ]}
        formState={form}
        onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
        onSubmit={handleSubmit}
        submitLabel={form.id ? 'Update Section' : 'Add Section'}
        saving={saving}
        error={error}
        columns={[
          { key: 'name', header: 'Section' },
          { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'n/a' },
          { key: 'academic_year', header: 'Academic Year', render: (row) => row.school_class?.academic_year?.name || 'n/a' },
          { key: 'capacity', header: 'Capacity' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <IconButton color="primary" onClick={() => setForm({ id: row.id, school_class_id: row.school_class_id, name: row.name, capacity: String(row.capacity || 40) })}>
                  <EditOutlinedIcon />
                </IconButton>
                <IconButton color="error" onClick={() => dispatch(deleteSection(row.id))}>
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              </Stack>
            ),
          },
        ]}
        rows={filteredSections}
      />
    </Stack>
  );
}
