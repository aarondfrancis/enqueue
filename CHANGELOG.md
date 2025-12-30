# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Require Laravel 12.24+ (instead of 12.0+) for PHPUnit 12 compatibility

## [0.1.0] - 2025-12-28

### Added
- `Enqueueable` interface with static `enqueue()` method for declarative job enqueueing
- `jobs:enqueue` Artisan command that discovers and enqueues jobs implementing the interface
- Optional `shouldEnqueue()` method for schedule-based or boolean-based conditional enqueueing
- Support for Laravel's fluent scheduling API (e.g., `$event->hourly()`)
- `--pretend` flag to preview which jobs would be enqueued without actually dispatching them


[Unreleased]: https://github.com/aaronfrancis/enqueue/compare/HEAD
[0.1.0]: https://github.com/aarondfrancis/enqueue/releases/tag/v0.1.0
