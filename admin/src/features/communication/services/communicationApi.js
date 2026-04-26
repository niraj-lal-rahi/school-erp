import { axiosClient } from '../../../api/axiosClient';

const basePath = '/communication';

export const communicationApi = {
  getChannels: (params) => axiosClient.get(`${basePath}/channels`, { params }),

  getTemplates: (params) => axiosClient.get(`${basePath}/templates`, { params }),
  createTemplate: (payload) => axiosClient.post(`${basePath}/templates`, payload),
  updateTemplate: (id, payload) => axiosClient.put(`${basePath}/templates/${id}`, payload),
  deleteTemplate: (id) => axiosClient.delete(`${basePath}/templates/${id}`),

  getAnnouncements: (params) => axiosClient.get(`${basePath}/announcements`, { params }),
  createAnnouncement: (payload) => axiosClient.post(`${basePath}/announcements`, payload),
  updateAnnouncement: (id, payload) => axiosClient.put(`${basePath}/announcements/${id}`, payload),
  deleteAnnouncement: (id) => axiosClient.delete(`${basePath}/announcements/${id}`),
  publishAnnouncement: (id, payload = {}) => axiosClient.post(`${basePath}/announcements/${id}/publish`, payload),
  cancelAnnouncement: (id) => axiosClient.post(`${basePath}/announcements/${id}/cancel`),
  getAnnouncementRecipients: (id) => axiosClient.get(`${basePath}/announcements/${id}/recipients`),

  getMessages: (params) => axiosClient.get(`${basePath}/messages`, { params }),
  createMessage: (payload) => axiosClient.post(`${basePath}/messages`, payload),
  deleteMessage: (id) => axiosClient.delete(`${basePath}/messages/${id}`),
  markMessageRead: (id) => axiosClient.post(`${basePath}/messages/${id}/mark-read`),
  archiveMessage: (id) => axiosClient.post(`${basePath}/messages/${id}/archive`),

  getConversations: (params) => axiosClient.get(`${basePath}/conversations`, { params }),
  createConversation: (payload) => axiosClient.post(`${basePath}/conversations`, payload),
  updateConversation: (id, payload) => axiosClient.put(`${basePath}/conversations/${id}`, payload),
  deleteConversation: (id) => axiosClient.delete(`${basePath}/conversations/${id}`),
  getConversationMessages: (id) => axiosClient.get(`${basePath}/conversations/${id}/messages`),
  addConversationParticipant: (id, payload) => axiosClient.post(`${basePath}/conversations/${id}/participants`, payload),
  removeConversationParticipant: (id, participantId) => axiosClient.delete(`${basePath}/conversations/${id}/participants/${participantId}`),

  getNotifications: (params) => axiosClient.get(`${basePath}/notifications`, { params }),
  markNotificationRead: (id) => axiosClient.post(`${basePath}/notifications/${id}/mark-read`),
  getNotificationDeliveryReport: (params) => axiosClient.get(`${basePath}/reports/notification-delivery`, { params }),
  getAnnouncementEngagementReport: (params) => axiosClient.get(`${basePath}/reports/announcement-engagement`, { params }),
  getMessageVolumeReport: (params) => axiosClient.get(`${basePath}/reports/message-volume`, { params }),

  getScheduledMessages: (params) => axiosClient.get(`${basePath}/scheduled-messages`, { params }),
  createScheduledMessage: (payload) => axiosClient.post(`${basePath}/scheduled-messages`, payload),
  updateScheduledMessage: (id, payload) => axiosClient.put(`${basePath}/scheduled-messages/${id}`, payload),
  deleteScheduledMessage: (id) => axiosClient.delete(`${basePath}/scheduled-messages/${id}`),
  cancelScheduledMessage: (id) => axiosClient.post(`${basePath}/scheduled-messages/${id}/cancel`),
  processDueScheduledMessages: () => axiosClient.post(`${basePath}/scheduled-messages/process-due`),

  getGroups: (params) => axiosClient.get(`${basePath}/groups`, { params }),
  createGroup: (payload) => axiosClient.post(`${basePath}/groups`, payload),
  updateGroup: (id, payload) => axiosClient.put(`${basePath}/groups/${id}`, payload),
  deleteGroup: (id) => axiosClient.delete(`${basePath}/groups/${id}`),
  addGroupMember: (id, payload) => axiosClient.post(`${basePath}/groups/${id}/members`, payload),
  removeGroupMember: (id, memberId) => axiosClient.delete(`${basePath}/groups/${id}/members/${memberId}`),

  getPreferences: (params) => axiosClient.get(`${basePath}/preferences`, { params }),
  updatePreference: (id, payload) => axiosClient.put(`${basePath}/preferences/${id}`, payload),

  getAcademicYears: () => axiosClient.get('/academic-years'),
  getClasses: () => axiosClient.get('/classes'),
  getSections: () => axiosClient.get('/sections'),
  getStudents: (params) => axiosClient.get('/students', { params }),
  getGuardians: (params) => axiosClient.get('/guardians', { params }),
  getStaff: (params) => axiosClient.get('/hr/staff', { params }),
};
