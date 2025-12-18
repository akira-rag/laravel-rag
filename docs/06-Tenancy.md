# Tenancy

The package supports two modes:

- Single‑tenant (default): `tenant_id` is null; no tenant resolution.
- Multi‑tenant (opt‑in): `tenant_id` is resolved at runtime.

Components:
- `TenantResolver` (interface)
- `NullTenantResolver` (default)
- `TenantContext` (enabled/column/resolver/current)
- `ScopesTenant` (Global Scope filtering by tenant)

Rules:
- Public API never accepts `tenant_id`.
- All queries are filtered by current tenant (or `NULL` in single‑tenant).

Resolver example:

```php
final class JetstreamTenantResolver implements \Akira\Rag\Tenant\TenantResolver
{
    public function resolve(): ?string
    {
        return auth()->user()?->currentTeam?->id;
    }
}
```

Validation rules
- Public API forbids `tenant_id` in payloads; the manager throws an explicit domain exception on violation.
- All queries are automatically scoped by the current tenant (or by NULL when single‑tenant), ensuring zero cross‑tenant leakage.

Testing patterns
- Toggle `tenancy.enabled` per test and swap resolvers to emulate multiple tenants and verify isolation.

Previous: [05 Database](05-Database.md) | Next: [07 Ingestion](07-Ingestion.md)
