import { axiosClient } from '../../../api/axiosClient';

export const academicManagementEndpointMap = {
  academicYears: 'academic-years',
  terms: 'terms',
  classes: 'classes',
  sections: 'sections',
  subjects: 'subjects',
  classSubjects: 'class-subjects',
  teacherAssignments: 'teacher-assignments',
  curriculum: 'curriculum',
  lessonPlans: 'lesson-plans',
  assignments: 'assignments',
  academicCalendar: 'academic-calendar',
  gradingStructures: 'grading-structures',
};

function endpointFor(resource) {
  return `/academic-management/${academicManagementEndpointMap[resource]}`;
}

export const academicManagementApi = {
  getOptions() {
    return axiosClient.get('/academic-management/options');
  },
  list(resource, params) {
    return axiosClient.get(endpointFor(resource), { params });
  },
  show(resource, id) {
    return axiosClient.get(`${endpointFor(resource)}/${id}`);
  },
  create(resource, payload) {
    return axiosClient.post(endpointFor(resource), payload);
  },
  update(resource, id, payload) {
    return axiosClient.put(`${endpointFor(resource)}/${id}`, payload);
  },
  destroy(resource, id) {
    return axiosClient.delete(`${endpointFor(resource)}/${id}`);
  },
  updateAcademicYearStatus(id, payload) {
    return axiosClient.patch(`/academic-management/academic-years/${id}/status`, payload);
  },
};
