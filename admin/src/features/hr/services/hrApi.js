import { axiosClient } from '../../../api/axiosClient';

const basePath = '/hr';

function buildNestedPath(staffId, suffix) {
  return `${basePath}/staff/${staffId}/${suffix}`;
}

export const hrApi = {
  getStaff: (params) => axiosClient.get(`${basePath}/staff`, { params }),
  getStaffProfile: (staffId) => axiosClient.get(`${basePath}/staff/${staffId}`),
  createStaff: (payload) => axiosClient.post(`${basePath}/staff`, payload),
  updateStaff: (staffId, payload) => axiosClient.put(`${basePath}/staff/${staffId}`, payload),
  deleteStaff: (staffId) => axiosClient.delete(`${basePath}/staff/${staffId}`),
  runLifecycleAction: (staffId, action, payload) => axiosClient.post(`${basePath}/staff/${staffId}/${action}`, payload),

  getDepartments: (params) => axiosClient.get(`${basePath}/departments`, { params }),
  createDepartment: (payload) => axiosClient.post(`${basePath}/departments`, payload),
  updateDepartment: (id, payload) => axiosClient.put(`${basePath}/departments/${id}`, payload),
  deleteDepartment: (id) => axiosClient.delete(`${basePath}/departments/${id}`),

  getDesignations: (params) => axiosClient.get(`${basePath}/designations`, { params }),
  createDesignation: (payload) => axiosClient.post(`${basePath}/designations`, payload),
  updateDesignation: (id, payload) => axiosClient.put(`${basePath}/designations/${id}`, payload),
  deleteDesignation: (id) => axiosClient.delete(`${basePath}/designations/${id}`),

  getAttendance: (params) => axiosClient.get(`${basePath}/staff-attendance`, { params }),
  createAttendance: (payload) => axiosClient.post(`${basePath}/staff-attendance`, payload),
  updateAttendance: (id, payload) => axiosClient.put(`${basePath}/staff-attendance/${id}`, payload),
  deleteAttendance: (id) => axiosClient.delete(`${basePath}/staff-attendance/${id}`),

  getLeaveTypes: (params) => axiosClient.get(`${basePath}/leave-types`, { params }),
  createLeaveType: (payload) => axiosClient.post(`${basePath}/leave-types`, payload),
  updateLeaveType: (id, payload) => axiosClient.put(`${basePath}/leave-types/${id}`, payload),
  deleteLeaveType: (id) => axiosClient.delete(`${basePath}/leave-types/${id}`),

  getLeaveApplications: (params) => axiosClient.get(`${basePath}/leave-applications`, { params }),
  createLeaveApplication: (payload) => axiosClient.post(`${basePath}/leave-applications`, payload),
  updateLeaveApplication: (id, payload) => axiosClient.put(`${basePath}/leave-applications/${id}`, payload),
  deleteLeaveApplication: (id) => axiosClient.delete(`${basePath}/leave-applications/${id}`),
  runLeaveWorkflow: (id, action, payload) => axiosClient.post(`${basePath}/leave-applications/${id}/${action}`, payload),

  getLeaveBalances: (params) => axiosClient.get(`${basePath}/leave-balances`, { params }),
  getStaffLeaveBalance: (staffId, params) => axiosClient.get(buildNestedPath(staffId, 'leave-balance'), { params }),

  getSalaryComponents: (params) => axiosClient.get(`${basePath}/salary-components`, { params }),
  createSalaryComponent: (payload) => axiosClient.post(`${basePath}/salary-components`, payload),
  updateSalaryComponent: (id, payload) => axiosClient.put(`${basePath}/salary-components/${id}`, payload),
  deleteSalaryComponent: (id) => axiosClient.delete(`${basePath}/salary-components/${id}`),

  getSalaryStructures: (params) => axiosClient.get(`${basePath}/salary-structures`, { params }),
  createSalaryStructure: (payload) => axiosClient.post(`${basePath}/salary-structures`, payload),
  updateSalaryStructure: (id, payload) => axiosClient.put(`${basePath}/salary-structures/${id}`, payload),
  deleteSalaryStructure: (id) => axiosClient.delete(`${basePath}/salary-structures/${id}`),

  getPayrollRuns: (params) => axiosClient.get(`${basePath}/payroll-runs`, { params }),
  createPayrollRun: (payload) => axiosClient.post(`${basePath}/payroll-runs`, payload),
  updatePayrollRun: (id, payload) => axiosClient.put(`${basePath}/payroll-runs/${id}`, payload),
  deletePayrollRun: (id) => axiosClient.delete(`${basePath}/payroll-runs/${id}`),
  runPayrollWorkflow: (id, action) => axiosClient.post(`${basePath}/payroll-runs/${id}/${action}`),

  getPayslips: (params) => axiosClient.get(`${basePath}/payslips`, { params }),
  updatePayslip: (id, payload) => axiosClient.put(`${basePath}/payslips/${id}`, payload),

  getStaffDocuments: (staffId) => axiosClient.get(buildNestedPath(staffId, 'documents')),
  uploadStaffDocument: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'upload-document'), payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  updateStaffDocument: (id, payload) => axiosClient.put(`${basePath}/staff-documents/${id}`, payload),
  deleteStaffDocument: (id) => axiosClient.delete(`${basePath}/staff-documents/${id}`),

  getStaffEmergencyContacts: (staffId) => axiosClient.get(buildNestedPath(staffId, 'emergency-contacts')),
  createStaffEmergencyContact: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'emergency-contacts'), payload),
  updateStaffEmergencyContact: (id, payload) => axiosClient.put(`${basePath}/staff-emergency-contacts/${id}`, payload),
  deleteStaffEmergencyContact: (id) => axiosClient.delete(`${basePath}/staff-emergency-contacts/${id}`),

  getStaffQualifications: (staffId) => axiosClient.get(buildNestedPath(staffId, 'qualifications')),
  createStaffQualification: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'qualifications'), payload),
  updateStaffQualification: (id, payload) => axiosClient.put(`${basePath}/staff-qualifications/${id}`, payload),
  deleteStaffQualification: (id) => axiosClient.delete(`${basePath}/staff-qualifications/${id}`),

  getStaffExperiences: (staffId) => axiosClient.get(buildNestedPath(staffId, 'experiences')),
  createStaffExperience: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'experiences'), payload),
  updateStaffExperience: (id, payload) => axiosClient.put(`${basePath}/staff-experiences/${id}`, payload),
  deleteStaffExperience: (id) => axiosClient.delete(`${basePath}/staff-experiences/${id}`),

  getStaffBankDetails: (staffId) => axiosClient.get(buildNestedPath(staffId, 'bank-details')),
  createStaffBankDetail: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'bank-details'), payload),
  updateStaffBankDetail: (id, payload) => axiosClient.put(`${basePath}/staff-bank-details/${id}`, payload),
  deleteStaffBankDetail: (id) => axiosClient.delete(`${basePath}/staff-bank-details/${id}`),

  getStaffNotes: (staffId) => axiosClient.get(buildNestedPath(staffId, 'notes')),
  createStaffNote: (staffId, payload) => axiosClient.post(buildNestedPath(staffId, 'notes'), payload),
  updateStaffNote: (id, payload) => axiosClient.put(`${basePath}/staff-notes/${id}`, payload),
  deleteStaffNote: (id) => axiosClient.delete(`${basePath}/staff-notes/${id}`),

  getStaffStatusHistory: (staffId) => axiosClient.get(buildNestedPath(staffId, 'status-history')),
};
