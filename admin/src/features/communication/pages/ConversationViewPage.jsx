import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { useCommunicationAccess } from '../hooks/useCommunicationAccess';
import {
  addConversationParticipant,
  fetchCommunicationReferenceData,
  fetchConversationMessages,
  fetchConversations,
  removeConversationParticipant,
} from '../store/communicationSlice';
import { participantTypeOptions, statusOptions } from '../types/options';

export function ConversationViewPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useCommunicationAccess();
  const { conversations, conversationMessages, conversationsPagination, referenceData, loading, error } = useAppSelector((state) => state.communication);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [selectedConversationId, setSelectedConversationId] = useState(null);
  const [participantForm, setParticipantForm] = useState({ participant_type: 'staff', participant_id: '' });

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchConversations({
      page,
      per_page: 10,
      search,
      status: statusFilter || undefined,
    }));
  }, [dispatch, page, search, statusFilter]);

  const selectedConversation = useMemo(
    () => conversations.find((item) => item.id === selectedConversationId) || null,
    [conversations, selectedConversationId],
  );

  const participants = selectedConversation?.participants || [];

  const participantChoices = useMemo(() => {
    if (participantForm.participant_type === 'student') {
      return referenceData.students;
    }
    if (participantForm.participant_type === 'guardian') {
      return referenceData.guardians;
    }
    return referenceData.staffMembers;
  }, [participantForm.participant_type, referenceData]);

  useEffect(() => {
    if (selectedConversationId) {
      dispatch(fetchConversationMessages(selectedConversationId));
    }
  }, [dispatch, selectedConversationId]);

  return (
    <CommunicationPageShell
      title="Conversation View"
      description="Follow message threads, participant membership, and conversation health in one focused workspace."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Conversations</Typography>
              <TextField label="Search" value={search} onChange={(event) => setSearch(event.target.value)} />
              <TextField select label="Status" value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)}>
                <MenuItem value="">All</MenuItem>
                {statusOptions.group.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
              {conversations.map((conversation) => (
                <Paper
                  key={conversation.id}
                  elevation={0}
                  sx={{
                    p: 2,
                    border: '1px solid rgba(20,33,61,0.08)',
                    cursor: 'pointer',
                    backgroundColor: selectedConversationId === conversation.id ? 'rgba(11,110,79,0.08)' : 'transparent',
                  }}
                  onClick={() => setSelectedConversationId(conversation.id)}
                >
                  <Stack spacing={0.5}>
                    <Typography fontWeight={600}>{conversation.title || `Conversation #${conversation.id}`}</Typography>
                    <Typography variant="body2" color="text.secondary">
                      {conversation.conversation_type}
                    </Typography>
                    <CommunicationStatusBadge value={conversation.status} />
                  </Stack>
                </Paper>
              ))}
              <Typography variant="caption" color="text.secondary">
                Page {page} of {conversationsPagination.totalPages || 1}
              </Typography>
              <Stack direction="row" spacing={1}>
                <Button disabled={page <= 1} onClick={() => setPage((current) => current - 1)}>Previous</Button>
                <Button disabled={page >= (conversationsPagination.totalPages || 1)} onClick={() => setPage((current) => current + 1)}>Next</Button>
              </Stack>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 7 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            {selectedConversation ? (
              <Stack spacing={2.5}>
                <Stack spacing={0.5}>
                  <Typography variant="h6">{selectedConversation.title || `Conversation #${selectedConversation.id}`}</Typography>
                  <Typography variant="body2" color="text.secondary">
                    {selectedConversation.conversation_type}
                  </Typography>
                </Stack>

                <Stack spacing={1}>
                  <Typography variant="subtitle1">Participants</Typography>
                  {participants.length ? participants.map((participant) => (
                    <Stack key={participant.id} direction="row" justifyContent="space-between" alignItems="center">
                      <Typography variant="body2">
                        {participant.participant_type} #{participant.participant_id}
                      </Typography>
                      {canManage ? (
                        <Button
                          size="small"
                          color="error"
                          onClick={() => dispatch(removeConversationParticipant({ id: selectedConversation.id, participantId: participant.id }))}
                        >
                          Remove
                        </Button>
                      ) : null}
                    </Stack>
                  )) : (
                    <Typography variant="body2" color="text.secondary">No participants yet.</Typography>
                  )}
                </Stack>

                {canManage ? (
                  <Grid container spacing={2}>
                    <Grid size={{ xs: 12, md: 4 }}>
                      <TextField
                        select
                        fullWidth
                        label="Participant Type"
                        value={participantForm.participant_type}
                        onChange={(event) => setParticipantForm((current) => ({ ...current, participant_type: event.target.value, participant_id: '' }))}
                      >
                        {participantTypeOptions
                          .filter((option) => option.value !== 'user')
                          .map((option) => (
                            <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                          ))}
                      </TextField>
                    </Grid>
                    <Grid size={{ xs: 12, md: 5 }}>
                      <TextField
                        select
                        fullWidth
                        label="Participant"
                        value={participantForm.participant_id}
                        onChange={(event) => setParticipantForm((current) => ({ ...current, participant_id: event.target.value }))}
                      >
                        <MenuItem value="">Select participant</MenuItem>
                        {participantChoices.map((item) => (
                          <MenuItem key={item.id} value={item.id}>{item.full_name || item.name || `#${item.id}`}</MenuItem>
                        ))}
                      </TextField>
                    </Grid>
                    <Grid size={{ xs: 12, md: 3 }}>
                      <Button
                        fullWidth
                        variant="contained"
                        sx={{ height: '100%' }}
                        onClick={() => dispatch(addConversationParticipant({
                          id: selectedConversation.id,
                          payload: participantForm,
                        }))}
                      >
                        Add Participant
                      </Button>
                    </Grid>
                  </Grid>
                ) : null}

                <Stack spacing={1}>
                  <Typography variant="subtitle1">Messages</Typography>
                  {(conversationMessages[selectedConversation.id] || []).length ? (
                    (conversationMessages[selectedConversation.id] || []).map((message) => (
                      <Paper key={message.id} elevation={0} sx={{ p: 2, border: '1px solid rgba(20,33,61,0.08)' }}>
                        <Stack spacing={0.5}>
                          <Typography fontWeight={600}>{message.subject || 'No subject'}</Typography>
                          <Typography variant="body2" color="text.secondary">{message.body}</Typography>
                          <Stack direction="row" spacing={1} alignItems="center">
                            <CommunicationStatusBadge value={message.status} />
                            <Typography variant="caption" color="text.secondary">{message.sent_at || message.created_at}</Typography>
                          </Stack>
                        </Stack>
                      </Paper>
                    ))
                  ) : (
                    <Typography variant="body2" color="text.secondary">
                      No messages loaded for this conversation yet.
                    </Typography>
                  )}
                </Stack>
              </Stack>
            ) : (
              <Typography color="text.secondary">Select a conversation to inspect its thread and participants.</Typography>
            )}
          </Paper>
        </Grid>
      </Grid>
    </CommunicationPageShell>
  );
}
