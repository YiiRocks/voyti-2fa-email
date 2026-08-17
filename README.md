# Voyti 2FA — Email Method

Emailed one-time-code two-factor authentication method for [Voyti](https://github.com/YiiRocks/voyti), the Yii3 user-management extension. At the start of the login-confirmation step it mails a fresh six-digit code, checked against what the user enters.

[![Packagist Version](https://img.shields.io/packagist/v/yiirocks/voyti-2fa-email.svg)](https://packagist.org/packages/yiirocks/voyti-2fa-email)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/yiirocks/voyti-2fa-email.svg)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/dt/yiirocks/voyti-2fa-email.svg)](https://packagist.org/packages/yiirocks/voyti-2fa-email)
[![GitHub License](https://img.shields.io/github/license/yiirocks/voyti-2fa-email.svg)](https://github.com/yiirocks/voyti-2fa-email/blob/main/LICENSE.md)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/yiirocks/voyti-2fa-email/build.yml?branch=main)](https://github.com/yiirocks/voyti-2fa-email/actions)

Stats for Nerds

[![Coverage](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Fvoyti-2fa-email%2Fbadges%2Fcoverage.json)](https://github.com/yiirocks/voyti-2fa-email/tree/badges)
[![MSI](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Fvoyti-2fa-email%2Fbadges%2Fmsi.json)](https://github.com/yiirocks/voyti-2fa-email/tree/badges)
[![Tests](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Fvoyti-2fa-email%2Fbadges%2Ftests.json)](https://github.com/yiirocks/voyti-2fa-email/tree/badges)
[![Assertions](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Fvoyti-2fa-email%2Fbadges%2Fassertions.json)](https://github.com/yiirocks/voyti-2fa-email/tree/badges)

## Overview

A two-factor **method** package for Voyti's [voyti-2fa](https://github.com/YiiRocks/voyti-2fa) base. Install it and it registers itself — its button appears on the settings screen's method switcher and it becomes selectable in the login confirmation step. It needs only a configured mailer; no extra configuration.

## Installation

The `yiirocks/voyti-2fa` base is pulled in automatically as a dependency:

```bash
composer require yiirocks/voyti-2fa-email
```

## Documentation

The complete reference guide is available at [Yii.Rocks](https://www.yii.rocks/voyti/two-factor/).
