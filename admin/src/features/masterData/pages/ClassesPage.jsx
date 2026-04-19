import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { MasterDataPageShell } from '../components/MasterDataPageShell';
import { createSchoolClass, fetchMasterData } from '../store/masterDataSlice';

export function ClassesPage() {
  const dispatch = useAppDispatch();
  const { classes, academicYears, saving, error } = useAppSelector((state) => state.masterData);
  const [form, setForm] = useState({
    academic_year_id: '',
    name: '',
    code: '',
    grade_level: '',
    sort_order: '',
  });

  useEffect(() => {
    dispatch(fetchMasterData());
  }, [dispatch]);

  const yearOptions = useMemo(
    () => academicYears.map((year) => ({ value: year.id, label: year.name })),
    [academicYears]
  );

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(createSchoolClass({
      ...form,
      academic_year_id: Number(form.academic_year_id),
      grade_level: Number(form.grade_level),
      sort_order: Number(form.sort_order || 0),
    }));

    if (!result.error) {
      setForm({
        academic_year_id: '',
        name: '',
        code: '',
        grade_level: '',
        sort_order: '',
      });
    }
  }

  return (
    <MasterDataPageShell
      title="Class Masters"
      description="Create classes tied to academic years so the student enrollment and admission forms can use dropdown selection."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', select: true, options: yearOptions },
        { key: 'name', label: 'Class Name' },
        { key: 'code', label: 'Code' },
        { key: 'grade_level', label: 'Grade Level' },
        { key: 'sort_order', label: 'Sort Order' },
      ]}
      formState={form}
      onFieldChange={(key, value) => setForm((current) => ({ ...current, [key]: value }))}
      onSubmit={handleSubmit}
      submitLabel="Add Class"
      saving={saving}
      error={error}
      columns={[
        { key: 'name', header: 'Class' },
        { key: 'code', header: 'Code' },
        { key: 'grade_level', header: 'Grade' },
        { key: 'year', header: 'Academic Year', render: (row) => row.academic_year?.name || 'n/a' },
      ]}
      rows={classes}
    />
  );
}
