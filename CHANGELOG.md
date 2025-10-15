# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

### Features
- Universal cache driver support without tag dependencies
- Identifier-based caching system
- Configurable TTL, variant, and version settings
- Automatic identifier resolution from route parameters and object properties
- Cache store inspection and management commands
- Laravel Prompts integration for interactive commands

## [1.0.0] - 2024-01-XX

### Added
- Initial stable release
- Complete documentation and examples
- Packagist distribution ready
