# Contributing to madtec/omnileads-sdk

Thanks for taking the time to contribute. This document outlines the process and the
expectations for pull requests.

## Getting started

1. Fork the repository on GitHub.
2. Clone your fork:
   ```bash
   git clone git@github.com:<your-user>/omnileads-sdk-php.git
   cd omnileads-sdk-php
   ```
3. Install dependencies:
   ```bash
   composer install
   ```

## Running checks locally

Before opening a PR, run all three pipelines that CI runs:

```bash
composer test          # Pest test suite
composer analyse       # PHPStan level 8
composer format-test   # Laravel Pint --test
```

To auto-fix style issues:

```bash
composer format
```

## Pull request requirements

- All checks above must be green.
- Tests are added or updated for the change. Aim to keep coverage at or above 85%.
- `CHANGELOG.md` is updated under the `[Unreleased]` section.
- Public API additions stay strongly typed — no raw arrays in method signatures.
- No hardcoded credentials, API keys, or JWTs in code or tests.
- The PR description explains the *why*, not only the *what*.

## Commit messages

We do not strictly enforce Conventional Commits, but a short prefix helps:

```
feat: add pagination iterator for byDay
fix: correct timezone handling in DateParser
docs: extend webhook controller example
test: add 401 path coverage for AuthResource
chore: bump dependabot weekly limit
```

## Review process

A maintainer will review your PR. Please address feedback in additional commits rather
than force-pushing — squash happens at merge time. If review is taking longer than a few
days, feel free to ping in the PR.

## Reporting bugs

Use the **Bug report** issue template. The more reproduction context you provide, the
faster we can help.

## Security

Do not open public issues for security vulnerabilities. See [SECURITY.md](SECURITY.md).
