# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - YYYY-MM-DD

### Added

- Initial release: full coverage of OmniLeads API
- Project, ProjectSource, ProjectData, Auth, LimitType, GeoRegion, Sync resources
- Webhook payload parser for `import.completed`
- Laravel ServiceProvider, Facade, Manager, config publishing
- Hardcoded enums for `SourceType`, `SourceData`, `ProjectState`, `DayOfWeek`
- Built-in `GeoRegions` registry with all 86 IDs from the spec
- Pagination generator (`iterateByDay`) for the `/Project/data/by-day` endpoint
- Idempotent state changes via `PATCH /Project/{id}/state`
- Retry mechanism with exponential backoff for 5xx and network errors
- Two-flavour authentication: `X-Api-Key` for the bulk of the API and `Bearer` JWT for webhook settings
- Pre-flight validation (GUID, ISO date, page bounds) raising `ConfigurationException` before any HTTP request

[Unreleased]: https://github.com/madtec/omnileads-sdk-php/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/madtec/omnileads-sdk-php/releases/tag/v1.0.0
