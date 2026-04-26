export const channelOptions = [
  { value: 'email', label: 'Email' },
  { value: 'sms', label: 'SMS' },
  { value: 'push', label: 'Push' },
  { value: 'in_app', label: 'In-App' },
  { value: 'multi', label: 'Multi-Channel' },
];

export const singleChannelOptions = channelOptions.filter((option) => option.value !== 'multi');

export const statusOptions = {
  announcement: [
    { value: 'draft', label: 'Draft' },
    { value: 'scheduled', label: 'Scheduled' },
    { value: 'published', label: 'Published' },
    { value: 'expired', label: 'Expired' },
    { value: 'cancelled', label: 'Cancelled' },
  ],
  message: [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'read', label: 'Read' },
    { value: 'failed', label: 'Failed' },
    { value: 'archived', label: 'Archived' },
  ],
  notification: [
    { value: 'pending', label: 'Pending' },
    { value: 'sent', label: 'Sent' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'failed', label: 'Failed' },
    { value: 'bounced', label: 'Bounced' },
    { value: 'read', label: 'Read' },
  ],
  scheduled: [
    { value: 'pending', label: 'Pending' },
    { value: 'processing', label: 'Processing' },
    { value: 'sent', label: 'Sent' },
    { value: 'failed', label: 'Failed' },
    { value: 'cancelled', label: 'Cancelled' },
  ],
  group: [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
  ],
  template: [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
  ],
};

export const audienceOptions = [
  { value: 'all', label: 'All' },
  { value: 'students', label: 'Students' },
  { value: 'parents', label: 'Parents' },
  { value: 'staff', label: 'Staff' },
  { value: 'teachers', label: 'Teachers' },
  { value: 'class', label: 'Class' },
  { value: 'section', label: 'Section' },
  { value: 'individual', label: 'Individual' },
];

export const announcementTypeOptions = [
  { value: 'general', label: 'General' },
  { value: 'academic', label: 'Academic' },
  { value: 'fee', label: 'Fee' },
  { value: 'attendance', label: 'Attendance' },
  { value: 'exam', label: 'Exam' },
  { value: 'event', label: 'Event' },
  { value: 'emergency', label: 'Emergency' },
];

export const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'normal', label: 'Normal' },
  { value: 'high', label: 'High' },
  { value: 'urgent', label: 'Urgent' },
];

export const templateTypeOptions = singleChannelOptions;

export const conversationTypeOptions = [
  { value: 'direct', label: 'Direct' },
  { value: 'group', label: 'Group' },
  { value: 'parent_teacher', label: 'Parent-Teacher' },
  { value: 'staff', label: 'Staff' },
  { value: 'class_group', label: 'Class Group' },
];

export const messageTypeOptions = [
  { value: 'direct', label: 'Direct' },
  { value: 'group', label: 'Group' },
  { value: 'system', label: 'System' },
  { value: 'notification', label: 'Notification' },
];

export const recipientTypeOptions = [
  { value: 'student', label: 'Student' },
  { value: 'guardian', label: 'Parent / Guardian' },
  { value: 'staff', label: 'Staff' },
  { value: 'group', label: 'Communication Group' },
];

export const participantTypeOptions = [
  { value: 'student', label: 'Student' },
  { value: 'guardian', label: 'Parent / Guardian' },
  { value: 'staff', label: 'Staff' },
  { value: 'user', label: 'User' },
];

export const groupTypeOptions = [
  { value: 'class', label: 'Class' },
  { value: 'section', label: 'Section' },
  { value: 'staff', label: 'Staff' },
  { value: 'custom', label: 'Custom' },
];
