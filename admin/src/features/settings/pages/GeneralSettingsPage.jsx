import { SettingsGroupEditor } from '../components/SettingsGroupEditor';
import { SettingsPageShell } from '../components/SettingsPageShell';

export function GeneralSettingsPage() {
  return (
    <SettingsPageShell
      title="General Settings"
      description="Manage the base configuration values that shape tenant-wide behavior, labels, and safe public metadata."
    >
      <SettingsGroupEditor
        groupCode="general"
        suggestedPrefix="general"
        title="General Configuration"
        description="Use this section for tenant-safe core settings like naming, campus defaults, and application-facing metadata."
      />
    </SettingsPageShell>
  );
}
