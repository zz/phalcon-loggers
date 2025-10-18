# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a Phalcon PHP logging library that provides adapters for Slack and Sentry. The library is designed to work with Phalcon 5.x and PHP 8.0+, offering PSR-compatible logging functionality with multiple adapters that can be used simultaneously.

**Namespace**: `Easyconn\PhalconLogger`

## Development Commands

### Install dependencies
```bash
composer install
```

### Run tests
```bash
vendor/bin/phpunit
```

### Run specific test
```bash
vendor/bin/phpunit --filter <TestName>
```

### Install Sentry support (optional)
```bash
composer require sentry/sentry
```

## Architecture

### Core Components

1. **Service** (`src/Service.php`)
   - Entry point for registering loggers in Phalcon DI
   - Auto-detects available adapters (Sentry enabled only if `\Raven_Client` exists)
   - Registers both individual loggers (slack, sentry) and a combined logger (logger) as DI services
   - The combined 'logger' service is a `Multiple` instance that forwards calls to all registered adapters

2. **Multiple** (`src/Multiple.php`)
   - Extends `Phalcon\Logger\Logger\Multiple`
   - Aggregates multiple logger adapters and forwards messages to all of them
   - Adds `logException()` method to properly handle exception logging across adapters
   - Adds `special()` and `custom()` convenience methods for Phalcon's special log levels
   - Implements `setRequestId()` to propagate request IDs to all child loggers

3. **Adapters**
   - **Slack** (`src/Adapter/Slack.php`): Self-contained, sends logs to Slack webhooks via curl. Supports attachments, mentions, and custom formatting. Can run synchronously or in background via `exec`.
   - **Sentry** (`src/Adapter/Sentry.php`): Wraps Sentry SDK for error tracking. Maps Phalcon log levels to Sentry severity levels. Supports breadcrumbs, tags, user/extra context, and exception filtering.

4. **Formatter** (`src/Formatter.php`)
   - Custom formatter supporting message interpolation (placeholders in curly braces like `{key}`)

### Configuration

Configuration can be passed as array, `Phalcon\Config` object, or file path. See `src/config.logger.php` for structure.

Key configuration concepts:
- **environment**: Controls which environment adapters are active in (e.g., 'prod', 'staging', 'dev')
- **levels**: Array of Phalcon log levels that each adapter should respond to
- **requestId**: Optional UUID for tracing logs across requests
- Sentry and Slack have separate configurations for credentials, options, and behavior

### Adapter Activation Rules

- **Slack**: Always available, activates if `webhookUrl` is set, current environment matches `slack.environments`, and log level is in `slack.levels`
- **Sentry**: Only loads if `\Raven_Client` class exists, activates if current environment matches `sentry.environments` and log level is in `sentry.levels`

## Branch Strategy

- Main branch: **master**
- Current working branch: **phalcon-5** (recent updates for Phalcon 5.1.3 and PHP 8.4 compatibility)

## Important Notes

- The library was updated from namespace `CrazyFactory\PhalconLogger` to `Easyconn\PhalconLogger` (see composer.json)
- However, some code comments still reference the old namespace `CrazyFactory\PhalconLogger` in return type annotations
- Sentry adapter uses the newer `sentry/sdk` package (v3.0+) but has legacy references to `\Raven_Client` in comments
- Tests use PHPUnit 5.7.0 and Mockery for mocking
- The library distinguishes between "log levels" (Phalcon) and "severity levels" (Sentry) with explicit mapping
