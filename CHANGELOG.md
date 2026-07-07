# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] - 2026-07-07

### Fixed

- Fixed browser scanner stub path resolution in CI and package installations.

## [1.0.0] - 2026-07-07

### Added

- Cookie consent banner with configurable categories.
- Cookie preferences page for updating user consent.
- Support for required, analytics, marketing and functional consent categories.
- Google Consent Mode integration.
- Granular consent management by vendor/service.
- Consent persistence with database storage.
- Consent retention management.
- Consent revocation support.
- Consent renewal when cookie configuration or policy versions change.
- Policy versioning and accepted policy tracking.
- Complete consent audit log.
- Consent history tracking.
- Publishable and fully customizable Blade views.
- Blade directives and components.
- JavaScript SDK exposed through `window.Complihance`.
- HTTP API for consent collection and management.
- Data providers for custom reporting dashboards.
- Cookie scanner with HTTP header scanning.
- Browser cookie scanner powered by Playwright.
- Cookie scan persistence and scan history.
- Cookie scan diff command.
- Automatic cookie technology classification.
- Preventive iframe and embedded content blocking.
- Configurable blocked-content placeholders.
- Inline consent request from blocked placeholders.
- Consent export command.
- Consent retention cleanup command.
- Package installation command.

[Unreleased]: https://github.com/KostantinoAbate/complihance/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/KostantinoAbate/complihance/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/KostantinoAbate/complihance/releases/tag/v1.0.0
