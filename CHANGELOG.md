# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.2] - 2024-01-XX

### Added
- Global enable/disable configuration option (`FRAGMENT_CACHE_ENABLED`)
- `isEnabled()` method to check caching status
- Comprehensive tests for enabled/disabled functionality
- Development vs production configuration examples

### Changed
- `rememberHtml()` method now bypasses caching when disabled
- `forget()` method now does nothing when caching is disabled
- Blade directive `@fragmentCache` now respects enabled setting
- Enhanced documentation with enable/disable examples

### Fixed
- Improved error handling for null configuration values

## [1.0.1] - 2024-01-XX

### Added
- Livewire trait example (`CachesRenderedHtml`) in README and examples documentation
- Comprehensive Livewire integration examples with `renderCached()` method
- Organization-based identifier examples for multi-tenant scenarios

### Changed
- Updated GitHub Actions workflows to use latest action versions (v4)
- Improved documentation structure with recommended vs alternative approaches
- Enhanced Livewire examples with real-world usage patterns

### Removed
- Removed `fragment-cache:inspect-store` command reference from documentation
- Cleaned up documentation to focus on core functionality

### Fixed
- Fixed deprecated GitHub Actions workflow warnings
- Updated `actions/upload-artifact` from v3 to v4
- Updated `actions/cache` from v3 to v4

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Laravel HTML Fragment Cache package
- Support for all Laravel cache drivers (Redis, Memcached, Database, File, Array, DynamoDB, Octane)
- Configurable cache store selection
- Blade directive `@fragmentCache` for easy HTML caching
- Facade `FragmentCache` for programmatic usage
- `UsesHtmlFragmentCache` trait for controller integration
- Interactive Artisan commands for cache management
- Livewire integration examples
- Comprehensive test suite
- Complete documentation and examples
- Packagist distribution ready

### Features
- Universal cache driver support without tag dependencies
- Identifier-based caching system
- Configurable TTL, variant, and version settings
- Automatic identifier resolution from route parameters and object properties
- Cache store inspection and management commands
- Laravel Prompts integration for interactive commands
