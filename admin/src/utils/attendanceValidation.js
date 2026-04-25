export function validateAttendanceRequiredFields(values, fields) {
  return fields.reduce((errors, field) => {
    const value = values[field.key];
    const isEmpty = value === undefined || value === null || value === '';

    if (field.required && isEmpty) {
      errors[field.key] = `${field.label} is required.`;
    }

    return errors;
  }, {});
}

export function validateDateRange(startDate, endDate, startLabel = 'Start date', endLabel = 'End date') {
  const errors = {};

  if (startDate && endDate && endDate < startDate) {
    errors.end_date = `${endLabel} must be on or after ${startLabel.toLowerCase()}.`;
  }

  return errors;
}
