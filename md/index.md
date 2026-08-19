# PHP 2FA

Framework-agnostic Two-Factor Authentication (TOTP) actions for PHP. Works with any authenticator app (Google Authenticator, Authy, etc.), with optional first-party Laravel support.

Inspired by [Laravel Fortify](https://github.com/laravel/fortify) and built on top of [`pragmarx/google2fa`](https://github.com/antonioribeiro/google2fa).

## Features

- Enable / disable / confirm 2FA
- Verify OTP codes
- Recovery code generation, verification, and regeneration
- Enable → confirm flow (a user must verify a code before 2FA is active)
- Framework-agnostic core — use it with any PHP application
- Optional Laravel integration with a service provider, config, and Eloquent trait
- AES-256-CBC encryption out of the box (`OpenSslEncryptor`)
- Bring your own encryptor via the `Encryptor` contract

## Requirements

- PHP 8.3+
- OpenSSL extension

## Getting started

- [Installation](installation/) — add the package to your project
- [Quick start (vanilla PHP)](quick-start/) — implement `TwoFactorUser` and use `TwoFactorManager`
- [Individual actions](actions/) — granular control with dependency injection
- [Laravel usage](laravel/) — trait, migration, and controller wiring
- [Configuration](configuration/) — config reference
- [Custom encryptor](custom-encryptor/) — plug in your own encryption strategy

> Full working examples are available in [`docs/examples/`](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples) — both [PHP](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/php) and [Laravel](https://github.com/mrpunyapal/php-2fa/tree/main/docs/examples/laravel) variants.