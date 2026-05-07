#!/bin/bash
set -e

PROJECT_NAME="test-ddd-project"
GITHUB_REPO="https://github.com/MMoza/laravel-ddd"

echo "🧪 Starting Laravel DDD Starter Package Tests"
echo "================================================"

# Cleanup if previous test exists
if [ -d "$PROJECT_NAME" ]; then
    echo "🧹 Cleaning up previous test project..."
    rm -rf $PROJECT_NAME
fi

# 1. Create new Laravel project
echo "📦 Step 1: Creating new Laravel project..."
composer create-project laravel/laravel $PROJECT_NAME --prefer-dist 2>&1 | tail -3
cd $PROJECT_NAME

# 2. Install package from GitHub
echo "📦 Step 2: Installing DDD Starter from GitHub..."
composer config repositories.ddd vcs $GITHUB_REPO
composer require laravel-ddd/starter:dev-main 2>&1 | tail -3

# 3. Run ddd:install with options
echo "🚀 Step 3: Running ddd:install..."
php artisan ddd:install --auth=none --module=users

# 4. Verify created structure
echo ""
echo "✅ Step 4: Verifying created structure..."
echo "----------------------------------------"

# Check Domains/Base
if [ -d "app/Domains/Base" ]; then
    echo "✅ app/Domains/Base/ exists"
else
    echo "❌ app/Domains/Base/ missing"
    exit 1
fi

# Check Domains/Users
if [ -d "app/Domains/Users" ]; then
    echo "✅ app/Domains/Users/ exists"
else
    echo "❌ app/Domains/Users/ missing"
    exit 1
fi

# Check Base classes
for file in Entity.php ValueObject.php RepositoryInterface.php Service.php; do
    if [ -f "app/Domains/Base/$file" ]; then
        echo "✅ app/Domains/Base/$file exists"
    else
        echo "❌ app/Domains/Base/$file missing"
        exit 1
    fi
done

# Check Users module structure
for dir in Entities Repositories Services Http/Controllers Http/Requests Http/Resources Routes Providers Tests; do
    if [ -d "app/Domains/Users/$dir" ]; then
        echo "✅ app/Domains/Users/$dir exists"
    else
        echo "❌ app/Domains/Users/$dir missing"
        exit 1
    fi
done

# Check User entity
if [ -f "app/Domains/Users/Entities/User.php" ]; then
    echo "✅ app/Domains/Users/Entities/User.php exists"
else
    echo "❌ app/Domains/Users/Entities/User.php missing"
    exit 1
fi

# Check User model
if [ -f "app/Models/User.php" ]; then
    echo "✅ app/Models/User.php exists"
else
    echo "❌ app/Models/User.php missing"
    exit 1
fi

# Check migration
MIGRATION_COUNT=$(ls database/migrations/*_create_users_table.php 2>/dev/null | wc -l)
if [ "$MIGRATION_COUNT" -gt 0 ]; then
    echo "✅ Users migration exists in database/migrations/"
else
    echo "❌ Users migration missing in database/migrations/"
    exit 1
fi

# 5. Test ddd:make-module for Posts
echo ""
echo "📦 Step 5: Testing ddd:make-module Posts..."
php artisan ddd:make-module Posts

# Verify Posts structure
if [ -d "app/Domains/Posts" ]; then
    echo "✅ app/Domains/Posts/ created"
else
    echo "❌ app/Domains/Posts/ missing"
    exit 1
fi

# Check Post entity (singular)
if [ -f "app/Domains/Posts/Entities/Post.php" ]; then
    echo "✅ app/Domains/Posts/Entities/Post.php exists"
else
    echo "❌ app/Domains/Posts/Entities/Post.php missing"
    exit 1
fi

# Check Post migration
POST_MIGRATION_COUNT=$(ls database/migrations/*_create_posts_table.php 2>/dev/null | wc -l)
if [ "$POST_MIGRATION_COUNT" -gt 0 ]; then
    echo "✅ Posts migration exists in database/migrations/"
else
    echo "❌ Posts migration missing in database/migrations/"
    exit 1
fi

# 6. List DDD commands
echo ""
echo "🔧 Step 6: Available DDD commands..."
php artisan list ddd 2>&1 | grep "ddd:"

# 7. Cleanup
echo ""
echo "🧹 Cleaning up test project..."
cd ..
rm -rf $PROJECT_NAME

echo ""
echo "✅ All tests passed successfully!"
echo "================================================"
echo "🎉 Laravel DDD Starter Kit is working correctly!"
