import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function CurriculumPage() {
  return (
    <AcademicManagementCrudPage
      resource="curriculum"
      title="Curriculum / Syllabus"
      description="Plan syllabus units, chapters, and learning outcomes by year, class, subject, and term."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', type: 'select', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'academic_term_id', label: 'Term', type: 'select', optionsKey: 'terms', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'title', label: 'Title' },
        { key: 'sequence', label: 'Sequence', type: 'number' },
        { key: 'description', label: 'Description', type: 'textarea', md: 12 },
        { key: 'learning_outcomes', label: 'Learning Outcomes', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'title', header: 'Title' },
        { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || row.school_class_id },
        { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.subject_id },
        { key: 'term', header: 'Term', render: (row) => row.term?.name || '-' },
        { key: 'sequence', header: 'Sequence' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
      ]}
    />
  );
}
