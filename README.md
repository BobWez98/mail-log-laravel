# Laravel Mail Log
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/BobWez98/mail-log-laravel/tests.yml?label=tests)](https://github.com/BobWez98/mail-log-laravel/actions/workflows/tests.yml)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/bobwez98/mail-log-laravel.svg)](https://packagist.org/packages/bobwez98/mail-log-laravel)

Automatically log outgoing Laravel emails and their send status to your application's database.

## Requirements

- PHP 8.4+
- Laravel 13

## Features

- Automatically logs mail through Laravel's native mail events
- Correlates sending and sent events with a unique `X-Mail-Log-ID` header
- Stores senders, recipients, subject, message body, and mail event data
- Tracks when a message is pending and when it has been sent
- Provides an Eloquent model with enum, array, and datetime casts
- Can be disabled through configuration or an environment variable

## Installation

Install the Composer package:

```bash
composer require bobwez98/mail-log-laravel
```

The service provider is registered through Laravel package discovery. Run the migrations to create the `mail_logs` table:

```bash
php artisan migrate
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BobWez98\MailLog\ServiceProvider" --tag=config
```

The published configuration is available at `config/mail-log-laravel.php`:

```php
<?php

return [
    'enabled' => env('MAIL_LOG_ENABLED', true),
];
```

Mail logging is enabled by default. It can be disabled in your `.env` file:

```dotenv
MAIL_LOG_ENABLED=false
```

## How It Works

Before Laravel sends an email, the package:

1. Generates a unique message ID.
2. Adds the ID to the email as an `X-Mail-Log-ID` header.
3. Creates a mail log with the `pending` status.

After Laravel dispatches the `MessageSent` event, the package uses the header to find the corresponding record, changes its status to `success`, and records when it was sent.

> [!IMPORTANT]
> Logged messages must contain a sender, recipient, subject, an HTML or plain-text body, and non-empty mail event data.

## Usage

No changes to your mailables are required. Once installed and migrated, outgoing emails are logged automatically.

### Using a framework?

Laravel mail log supports multiple frameworks to add a UI. For example:
- [Statamic](https://github.com/BobWez98/mail-log-statamic)
- Nova - Coming soon
- Filament - Coming soon

### Query the logs yourself

Mail logs can be queried through the included Eloquent model:

```php
<?php

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;

$mailLogs = MailLog::query()
    ->latest()
    ->get();

$successfulMailLogs = MailLog::query()
    ->where('status', LogStatus::SUCCESS)
    ->latest()
    ->get();
```

Each mail log contains the following data:

| Attribute | Description |
| --- | --- |
| `message_id` | Unique UUID used to correlate mail events |
| `status` | Current `LogStatus` value |
| `from` | Formatted sender addresses |
| `to` | Formatted recipient addresses |
| `subject` | Email subject |
| `body` | HTML or plain-text email body |
| `data` | Laravel mail event data |
| `sent_at` | Date and time Laravel dispatched the sent event |

The available statuses are `pending`, `success`, and `failed`. The package automatically uses `pending` and `success`; `failed` is available for custom integrations.

## Quality

To ensure the quality of this package, run the following command:

```bash
composer quality
```

This will execute five tasks:

1. Checks if the code is correctly formatted
2. Checks for issues using static code analysis
3. Makes sure all tests pass
4. Verifies 100% code coverage
5. Checks for pending Rector changes

Code coverage requires Xdebug with coverage mode enabled.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please report security vulnerabilities privately to [info@bobwezelman.nl](mailto:info@bobwezelman.nl).

## Credits

- [Bob Wezelman](https://github.com/BobWez98)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
