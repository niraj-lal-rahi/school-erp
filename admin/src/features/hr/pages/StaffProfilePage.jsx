import { Alert, CircularProgress, Grid, Stack, Tab, Tabs } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  createStaffBankDetail,
  createStaffExperience,
  createStaffNote,
  createStaffQualification,
  deleteStaffBankDetail,
  deleteStaffDocument,
  createStaffEmergencyContact,
  deleteStaffEmergencyContact,
  deleteStaffExperience,
  deleteStaffNote,
  deleteStaffQualification,
  fetchHrOptions,
  fetchHrResource,
  fetchStaffProfile,
  runStaffLifecycleAction,
  uploadStaffDocument,
  createHrResource,
  updateStaffBankDetail,
  updateStaffExperience,
  updateStaffQualification,
} from '../store/hrSlice';
import { StaffAttendanceTab } from '../components/StaffAttendanceTab';
import { StaffBankDetailsTab } from '../components/StaffBankDetailsTab';
import { StaffDocumentsTab } from '../components/StaffDocumentsTab';
import { StaffExperienceTab } from '../components/StaffExperienceTab';
import { StaffLeaveTab } from '../components/StaffLeaveTab';
import { StaffNotesTab } from '../components/StaffNotesTab';
import { StaffPayrollTab } from '../components/StaffPayrollTab';
import { StaffProfileOverviewPanel } from '../components/StaffProfileOverviewPanel';
import { StaffQualificationsTab } from '../components/StaffQualificationsTab';
import { StaffStatusHistoryTab } from '../components/StaffStatusHistoryTab';

