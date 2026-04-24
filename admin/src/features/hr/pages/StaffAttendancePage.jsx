import { HrCrudPage } from '../components/HrCrudPage';

export function StaffAttendancePage() {
  return (
    <HrCrudPage
      resource="attendance"
      title="Staff Attendance"
      description="Track present, absent, late, leave, and holiday status with manual or biometric-ready fields."
      fields={[
        {
          key: 'staff_id',
          label: 'Staff',
          type: 'select',
          required: true,
          options: (options) => [{ value: '', label: 'Select' }, ...(options.staff || []).map((item) => ({ value: item.id, label: `${item.full_name} (${item.employee_code})` }))],
        },
        { key: 'attendance_date', label: 'Attendance Date', type: 'date', required: true },
        { key: 'check_in_time', label: 'Check In' },
        { key: 'check_out_time', label: 'Check Out' },
        { key: 'attendance_status', label: 'Status', type: 'select', required: true, options: ['present', 'absent', 'half_day', 'late', 'leave', 'holiday'].map((item) => ({ value: item, label: item })) },
        { key: 'source', label: 'Source', type: 'select', options: ['manual', 'biometric', 'import', 'mobile'].map((item) => ({ value: item, label: item })) },
        { key: 'remarks', label: 'Remarks', type: 'textarea' },
      ]}
      columns={[
        { key: 'attendance_date', header: 'Date' },
        { key: 'staff', header: 'Staff', render: (row) => row.staff ? `${row.staff.full_name} (${row.staff.employee_code})` : row.staff_id },
        { key: 'attendance_status', header: 'Status' },
        { key: 'check_in_time', header: 'Check In' },
        { key: 'check_out_time', header: 'Check Out' },
        { key: 'source', header: 'Source' },
      ]}
      filters={[
        {
          key: 'staff_id',
          label: 'Staff',
          options: (options) => [{ value: '', label: 'All' }, ...(options.staff || []).map((item) => ({ value: item.id, label: item.full_name }))],
        },
      ]}
      normalizeRecord={(row) => ({
        id: row.id,
        staff_id: row.staff?.id || row.staff_id || '',
        attendance_date: row.attendance_date || '',
        check_in_time: row.check_in_time || '',
        check_out_time: row.check_out_time || '',
        attendance_status: row.attendance_status || 'present',
        source: row.source || 'manual',
        remarks: row.remarks || '',
      })}
      emptyState="No attendance records found."
    />
  );
}
