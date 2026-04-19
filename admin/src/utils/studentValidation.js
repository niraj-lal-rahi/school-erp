const lifecycleStatuses = ['active', 'inactive', 'alumni'];
const genders = ['male', 'female', 'other'];

export function validateStudentForm(values) {
  const errors = {};

  if (!values.admission_no?.trim()) errors.admission_no = 'Admission number is required.';
  if (!values.first_name?.trim()) errors.first_name = 'First name is required.';
  if (!values.last_name?.trim()) errors.last_name = 'Last name is required.';
  if (!values.gender || !genders.includes(values.gender)) errors.gender = 'Choose a valid gender.';
  if (!values.date_of_birth) errors.date_of_birth = 'Date of birth is required.';
  if (!values.admission_date) errors.admission_date = 'Admission date is required.';
  if (!values.status || !lifecycleStatuses.includes(values.status)) errors.status = 'Choose a valid status.';
  if (!values.enrollment?.academic_year_id) errors.enrollment_academic_year_id = 'Academic year is required.';
  if (!values.enrollment?.school_class_id) errors.enrollment_school_class_id = 'Class is required.';
  if (!values.admission?.status) errors.admission_status = 'Admission status is required.';
  if (!values.guardians?.length) errors.guardians = 'At least one guardian mapping is required.';

  if (values.email && !/^\S+@\S+\.\S+$/.test(values.email)) {
    errors.email = 'Enter a valid email address.';
  }

  return errors;
}
