# Contributing to Tourist eSIM PHP SDK

Thank you for your interest in contributing to the Tourist eSIM PHP SDK! This guide will help you get started.

## Development Setup

### Prerequisites
- PHP 8.1+
- Composer
- Git

### Local Setup

```bash
git clone https://github.com/touristesim/touristesim-php-sdk.git
cd touristesim-php-sdk
composer install
```

## Project Structure

```
touristesim-php-sdk/
├── src/
│   ├── TouristEsim.php          # Main SDK entry point
│   ├── Config.php               # Configuration class
│   ├── HttpClient.php           # HTTP client wrapper
│   ├── Auth/
│   │   ├── OAuthClient.php      # OAuth 2.0 implementation
│   │   ├── Token.php            # Token data object
│   │   └── TokenCache.php       # Token caching
│   ├── Exceptions/
│   │   └── ApiException.php     # Exception hierarchy
│   ├── Models/
│   │   └── Model.php            # Base and specific models
│   ├── Resources/
│   │   ├── Resource.php         # Base resource class
│   │   ├── Plans.php            # Plans resource
│   │   ├── Countries.php        # Countries resource
│   │   ├── Regions.php          # Regions resource
│   │   ├── Orders.php           # Orders resource
│   │   ├── Esims.php            # eSIMs resource
│   │   ├── Balance.php          # Balance resource
│   │   └── Webhooks.php         # Webhooks resource
│   └── Support/
│       ├── Collection.php       # Collection class
│       └── PaginatedCollection.php  # Pagination support
├── tests/
│   ├── Unit/                    # Unit tests
│   └── Feature/                 # Feature tests
├── examples/                    # Example scripts
├── composer.json
├── phpunit.xml
├── README.md
└── CONTRIBUTING.md
```

## Code Style

The SDK follows PSR-12 code style guidelines. Before committing:

```bash
vendor/bin/phpcs src/
```

To auto-fix style issues:

```bash
vendor/bin/phpcbf src/
```

## Static Analysis

Run PHPStan for type checking:

```bash
vendor/bin/phpstan analyse src/ --level 8
```

## Testing

Run all tests:

```bash
vendor/bin/phpunit
```

Run specific test suite:

```bash
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Feature/
```

Run with coverage:

```bash
vendor/bin/phpunit --coverage-html coverage/
```

## Before Submitting a PR

1. **Fork the repository**
   ```bash
   git clone https://github.com/your-username/touristesim-php-sdk.git
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Add or update code
   - Add tests for new functionality
   - Update documentation

4. **Run quality checks**
   ```bash
   vendor/bin/phpcs src/
   vendor/bin/phpstan analyse src/
   vendor/bin/phpunit
   ```

5. **Commit with clear messages**
   ```bash
   git commit -m "Add descriptive commit message"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Submit a Pull Request** to the main repository

## PR Guidelines

- **Keep PRs focused** - One feature or fix per PR
- **Write tests** - New functionality must have tests
- **Update docs** - Update README if adding new features
- **Clear commits** - Use descriptive commit messages
- **Follow style** - Code must pass phpcs checks

## Adding New Resources

To add a new API resource:

1. Create a new class in `src/Resources/YourResource.php`
2. Extend the `Resource` base class
3. Implement resource methods (get, find, create, etc.)
4. Add to the main `TouristEsim` class
5. Add tests in `tests/Feature/YourResourceTest.php`
6. Update README with usage examples

### Example Resource Template

```php
<?php

namespace TouristeSIM\Sdk\Resources;

class YourResource extends Resource
{
    /**
     * Get all items
     */
    public function all(array $filters = []): PaginatedCollection
    {
        $response = $this->client->get('/endpoint', $filters);
        return new PaginatedCollection(
            Collection::make($response['data']['items'] ?? []),
            $response['data']['pagination'] ?? []
        );
    }

    /**
     * Get single item
     */
    public function find(int $id): YourModel
    {
        $response = $this->client->get("/endpoint/{$id}");
        return new YourModel($response['data']);
    }
}
```

## Reporting Issues

When reporting issues, please include:
- PHP version
- SDK version
- Detailed error message
- Steps to reproduce
- Expected vs actual behavior

## Questions?

- Email: tech@touristesim.net
- GitHub Issues: https://github.com/touristesim/touristesim-php-sdk/issues

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing! 🎉
