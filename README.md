# SQLighter: Laravel package for backing up your SQLite database.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joeymckenzie/sqlighter.svg?style=flat-square)](https://packagist.org/packages/joeymckenzie/sqlighter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/joeymckenzie/sqlighter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/joeymckenzie/sqlighter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/joeymckenzie/sqlighter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/joeymckenzie/sqlighter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/joeymckenzie/sqlighter.svg?style=flat-square)](https://packagist.org/packages/joeymckenzie/sqlighter)

A lightweight SQLite backup solution for Laravel applications. SQLighter provides automated backups of your SQLite
database with configurable retention policies.

- 🔄 Automated SQLite database backups
- ⚙️ Configurable backup frequency
- 📦 Backup file rotation with configurable retention
- 🗂️ Automatic backup directory creation
- 🚫 Git-friendly (auto-generates appropriate .gitignore)
- 💡 Simple integration with Laravel's scheduler

## Installation

You can install the package via composer:

```bash
composer require joeymckenzie/sqlighter
```

## Usage

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag="sqligther-config"
```

This will create a `config/sqligther.php` configuration file. Customize the options to fit your needs:

```php
return [
    // Enable/disable automatic backups
    'enabled' => env('SQLIGTHER_ENABLED', true),

    // Hours between backups
    'frequency' => env('SQLIGTHER_FREQUENCY', 3),

    // SQLite database filename
    'database' => env('SQLIGTHER_DATABASE', 'database.sqlite'),

    // How many backup copies to maintain
    'copies_to_maintain' => env('SQLIGTHER_COPIES', 5),

    // Where to store backups (relative to database directory)
    'storage_folder' => env('SQLIGTHER_STORAGE', 'backups/'),

    // Prefix for backup files
    'file_prefix' => env('SQLIGTHER_PREFIX', 'backup'),
];
```

### Automatic Backups

SQLighter automatically registers a scheduled command to perform backups based on your configuration. No additional
setup is required other than ensuring your Laravel scheduler is running:

```bash
cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Backups

You can also trigger a backup manually using the provided Artisan command:

```bash
php artisan sqlighter:backup
```

### Backup Storage

Backups are stored in your Laravel database directory under the configured `storage_folder` (default: `backups/`). A
`.gitignore` file is automatically created to prevent backups from being committed to your repository.

### File Naming

Backup files are named using the following format:

```
{prefix}-{timestamp}.sql
```

For example: `backup-1698765432.sql`

## Testing

Pest is used to test the backup command. To run tests, just use the composer script:

```bash
composer test
```

## Security Vulnerabilities

If you discover a security vulnerability within SQLighter, please send an e-mail to Joey McKenzie
via [joey.mckenzie27@gmail.com](mailto:joey.mckenzie27@gmail.com).

## Credits

- [Joey McKenzie](https://github.com/joeymckenzie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
