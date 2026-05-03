import { SettingsGroupEditor } from '../components/SettingsGroupEditor';
import { SettingsPageShell } from '../components/SettingsPageShell';

export function AcademicSettingsPage() {
  return (
    <SettingsPageShell
      title="Academic Settings"
      description="Tune academic defaults like grade scales, attendance modes, and academic workflow preferences without touching code."
    >
      <SettingsGroupEditor
        groupCode="academic"
        suggestedPrefix="academic"
        title="Academic Defaults"
        description="Keep academic policies centralized so class operations, grading behavior, and academic defaults stay consistent across the tenant."
      />
    </SettingsPageShell>
  );
}
