<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Saas\TenantDomainController;
use App\Http\Controllers\Api\V1\Saas\TenantUsageController;
use App\Modules\SuperAdmin\Controllers\PlanFeatureController;
use App\Modules\SuperAdmin\Controllers\PlatformAuditLogController;
use App\Modules\SuperAdmin\Controllers\PlatformEmergencyAccessController;
use App\Modules\SuperAdmin\Controllers\PlatformImpersonationController;
use App\Modules\SuperAdmin\Controllers\PlatformTenantBackupController;
use App\Modules\SuperAdmin\Controllers\PlatformTenantController;
use App\Modules\SuperAdmin\Controllers\PlatformFeatureController;
use App\Modules\SuperAdmin\Controllers\PlatformFailedJobController;
use App\Modules\SuperAdmin\Controllers\PlatformHealthController;
use App\Modules\SuperAdmin\Controllers\PlatformSettingController;
use App\Modules\SuperAdmin\Controllers\PlatformSystemErrorController;
use App\Modules\SuperAdmin\Controllers\SubscriptionPlanController;
use App\Modules\SuperAdmin\Controllers\TenantBillingController;
use App\Modules\SuperAdmin\Controllers\TenantDatabaseController;
use App\Modules\SuperAdmin\Controllers\TenantFeatureAccessController;
use App\Modules\SuperAdmin\Controllers\TenantProvisioningController;
use App\Modules\SuperAdmin\Controllers\TenantSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/platform')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'platformLogin'])->middleware('api.rate:login');
    Route::middleware(['auth:api', 'ensure.platform.admin', 'api.rate:api'])->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
    Route::post('/impersonation/stop', [PlatformImpersonationController::class, 'stop'])->middleware(['auth:api', 'api.rate:api']);

    Route::middleware(['auth:api', 'ensure.platform.admin', 'api.rate:api'])->group(function (): void {
        Route::get('/tenants', [PlatformTenantController::class, 'index']);
        Route::post('/tenants', [PlatformTenantController::class, 'store']);
        Route::get('/tenants/{id}', [PlatformTenantController::class, 'show']);
        Route::put('/tenants/{id}', [PlatformTenantController::class, 'update']);
        Route::post('/tenants/{id}/activate', [PlatformTenantController::class, 'activate']);
        Route::post('/tenants/{id}/suspend', [PlatformTenantController::class, 'suspend']);
        Route::post('/tenants/{id}/cancel', [PlatformTenantController::class, 'cancel']);
        Route::post('/tenants/{id}/impersonate', [PlatformImpersonationController::class, 'start']);
        Route::post('/tenants/{id}/emergency-access/request', [PlatformEmergencyAccessController::class, 'request']);
        Route::post('/tenants/{id}/backup', [PlatformTenantBackupController::class, 'store']);
        Route::get('/tenants/{id}/backups', [PlatformTenantBackupController::class, 'index']);
        Route::post('/emergency-access/{id}/approve', [PlatformEmergencyAccessController::class, 'approve']);
        Route::post('/emergency-access/{id}/revoke', [PlatformEmergencyAccessController::class, 'revoke']);
        Route::post('/tenants/{id}/provision-database', [TenantProvisioningController::class, 'provisionDatabase']);
        Route::post('/tenants/{id}/test-database', [TenantDatabaseController::class, 'test']);

        Route::post('/tenants/onboard-school', [TenantProvisioningController::class, 'store']);

        Route::get('/plans', [SubscriptionPlanController::class, 'index']);
        Route::post('/plans', [SubscriptionPlanController::class, 'store']);
        Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show']);
        Route::put('/plans/{id}', [SubscriptionPlanController::class, 'update']);
        Route::delete('/plans/{id}', [SubscriptionPlanController::class, 'destroy']);
        Route::get('/plans/{id}/features', [PlanFeatureController::class, 'index']);
        Route::put('/plans/{id}/features', [PlanFeatureController::class, 'update']);
        Route::get('/features', [PlatformFeatureController::class, 'index']);
        Route::put('/features/{featureCode}', [PlatformFeatureController::class, 'update']);
        Route::get('/settings', [PlatformSettingController::class, 'index']);
        Route::get('/settings/{group}', [PlatformSettingController::class, 'show']);
        Route::put('/settings/{group}', [PlatformSettingController::class, 'update']);
        Route::get('/health', [PlatformHealthController::class, 'index']);
        Route::get('/tenants/{id}/health', [PlatformHealthController::class, 'showTenant']);
        Route::get('/failed-jobs', [PlatformFailedJobController::class, 'index']);
        Route::get('/system-errors', [PlatformSystemErrorController::class, 'index']);
        Route::get('/audit-logs', [PlatformAuditLogController::class, 'index']);

        Route::post('/tenants/{id}/subscribe', [TenantSubscriptionController::class, 'subscribe']);
        Route::post('/tenants/{id}/change-plan', [TenantSubscriptionController::class, 'changePlan']);
        Route::post('/tenants/{id}/renew', [TenantSubscriptionController::class, 'renew']);
        Route::post('/tenants/{id}/cancel-subscription', [TenantSubscriptionController::class, 'cancel']);

        Route::get('/tenants/{id}/features', [TenantFeatureAccessController::class, 'index']);
        Route::put('/tenants/{id}/features', [TenantFeatureAccessController::class, 'update']);

        Route::get('/tenants/{id}/usage', [TenantUsageController::class, 'show']);
        Route::post('/tenants/{id}/sync-usage', [TenantUsageController::class, 'sync']);

        Route::get('/tenants/{id}/billing', [TenantBillingController::class, 'index']);
        Route::post('/billing/{id}/mark-paid', [TenantBillingController::class, 'markPaid']);
        Route::post('/billing/{id}/mark-failed', [TenantBillingController::class, 'markFailed']);

        Route::get('/tenants/{id}/domains', [TenantDomainController::class, 'index']);
        Route::post('/tenants/{id}/domains', [TenantDomainController::class, 'store']);
        Route::post('/domains/{id}/verify', [TenantDomainController::class, 'verify']);

        // Reserved platform route space for future:
        // /platform/audit-logs
        // /platform/security
        // /platform/health
    });
});
