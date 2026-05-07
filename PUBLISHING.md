# Publishing Laravel DDD Starter Kit

Complete guide to publish the package to GitHub and Packagist.

## Step 1: Prepare GitHub Repository

### Update composer.json
Ensure the package metadata is correct:

```json
{
    "name": "laravel-ddd/starter",
    "description": "DDD Starter Kit for Laravel 13/12 - Transform your Laravel project into a Domain-Driven Design architecture",
    "keywords": ["laravel", "ddd", "domain-driven-design", "starter-kit"],
    "homepage": "https://github.com/laravel-ddd/starter",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your-email@example.com"
        }
    ]
}
```

### Create GitHub Repository

1. Go to https://github.com/new
2. Repository name: `starter` (or `laravel-ddd-starter`)
3. Description: "DDD Starter Kit for Laravel 13/12"
4. Choose Public
5. Initialize with: None (we already have files)
6. Click "Create repository"

### Push to GitHub

```bash
# Add the remote
git remote set-url origin https://github.com/laravel-ddd/starter.git

# Verify
git remote -v

# Push to GitHub
git push -u origin main

# Create a release
git tag -a v1.0.0 -m "Initial release: Laravel DDD Starter Kit v1.0.0"
git push origin v1.0.0
```

## Step 2: Register on Packagist

### Create Packagist Account

1. Go to https://packagist.org
2. Sign up for a free account
3. Verify your email

### Submit Package

1. After login, click "Submit Package"
2. Enter repository URL: `https://github.com/laravel-ddd/starter`
3. Click "Check"
4. Review the information
5. Click "Submit"

### Enable Auto-Updates

1. Go to your package page on Packagist
2. Click "Settings"
3. Scroll to "GitHub Post Update Hook"
4. Follow instructions to enable automatic updates on git push

## Step 3: Add Packagist Badge

Update README.md with:

```markdown
[![Latest Stable Version](https://poser.pugx.org/laravel-ddd/starter/v/stable)](https://packagist.org/packages/laravel-ddd/starter)
[![Total Downloads](https://poser.pugx.org/laravel-ddd/starter/downloads)](https://packagist.org/packages/laravel-ddd/starter)
[![Latest Unstable Version](https://poser.pugx.org/laravel-ddd/starter/v/unstable)](https://packagist.org/packages/laravel-ddd/starter)
[![License](https://poser.pugx.org/laravel-ddd/starter/license)](https://packagist.org/packages/laravel-ddd/starter)
```

## Step 4: Create GitHub Releases

1. Go to Releases: https://github.com/laravel-ddd/starter/releases
2. Click "Draft a new release"
3. Tag: v1.0.0
4. Title: Laravel DDD Starter Kit v1.0.0
5. Description:

```markdown
# Laravel DDD Starter Kit v1.0.0

Initial release with complete DDD architecture support for Laravel 13 and 12.

## Features

- ✅ 10 powerful Artisan commands
- ✅ Interactive DDD installer
- ✅ Complete module generation
- ✅ Base classes for DDD patterns
- ✅ Comprehensive documentation
- ✅ Code generation stubs

## Installation

```bash
composer require laravel-ddd/starter
php artisan ddd:install
```

## Documentation

- [README.md](https://github.com/laravel-ddd/starter#readme)
- [Commands Reference](docs/commands.md)
- [Best Practices](docs/best-practices.md)
- [Routing Guide](docs/routes.md)

## Requirements

- Laravel 13.x, 12.x
- PHP 8.2+

## License

MIT License
```

## Step 5: Verify Installation

Test that the package can be installed:

```bash
# Create a new Laravel project
composer create-project laravel/laravel test-ddd

# Navigate to project
cd test-ddd

# Install the package
composer require laravel-ddd/starter

# Run the installer
php artisan ddd:install
```

## Step 6: Community Promotion

### Share on:
- Laravel News
- Laracasts
- Reddit (r/laravel)
- Twitter
- Laravel Discord community
- Hacker News (if appropriate)

### Sample Post:

```markdown
# Introducing Laravel DDD Starter Kit

I've created a complete Composer package that transforms any Laravel 13/12 
project into a Domain-Driven Design architecture.

## Features:
- Interactive `ddd:install` command
- 10 Artisan commands for generating modules, entities, services, etc.
- Complete DDD structure scaffold
- Comprehensive documentation and examples
- Base classes for DDD patterns

## Installation:
```bash
composer require laravel-ddd/starter
php artisan ddd:install
```

GitHub: https://github.com/laravel-ddd/starter
Packagist: https://packagist.org/packages/laravel-ddd/starter

Would love feedback and contributions!
```

## Step 7: Maintenance

### Keep Package Updated

1. Monitor Laravel releases
2. Update PHP minimum version if needed
3. Add new features based on community feedback
4. Create new releases and tags
5. Update CHANGELOG.md

### Example Release Process

```bash
# Make changes
# ... edit files ...

# Update CHANGELOG.md
# ... add new version section ...

# Create git tag
git tag -a v1.1.0 -m "Add domain event support"

# Push
git push origin main
git push origin v1.1.0

# On Packagist, the update happens automatically
```

### Semantic Versioning

Follow [Semantic Versioning](https://semver.org/):

- **PATCH** (1.0.X) - Bug fixes
- **MINOR** (1.X.0) - New features, backwards compatible
- **MAJOR** (X.0.0) - Breaking changes

## Troubleshooting

### Package Not Appearing on Packagist

1. Check that repository is public
2. Ensure composer.json is valid: `composer validate`
3. Try refreshing package on Packagist dashboard
4. Wait 5 minutes and try again

### Installation Fails

Check that:
1. composer.json has correct namespace: `LaravelDdd\\Starter\\`
2. src/ directory is properly PSR-4 autoloaded
3. Service Provider is registered in extra/laravel/providers
4. No syntax errors in PHP files

### Commands Not Available

Verify:
1. Service Provider is being loaded
2. Commands are registered in Providers/DddServiceProvider.php
3. Run `php artisan list` to see available commands

## Next Steps After Release

1. Monitor GitHub issues for feedback
2. Engage with community
3. Plan v1.1.0 features
4. Accept pull requests from contributors
5. Maintain CONTRIBUTING.md guidelines

## Resources

- [Packagist Documentation](https://packagist.org/about)
- [Composer Docs](https://getcomposer.org/doc/)
- [Laravel Package Development](https://laravel.com/docs/packages)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
