import { SettingsGroupEditor } from '../components/SettingsGroupEditor';
import { SettingsPageShell } from '../components/SettingsPageShell';

export function FinanceSettingsPage() {
  return (
    <SettingsPageShell
      title="Finance Settings"
      description="Set receipt conventions, billing defaults, and other finance behaviors that should stay configurable per school."
    >
      <SettingsGroupEditor
        groupCode="finance"
        suggestedPrefix="finance"
        title="Finance Defaults"
        description="Shape invoice, receipt, and finance-side defaults here so fee operations stay aligned with how the tenant actually runs."
      />
    </SettingsPageShell>
  );
}
