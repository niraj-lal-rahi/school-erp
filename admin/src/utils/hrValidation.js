export function validateRequiredFields(values, fields) {
  return fields.reduce((errors, field) => {
    const value = values[field.key];
    const isEmpty = value === undefined || value === null || value === '';

    if (field.required && isEmpty) {
      errors[field.key] = `${field.label} is required.`;
    }

    return errors;
  }, {});
}

export function validateStaffForm(values) {
  const errors = validateRequiredFields(values, [
    { key: 'employee_code', label: 'Employee Code', required: true },
    { key: 'first_name', label: 'First Name', required: true },
    { key: 'gender', label: 'Gender', required: true },
    { key: 'staff_type', label: 'Staff Type', required: true },
    { key: 'employment_type', label: 'Employment Type', required: true },
    { key: 'joining_date', label: 'Joining Date', required: true },
    { key: 'current_status', label: 'Current Status', required: true },
  ]);

  if (values.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
    errors.email = 'Email format looks invalid.';
  }

  if (values.leaving_date && values.joining_date && values.leaving_date < values.joining_date) {
    errors.leaving_date = 'Leaving date must be after the joining date.';
  }

  return errors;
}
