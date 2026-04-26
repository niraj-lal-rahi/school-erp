import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { communicationApi } from '../services/communicationApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function extractCollection(payload) {
  if (Array.isArray(payload?.data)) {
    return payload.data;
  }

  if (Array.isArray(payload)) {
    return payload;
  }

  return [];
}

function normalizePaginated(payload) {
  return {
    items: payload?.data || [],
    pagination: {
      page: payload?.current_page || 1,
      totalPages: payload?.last_page || 1,
      total: payload?.total || 0,
    },
  };
}

function upsertItem(items, payload) {
  const exists = items.some((item) => item.id === payload.id);
  if (!exists) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

function createMutationThunk(type, request, fallback) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createUpdateThunk(type, request, fallback) {
  return createAsyncThunk(type, async ({ id, payload }, thunkApi) => {
    try {
      const response = await request(id, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createDeleteThunk(type, request, fallback) {
  return createAsyncThunk(type, async (id, thunkApi) => {
    try {
      await request(id);
      return id;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchCommunicationReferenceData = createAsyncThunk(
  'communication/fetchReferenceData',
  async (_, thunkApi) => {
    try {
      const [academicYears, classes, sections, students, guardians, staffMembers, groups, templates] = await Promise.all([
        communicationApi.getAcademicYears(),
        communicationApi.getClasses(),
        communicationApi.getSections(),
        communicationApi.getStudents({ per_page: 100 }),
        communicationApi.getGuardians({ per_page: 100 }),
        communicationApi.getStaff({ per_page: 100 }),
        communicationApi.getGroups({ per_page: 100 }),
        communicationApi.getTemplates({ per_page: 100 }),
      ]);

      return {
        academicYears: extractCollection(academicYears.data.data),
        classes: extractCollection(classes.data.data),
        sections: extractCollection(sections.data.data),
        students: extractCollection(students.data.data?.data || students.data.data),
        guardians: extractCollection(guardians.data.data?.data || guardians.data.data),
        staffMembers: extractCollection(staffMembers.data.data?.data || staffMembers.data.data),
        groups: extractCollection(groups.data.data?.data || groups.data.data),
        templates: extractCollection(templates.data.data?.data || templates.data.data),
      };
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, 'Failed to load communication reference data.'));
    }
  },
);

export const fetchAnnouncements = createAsyncThunk('communication/fetchAnnouncements', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getAnnouncements(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load announcements.'));
  }
});

export const createAnnouncement = createMutationThunk('communication/createAnnouncement', communicationApi.createAnnouncement, 'Failed to create announcement.');
export const updateAnnouncement = createUpdateThunk('communication/updateAnnouncement', communicationApi.updateAnnouncement, 'Failed to update announcement.');
export const deleteAnnouncement = createDeleteThunk('communication/deleteAnnouncement', communicationApi.deleteAnnouncement, 'Failed to delete announcement.');
export const publishAnnouncement = createAsyncThunk('communication/publishAnnouncement', async ({ id, payload }, thunkApi) => {
  try {
    const response = await communicationApi.publishAnnouncement(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to publish announcement.'));
  }
});
export const cancelAnnouncement = createAsyncThunk('communication/cancelAnnouncement', async (id, thunkApi) => {
  try {
    const response = await communicationApi.cancelAnnouncement(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel announcement.'));
  }
});
export const fetchAnnouncementRecipients = createAsyncThunk('communication/fetchAnnouncementRecipients', async (id, thunkApi) => {
  try {
    const response = await communicationApi.getAnnouncementRecipients(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load announcement recipients.'));
  }
});

export const fetchTemplates = createAsyncThunk('communication/fetchTemplates', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getTemplates(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load templates.'));
  }
});
export const createTemplate = createMutationThunk('communication/createTemplate', communicationApi.createTemplate, 'Failed to create template.');
export const updateTemplate = createUpdateThunk('communication/updateTemplate', communicationApi.updateTemplate, 'Failed to update template.');
export const deleteTemplate = createDeleteThunk('communication/deleteTemplate', communicationApi.deleteTemplate, 'Failed to delete template.');

export const fetchMessages = createAsyncThunk('communication/fetchMessages', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getMessages(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load messages.'));
  }
});
export const createMessage = createMutationThunk('communication/createMessage', communicationApi.createMessage, 'Failed to send message.');
export const deleteMessage = createDeleteThunk('communication/deleteMessage', communicationApi.deleteMessage, 'Failed to delete message.');
export const markMessageRead = createAsyncThunk('communication/markMessageRead', async (id, thunkApi) => {
  try {
    const response = await communicationApi.markMessageRead(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark message as read.'));
  }
});
export const archiveMessage = createAsyncThunk('communication/archiveMessage', async (id, thunkApi) => {
  try {
    const response = await communicationApi.archiveMessage(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to archive message.'));
  }
});

export const fetchConversations = createAsyncThunk('communication/fetchConversations', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getConversations(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load conversations.'));
  }
});
export const createConversation = createMutationThunk('communication/createConversation', communicationApi.createConversation, 'Failed to create conversation.');
export const updateConversation = createUpdateThunk('communication/updateConversation', communicationApi.updateConversation, 'Failed to update conversation.');
export const deleteConversation = createDeleteThunk('communication/deleteConversation', communicationApi.deleteConversation, 'Failed to delete conversation.');
export const fetchConversationMessages = createAsyncThunk('communication/fetchConversationMessages', async (id, thunkApi) => {
  try {
    const response = await communicationApi.getConversationMessages(id);
    return { id, items: extractCollection(response.data.data) };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load conversation messages.'));
  }
});
export const addConversationParticipant = createAsyncThunk('communication/addConversationParticipant', async ({ id, payload }, thunkApi) => {
  try {
    const response = await communicationApi.addConversationParticipant(id, payload);
    return { id, participant: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to add participant.'));
  }
});
export const removeConversationParticipant = createAsyncThunk('communication/removeConversationParticipant', async ({ id, participantId }, thunkApi) => {
  try {
    await communicationApi.removeConversationParticipant(id, participantId);
    return { id, participantId };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to remove participant.'));
  }
});

export const fetchNotifications = createAsyncThunk('communication/fetchNotifications', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getNotifications(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load notifications.'));
  }
});
export const markNotificationRead = createAsyncThunk('communication/markNotificationRead', async (id, thunkApi) => {
  try {
    const response = await communicationApi.markNotificationRead(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark notification as read.'));
  }
});

export const fetchScheduledMessages = createAsyncThunk('communication/fetchScheduledMessages', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getScheduledMessages(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load scheduled messages.'));
  }
});
export const createScheduledMessage = createMutationThunk('communication/createScheduledMessage', communicationApi.createScheduledMessage, 'Failed to create scheduled message.');
export const updateScheduledMessage = createUpdateThunk('communication/updateScheduledMessage', communicationApi.updateScheduledMessage, 'Failed to update scheduled message.');
export const deleteScheduledMessage = createDeleteThunk('communication/deleteScheduledMessage', communicationApi.deleteScheduledMessage, 'Failed to delete scheduled message.');
export const cancelScheduledMessage = createAsyncThunk('communication/cancelScheduledMessage', async (id, thunkApi) => {
  try {
    const response = await communicationApi.cancelScheduledMessage(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel scheduled message.'));
  }
});
export const processDueScheduledMessages = createAsyncThunk('communication/processDueScheduledMessages', async (_, thunkApi) => {
  try {
    const response = await communicationApi.processDueScheduledMessages();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to process due scheduled messages.'));
  }
});

export const fetchGroups = createAsyncThunk('communication/fetchGroups', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getGroups(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load communication groups.'));
  }
});
export const createGroup = createMutationThunk('communication/createGroup', communicationApi.createGroup, 'Failed to create communication group.');
export const updateGroup = createUpdateThunk('communication/updateGroup', communicationApi.updateGroup, 'Failed to update communication group.');
export const deleteGroup = createDeleteThunk('communication/deleteGroup', communicationApi.deleteGroup, 'Failed to delete communication group.');
export const addGroupMember = createAsyncThunk('communication/addGroupMember', async ({ id, payload }, thunkApi) => {
  try {
    const response = await communicationApi.addGroupMember(id, payload);
    return { id, member: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to add group member.'));
  }
});
export const removeGroupMember = createAsyncThunk('communication/removeGroupMember', async ({ id, memberId }, thunkApi) => {
  try {
    await communicationApi.removeGroupMember(id, memberId);
    return { id, memberId };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to remove group member.'));
  }
});

export const fetchPreferences = createAsyncThunk('communication/fetchPreferences', async (params = {}, thunkApi) => {
  try {
    const response = await communicationApi.getPreferences(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load notification preferences.'));
  }
});
export const updatePreference = createUpdateThunk('communication/updatePreference', communicationApi.updatePreference, 'Failed to update notification preference.');

export const fetchCommunicationReports = createAsyncThunk('communication/fetchReports', async (params = {}, thunkApi) => {
  try {
    const [delivery, engagement, volume] = await Promise.all([
      communicationApi.getNotificationDeliveryReport(params),
      communicationApi.getAnnouncementEngagementReport(params),
      communicationApi.getMessageVolumeReport(params),
    ]);

    return {
      delivery: delivery.data.data,
      engagement: engagement.data.data,
      volume: volume.data.data,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load communication reports.'));
  }
});

const initialState = {
  announcements: [],
  templates: [],
  messages: [],
  conversations: [],
  notifications: [],
  scheduledMessages: [],
  groups: [],
  preferences: [],
  announcementRecipients: [],
  conversationMessages: {},
  reports: null,
  referenceData: {
    academicYears: [],
    classes: [],
    sections: [],
    students: [],
    guardians: [],
    staffMembers: [],
    groups: [],
    templates: [],
  },
  loading: false,
  saving: false,
  error: null,
  announcementsPagination: { page: 1, totalPages: 1, total: 0 },
  templatesPagination: { page: 1, totalPages: 1, total: 0 },
  messagesPagination: { page: 1, totalPages: 1, total: 0 },
  conversationsPagination: { page: 1, totalPages: 1, total: 0 },
  notificationsPagination: { page: 1, totalPages: 1, total: 0 },
  scheduledMessagesPagination: { page: 1, totalPages: 1, total: 0 },
  groupsPagination: { page: 1, totalPages: 1, total: 0 },
  preferencesPagination: { page: 1, totalPages: 1, total: 0 },
};

const communicationSlice = createSlice({
  name: 'communication',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchCommunicationReferenceData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCommunicationReferenceData.fulfilled, (state, action) => {
        state.loading = false;
        state.referenceData = action.payload;
      })
      .addCase(fetchCommunicationReferenceData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchAnnouncementRecipients.fulfilled, (state, action) => {
        state.announcementRecipients = extractCollection(action.payload);
      })
      .addCase(fetchConversationMessages.fulfilled, (state, action) => {
        state.conversationMessages[action.payload.id] = action.payload.items;
      })
      .addCase(fetchCommunicationReports.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCommunicationReports.fulfilled, (state, action) => {
        state.loading = false;
        state.reports = action.payload;
      })
      .addCase(fetchCommunicationReports.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchAnnouncements, 'announcements', 'announcementsPagination'],
      [fetchTemplates, 'templates', 'templatesPagination'],
      [fetchMessages, 'messages', 'messagesPagination'],
      [fetchConversations, 'conversations', 'conversationsPagination'],
      [fetchNotifications, 'notifications', 'notificationsPagination'],
      [fetchScheduledMessages, 'scheduledMessages', 'scheduledMessagesPagination'],
      [fetchGroups, 'groups', 'groupsPagination'],
      [fetchPreferences, 'preferences', 'preferencesPagination'],
    ].forEach(([thunk, key, paginationKey]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          state[key] = action.payload.items;
          state[paginationKey] = action.payload.pagination;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      [createAnnouncement, 'announcements'],
      [updateAnnouncement, 'announcements'],
      [publishAnnouncement, 'announcements'],
      [cancelAnnouncement, 'announcements'],
      [createTemplate, 'templates'],
      [updateTemplate, 'templates'],
      [createMessage, 'messages'],
      [markMessageRead, 'messages'],
      [archiveMessage, 'messages'],
      [createConversation, 'conversations'],
      [updateConversation, 'conversations'],
      [markNotificationRead, 'notifications'],
      [createScheduledMessage, 'scheduledMessages'],
      [updateScheduledMessage, 'scheduledMessages'],
      [cancelScheduledMessage, 'scheduledMessages'],
      [createGroup, 'groups'],
      [updateGroup, 'groups'],
      [updatePreference, 'preferences'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [deleteAnnouncement, deleteTemplate, deleteMessage, deleteConversation, deleteScheduledMessage, deleteGroup].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(deleteAnnouncement.fulfilled, (state, action) => {
        state.saving = false;
        state.announcements = state.announcements.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteTemplate.fulfilled, (state, action) => {
        state.saving = false;
        state.templates = state.templates.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteMessage.fulfilled, (state, action) => {
        state.saving = false;
        state.messages = state.messages.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteConversation.fulfilled, (state, action) => {
        state.saving = false;
        state.conversations = state.conversations.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteScheduledMessage.fulfilled, (state, action) => {
        state.saving = false;
        state.scheduledMessages = state.scheduledMessages.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteGroup.fulfilled, (state, action) => {
        state.saving = false;
        state.groups = state.groups.filter((item) => item.id !== action.payload);
      })
      .addCase(addConversationParticipant.fulfilled, (state, action) => {
        state.saving = false;
        state.conversations = state.conversations.map((item) => {
          if (item.id !== action.payload.id) {
            return item;
          }

          return {
            ...item,
            participants: [...(item.participants || []), action.payload.participant],
          };
        });
      })
      .addCase(removeConversationParticipant.fulfilled, (state, action) => {
        state.saving = false;
        state.conversations = state.conversations.map((item) => {
          if (item.id !== action.payload.id) {
            return item;
          }

          return {
            ...item,
            participants: (item.participants || []).filter((participant) => participant.id !== action.payload.participantId),
          };
        });
      })
      .addCase(addGroupMember.fulfilled, (state, action) => {
        state.saving = false;
        state.groups = state.groups.map((item) => {
          if (item.id !== action.payload.id) {
            return item;
          }

          return {
            ...item,
            members: [...(item.members || []), action.payload.member],
          };
        });
      })
      .addCase(removeGroupMember.fulfilled, (state, action) => {
        state.saving = false;
        state.groups = state.groups.map((item) => {
          if (item.id !== action.payload.id) {
            return item;
          }

          return {
            ...item,
            members: (item.members || []).filter((member) => member.id !== action.payload.memberId),
          };
        });
      })
      .addCase(processDueScheduledMessages.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(processDueScheduledMessages.fulfilled, (state) => {
        state.saving = false;
      })
      .addCase(processDueScheduledMessages.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default communicationSlice.reducer;