export function StaffProfilePage() {
  const { staffId } = useParams();
  const dispatch = useAppDispatch();
  const { currentStaff, profileLoading, profileSaving, profileError, options, resources } = useAppSelector((state) => state.hr);
  const [tab, setTab] = useState('overview');

  useEffect(() => {
    dispatch(fetchStaffProfile(staffId));
    dispatch(fetchHrOptions());
    dispatch(fetchHrResource({ resource: 'salaryStructures', params: { staff_id: staffId } }));
    dispatch(fetchHrResource({ resource: 'payslips', params: { staff_id: staffId } }));
  }, [dispatch, staffId]);

  const leaveApplications = useMemo(
    () => (resources.leaveApplications.items || []).filter((item) => String(item.staff_id) === String(staffId)),
    [resources.leaveApplications.items, staffId],
  );
  const leaveBalances = useMemo(
    () => (resources.leaveBalances.items || []).filter((item) => String(item.staff_id) === String(staffId)),
    [resources.leaveBalances.items, staffId],
  );

  useEffect(() => {
    dispatch(fetchHrResource({ resource: 'leaveApplications', params: { staff_id: staffId } }));
    dispatch(fetchHrResource({ resource: 'leaveBalances', params: { staff_id: staffId } }));
  }, [dispatch, staffId]);

  async function refreshProfile() {
    return dispatch(fetchStaffProfile(staffId));
  }

  if (profileLoading || !currentStaff) {
    return (
      <Stack alignItems="center" py={8}>
        <CircularProgress />
      </Stack>
    );
  }

  return (
    <Stack spacing={3}>
      <Alert severity="info">
        Staff profiles bring together HR operations and academic readiness in one place, so this view is the best place to verify whether a teacher or employee record is truly complete.
      </Alert>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12 }}>
          <Tabs value={tab} onChange={(_, nextValue) => setTab(nextValue)} variant="scrollable" allowScrollButtonsMobile>
            <Tab value="overview" label="Overview" />
            <Tab value="documents" label="Documents" />
            <Tab value="attendance" label="Attendance" />
            <Tab value="leave" label="Leave" />
            <Tab value="payroll" label="Payroll" />
            <Tab value="bank" label="Bank Details" />
            <Tab value="qualifications" label="Qualifications" />
            <Tab value="experience" label="Experience" />
            <Tab value="notes" label="Notes" />
            <Tab value="status-history" label="Status History" />
          </Tabs>
        </Grid>

        {tab === 'overview' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffProfileOverviewPanel staff={currentStaff} saving={false} error={profileError} onLifecycleSubmit={() => {}} readOnly />}>
              <StaffProfileOverviewPanel
                staff={currentStaff}
                saving={profileSaving}
                error={profileError}
                onLifecycleSubmit={async (action, payload) => {
                  const result = await dispatch(runStaffLifecycleAction({ staffId, action, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onCreateEmergencyContact={async (payload) => {
                  const result = await dispatch(createStaffEmergencyContact({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDeleteEmergencyContact={async (contactId) => {
                  const result = await dispatch(deleteStaffEmergencyContact(contactId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'documents' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffDocumentsTab documents={currentStaff.documents || []} saving={false} error={profileError} onUpload={() => {}} onDelete={() => {}} />}>
              <StaffDocumentsTab
                documents={currentStaff.documents || []}
                saving={profileSaving}
                error={profileError}
                onUpload={async (payload) => {
                  const result = await dispatch(uploadStaffDocument({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDelete={async (documentId) => {
                  const result = await dispatch(deleteStaffDocument(documentId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'attendance' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffAttendanceTab items={currentStaff.attendance_records || []} saving={false} error={profileError} onCreate={() => {}} />}>
              <StaffAttendanceTab
                items={currentStaff.attendance_records || []}
                saving={resources.attendance.saving}
                error={resources.attendance.error}
                onCreate={async (payload) => {
                  const result = await dispatch(createHrResource({ resource: 'attendance', payload: { ...payload, staff_id: currentStaff.id } }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'leave' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffLeaveTab applications={leaveApplications} balances={leaveBalances} leaveTypes={options.leaveTypes || []} saving={false} error={resources.leaveApplications.error} onCreate={() => {}} />}>
              <StaffLeaveTab
                applications={leaveApplications}
                balances={leaveBalances}
                leaveTypes={options.leaveTypes || []}
                saving={resources.leaveApplications.saving}
                error={resources.leaveApplications.error}
                onCreate={async (payload) => {
                  const result = await dispatch(createHrResource({ resource: 'leaveApplications', payload: { ...payload, staff_id: currentStaff.id } }));
                  if (!result.error) {
                    dispatch(fetchHrResource({ resource: 'leaveApplications', params: { staff_id: staffId } }));
                    dispatch(fetchHrResource({ resource: 'leaveBalances', params: { staff_id: staffId } }));
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'payroll' ? (
          <Grid size={{ xs: 12 }}>
            <StaffPayrollTab
              salaryStructures={resources.salaryStructures.items || []}
              payslips={resources.payslips.items || []}
            />
          </Grid>
        ) : null}

        {tab === 'bank' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffBankDetailsTab items={currentStaff.bank_details || []} saving={false} error={profileError} onCreate={() => {}} onUpdate={() => {}} onDelete={() => {}} />}>
              <StaffBankDetailsTab
                items={currentStaff.bank_details || []}
                saving={profileSaving}
                error={profileError}
                onCreate={async (payload) => {
                  const result = await dispatch(createStaffBankDetail({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onUpdate={async (bankDetailId, payload) => {
                  const result = await dispatch(updateStaffBankDetail({ bankDetailId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDelete={async (bankDetailId) => {
                  const result = await dispatch(deleteStaffBankDetail(bankDetailId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'qualifications' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffQualificationsTab items={currentStaff.qualifications || []} saving={false} error={profileError} onCreate={() => {}} onUpdate={() => {}} onDelete={() => {}} />}>
              <StaffQualificationsTab
                items={currentStaff.qualifications || []}
                saving={profileSaving}
                error={profileError}
                onCreate={async (payload) => {
                  const result = await dispatch(createStaffQualification({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onUpdate={async (qualificationId, payload) => {
                  const result = await dispatch(updateStaffQualification({ qualificationId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDelete={async (qualificationId) => {
                  const result = await dispatch(deleteStaffQualification(qualificationId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'experience' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffExperienceTab items={currentStaff.work_experiences || []} saving={false} error={profileError} onCreate={() => {}} onUpdate={() => {}} onDelete={() => {}} />}>
              <StaffExperienceTab
                items={currentStaff.work_experiences || []}
                saving={profileSaving}
                error={profileError}
                onCreate={async (payload) => {
                  const result = await dispatch(createStaffExperience({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onUpdate={async (experienceId, payload) => {
                  const result = await dispatch(updateStaffExperience({ experienceId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDelete={async (experienceId) => {
                  const result = await dispatch(deleteStaffExperience(experienceId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'notes' ? (
          <Grid size={{ xs: 12 }}>
            <PermissionGate permission="hr.manage" fallback={<StaffNotesTab items={currentStaff.notes_entries || []} saving={false} error={profileError} onCreate={() => {}} onDelete={() => {}} />}>
              <StaffNotesTab
                items={currentStaff.notes_entries || []}
                saving={profileSaving}
                error={profileError}
                onCreate={async (payload) => {
                  const result = await dispatch(createStaffNote({ staffId, payload }));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
                onDelete={async (noteId) => {
                  const result = await dispatch(deleteStaffNote(noteId));
                  if (!result.error) {
                    refreshProfile();
                  }
                  return result;
                }}
              />
            </PermissionGate>
          </Grid>
        ) : null}

        {tab === 'status-history' ? (
          <Grid size={{ xs: 12 }}>
            <StaffStatusHistoryTab items={currentStaff.status_history || []} />
          </Grid>
        ) : null}
      </Grid>
    </Stack>
  );
}
