# Contributing

Thank you for your interest in contributing to `akira/laravel-rag`.

This project maintains **strict architectural and quality standards**.
Please read this document before opening an issue or pull request.

---

## Before Opening an Issue

If you want to:

- propose a new feature
- change behavior
- refactor internals

Please open an issue first describing:

- the problem you are solving
- why it belongs in this project
- how it aligns with the existing architecture

Early alignment avoids wasted work.

---

## Contribution Principles

Good contributions:

- are narrow in scope
- improve correctness, clarity, or operability
- avoid hidden side effects
- include tests
- respect existing abstractions

Large, unfocused pull requests are discouraged.

---

## Coding Standards

All contributions must follow:

- PHP 8.4+
- `strict_types=1`
- Laravel 12 conventions
- Akira package standards
- Explicit configuration over defaults
- No global state
- No silent behavior changes

Business logic must live in:

- Actions
- Services
- Pipelines

Not in:

- Commands
- Facades
- Controllers

---

## Testing Requirements

Any behavioral change MUST include tests.

Tests must:

- be deterministic
- avoid external services
- support single-tenant and multi-tenant modes
- clearly describe intent

If a feature cannot be tested reliably, it is likely not ready.

---

## Documentation

Changes that affect:

- commands
- configuration
- public APIs
- behavior

MUST update the relevant documentation under `docs/`.

This project prefers:

- fewer documentation files
- clear explanations
- real examples

---

## What Is Usually Rejected

The following are commonly rejected:

- magic abstractions
- provider-specific shortcuts
- UI-driven logic in the core
- breaking changes without strong justification
- large PRs without prior discussion

---

## Review Process

Pull requests are reviewed with focus on:

- architectural consistency
- long-term maintainability
- operational impact
- clarity of intent

Feedback may request:

- simplification
- additional tests
- design adjustments

---

## Code of Conduct

Be respectful and constructive.

Disagreements are expected, but discussions must focus on ideas and trade-offs,
not individuals.

---

## Final Note

This project values long-term thinking and careful design.

If that matches how you like to work, contributions are welcome.