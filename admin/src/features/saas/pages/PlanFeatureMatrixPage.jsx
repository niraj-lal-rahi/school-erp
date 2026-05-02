import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchPlans, syncPlanFeatures } from '../store/saasSlice';
import { SaasFeatureMatrix } from '../components/SaasFeatureMatrix';
import { SaasPageShell } from '../components/SaasPageShell';
import { featurePresetOptions } from '../types/options';

export function PlanFeatureMatrixPage() {
  const dispatch = useAppDispatch();
  const { plans, saving, error } = useAppSelector((state) => state.saas);
  const [planId, setPlanId] = useState('');
  const [draftFeatures, setDraftFeatures] = useState([]);

  useEffect(() => {
    dispatch(fetchPlans());
  }, [dispatch]);

  const selectedPlan = useMemo(() => plans.find((plan) => String(plan.id) === String(planId)), [plans, planId]);

  useEffect(() => {
    if (selectedPlan) {
      const planFeatures = selectedPlan.features?.length
        ? selectedPlan.features
        : featurePresetOptions.map((feature) => ({
            feature_code: feature.code,
            feature_name: feature.label,
            module: feature.code,
            is_enabled: false,
            limit_value: '',
          }));

      setDraftFeatures(planFeatures);
    }
  }, [selectedPlan]);

  const handleToggle = (code, checked) => {
    setDraftFeatures((current) => current.map((feature) => (
      (feature.feature_code || feature.code) === code ? { ...feature, is_enabled: checked } : feature
    )));
  };

  const handleLimitChange = (code, value) => {
    setDraftFeatures((current) => current.map((feature) => (
      (feature.feature_code || feature.code) === code ? { ...feature, limit_value: value } : feature
    )));
  };

  const handleSave = () => {
    if (!selectedPlan) {
      return;
    }

    dispatch(syncPlanFeatures({
      id: selectedPlan.id,
      payload: {
        features: draftFeatures.map((feature) => ({
          feature_code: feature.feature_code || feature.code,
          feature_name: feature.feature_name || feature.label,
          module: feature.module || feature.feature_code || feature.code,
          is_enabled: Boolean(feature.is_enabled),
          limit_value: feature.limit_value === '' ? null : feature.limit_value,
        })),
      },
    }));
  };

  return (
    <SaasPageShell
      title="Plan Feature Matrix"
      description="Shape what each commercial plan unlocks, and set module-specific limits without losing the product view of the bundle."
      actions={(
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}>
          <TextField
            select
            size="small"
            label="Plan"
            value={planId}
            onChange={(event) => setPlanId(event.target.value)}
            sx={{ minWidth: 240 }}
          >
            {plans.map((plan) => (
              <MenuItem key={plan.id} value={plan.id}>
                {plan.name}
              </MenuItem>
            ))}
          </TextField>
          <Button variant="contained" startIcon={<SaveOutlinedIcon />} disabled={!selectedPlan || saving} onClick={handleSave}>
            Save Features
          </Button>
        </Stack>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <SaasFeatureMatrix
        title="Feature Access"
        description="Enable modules and set optional numeric limits per feature."
        features={draftFeatures}
        editable
        onToggle={handleToggle}
        onLimitChange={handleLimitChange}
      />
    </SaasPageShell>
  );
}
