import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function AcademicCalendarPage() {
  return (
    <AcademicManagementCrudPage
      resource="academicCalendar"
      title="Academic Calendar"
      description="Publish holidays, PTMs, orientations, exam dates, and tenant-scoped academic events."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'title', label: 'Title' },
        { key: 'event_type', label: 'Event Type' },
        { key: 'start_datetime', label: 'Start', type: 'datetime-local' },
        { key: 'end_datetime', label: 'End', type: 'datetime-local' },
        { key: 'audience_type', label: 'Audience', type: 'select', options: [{ value: 'all', label: 'All' }, { value: 'staff', label: 'Staff' }, { value: 'students', label: 'Students' }, { value: 'parents', label: 'Parents' }, { value: 'class', label: 'Class' }, { value: 'section', label: 'Section' }] },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', type: 'select', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'is_holiday', label: 'Holiday', type: 'checkbox' },
        { key: 'description', label: 'Description', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'title', header: 'Title' },
        { key: 'event_type', header: 'Type' },
        { key: 'start_datetime', header: 'Start' },
        { key: 'end_datetime', header: 'End' },
        { key: 'audience_type', header: 'Audience' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
      ]}
    />
  );
}
