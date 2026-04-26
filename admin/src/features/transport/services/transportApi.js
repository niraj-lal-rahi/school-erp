import { axiosClient } from '../../../api/axiosClient';

const basePath = '/transport';

export const transportApi = {
  getVehicles: (params) => axiosClient.get(`${basePath}/vehicles`, { params }),
  createVehicle: (payload) => axiosClient.post(`${basePath}/vehicles`, payload),
  updateVehicle: (id, payload) => axiosClient.put(`${basePath}/vehicles/${id}`, payload),
  deleteVehicle: (id) => axiosClient.delete(`${basePath}/vehicles/${id}`),
  getVehicleLocation: (id) => axiosClient.get(`${basePath}/vehicle-location/${id}`),

  getDrivers: (params) => axiosClient.get(`${basePath}/drivers`, { params }),
  createDriver: (payload) => axiosClient.post(`${basePath}/drivers`, payload),
  updateDriver: (id, payload) => axiosClient.put(`${basePath}/drivers/${id}`, payload),
  deleteDriver: (id) => axiosClient.delete(`${basePath}/drivers/${id}`),

  getRoutes: (params) => axiosClient.get(`${basePath}/routes`, { params }),
  createRoute: (payload) => axiosClient.post(`${basePath}/routes`, payload),
  updateRoute: (id, payload) => axiosClient.put(`${basePath}/routes/${id}`, payload),
  deleteRoute: (id) => axiosClient.delete(`${basePath}/routes/${id}`),

  getRouteStops: (params) => axiosClient.get(`${basePath}/route-stops`, { params }),
  createRouteStop: (payload) => axiosClient.post(`${basePath}/route-stops`, payload),
  updateRouteStop: (id, payload) => axiosClient.put(`${basePath}/route-stops/${id}`, payload),
  deleteRouteStop: (id) => axiosClient.delete(`${basePath}/route-stops/${id}`),
  getRouteStopsByRoute: (routeId) => axiosClient.get(`${basePath}/routes/${routeId}/stops`),

  getRouteAssignments: (params) => axiosClient.get(`${basePath}/route-vehicle-assignments`, { params }),
  createRouteAssignment: (payload) => axiosClient.post(`${basePath}/route-vehicle-assignments`, payload),
  updateRouteAssignment: (id, payload) => axiosClient.put(`${basePath}/route-vehicle-assignments/${id}`, payload),
  deleteRouteAssignment: (id) => axiosClient.delete(`${basePath}/route-vehicle-assignments/${id}`),

  getStudentAllocations: (params) => axiosClient.get(`${basePath}/student-allocations`, { params }),
  createStudentAllocation: (payload) => axiosClient.post(`${basePath}/student-allocations`, payload),
  updateStudentAllocation: (id, payload) => axiosClient.put(`${basePath}/student-allocations/${id}`, payload),
  deleteStudentAllocation: (id) => axiosClient.delete(`${basePath}/student-allocations/${id}`),

  getStaffAllocations: (params) => axiosClient.get(`${basePath}/staff-allocations`, { params }),
  createStaffAllocation: (payload) => axiosClient.post(`${basePath}/staff-allocations`, payload),
  updateStaffAllocation: (id, payload) => axiosClient.put(`${basePath}/staff-allocations/${id}`, payload),
  deleteStaffAllocation: (id) => axiosClient.delete(`${basePath}/staff-allocations/${id}`),

  getTrips: (params) => axiosClient.get(`${basePath}/trips`, { params }),
  createTrip: (payload) => axiosClient.post(`${basePath}/trips`, payload),
  updateTrip: (id, payload) => axiosClient.put(`${basePath}/trips/${id}`, payload),
  deleteTrip: (id) => axiosClient.delete(`${basePath}/trips/${id}`),
  startTrip: (id, payload) => axiosClient.post(`${basePath}/trips/${id}/start`, payload),
  completeTrip: (id, payload) => axiosClient.post(`${basePath}/trips/${id}/complete`, payload),
  cancelTrip: (id, payload) => axiosClient.post(`${basePath}/trips/${id}/cancel`, payload),
  markBoarded: (id, payload) => axiosClient.post(`${basePath}/trips/${id}/mark-boarded`, payload),
  markDropped: (id, payload) => axiosClient.post(`${basePath}/trips/${id}/mark-dropped`, payload),
  getTripLogs: (params) => axiosClient.get(`${basePath}/trip-logs`, { params }),

  getMaintenanceLogs: (params) => axiosClient.get(`${basePath}/maintenance`, { params }),
  createMaintenanceLog: (payload) => axiosClient.post(`${basePath}/maintenance`, payload),
  updateMaintenanceLog: (id, payload) => axiosClient.put(`${basePath}/maintenance/${id}`, payload),
  deleteMaintenanceLog: (id) => axiosClient.delete(`${basePath}/maintenance/${id}`),

  getFuelLogs: (params) => axiosClient.get(`${basePath}/fuel-logs`, { params }),
  createFuelLog: (payload) => axiosClient.post(`${basePath}/fuel-logs`, payload),
  updateFuelLog: (id, payload) => axiosClient.put(`${basePath}/fuel-logs/${id}`, payload),
  deleteFuelLog: (id) => axiosClient.delete(`${basePath}/fuel-logs/${id}`),

  getGpsLogs: (params) => axiosClient.get(`${basePath}/gps-logs`, { params }),
  createGpsLog: (payload) => axiosClient.post(`${basePath}/gps-logs`, payload),

  getReports: (params) => axiosClient.get(`${basePath}/reports`, { params }),

  getAcademicYears: (params) => axiosClient.get('/academic-years', { params }),
  getStudents: (params) => axiosClient.get('/students', { params }),
  getStaff: (params) => axiosClient.get('/hr/staff', { params }),
};
