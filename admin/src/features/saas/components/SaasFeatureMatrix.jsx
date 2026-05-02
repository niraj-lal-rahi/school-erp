import { Checkbox, Paper, Stack, Table, TableBody, TableCell, TableHead, TableRow, TextField, Typography } from '@mui/material';

export function SaasFeatureMatrix({ title, description, features, onToggle, onLimitChange, editable = false }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={1} mb={3}>
        <Typography variant="h5">{title}</Typography>
        <Typography variant="body2" color="text.secondary">
          {description}
        </Typography>
      </Stack>

      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Feature</TableCell>
            <TableCell>Module</TableCell>
            <TableCell>Enabled</TableCell>
            <TableCell>Limit</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {features.length ? features.map((feature) => (
            <TableRow key={feature.feature_code || feature.code}>
              <TableCell>{feature.feature_name || feature.label || feature.feature_code}</TableCell>
              <TableCell>{feature.module || 'general'}</TableCell>
              <TableCell>
                <Checkbox
                  checked={Boolean(feature.is_enabled)}
                  disabled={!editable}
                  onChange={(event) => onToggle?.(feature.feature_code || feature.code, event.target.checked)}
                />
              </TableCell>
              <TableCell sx={{ width: 180 }}>
                <TextField
                  size="small"
                  fullWidth
                  value={feature.limit_value ?? ''}
                  disabled={!editable}
                  onChange={(event) => onLimitChange?.(feature.feature_code || feature.code, event.target.value)}
                  placeholder="Unlimited"
                />
              </TableCell>
            </TableRow>
          )) : (
            <TableRow>
              <TableCell colSpan={4}>
                <Typography py={4} textAlign="center" color="text.secondary">
                  No feature definitions available yet.
                </Typography>
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </Paper>
  );
}
