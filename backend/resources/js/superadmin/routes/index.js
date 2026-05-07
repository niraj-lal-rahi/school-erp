import DashboardPage from '../pages/DashboardPage';
import TenantListPage from '../pages/TenantListPage';
import TenantDetailPage from '../pages/TenantDetailPage';
import TenantProvisioningStatusPage from '../pages/TenantProvisioningStatusPage';
import TenantDatabaseConnectionPage from '../pages/TenantDatabaseConnectionPage';
import PlansPage from '../pages/PlansPage';
import SubscriptionsPage from '../pages/SubscriptionsPage';
import BillingPage from '../pages/BillingPage';
import FeatureFlagsPage from '../pages/FeatureFlagsPage';
import PlatformSettingsPage from '../pages/PlatformSettingsPage';
import SecuritySettingsPage from '../pages/SecuritySettingsPage';
import SystemHealthPage from '../pages/SystemHealthPage';
import AuditLogsPage from '../pages/AuditLogsPage';
import ImpersonationLogsPage from '../pages/ImpersonationLogsPage';
import EmergencyAccessPage from '../pages/EmergencyAccessPage';
import BackupsPage from '../pages/BackupsPage';

const superAdminRoutes = [
  { path: '/superadmin', name: 'dashboard', component: DashboardPage },
  { path: '/superadmin/tenants', name: 'tenant-list', component: TenantListPage },
  { path: '/superadmin/tenants/:tenantId', name: 'tenant-detail', component: TenantDetailPage },
  { path: '/superadmin/tenants/:tenantId/provisioning', name: 'tenant-provisioning', component: TenantProvisioningStatusPage },
  { path: '/superadmin/tenants/:tenantId/database', name: 'tenant-database', component: TenantDatabaseConnectionPage },
  { path: '/superadmin/plans', name: 'plans', component: PlansPage },
  { path: '/superadmin/subscriptions', name: 'subscriptions', component: SubscriptionsPage },
  { path: '/superadmin/billing', name: 'billing', component: BillingPage },
  { path: '/superadmin/feature-flags', name: 'feature-flags', component: FeatureFlagsPage },
  { path: '/superadmin/platform-settings', name: 'platform-settings', component: PlatformSettingsPage },
  { path: '/superadmin/security-settings', name: 'security-settings', component: SecuritySettingsPage },
  { path: '/superadmin/system-health', name: 'system-health', component: SystemHealthPage },
  { path: '/superadmin/audit-logs', name: 'audit-logs', component: AuditLogsPage },
  { path: '/superadmin/impersonation-logs', name: 'impersonation-logs', component: ImpersonationLogsPage },
  { path: '/superadmin/emergency-access', name: 'emergency-access', component: EmergencyAccessPage },
  { path: '/superadmin/backups', name: 'backups', component: BackupsPage },
];

export default superAdminRoutes;
