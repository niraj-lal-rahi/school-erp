import { SettingsGroupEditor } from '../components/SettingsGroupEditor';
import { SettingsPageShell } from '../components/SettingsPageShell';

export function NotificationSettingsPage() {
  return (
    <SettingsPageShell
      title="Notification Settings"
      description="Control the default channels, sender conventions, and messaging preferences the rest of the platform can inherit safely."
    >
      <SettingsGroupEditor
        groupCode="notifications"
        suggestedPrefix="notifications"
        title="Notification Defaults"
        description="Use these settings for sender names, channel defaults, and tenant-level communication preferences that should stay adjustable."
      />
    </SettingsPageShell>
  );
}
