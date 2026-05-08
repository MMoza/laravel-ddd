# AGENTS.md - Laravel DDD Starter Kit

## GitFlow Workflow

### Branch Structure
```
main        → Production code (only merges from release/*)
develop     → Active development branch
feature/*   → New features (from develop)
fix/*       → Bug fixes (from develop)
release/*   → Release preparation (from develop → main)
hotfix/*    → Urgent production fixes (from main → develop + main)
```

### Creating New Work

**Feature:**
```bash
git checkout develop
git pull origin develop
git checkout -b feature/description
# Work + tests
git push origin feature/description
# Create PR → develop
```

**Bug Fix:**
```bash
git checkout develop
git pull origin develop
git checkout -b fix/description
# Work + tests
git push origin fix/description
# Create PR → develop
```

**Release:**
```bash
git checkout develop
git pull origin develop
git checkout -b release/x.y.z
# Update composer.json version
# Final tests
git tag -a vx.y.z -m "Release vx.y.z"
git push origin release/x.y.z
# Create PR → main
```

**Hotfix:**
```bash
git checkout main
git pull origin main
git checkout -b hotfix/description
# Fix + tests
git tag -a vx.y.z -m "Release vx.y.z"
git push origin hotfix/description
# Create PR → main
```

### Conventional Commits

| Type | Description |
|------|-------------|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `docs:` | Documentation |
| `style:` | Formatting (no code changes) |
| `refactor:` | Code refactoring |
| `test:` | Tests |
| `chore:` | Maintenance |

**Examples:**
```
feat: Add test package selection
fix: Missing moduleName parameter
docs: Update README
refactor: Improve test generation
```

### Testing

Always run tests before committing:
```bash
./vendor/bin/phpunit
```

### Branch Protection Rules

| Branch | Protected | Rules |
|--------|-----------|-------|
| `main` | ✅ | Require 1 review, no force push |
| `develop` | ✅ | Require 1 review, no force push |
| `release/*` | ❌ | Require 1 review |
| `hotfix/*` | ❌ | Require 1 review |

### Versioning

Use semantic versioning: `vMajor.Minor.Patch`
- `v1.0.0` - Initial release
- `v1.1.0` - New features
- `v1.0.1` - Bug fixes

Tags are only created from `main` or `release/*` branches.
