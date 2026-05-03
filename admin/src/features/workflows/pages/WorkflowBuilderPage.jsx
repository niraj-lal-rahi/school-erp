import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import ArrowDownwardOutlinedIcon from '@mui/icons-material/ArrowDownwardOutlined';
import ArrowUpwardOutlinedIcon from '@mui/icons-material/ArrowUpwardOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { useWorkflowAccess } from '../hooks/useWorkflowAccess';
import {
  createWorkflowStep,
  deleteWorkflowStep,
  fetchWorkflowDefinition,
  fetchWorkflowDefinitions,
  updateWorkflowStep,
} from '../store/workflowsSlice';

const initialStep = {
  step_name: '',
  step_type: 'approval',
  sequence: 1,
  assigned_role_id: '',
  assigned_user_id: '',
  status: 'active',
  config: '{"message":"Review and continue"}',
};

export function WorkflowBuilderPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useWorkflowAccess();
  const { definitions, selectedDefinition, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [selectedDefinitionId, setSelectedDefinitionId] = useState('');
  const [stepForm, setStepForm] = useState(initialStep);

  useEffect(() => {
    dispatch(fetchWorkflowDefinitions({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    if (!selectedDefinitionId && definitions.length) {
      setSelectedDefinitionId(String(definitions[0].id));
    }
  }, [definitions, selectedDefinitionId]);

  useEffect(() => {
    if (selectedDefinitionId) {
      dispatch(fetchWorkflowDefinition(selectedDefinitionId));
    }
  }, [dispatch, selectedDefinitionId]);

  const steps = useMemo(
    () => [...(selectedDefinition?.steps || [])].sort((a, b) => a.sequence - b.sequence),
    [selectedDefinition?.steps],
  );

  return (
    <WorkflowPageShell
      title="Workflow Builder"
      description="Sequence approvals, conditions, delays, and actions into reusable tenant-scoped workflow paths."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Select Definition</Typography>
              <TextField
                select
                label="Workflow"
                value={selectedDefinitionId}
                onChange={(event) => setSelectedDefinitionId(event.target.value)}
                fullWidth
              >
                {definitions.map((definition) => (
                  <MenuItem key={definition.id} value={definition.id}>
                    {definition.name}
                  </MenuItem>
                ))}
              </TextField>
              {selectedDefinition ? (
                <Stack spacing={1}>
                  <Typography variant="body2" color="text.secondary">{selectedDefinition.description || 'No description yet.'}</Typography>
                  <WorkflowStatusChip value={selectedDefinition.status} />
                </Stack>
              ) : null}
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 8 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Step Designer</Typography>
              {canManage ? (
                <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                  <TextField label="Step Name" value={stepForm.step_name} onChange={(event) => setStepForm((current) => ({ ...current, step_name: event.target.value }))} fullWidth />
                  <TextField select label="Step Type" value={stepForm.step_type} onChange={(event) => setStepForm((current) => ({ ...current, step_type: event.target.value }))} sx={{ minWidth: 180 }}>
                    {['approval', 'notification', 'condition', 'action', 'delay'].map((value) => (
                      <MenuItem key={value} value={value}>{value}</MenuItem>
                    ))}
                  </TextField>
                  <Button
                    variant="contained"
                    startIcon={<AddOutlinedIcon />}
                    disabled={saving || !selectedDefinitionId || !stepForm.step_name}
                    onClick={() => dispatch(createWorkflowStep({
                      definitionId: selectedDefinitionId,
                      payload: {
                        ...stepForm,
                        sequence: steps.length + 1,
                        assigned_role_id: stepForm.assigned_role_id || null,
                        assigned_user_id: stepForm.assigned_user_id || null,
                        config: JSON.parse(stepForm.config || '{}'),
                      },
                    })).then((result) => {
                      if (!result.error) {
                        setStepForm(initialStep);
                      }
                    })}
                  >
                    Add Step
                  </Button>
                </Stack>
              ) : null}

              <Stack spacing={2}>
                {steps.map((step, index) => (
                  <Paper key={step.id} variant="outlined" sx={{ p: 2 }}>
                    <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="space-between" alignItems={{ xs: 'flex-start', md: 'center' }}>
                      <Stack spacing={0.5}>
                        <Typography variant="subtitle1">{step.sequence}. {step.step_name}</Typography>
                        <Typography variant="body2" color="text.secondary">
                          {step.step_type} step {step.assigned_role_id ? `· role ${step.assigned_role_id}` : ''} {step.assigned_user_id ? `· user ${step.assigned_user_id}` : ''}
                        </Typography>
                      </Stack>
                      <Stack direction="row" spacing={1}>
                        <WorkflowStatusChip value={step.status} />
                        {canManage ? (
                          <Button
                            size="small"
                            disabled={index === 0}
                            startIcon={<ArrowUpwardOutlinedIcon />}
                            onClick={() => dispatch(updateWorkflowStep({
                              id: step.id,
                              payload: { sequence: Math.max(1, step.sequence - 1) },
                            })).then(() => dispatch(fetchWorkflowDefinition(selectedDefinitionId)))}
                          >
                            Up
                          </Button>
                        ) : null}
                        {canManage ? (
                          <Button
                            size="small"
                            disabled={index === steps.length - 1}
                            startIcon={<ArrowDownwardOutlinedIcon />}
                            onClick={() => dispatch(updateWorkflowStep({
                              id: step.id,
                              payload: { sequence: step.sequence + 1 },
                            })).then(() => dispatch(fetchWorkflowDefinition(selectedDefinitionId)))}
                          >
                            Down
                          </Button>
                        ) : null}
                        {canManage ? (
                          <Button
                            size="small"
                            color="error"
                            startIcon={<DeleteOutlineOutlinedIcon />}
                            onClick={() => dispatch(deleteWorkflowStep(step.id))}
                          >
                            Remove
                          </Button>
                        ) : null}
                      </Stack>
                    </Stack>
                  </Paper>
                ))}

                {!loading && !steps.length ? (
                  <Typography variant="body2" color="text.secondary">
                    Choose a workflow definition and start adding steps to shape the runtime path.
                  </Typography>
                ) : null}
              </Stack>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </WorkflowPageShell>
  );
}
