# Security Policy

This document describes the security model and reporting process for
`akira/laravel-rag`.

---

## Supported Scope

Security considerations apply to:

- the core RAG pipeline
- CLI commands
- backup, export, and restore flows
- multi-tenant isolation
- encryption mechanisms

User applications built on top of this package are responsible for their
own access control and authentication.

---

## Multi-Tenant Safety

The package is designed to support:

- single-tenant mode
- multi-tenant mode via explicit tenant resolution

Security guarantees:

- tenant context is resolved centrally
- tenant identifiers are never accepted via public APIs
- all queries must be tenant-scoped

Any cross-tenant data leakage is considered a **critical vulnerability**.

---

## Backup & Encryption

Backups and exports may contain sensitive data.

Security guarantees:

- encryption uses Laravel’s Encrypter (`APP_KEY`)
- encrypted backups include metadata headers
- plaintext data is never logged

Recommendations:

- rotate `APP_KEY` periodically
- restrict access to backup files
- store backups outside the web root

---

## CLI Command Safety

Some commands are **destructive** by nature:

- `rag:restore`
- bulk re-embedding
- data pruning operations

Safety measures include:

- interactive confirmation prompts
- dry-run modes
- tenant scoping
- clear warnings before execution

Users are responsible for restricting access to these commands.

---

## Reporting Vulnerabilities

If you discover a security vulnerability:

1. **Do not open a public issue**
2. Contact the maintainers privately
3. Provide:

- a clear description
- steps to reproduce
- potential impact
- suggested mitigation (if known)

We aim to:

- acknowledge reports promptly
- assess impact carefully
- release fixes responsibly

---

## Disclosure Policy

Security issues are handled with coordinated disclosure.

Details are shared publicly only after:

- a fix is available
- affected users have time to update

---

## Final Note

Security is a continuous process.

If you are unsure whether something is a vulnerability, please report it.
Responsible disclosure is always appreciated.