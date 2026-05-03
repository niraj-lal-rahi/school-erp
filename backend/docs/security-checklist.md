# Security Checklist

- `APP_ENV=production` and `APP_DEBUG=false` in production.
- Enforce `auth:api`, tenant resolution, tenant activity, and tenant isolation middleware on tenant routes.
- Apply route-level rate limiting for login, API, webhook, and download-sensitive endpoints.
- Validate webhook signatures before processing any payment side effects.
- Never expose storage file paths in API payloads.
- Serve private files through controlled download services or signed temporary URLs only.
- Mask secrets, signatures, tokens, and passwords in audits and logs.
- Keep tenant-aware cache keys and clear them on setting, RBAC, feature, and tenant state changes.
- Review `fillable` arrays for every writeable model before release.
- Ensure background jobs are idempotent and overlap-safe for payments, reports, documents, and automation.
