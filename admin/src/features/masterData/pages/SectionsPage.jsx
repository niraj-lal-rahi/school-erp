import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createSection, fetchMasterData } from '../store/masterDataSlice';

export function SectionsPage() {
  const dispatch = useAppDispatch();
  const { sections, classes, saving, error } = useAppSelector((state) => state.masterData);
  const [form, setForm] = useState({
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
    const result = await dispatch(createSection({
      school_class_id: Number(form.school_class_id),
      name: form.name,
      capacity: Number(form.capacity || 40),
    }));

    if (!result.error) {
      setForm({
        school_class_id: '',
        name: '',
        capacity: '40',
      });
    }
  }

  return (
    <MasterDataPageShell
      title="Sections"
      description="Create sections under each class so enrollments can assign students to actual class divisions."
      fields={[
        { key: 'school_class_id', label: 'Class', select: true, options: classOptions },
        { key: 'name', label: 'Section Name' },
        { key: 'capacity', label: 'Capacity' },
      ]}
      formState={form}
      onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
      onSubmit={handleSubmit}
      submitLabel="Add Section"
      saving={saving}
      error={error}
      columns={[
        { key: 'name', header: 'Section' },
        { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'n/a' },
        { key: 'academic_year', header: 'Academic Year', render: (row) => row.school_class?.academic_year?.name || 'n/a' },
        { key: 'capacity', header: 'Capacity' },
      ]}
      rows={sections}
    />
  );
}
