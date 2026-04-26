import { Alert } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationComposerCard } from '../components/CommunicationComposerCard';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { createMessage, fetchCommunicationReferenceData } from '../store/communicationSlice';

const initialForm = {
  recipient_type: 'student',
  recipient_id: '',
  subject: '',
  body: '',
  message_type: 'direct',
  priority: 'normal',
  channels: ['in_app'],
};

export function SendMessagePage() {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { referenceData, saving, error } = useAppSelector((state) => state.communication);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchCommunicationReferenceData());
  }, [dispatch]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(createMessage(form));
    if (!result.error) {
      navigate('/communication/messages');
    }
  }

  return (
    <CommunicationPageShell
      title="Send Message"
      description="Compose direct, operational, or group communication with channel and priority controls in one fast workflow."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <CommunicationComposerCard
        title="Compose Message"
        description="Use direct delivery for one recipient or switch to a communication group for broader distribution."
        form={form}
        setForm={setForm}
        onSubmit={handleSubmit}
        submitting={saving}
        groups={referenceData.groups}
        students={referenceData.students}
        guardians={referenceData.guardians}
        staffMembers={referenceData.staffMembers}
      />
    </CommunicationPageShell>
  );
}
