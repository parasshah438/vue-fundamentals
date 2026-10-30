# Laravel 13 + React Full-Stack Blogging Website - Complete Setup Guide (A to Z)

> A modern, fully responsive full-stack blogging platform built with Laravel 13 (REST API), React 19 (SPA), Laravel Sanctum, Laravel Fortify, Spatie Laravel Permission, React Router, Axios, and Bootstrap 5.3. Includes a public website, an admin dashboard, role-based access control, comments, tags/categories, a media library, and dark/light mode.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Project Architecture](#project-architecture)
- [Step 1: Create Laravel 13 Backend Project](#step-1-create-laravel-13-backend-project)
- [Step 2: Install Authentication Packages (Sanctum & Fortify)](#step-2-install-authentication-packages-sanctum--fortify)
- [Step 3: Install Spatie Laravel Permission](#step-3-install-spatie-laravel-permission)
- [Step 4: Environment & Database Configuration](#step-4-environment--database-configuration)
- [Step 5: Set Up React with Vite](#step-5-set-up-react-with-vite)
- [Step 6: Install Frontend Dependencies](#step-6-install-frontend-dependencies)
- [Step 7: Configure Vite for React](#step-7-configure-vite-for-react)
- [Step 8: Configure Sanctum & Middleware](#step-8-configure-sanctum--middleware)
- [Step 9: Configure Fortify](#step-9-configure-fortify)
- [Step 10: Setup User Model, Roles & Permissions](#step-10-setup-user-model-roles--permissions)
- [Step 11: Database Schema Design & Migrations](#step-11-database-schema-design--migrations)
- [Step 12: Eloquent Models & Relationships](#step-12-eloquent-models--relationships)
- [Step 13: API Routes, Controllers & Policies](#step-13-api-routes-controllers--policies)
- [Step 14: SPA View & Web Routes](#step-14-spa-view--web-routes)
- [Step 15: Axios Setup, Auth Context & Hooks](#step-15-axios-setup-auth-context--hooks)
- [Step 16: React Router, Layouts & Route Guards](#step-16-react-router-layouts--route-guards)
- [Step 17: Build Public Blogging Website](#step-17-build-public-blogging-website)
- [Step 18: Build Admin Dashboard & Management Modules](#step-18-build-admin-dashboard--management-modules)
- [Step 19: Comments, Bookmarks & Newsletter](#step-19-comments-bookmarks--newsletter)
- [Step 20: SEO, Media Library & Settings](#step-20-seo-media-library--settings)
- [Step 21: Database Seeders (Sample Data & Default Admin)](#step-21-database-seeders-sample-data--default-admin)
- [Step 22: Run Migrations, Seed & Start Development](#step-22-run-migrations-seed--start-development)
- [Step 23: Using Ready-Made HTML Bootstrap Templates (Blog & Admin)](#step-23-using-ready-made-html-bootstrap-templates-blog--admin)
- [Project File Structure](#project-file-structure)
- [Production Build & Deployment](#production-build--deployment)
- [Security Best Practices](#security-best-practices)
- [Performance Optimization](#performance-optimization)
- [Troubleshooting](#troubleshooting)
- [Useful Commands](#useful-commands)

---

## Project Overview

This guide builds a full-featured blogging platform with Laravel 13 as a RESTful API backend and React as a single-page application. It includes a public blog and an admin dashboard with role-based permissions.

**How it is wired:** Laravel serves the SPA shell and the API from the same origin (`http://localhost:8000`). Vite only supplies the JS/CSS assets and hot reload during development. Because the browser talks to a single origin, Sanctum cookie authentication works with no CORS configuration.

---

## Key Features

### Public Website
- Home page with hero, featured, trending and latest posts
- Blog listing with categories, tags, search and pagination
- Single post view with author, read time, related posts, comments
- Category, tag and author archives
- Bookmark posts
- Comment system with guest and authenticated support (guest comments are moderated)
- Newsletter subscription
- About and Contact pages
- Social sharing, SEO-friendly URLs, per-post meta tags
- Responsive design, skeleton loaders and empty states

### Admin Dashboard
- Analytics overview with charts and stats
- Post management (CRUD, publish/unpublish, schedule)
- Category and tag management
- Media library with image uploads
- User management
- Role and permission management
- Comment moderation and replies
- SEO fields per post
- Site settings and profile management
- Light/Dark mode

### Security & Auth
- Session-based SPA auth via Sanctum
- Fortify auth flows (Login, Register, Forgot/Reset password, Email verification)
- Spatie RBAC (Super Admin, Admin, Editor, Author, User)
- Server-side authorization with policies and permission middleware
- CSRF protection, rate limiting, protected routes
- Server-side validation with error display in the UI

---

## Tech Stack

| Layer | Technology | Purpose |
| :-- | :-- | :-- |
| Backend | Laravel 13.x | API, auth, business logic, DB |
| Frontend | React 19.x | SPA (public site and admin) |
| Auth | Laravel Sanctum + Fortify | Session auth, CSRF, auth flows |
| RBAC | Spatie Laravel Permission | Roles and permissions |
| Routing | React Router DOM 7.x | Client routing, guards, nested routes |
| HTTP | Axios 1.x | API calls with credentials |
| UI | Bootstrap 5.3 + Bootstrap Icons | Responsive UI, icons, dark mode |
| Charts | Chart.js + react-chartjs-2 | Analytics |
| Editor | react-quill-new + DOMPurify | Rich text editing and safe HTML rendering |
| Utils | date-fns, react-hot-toast | Dates and notifications |
| State | React Context API | Auth and theme state |

---

## Prerequisites

- PHP 8.3+ (as required by Laravel 13)
- Composer 2.7+
- Node.js 20.19+ (LTS) and npm
- MySQL 8.0+ / MariaDB 10.11+
- Git
- Local server (Laragon/WAMP/XAMPP/Herd) for MySQL. The app itself runs with `php artisan serve`.

---

## Project Architecture

```text
Browser ── http://localhost:8000 ──► Laravel 13 ──► MySQL
   │                                   ├─ /              SPA shell (app.blade.php)
   │                                   ├─ /api/*         REST API (Sanctum session auth)
   │                                   ├─ /login ...     Fortify JSON endpoints
   │                                   └─ /storage/*     Uploaded files
   │
   └── http://localhost:5173 (dev only) ► Vite: JS/CSS assets + hot reload
```

---

## Step 1: Create Laravel 13 Backend Project

```bash
composer create-project laravel/laravel laravel-react-blog
cd laravel-react-blog
```

---

## Step 2: Install Authentication Packages (Sanctum & Fortify)

```bash
# Sanctum + routes/api.php (answer "no" if asked to run migrations now)
php artisan install:api

# Fortify + its actions, service provider and config
composer require laravel/fortify
php artisan fortify:install
```

`fortify:install` registers `App\Providers\FortifyServiceProvider` in `bootstrap/providers.php`. Verify that it is listed.

---

## Step 3: Install Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Migrations run later, in [Step 11](#step-11-database-schema-design--migrations).

---

## Step 4: Environment & Database Configuration

Update `.env` (keep the generated `APP_KEY`):

```env
APP_NAME="Blogify"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_react_blog
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@blogify.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Create the database `laravel_react_blog` in your DB client.

> Always open the app at `http://localhost:8000`. Mixing `localhost` and `127.0.0.1` creates two different cookie scopes.

---

## Step 5: Set Up React with Vite

Laravel ships with Vite and (in new skeletons) Tailwind. This project uses Bootstrap, so remove Tailwind and the default entry files.

```bash
npm install
npm uninstall tailwindcss @tailwindcss/vite   # skip if not installed
npm install react react-dom
npm install -D @vitejs/plugin-react
```

Delete the default files that are no longer used:

- `resources/js/app.js`
- `resources/js/bootstrap.js`
- `resources/css/app.css`

---

## Step 6: Install Frontend Dependencies

```bash
npm install react-router-dom axios bootstrap bootstrap-icons
npm install chart.js react-chartjs-2 date-fns react-hot-toast
npm install react-quill-new dompurify
```

---

## Step 7: Configure Vite for React

### 7.1 `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/main.jsx'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            // __dirname does not exist in ES modules
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
```

---

## Step 8: Configure Sanctum & Middleware

The SPA and the API share one origin, so no CORS setup is needed. Laravel 13 has no `config/cors.php` by default and none is required here.

### 8.1 `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Session/cookie auth for requests coming from the SPA
        $middleware->statefulApi();

        // Fortify views are disabled, so there is no named "login" route to redirect to
        $middleware->redirectGuestsTo('/login');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### 8.2 `bootstrap/providers.php`

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
```

---

## Step 9: Configure Fortify

### 9.1 `config/fortify.php`

Disable Fortify's Blade views (the SPA renders the forms), keep only the features the SPA uses, and redirect to `/` after email verification.

```php
'views' => false,

'home' => '/',

'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
],
```

Profile and password updates are handled by `ProfileController` (Step 13), so Fortify's `updateProfileInformation` and `updatePasswords` features are intentionally disabled.

### 9.2 `app/Providers/FortifyServiceProvider.php`

Fortify returns JSON automatically for requests sent with `Accept: application/json`, so no custom response classes are needed.

```php
<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Only active users can log in
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && $user->status === 'active' && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // Point the reset e-mail link to the SPA route
        ResetPassword::createUrlUsing(fn ($user, string $token) =>
            url('/reset-password/'.$token).'?email='.urlencode($user->getEmailForPasswordReset())
        );
    }
}
```

### 9.3 `app/Actions/Fortify/CreateNewUser.php`

Replace the generated file so new users receive the `user` role:

```php
<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        return tap(
            User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]),
            fn (User $user) => $user->assignRole('user')
        );
    }
}
```

The generated `PasswordValidationRules` trait and `ResetUserPassword` action stay as generated.

---

## Step 10: Setup User Model, Roles & Permissions

### 10.1 Update `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'bio', 'avatar', 'website',
        'facebook', 'twitter', 'instagram', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->attributes['avatar'] ?? null;

        return $avatar ? '/storage/'.$avatar : null;
    }

    public function posts() { return $this->hasMany(Post::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function bookmarks() { return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps(); }
}
```

`email_verified_at` is deliberately not mass assignable. Seeders and admin code set it explicitly.

### 10.2 Extend Users Table

```bash
php artisan make:migration add_profile_social_to_users_table --table=users
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('website')->nullable()->after('avatar');
            $table->string('facebook')->nullable()->after('website');
            $table->string('twitter')->nullable()->after('facebook');
            $table->string('instagram')->nullable()->after('twitter');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'avatar', 'website', 'facebook', 'twitter', 'instagram', 'status']);
        });
    }
};
```

---

## Step 11: Database Schema Design & Migrations

Create the migrations **in this order**. Foreign keys require the parent tables to exist first. If two files get the same timestamp prefix, rename one so they sort in the order listed.

```bash
php artisan make:migration create_categories_table
php artisan make:migration create_tags_table
php artisan make:migration create_posts_table
php artisan make:migration create_post_tag_table
php artisan make:migration create_comments_table
php artisan make:migration create_media_table
php artisan make:migration create_bookmarks_table
php artisan make:migration create_newsletters_table
php artisan make:migration create_settings_table
```

Put the code below inside each `up()` method.

### 11.1 `create_categories_table`

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->boolean('status')->default(true);
    $table->timestamps();
});
```

### 11.2 `create_tags_table`

```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->timestamps();
});
```

### 11.3 `create_posts_table`

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('excerpt')->nullable();
    $table->longText('content');
    $table->string('featured_image')->nullable();
    $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_trending')->default(false);
    $table->unsignedBigInteger('views')->default(0);
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->string('meta_keywords')->nullable();
    $table->timestamps();
    $table->index(['status', 'published_at']);
});
```

### 11.4 `create_post_tag_table`

```php
Schema::create('post_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['post_id', 'tag_id']);
});
```

### 11.5 `create_comments_table`

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
    $table->string('name')->nullable();
    $table->string('email')->nullable();
    $table->text('content');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();
    $table->index(['post_id', 'status']);
});
```

### 11.6 `create_media_table`

```php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('file_name');
    $table->string('file_path');
    $table->string('mime_type');
    $table->unsignedBigInteger('size');
    $table->string('alt_text')->nullable();
    $table->timestamps();
});
```

### 11.7 `create_bookmarks_table`

```php
Schema::create('bookmarks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['user_id', 'post_id']);
});
```

### 11.8 `create_newsletters_table`

```php
Schema::create('newsletters', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->boolean('status')->default(true);
    $table->timestamps();
});
```

### 11.9 `create_settings_table`

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->longText('value')->nullable();
    $table->timestamps();
});
```

Run all migrations (Laravel defaults, Sanctum, Fortify, Spatie and the tables above):

```bash
php artisan migrate
```

---

## Step 12: Eloquent Models & Relationships

Create the models **without** `-m`. The migrations already exist from Step 11, and `-m` would create duplicate `create_*_table` migrations that fail with "table already exists".

```bash
php artisan make:model Post
php artisan make:model Category
php artisan make:model Tag
php artisan make:model Comment
php artisan make:model Media
php artisan make:model Newsletter
php artisan make:model Setting
```

### 12.1 `app/Models/Concerns/HasSlug.php`

Shared unique-slug helper for posts, categories and tags.

```php
<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
```

### 12.2 `app/Models/Post.php`

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'status', 'published_at', 'is_featured', 'is_trending',
        'views', 'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function approvedComments() { return $this->hasMany(Comment::class)->where('status', 'approved')->whereNull('parent_id'); }
    public function bookmarkedBy() { return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps(); }
}
```

### 12.3 `app/Models/Category.php`

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function posts() { return $this->hasMany(Post::class); }
}
```

### 12.4 `app/Models/Tag.php`

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug'];

    public function posts() { return $this->belongsToMany(Post::class); }
}
```

### 12.5 `app/Models/Comment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'name', 'email', 'content', 'status'];

    // Guest e-mails must never leak through the public API; admin code uses makeVisible('email')
    protected $hidden = ['email'];

    public function post() { return $this->belongsTo(Post::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
}
```

### 12.6 `app/Models/Media.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_name', 'file_path', 'mime_type', 'size', 'alt_text'];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return '/storage/'.$this->file_path;
    }

    public function user() { return $this->belongsTo(User::class); }
}
```

### 12.7 `app/Models/Newsletter.php` and `app/Models/Setting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = ['email', 'status'];
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
}
```

---

## Step 13: API Routes, Controllers & Policies

### 13.1 Generate Controllers

```bash
php artisan make:controller Api/Public/PostController
php artisan make:controller Api/Public/CategoryController
php artisan make:controller Api/Public/CommentController
php artisan make:controller Api/Public/NewsletterController
php artisan make:controller Api/Admin/DashboardController
php artisan make:controller Api/Admin/PostController
php artisan make:controller Api/Admin/CategoryController
php artisan make:controller Api/Admin/TagController
php artisan make:controller Api/Admin/CommentController
php artisan make:controller Api/Admin/MediaController
php artisan make:controller Api/Admin/UserController
php artisan make:controller Api/Admin/RoleController
php artisan make:controller Api/Admin/PermissionController
php artisan make:controller Api/Admin/SettingController
php artisan make:controller Api/ProfileController
php artisan make:policy PostPolicy --model=Post
```

### 13.2 `routes/api.php`

All routes get the `/api` prefix automatically. Admin routes are guarded by permissions, and post ownership is enforced by `PostPolicy`.

```php
<?php

use App\Http\Controllers\Api\Admin\{
    CategoryController as AdminCategoryController,
    CommentController as AdminCommentController,
    DashboardController as AdminDashboardController,
    MediaController as AdminMediaController,
    PermissionController as AdminPermissionController,
    PostController as AdminPostController,
    RoleController as AdminRoleController,
    SettingController as AdminSettingController,
    TagController as AdminTagController,
    UserController as AdminUserController,
};
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Public\{
    CategoryController as PublicCategoryController,
    CommentController as PublicCommentController,
    NewsletterController as PublicNewsletterController,
    PostController as PublicPostController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('posts', [PublicPostController::class, 'index']);
    Route::get('posts/featured', [PublicPostController::class, 'featured']);
    Route::get('posts/trending', [PublicPostController::class, 'trending']);
    Route::get('posts/latest', [PublicPostController::class, 'latest']);
    Route::get('posts/{slug}', [PublicPostController::class, 'show']);
    Route::get('posts/{slug}/comments', [PublicCommentController::class, 'index']);
    Route::get('authors/{id}', [PublicPostController::class, 'author']);
    Route::get('categories', [PublicCategoryController::class, 'index']);
    Route::get('categories/{slug}', [PublicCategoryController::class, 'show']);
    Route::get('tags', [PublicCategoryController::class, 'tags']);
    Route::get('settings', [AdminSettingController::class, 'index']);
    Route::post('comments', [PublicCommentController::class, 'store'])->middleware('throttle:10,1');
    Route::post('newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])->middleware('throttle:5,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [ProfileController::class, 'me']);

    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::put('/', 'update');
        Route::put('password', 'updatePassword');
        Route::post('avatar', 'updateAvatar');
        Route::get('bookmarks', 'bookmarks');
        Route::get('bookmark-ids', 'bookmarkIds');
        Route::post('posts/{post}/bookmark', 'toggleBookmark');
    });

    Route::middleware('role:super-admin|admin|editor|author')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:view dashboard');

        // Posts: access is decided by PostPolicy (ownership + permissions)
        Route::apiResource('posts', AdminPostController::class);
        Route::post('posts/{post}/publish', [AdminPostController::class, 'publish']);
        Route::post('posts/{post}/unpublish', [AdminPostController::class, 'unpublish']);

        // Authors need to read categories and tags for the post form
        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::get('tags', [AdminTagController::class, 'index']);

        Route::middleware('permission:manage categories')->group(function () {
            Route::apiResource('categories', AdminCategoryController::class)->only(['store', 'update', 'destroy']);
        });

        Route::middleware('permission:manage tags')->group(function () {
            Route::apiResource('tags', AdminTagController::class)->only(['store', 'update', 'destroy']);
        });

        Route::middleware('permission:manage comments')->group(function () {
            Route::apiResource('comments', AdminCommentController::class)->only(['index', 'destroy']);
            Route::post('comments/{comment}/approve', [AdminCommentController::class, 'approve']);
            Route::post('comments/{comment}/reject', [AdminCommentController::class, 'reject']);
            Route::post('comments/{comment}/reply', [AdminCommentController::class, 'reply']);
        });

        // "media" singularizes to "medium", so the routes are declared explicitly
        Route::middleware('permission:manage media')->group(function () {
            Route::get('media', [AdminMediaController::class, 'index']);
            Route::post('media', [AdminMediaController::class, 'store']);
            Route::delete('media/{media}', [AdminMediaController::class, 'destroy']);
        });

        Route::middleware('permission:manage settings')->group(function () {
            Route::post('settings', [AdminSettingController::class, 'update']);
        });

        Route::middleware('permission:manage users')->group(function () {
            Route::apiResource('users', AdminUserController::class)->except('show');
        });

        Route::get('roles', [AdminRoleController::class, 'index'])->middleware('permission:manage users|manage roles');
        Route::get('permissions', [AdminPermissionController::class, 'index'])->middleware('permission:manage roles|manage permissions');

        Route::middleware('permission:manage roles')->group(function () {
            Route::apiResource('roles', AdminRoleController::class)->only(['store', 'update', 'destroy']);
        });

        Route::post('permissions', [AdminPermissionController::class, 'store'])->middleware('permission:manage permissions');
    });
});
```

### 13.3 `app/Policies/PostPolicy.php`

Policies are auto-discovered (`Post` maps to `PostPolicy`). Authors can only change their own posts. Users with `manage posts` can change any post.

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage posts', 'create posts']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['manage posts', 'create posts']);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('manage posts')
            || ($user->can('edit posts') && $post->user_id === $user->id);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('manage posts')
            || ($user->can('delete posts') && $post->user_id === $user->id);
    }

    public function publish(User $user): bool
    {
        return $user->hasAnyPermission(['manage posts', 'publish posts']);
    }
}
```

### 13.4 `app/Providers/AppServiceProvider.php`

Super admins pass every authorization check.

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);
    }
}
```

### 13.5 `routes/console.php`

Publishes scheduled posts when their time arrives. Run `php artisan schedule:work` in development, or a cron entry in production.

```php
<?php

use App\Models\Post;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => Post::where('status', 'scheduled')
    ->where('published_at', '<=', now())
    ->update(['status' => 'published']))
    ->everyMinute()
    ->name('publish-scheduled-posts');
```

### 13.6 Public Controllers

#### `Api/Public/PostController.php`

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private const LIST_COLUMNS = [
        'id', 'user_id', 'category_id', 'title', 'slug', 'excerpt',
        'featured_image', 'published_at', 'views', 'is_featured', 'is_trending',
    ];

    private function listQuery()
    {
        return Post::published()
            ->select(self::LIST_COLUMNS)
            ->with(['category:id,name,slug', 'user:id,name,avatar'])
            ->withCount(['comments as comments_count' => fn ($q) => $q->where('status', 'approved')]);
    }

    public function index(Request $request)
    {
        return $this->listQuery()
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->query('tag'), fn ($q, $slug) => $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
            ->when($request->query('author'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->query('search'), fn ($q, $term) => $q->where(
                fn ($w) => $w->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%")
            ))
            ->latest('published_at')
            ->paginate(9);
    }

    public function featured()
    {
        return $this->listQuery()->where('is_featured', true)->latest('published_at')->take(3)->get();
    }

    public function trending()
    {
        return $this->listQuery()->where('is_trending', true)->orderByDesc('views')->take(6)->get();
    }

    public function latest()
    {
        return $this->listQuery()->latest('published_at')->take(6)->get();
    }

    public function show(string $slug)
    {
        $post = Post::published()
            ->with([
                'category:id,name,slug',
                'tags:id,name,slug',
                'user:id,name,avatar,bio,website,facebook,twitter,instagram',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // toBase() avoids touching updated_at
        Post::whereKey($post->id)->toBase()->increment('views');
        $post->views++;
        $post->read_time = max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200));

        $related = $this->listQuery()
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return response()->json(['post' => $post, 'related' => $related]);
    }

    public function author(int $id)
    {
        return User::select('id', 'name', 'avatar', 'bio', 'website', 'facebook', 'twitter', 'instagram')
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->findOrFail($id);
    }
}
```

#### `Api/Public/CategoryController.php`

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::where('status', true)
            ->withCount(['posts as posts_count' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    public function show(string $slug)
    {
        return Category::where('status', true)->where('slug', $slug)->firstOrFail();
    }

    public function tags()
    {
        return Tag::withCount(['posts as posts_count' => fn ($q) => $q->published()])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->take(30)
            ->get();
    }
}
```

#### `Api/Public/CommentController.php`

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function index(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return $post->approvedComments()
            ->with([
                'user:id,name,avatar',
                'replies' => fn ($q) => $q->where('status', 'approved')->with('user:id,name,avatar')->oldest(),
            ])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $user = $request->user('sanctum');

        $data = $request->validate([
            'post_id' => ['required', Rule::exists('posts', 'id')->where('status', 'published')],
            'parent_id' => ['nullable', 'integer'],
            'content' => ['required', 'string', 'max:2000'],
            'name' => [$user ? 'nullable' : 'required', 'string', 'max:100'],
            'email' => [$user ? 'nullable' : 'required', 'email', 'max:150'],
        ]);

        // Replies are one level deep and must belong to the same post
        if (! empty($data['parent_id'])) {
            $validParent = Comment::whereKey($data['parent_id'])
                ->where('post_id', $data['post_id'])
                ->whereNull('parent_id')
                ->exists();

            if (! $validParent) {
                throw ValidationException::withMessages(['parent_id' => 'Invalid parent comment.']);
            }
        }

        $comment = Comment::create(Arr::only($data, ['post_id', 'parent_id', 'content', 'name', 'email']) + [
            'user_id' => $user?->id,
            'status' => $user ? 'approved' : 'pending',
        ]);

        return response()->json([
            'message' => $comment->status === 'approved' ? 'Comment posted.' : 'Your comment is awaiting moderation.',
            'comment' => $comment->load('user:id,name,avatar'),
        ], 201);
    }
}
```

#### `Api/Public/NewsletterController.php`

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:150']]);

        Newsletter::firstOrCreate(['email' => $data['email']]);

        return response()->json(['message' => 'Thanks for subscribing!']);
    }
}
```

### 13.7 Admin Controllers

#### `Api/Admin/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Newsletter;
use App\Models\Post;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $perMonth = Post::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return response()->json([
            'stats' => [
                'posts' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'views' => (int) Post::sum('views'),
                'comments' => Comment::count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
                'users' => User::count(),
                'subscribers' => Newsletter::count(),
            ],
            'posts_per_month' => $perMonth,
            'posts_by_category' => Category::withCount('posts')->get()
                ->map(fn ($c) => ['name' => $c->name, 'total' => $c->posts_count]),
            'recent_posts' => Post::with('user:id,name')->latest()->take(5)
                ->get(['id', 'user_id', 'title', 'slug', 'status', 'views', 'created_at']),
            'recent_comments' => Comment::with('post:id,title,slug')->latest()->take(5)->get(),
        ]);
    }
}
```

#### `Api/Admin/PostController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Post::class);
        $user = $request->user();

        return Post::select([
                'id', 'user_id', 'category_id', 'title', 'slug', 'status',
                'published_at', 'is_featured', 'is_trending', 'views', 'created_at',
            ])
            ->with(['category:id,name', 'user:id,name'])
            // Authors only see their own posts
            ->when(! $user->can('manage posts'), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('category_id'), fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->query('search'), fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->latest()
            ->paginate(min($request->integer('per_page', 10), 50));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Post::class);

        $data = $this->validated($request);
        $data['slug'] = Post::makeSlug($data['title']);
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post = Post::create(Arr::except($data, 'tags'));
        $post->tags()->sync($data['tags'] ?? []);

        return response()->json($post->load('category:id,name', 'tags:id,name'), 201);
    }

    public function show(Post $post)
    {
        Gate::authorize('update', $post);

        return $post->load('tags:id,name');
    }

    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        // The slug stays stable after creation so published URLs do not break
        $data = $this->validated($request, $post);

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post->update(Arr::except($data, 'tags'));
        $post->tags()->sync($data['tags'] ?? []);

        return $post->load('category:id,name', 'tags:id,name');
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();

        return response()->noContent();
    }

    public function publish(Post $post)
    {
        Gate::authorize('publish', Post::class);

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at?->isPast() ? $post->published_at : now(),
        ]);

        return $post;
    }

    public function unpublish(Post $post)
    {
        Gate::authorize('publish', Post::class);
        $post->update(['status' => 'draft']);

        return $post;
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'published_at' => ['nullable', 'date', Rule::requiredIf($request->input('status') === 'scheduled')],
            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        // Users without the publish permission cannot change the publication state
        if (! Gate::allows('publish', Post::class)) {
            $data['status'] = $post?->status ?? 'draft';
        }

        if ($data['status'] === 'published') {
            $data['published_at'] ??= $post?->published_at ?? now();
        }

        return $data;
    }
}
```

> Multipart requests cannot use the `PUT` method. The React editor sends `POST` with `_method=PUT`. Boolean fields must be sent as `1`/`0` in `FormData`.

#### `Api/Admin/CategoryController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::withCount('posts')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['boolean'],
        ]);

        return response()->json(Category::create($data + ['slug' => Category::makeSlug($data['name'])]), 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['boolean'],
        ]);

        $category->update($data + ['slug' => Category::makeSlug($data['name'], $category->id)]);

        return $category;
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return response()->json(['message' => 'This category has posts and cannot be deleted.'], 422);
        }

        $category->delete();

        return response()->noContent();
    }
}
```

#### `Api/Admin/TagController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        return Tag::withCount('posts')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:50', 'unique:tags,name']]);

        return response()->json(Tag::create($data + ['slug' => Tag::makeSlug($data['name'])]), 201);
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->ignore($tag)],
        ]);

        $tag->update($data + ['slug' => Tag::makeSlug($data['name'], $tag->id)]);

        return $tag;
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->noContent();
    }
}
```

#### `Api/Admin/CommentController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        return Comment::with(['post:id,title,slug', 'user:id,name'])
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->through(fn ($comment) => $comment->makeVisible('email'));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);

        return $comment;
    }

    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);

        return $comment;
    }

    public function reply(Request $request, Comment $comment)
    {
        $data = $request->validate(['content' => ['required', 'string', 'max:2000']]);

        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'parent_id' => $comment->parent_id ?? $comment->id,
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'status' => 'approved',
        ]);

        return response()->json($reply, 201);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }
}
```

#### `Api/Admin/MediaController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        return Media::latest()->paginate(24);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');

        $media = Media::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $request->input('alt_text'),
            'file_path' => $file->store('media', 'public'),
        ]);

        return response()->json($media, 201);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->noContent();
    }
}
```

#### `Api/Admin/UserController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::with('roles:id,name')
            ->when($request->query('search'), fn ($q, $v) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")
            ))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = new User(Arr::only($data, ['name', 'email', 'password', 'status']));
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles($data['roles'] ?? ['user']);

        return response()->json($user->load('roles:id,name'), 201);
    }

    public function update(Request $request, User $user)
    {
        $this->guardSuperAdmin($request, $user);
        $data = $this->validated($request, $user);

        // An empty password means "keep the current one"
        $user->update(array_filter(
            Arr::only($data, ['name', 'email', 'password', 'status']),
            fn ($value) => $value !== null
        ));

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        return $user->load('roles:id,name');
    }

    public function destroy(Request $request, User $user)
    {
        $this->guardSuperAdmin($request, $user);
        abort_if($user->is($request->user()), 422, 'You cannot delete your own account.');

        $user->delete();

        return response()->noContent();
    }

    private function guardSuperAdmin(Request $request, User $target): void
    {
        abort_if(
            $target->hasRole('super-admin') && ! $request->user()->hasRole('super-admin'),
            403,
            'Only a super admin can modify a super admin.'
        );
    }

    private function validated(Request $request, ?User $user = null): array
    {
        // Only a super admin may hand out the super-admin role
        $assignable = Role::query()
            ->when(! $request->user()->hasRole('super-admin'), fn ($q) => $q->where('name', '!=', 'super-admin'))
            ->pluck('name');

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', Password::defaults()],
            'status' => ['required', Rule::in(['active', 'inactive', 'banned'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => [Rule::in($assignable)],
        ]);
    }
}
```

#### `Api/Admin/RoleController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const CORE_ROLES = ['super-admin', 'admin', 'editor', 'author', 'user'];

    public function index()
    {
        return Role::with('permissions:id,name')->withCount('users')->orderBy('id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json($role->load('permissions:id,name'), 201);
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->name === 'super-admin', 403, 'The super-admin role cannot be modified.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        if (! in_array($role->name, self::CORE_ROLES, true)) {
            $role->update(['name' => $data['name']]);
        }
        $role->syncPermissions($data['permissions'] ?? []);

        return $role->load('permissions:id,name');
    }

    public function destroy(Role $role)
    {
        abort_if(in_array($role->name, self::CORE_ROLES, true), 403, 'Core roles cannot be deleted.');

        $role->delete();

        return response()->noContent();
    }
}
```

#### `Api/Admin/PermissionController.php`

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::orderBy('name')->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:permissions,name']]);

        return response()->json(Permission::create($data), 201);
    }
}
```

#### `Api/Admin/SettingController.php`

`index` is also used by the public `/api/public/settings` route. It only exposes site-level keys.

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        return Cache::rememberForever('settings', fn () => Setting::pluck('value', 'key'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        foreach (Arr::only($data, ['site_name', 'site_description', 'footer_text']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('site_logo')) {
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $request->file('site_logo')->store('branding', 'public')]);
        }

        Cache::forget('settings');

        return $this->index();
    }
}
```

### 13.8 `Api/ProfileController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $user->fill($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
        ]));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return response()->json(['message' => 'Password updated.']);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);

        return $user;
    }

    public function bookmarks(Request $request)
    {
        return $request->user()->bookmarks()
            ->published()
            ->with(['category:id,name,slug', 'user:id,name,avatar'])
            ->latest('bookmarks.created_at')
            ->paginate(9)
            ->through(fn ($post) => $post->makeHidden('content'));
    }

    public function bookmarkIds(Request $request)
    {
        return $request->user()->bookmarks()->pluck('posts.id');
    }

    public function toggleBookmark(Request $request, Post $post)
    {
        abort_unless($post->status === 'published', 404);

        $changes = $request->user()->bookmarks()->toggle($post->id);

        return response()->json(['bookmarked' => count($changes['attached']) > 0]);
    }
}
```

---

## Step 14: SPA View & Web Routes

### 14.1 `resources/views/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ config('app.name', 'Blogify') }} - articles, tutorials and stories.">
    <title>{{ config('app.name', 'Blogify') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite('resources/js/main.jsx')
</head>
<body>
    <div id="root"></div>
</body>
</html>
```

Apply the saved theme before React loads to avoid a flash. Add this inside `<head>`, before `@vite`:

```html
<script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('blog_theme') || 'light');</script>
```

### 14.2 `routes/web.php`

The catch-all lets React Router handle deep links. It must not swallow API, Sanctum or storage URLs. Fortify's `POST` routes are not affected because the catch-all is `GET` only.

```php
<?php

use Illuminate\Support\Facades\Route;

Route::view('/{any?}', 'app')->where('any', '(?!api/|sanctum/|storage/|up$).*');
```

---

## Step 15: Axios Setup, Auth Context & Hooks

### 15.1 `resources/js/bootstrap/axios.js`

A dedicated Axios instance with credentials, XSRF handling and one automatic retry on `419` (expired CSRF token).

```js
import axios from 'axios';

const api = axios.create({
    baseURL: '/',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export const getCsrfCookie = () => api.get('/sanctum/csrf-cookie');

api.interceptors.response.use(null, async (error) => {
    const { config, response } = error;

    if (response?.status === 419 && !config._retried) {
        config._retried = true;
        await getCsrfCookie();
        return api(config);
    }

    return Promise.reject(error);
});

// First validation message, or the server message, or a generic fallback
export const errorMessage = (error) => {
    const errors = error.response?.data?.errors;
    if (errors) return Object.values(errors).flat()[0];
    return error.response?.data?.message || 'Something went wrong. Please try again.';
};

export default api;
```

### 15.2 `resources/js/context/AuthContext.jsx`

```jsx
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api, { getCsrfCookie } from '@/bootstrap/axios';

const AuthContext = createContext(null);

const GUEST = { user: null, roles: [], permissions: [], loading: false };
const ADMIN_ROLES = ['super-admin', 'admin', 'editor', 'author'];
const toArray = (value) => (Array.isArray(value) ? value : [value]);

export const useAuth = () => {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
};

export const AuthProvider = ({ children }) => {
    const [state, setState] = useState({ ...GUEST, loading: true });

    const fetchUser = useCallback(async () => {
        try {
            const { data } = await api.get('/api/user');
            setState({ user: data.user, roles: data.roles, permissions: data.permissions, loading: false });
        } catch {
            setState(GUEST);
        }
    }, []);

    useEffect(() => { fetchUser(); }, [fetchUser]);

    const login = useCallback(async (credentials) => {
        await getCsrfCookie();
        await api.post('/login', credentials);
        await fetchUser();
    }, [fetchUser]);

    const register = useCallback(async (payload) => {
        await getCsrfCookie();
        await api.post('/register', payload);
        await fetchUser();
    }, [fetchUser]);

    const logout = useCallback(async () => {
        await api.post('/logout');
        setState(GUEST);
    }, []);

    const value = useMemo(() => ({
        ...state,
        isAuthenticated: !!state.user,
        canAccessAdmin: ADMIN_ROLES.some((r) => state.roles.includes(r)),
        hasRole: (role) => toArray(role).some((r) => state.roles.includes(r)),
        hasPermission: (perm) => toArray(perm).some((p) => state.permissions.includes(p)),
        login, register, logout, fetchUser,
    }), [state, login, register, logout, fetchUser]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
```

`hasRole` and `hasPermission` return `true` when **any** of the given values match.

### 15.3 `resources/js/context/ThemeContext.jsx`

```jsx
import { createContext, useContext, useEffect, useMemo, useState } from 'react';

const ThemeContext = createContext(null);
export const useTheme = () => useContext(ThemeContext);

export const ThemeProvider = ({ children }) => {
    const [theme, setTheme] = useState(() => localStorage.getItem('blog_theme') || 'light');

    useEffect(() => {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('blog_theme', theme);
    }, [theme]);

    const value = useMemo(
        () => ({ theme, toggleTheme: () => setTheme((t) => (t === 'light' ? 'dark' : 'light')) }),
        [theme]
    );

    return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
};
```

### 15.4 `resources/js/hooks/useFetch.js`

GET helper with loading/error state, request cancellation and a `reload` function.

```js
import { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import api from '@/bootstrap/axios';

export default function useFetch(url, params = {}) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const key = JSON.stringify(params);

    const load = useCallback(() => {
        const controller = new AbortController();
        setLoading(true);

        api.get(url, { params: JSON.parse(key), signal: controller.signal })
            .then((res) => { setData(res.data); setError(null); setLoading(false); })
            .catch((e) => {
                if (axios.isCancel(e)) return;
                setError(e);
                setLoading(false);
            });

        return () => controller.abort();
    }, [url, key]);

    useEffect(() => load(), [load]);

    return { data, loading, error, reload: load };
}
```

### 15.5 `resources/js/hooks/useForm.js`

```js
import { useState } from 'react';

// Returns [values, bind (onChange handler), setValues]
export default function useForm(initial) {
    const [values, setValues] = useState(initial);

    const bind = (e) => {
        const { name, type, checked, value } = e.target;
        setValues((v) => ({ ...v, [name]: type === 'checkbox' ? checked : value }));
    };

    return [values, bind, setValues];
}
```

### 15.6 Shared Components

`resources/js/components/common/Spinner.jsx`

```jsx
export default function Spinner({ fullPage = false }) {
    return (
        <div className={`d-flex justify-content-center align-items-center ${fullPage ? 'vh-100' : 'py-5'}`}>
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );
}
```

`resources/js/components/common/Field.jsx`

```jsx
// Bootstrap form control with server validation message. Use as="select" or as="textarea" when needed.
export default function Field({ label, name, error, as: Tag = 'input', children, ...props }) {
    return (
        <div className="mb-3">
            {label && <label htmlFor={name} className="form-label">{label}</label>}
            <Tag
                id={name}
                name={name}
                className={`${Tag === 'select' ? 'form-select' : 'form-control'} ${error ? 'is-invalid' : ''}`}
                {...props}
            >
                {children}
            </Tag>
            {error && <div className="invalid-feedback">{error}</div>}
        </div>
    );
}
```

`resources/js/components/common/Pagination.jsx`

```jsx
export default function Pagination({ page, lastPage, onChange }) {
    if (!lastPage || lastPage <= 1) return null;

    const pages = Array.from({ length: lastPage }, (_, i) => i + 1)
        .filter((p) => p === 1 || p === lastPage || Math.abs(p - page) <= 2);

    return (
        <nav aria-label="Pagination">
            <ul className="pagination justify-content-center mb-0">
                <li className={`page-item ${page === 1 ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => onChange(page - 1)}>Previous</button>
                </li>
                {pages.map((p) => (
                    <li key={p} className={`page-item ${p === page ? 'active' : ''}`}>
                        <button className="page-link" onClick={() => onChange(p)}>{p}</button>
                    </li>
                ))}
                <li className={`page-item ${page === lastPage ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => onChange(page + 1)}>Next</button>
                </li>
            </ul>
        </nav>
    );
}
```

---

## Step 16: React Router, Layouts & Route Guards

### 16.1 Route Guards

`resources/js/routes/ProtectedRoute.jsx`: requires a logged-in user.

```jsx
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

export default function ProtectedRoute() {
    const { isAuthenticated, loading } = useAuth();
    const location = useLocation();

    if (loading) return <Spinner fullPage />;

    return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace state={{ from: location }} />;
}
```

`resources/js/routes/GuestRoute.jsx`: login/register pages for guests only. Logged-in users go back to the page they came from.

```jsx
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

export default function GuestRoute() {
    const { isAuthenticated, canAccessAdmin, loading } = useAuth();
    const location = useLocation();

    if (loading) return <Spinner fullPage />;
    if (!isAuthenticated) return <Outlet />;

    const target = location.state?.from?.pathname || (canAccessAdmin ? '/admin/dashboard' : '/');
    return <Navigate to={target} replace />;
}
```

`resources/js/routes/AdminRoute.jsx`: requires a role that can use the admin area. Fine-grained access is enforced by the API.

```jsx
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

export default function AdminRoute() {
    const { isAuthenticated, canAccessAdmin, loading } = useAuth();
    const location = useLocation();

    if (loading) return <Spinner fullPage />;
    if (!isAuthenticated) return <Navigate to="/login" replace state={{ from: location }} />;

    return canAccessAdmin ? <Outlet /> : <Navigate to="/" replace />;
}
```

### 16.2 Layouts

`resources/js/layouts/PublicLayout.jsx`

```jsx
import { useState } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';
import { useTheme } from '@/context/ThemeContext';
import useFetch from '@/hooks/useFetch';
import NewsletterForm from '@/components/public/NewsletterForm';

export default function PublicLayout() {
    const { isAuthenticated, canAccessAdmin, user, logout } = useAuth();
    const { theme, toggleTheme } = useTheme();
    const { data: settings } = useFetch('/api/public/settings');
    const { data: categories } = useFetch('/api/public/categories');
    const [menuOpen, setMenuOpen] = useState(false);
    const [userMenu, setUserMenu] = useState(false);
    const [term, setTerm] = useState('');
    const navigate = useNavigate();

    const siteName = settings?.site_name || 'Blogify';
    const closeMenus = () => { setMenuOpen(false); setUserMenu(false); };

    const search = (e) => {
        e.preventDefault();
        if (term.trim()) {
            navigate(`/search?q=${encodeURIComponent(term.trim())}`);
            closeMenus();
        }
    };

    const handleLogout = async () => {
        await logout();
        toast.success('Logged out');
        closeMenus();
        navigate('/');
    };

    return (
        <div className="d-flex flex-column min-vh-100">
            <nav className="navbar navbar-expand-lg bg-body-tertiary border-bottom sticky-top">
                <div className="container">
                    <Link className="navbar-brand fw-bold" to="/" onClick={closeMenus}>{siteName}</Link>
                    <button className="navbar-toggler" type="button" aria-label="Toggle navigation" onClick={() => setMenuOpen(!menuOpen)}>
                        <span className="navbar-toggler-icon"></span>
                    </button>

                    <div className={`collapse navbar-collapse ${menuOpen ? 'show' : ''}`}>
                        <ul className="navbar-nav me-auto" onClick={closeMenus}>
                            <li className="nav-item"><NavLink className="nav-link" to="/blog">Blog</NavLink></li>
                            {categories?.slice(0, 4).map((c) => (
                                <li key={c.id} className="nav-item">
                                    <NavLink className="nav-link" to={`/category/${c.slug}`}>{c.name}</NavLink>
                                </li>
                            ))}
                            <li className="nav-item"><NavLink className="nav-link" to="/about">About</NavLink></li>
                            <li className="nav-item"><NavLink className="nav-link" to="/contact">Contact</NavLink></li>
                        </ul>

                        <form className="d-flex me-lg-3 my-2 my-lg-0" onSubmit={search} role="search">
                            <input className="form-control form-control-sm me-2" type="search" placeholder="Search..." value={term} onChange={(e) => setTerm(e.target.value)} />
                            <button className="btn btn-sm btn-outline-primary" type="submit"><i className="bi bi-search"></i></button>
                        </form>

                        <div className="d-flex align-items-center gap-2">
                            <button className="btn btn-sm btn-outline-secondary" onClick={toggleTheme} aria-label="Toggle theme">
                                <i className={`bi bi-${theme === 'light' ? 'moon' : 'sun'}`}></i>
                            </button>

                            {isAuthenticated ? (
                                <div className="position-relative">
                                    <button className="btn btn-sm btn-primary" onClick={() => setUserMenu(!userMenu)}>
                                        <i className="bi bi-person-circle me-1"></i>{user.name}
                                    </button>
                                    <ul className={`dropdown-menu dropdown-menu-end ${userMenu ? 'show' : ''}`} onClick={closeMenus}>
                                        {canAccessAdmin && <li><Link className="dropdown-item" to="/admin/dashboard">Dashboard</Link></li>}
                                        <li><Link className="dropdown-item" to="/profile">My Profile</Link></li>
                                        <li><hr className="dropdown-divider" /></li>
                                        <li><button className="dropdown-item" onClick={handleLogout}>Logout</button></li>
                                    </ul>
                                </div>
                            ) : (
                                <>
                                    <Link className="btn btn-sm btn-outline-primary" to="/login">Login</Link>
                                    <Link className="btn btn-sm btn-primary" to="/register">Sign up</Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            <main className="flex-grow-1"><Outlet /></main>

            <footer className="bg-body-tertiary border-top mt-5 py-5">
                <div className="container">
                    <div className="row g-4">
                        <div className="col-md-4">
                            <h5>{siteName}</h5>
                            <p className="text-body-secondary">{settings?.site_description}</p>
                        </div>
                        <div className="col-md-4">
                            <h6>Quick links</h6>
                            <ul className="list-unstyled">
                                <li><Link to="/blog">Blog</Link></li>
                                <li><Link to="/about">About</Link></li>
                                <li><Link to="/contact">Contact</Link></li>
                            </ul>
                        </div>
                        <div className="col-md-4">
                            <h6>Newsletter</h6>
                            <NewsletterForm />
                        </div>
                    </div>
                    <hr />
                    <p className="text-center text-body-secondary mb-0">{settings?.footer_text}</p>
                </div>
            </footer>
        </div>
    );
}
```

`resources/js/layouts/AuthLayout.jsx`

```jsx
import { Link, Outlet } from 'react-router-dom';

export default function AuthLayout() {
    return (
        <div className="min-vh-100 d-flex align-items-center bg-body-tertiary py-5">
            <div className="container" style={{ maxWidth: 440 }}>
                <Link to="/" className="d-block text-center fs-3 fw-bold text-decoration-none mb-4">Blogify</Link>
                <div className="card shadow-sm border-0">
                    <div className="card-body p-4"><Outlet /></div>
                </div>
            </div>
        </div>
    );
}
```

`resources/js/layouts/AdminLayout.jsx`

```jsx
import { useState } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';
import { useTheme } from '@/context/ThemeContext';

const MENU = [
    { to: '/admin/dashboard', icon: 'speedometer2', label: 'Dashboard', permission: 'view dashboard' },
    { to: '/admin/posts', icon: 'file-earmark-text', label: 'Posts', permission: ['manage posts', 'create posts'] },
    { to: '/admin/categories', icon: 'folder', label: 'Categories', permission: 'manage categories' },
    { to: '/admin/tags', icon: 'tags', label: 'Tags', permission: 'manage tags' },
    { to: '/admin/media', icon: 'images', label: 'Media', permission: 'manage media' },
    { to: '/admin/comments', icon: 'chat-dots', label: 'Comments', permission: 'manage comments' },
    { to: '/admin/users', icon: 'people', label: 'Users', permission: 'manage users' },
    { to: '/admin/roles', icon: 'shield-lock', label: 'Roles & Permissions', permission: 'manage roles' },
    { to: '/admin/settings', icon: 'gear', label: 'Settings', permission: 'manage settings' },
    { to: '/profile', icon: 'person-circle', label: 'Profile' },
];

export default function AdminLayout() {
    const { user, hasPermission, logout } = useAuth();
    const { theme, toggleTheme } = useTheme();
    const [open, setOpen] = useState(false);
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        toast.success('Logged out');
        navigate('/login');
    };

    return (
        <div className="d-flex">
            <aside className={`admin-sidebar bg-body-tertiary border-end p-3 ${open ? 'open' : ''}`}>
                <Link to="/" className="d-block fs-4 fw-bold text-decoration-none mb-4">Blogify</Link>
                <ul className="nav nav-pills flex-column gap-1" onClick={() => setOpen(false)}>
                    {MENU.filter((m) => !m.permission || hasPermission(m.permission)).map((m) => (
                        <li key={m.to} className="nav-item">
                            <NavLink to={m.to} className="nav-link">
                                <i className={`bi bi-${m.icon} me-2`}></i>{m.label}
                            </NavLink>
                        </li>
                    ))}
                </ul>
            </aside>

            <div className="flex-grow-1 min-vh-100 d-flex flex-column" style={{ minWidth: 0 }}>
                <header className="navbar bg-body border-bottom px-3">
                    <button className="btn btn-outline-secondary d-lg-none" onClick={() => setOpen(!open)} aria-label="Toggle sidebar">
                        <i className="bi bi-list"></i>
                    </button>
                    <div className="ms-auto d-flex align-items-center gap-2">
                        <Link to="/" className="btn btn-sm btn-outline-secondary">View site</Link>
                        <button className="btn btn-sm btn-outline-secondary" onClick={toggleTheme} aria-label="Toggle theme">
                            <i className={`bi bi-${theme === 'light' ? 'moon' : 'sun'}`}></i>
                        </button>
                        <span className="small d-none d-sm-inline">{user?.name}</span>
                        <button className="btn btn-sm btn-outline-danger" onClick={handleLogout}>Logout</button>
                    </div>
                </header>
                <main className="p-4 flex-grow-1"><Outlet /></main>
            </div>
        </div>
    );
}
```

### 16.3 Router `resources/js/routes/AppRouter.jsx`

Admin pages are lazy loaded, so public visitors never download the admin code.

```jsx
import { lazy, Suspense } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import GuestRoute from './GuestRoute';
import ProtectedRoute from './ProtectedRoute';
import AdminRoute from './AdminRoute';
import Spinner from '@/components/common/Spinner';

import PublicLayout from '@/layouts/PublicLayout';
import AuthLayout from '@/layouts/AuthLayout';
import AdminLayout from '@/layouts/AdminLayout';

import Home from '@/pages/public/Home';
import PostArchive from '@/pages/public/PostArchive';
import BlogDetail from '@/pages/public/BlogDetail';
import About from '@/pages/public/About';
import Contact from '@/pages/public/Contact';
import Login from '@/pages/auth/Login';
import Register from '@/pages/auth/Register';
import ForgotPassword from '@/pages/auth/ForgotPassword';
import ResetPassword from '@/pages/auth/ResetPassword';
import VerifyEmail from '@/pages/auth/VerifyEmail';
import Profile from '@/pages/Profile';
import NotFound from '@/pages/NotFound';

const Dashboard = lazy(() => import('@/pages/admin/Dashboard'));
const PostsIndex = lazy(() => import('@/pages/admin/posts/Index'));
const PostEditor = lazy(() => import('@/pages/admin/posts/Editor'));
const Categories = lazy(() => import('@/pages/admin/Categories'));
const Tags = lazy(() => import('@/pages/admin/Tags'));
const Media = lazy(() => import('@/pages/admin/Media'));
const Comments = lazy(() => import('@/pages/admin/Comments'));
const Users = lazy(() => import('@/pages/admin/Users'));
const Roles = lazy(() => import('@/pages/admin/Roles'));
const Settings = lazy(() => import('@/pages/admin/Settings'));

export default function AppRouter() {
    return (
        <BrowserRouter>
            <Suspense fallback={<Spinner fullPage />}>
                <Routes>
                    <Route element={<PublicLayout />}>
                        <Route path="/" element={<Home />} />
                        <Route path="/blog" element={<PostArchive />} />
                        <Route path="/blog/:slug" element={<BlogDetail />} />
                        <Route path="/category/:slug" element={<PostArchive />} />
                        <Route path="/tag/:slug" element={<PostArchive />} />
                        <Route path="/author/:id" element={<PostArchive />} />
                        <Route path="/search" element={<PostArchive />} />
                        <Route path="/about" element={<About />} />
                        <Route path="/contact" element={<Contact />} />

                        <Route element={<ProtectedRoute />}>
                            <Route path="/profile" element={<Profile />} />
                            <Route path="/email/verify" element={<VerifyEmail />} />
                        </Route>
                    </Route>

                    <Route element={<GuestRoute />}>
                        <Route element={<AuthLayout />}>
                            <Route path="/login" element={<Login />} />
                            <Route path="/register" element={<Register />} />
                            <Route path="/forgot-password" element={<ForgotPassword />} />
                            <Route path="/reset-password/:token" element={<ResetPassword />} />
                        </Route>
                    </Route>

                    <Route element={<AdminRoute />}>
                        <Route path="/admin" element={<Navigate to="/admin/dashboard" replace />} />
                        <Route element={<AdminLayout />}>
                            <Route path="/admin/dashboard" element={<Dashboard />} />
                            <Route path="/admin/posts" element={<PostsIndex />} />
                            <Route path="/admin/posts/create" element={<PostEditor />} />
                            <Route path="/admin/posts/:id/edit" element={<PostEditor />} />
                            <Route path="/admin/categories" element={<Categories />} />
                            <Route path="/admin/tags" element={<Tags />} />
                            <Route path="/admin/media" element={<Media />} />
                            <Route path="/admin/comments" element={<Comments />} />
                            <Route path="/admin/users" element={<Users />} />
                            <Route path="/admin/roles" element={<Roles />} />
                            <Route path="/admin/settings" element={<Settings />} />
                        </Route>
                    </Route>

                    <Route path="*" element={<NotFound />} />
                </Routes>
            </Suspense>
        </BrowserRouter>
    );
}
```

### 16.4 `resources/js/main.jsx`

```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { Toaster } from 'react-hot-toast';
import AppRouter from './routes/AppRouter';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import './assets/css/app.css';

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <ThemeProvider>
            <AuthProvider>
                <AppRouter />
                <Toaster position="top-right" />
            </AuthProvider>
        </ThemeProvider>
    </React.StrictMode>
);
```

Bootstrap's JavaScript is not imported. Navbar, dropdown and sidebar toggles are plain React state, so there is no conflict between Bootstrap's DOM handling and React.

### 16.5 `resources/js/assets/css/app.css`

```css
body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

.post-content { font-size: 1.1rem; line-height: 1.8; }
.post-content img { max-width: 100%; height: auto; }

.admin-sidebar { width: 250px; min-height: 100vh; flex-shrink: 0; }
.admin-sidebar .nav-link { color: var(--bs-body-color); }
.admin-sidebar .nav-link.active { color: #fff; }

@media (max-width: 991.98px) {
    .admin-sidebar { position: fixed; z-index: 1040; transform: translateX(-100%); transition: transform .2s; }
    .admin-sidebar.open { transform: none; }
}
```

---

## Step 17: Build Public Blogging Website

### 17.1 Components

`resources/js/components/public/PostCard.jsx`

```jsx
import { Link } from 'react-router-dom';
import { format } from 'date-fns';

export default function PostCard({ post }) {
    return (
        <article className="card border-0 shadow-sm h-100">
            <Link to={`/blog/${post.slug}`}>
                {post.featured_image ? (
                    <img
                        src={`/storage/${post.featured_image}`}
                        className="card-img-top"
                        alt={post.title}
                        loading="lazy"
                        style={{ height: 220, objectFit: 'cover' }}
                    />
                ) : (
                    <div className="bg-body-secondary d-flex align-items-center justify-content-center" style={{ height: 220 }}>
                        <i className="bi bi-image fs-1 text-secondary"></i>
                    </div>
                )}
            </Link>
            <div className="card-body">
                <Link to={`/category/${post.category.slug}`} className="badge bg-primary text-decoration-none mb-2">
                    {post.category.name}
                </Link>
                <h5 className="card-title">
                    <Link to={`/blog/${post.slug}`} className="text-decoration-none text-body">{post.title}</Link>
                </h5>
                <p className="card-text text-body-secondary">{post.excerpt}</p>
            </div>
            <div className="card-footer bg-transparent border-0 d-flex justify-content-between">
                <small className="text-body-secondary">
                    {post.user?.name} &middot; {format(new Date(post.published_at), 'MMM dd, yyyy')}
                </small>
                <small className="text-body-secondary"><i className="bi bi-eye me-1"></i>{post.views}</small>
            </div>
        </article>
    );
}
```

`resources/js/components/public/PostGrid.jsx`: grid with skeleton loader and empty state.

```jsx
import PostCard from './PostCard';

export default function PostGrid({ posts, loading }) {
    if (loading) {
        return (
            <div className="row g-4">
                {[0, 1, 2].map((i) => (
                    <div key={i} className="col-md-6 col-lg-4">
                        <div className="card border-0 shadow-sm h-100" aria-hidden="true">
                            <div className="bg-body-secondary" style={{ height: 220 }}></div>
                            <div className="card-body placeholder-glow">
                                <span className="placeholder col-6"></span>
                                <span className="placeholder col-12"></span>
                                <span className="placeholder col-8"></span>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (!posts?.length) {
        return (
            <div className="text-center text-body-secondary py-5">
                <i className="bi bi-journal-x fs-1"></i>
                <p className="mt-2 mb-0">No posts found.</p>
            </div>
        );
    }

    return (
        <div className="row g-4">
            {posts.map((post) => (
                <div key={post.id} className="col-md-6 col-lg-4"><PostCard post={post} /></div>
            ))}
        </div>
    );
}
```

`resources/js/components/public/NewsletterForm.jsx`

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';

export default function NewsletterForm() {
    const [email, setEmail] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        try {
            const { data } = await api.post('/api/public/newsletter/subscribe', { email });
            toast.success(data.message);
            setEmail('');
        } catch (err) {
            toast.error(errorMessage(err));
        } finally {
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit} className="input-group">
            <input type="email" className="form-control" placeholder="Your email" value={email} onChange={(e) => setEmail(e.target.value)} required />
            <button className="btn btn-primary" disabled={busy}>Subscribe</button>
        </form>
    );
}
```

### 17.2 Home Page `resources/js/pages/public/Home.jsx`

```jsx
import { Link } from 'react-router-dom';
import useFetch from '@/hooks/useFetch';
import PostGrid from '@/components/public/PostGrid';
import NewsletterForm from '@/components/public/NewsletterForm';

export default function Home() {
    const featured = useFetch('/api/public/posts/featured');
    const trending = useFetch('/api/public/posts/trending');
    const latest = useFetch('/api/public/posts/latest');
    const categories = useFetch('/api/public/categories');
    const hero = featured.data?.[0];

    return (
        <>
            <title>Home</title>

            <section className="bg-body-tertiary py-5">
                <div className="container py-4 text-center">
                    {hero ? (
                        <>
                            <span className="badge bg-primary mb-3">Featured</span>
                            <h1 className="display-5 fw-bold">{hero.title}</h1>
                            <p className="lead text-body-secondary mx-auto" style={{ maxWidth: 700 }}>{hero.excerpt}</p>
                            <Link to={`/blog/${hero.slug}`} className="btn btn-primary btn-lg">Read article</Link>
                        </>
                    ) : (
                        <>
                            <h1 className="display-5 fw-bold">Welcome to the blog</h1>
                            <Link to="/blog" className="btn btn-primary btn-lg mt-3">Browse articles</Link>
                        </>
                    )}
                </div>
            </section>

            <div className="container">
                <section className="mt-5">
                    <h2 className="h3 mb-4">Trending</h2>
                    <PostGrid posts={trending.data} loading={trending.loading} />
                </section>

                <section className="mt-5">
                    <div className="d-flex justify-content-between align-items-center mb-4">
                        <h2 className="h3 mb-0">Latest posts</h2>
                        <Link to="/blog">View all</Link>
                    </div>
                    <PostGrid posts={latest.data} loading={latest.loading} />
                </section>

                <section className="mt-5">
                    <h2 className="h3 mb-3">Categories</h2>
                    <div className="d-flex flex-wrap gap-2">
                        {categories.data?.map((c) => (
                            <Link key={c.id} to={`/category/${c.slug}`} className="btn btn-outline-primary">
                                {c.name} <span className="badge bg-primary ms-1">{c.posts_count}</span>
                            </Link>
                        ))}
                    </div>
                </section>

                <section className="mt-5 p-5 rounded bg-primary-subtle text-center">
                    <h2 className="h4">Never miss a post</h2>
                    <p>Get new articles delivered to your inbox.</p>
                    <div className="mx-auto" style={{ maxWidth: 420 }}><NewsletterForm /></div>
                </section>
            </div>
        </>
    );
}
```

### 17.3 Archive Page `resources/js/pages/public/PostArchive.jsx`

One page serves `/blog`, `/category/:slug`, `/tag/:slug`, `/author/:id` and `/search`. The API supports the `category`, `tag`, `author` and `search` filters.

```jsx
import { Link, useLocation, useParams, useSearchParams } from 'react-router-dom';
import useFetch from '@/hooks/useFetch';
import PostGrid from '@/components/public/PostGrid';
import Pagination from '@/components/common/Pagination';

export default function PostArchive() {
    const { slug, id } = useParams();
    const { pathname } = useLocation();
    const [params, setParams] = useSearchParams();
    const page = Number(params.get('page') || 1);
    const query = params.get('q') || '';

    let filter = {};
    let title = 'All posts';
    if (pathname.startsWith('/category/')) { filter = { category: slug }; title = `Category: ${slug.replace(/-/g, ' ')}`; }
    else if (pathname.startsWith('/tag/')) { filter = { tag: slug }; title = `Tag: ${slug.replace(/-/g, ' ')}`; }
    else if (pathname.startsWith('/author/')) { filter = { author: id }; title = 'Posts by author'; }
    else if (pathname.startsWith('/search')) { filter = { search: query }; title = `Search results for "${query}"`; }

    const { data, loading } = useFetch('/api/public/posts', { ...filter, page });
    const categories = useFetch('/api/public/categories');
    const tags = useFetch('/api/public/tags');

    const goTo = (p) => {
        setParams((prev) => { const next = new URLSearchParams(prev); next.set('page', p); return next; });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <div className="container py-5">
            <title>{title}</title>
            <h1 className="h2 mb-4 text-capitalize">{title}</h1>

            <div className="row g-4">
                <div className="col-lg-9">
                    <PostGrid posts={data?.data} loading={loading} />
                    <div className="mt-4">
                        <Pagination page={data?.current_page ?? 1} lastPage={data?.last_page ?? 1} onChange={goTo} />
                    </div>
                </div>

                <aside className="col-lg-3">
                    <div className="card border-0 shadow-sm mb-4">
                        <div className="card-body">
                            <h6>Categories</h6>
                            <ul className="list-unstyled mb-0">
                                {categories.data?.map((c) => (
                                    <li key={c.id} className="d-flex justify-content-between">
                                        <Link to={`/category/${c.slug}`}>{c.name}</Link>
                                        <span className="text-body-secondary">{c.posts_count}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <h6>Popular tags</h6>
                            <div className="d-flex flex-wrap gap-1">
                                {tags.data?.map((t) => (
                                    <Link key={t.id} to={`/tag/${t.slug}`} className="badge text-bg-secondary text-decoration-none">{t.name}</Link>
                                ))}
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    );
}
```

### 17.4 Blog Detail `resources/js/pages/public/BlogDetail.jsx`

Post HTML is sanitized with DOMPurify before rendering.

```jsx
import { useEffect, useMemo, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { format } from 'date-fns';
import DOMPurify from 'dompurify';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import Spinner from '@/components/common/Spinner';
import PostGrid from '@/components/public/PostGrid';
import CommentSection from '@/components/public/CommentSection';
import NotFound from '@/pages/NotFound';

export default function BlogDetail() {
    const { slug } = useParams();
    const { isAuthenticated } = useAuth();
    const { data, loading, error } = useFetch(`/api/public/posts/${slug}`);
    const [bookmarked, setBookmarked] = useState(false);

    const post = data?.post;
    const postId = post?.id;
    const html = useMemo(() => DOMPurify.sanitize(post?.content || ''), [post?.content]);

    useEffect(() => {
        if (!isAuthenticated || !postId) return;
        api.get('/api/profile/bookmark-ids').then(({ data: ids }) => setBookmarked(ids.includes(postId)));
    }, [isAuthenticated, postId]);

    const toggleBookmark = async () => {
        try {
            const { data: res } = await api.post(`/api/profile/posts/${postId}/bookmark`);
            setBookmarked(res.bookmarked);
            toast.success(res.bookmarked ? 'Saved to bookmarks' : 'Removed from bookmarks');
        } catch (err) {
            toast.error(errorMessage(err));
        }
    };

    if (loading) return <Spinner />;
    if (error || !post) return <NotFound />;

    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(post.title);
    const description = post.meta_description || post.excerpt || '';

    return (
        <article className="container py-5" style={{ maxWidth: 860 }}>
            <title>{post.meta_title || post.title}</title>
            <meta name="description" content={description} />
            {post.meta_keywords && <meta name="keywords" content={post.meta_keywords} />}
            <meta property="og:title" content={post.meta_title || post.title} />
            <meta property="og:description" content={description} />
            {post.featured_image && <meta property="og:image" content={`${window.location.origin}/storage/${post.featured_image}`} />}

            <Link to={`/category/${post.category.slug}`} className="badge bg-primary text-decoration-none mb-3">{post.category.name}</Link>
            <h1 className="display-6 fw-bold">{post.title}</h1>

            <div className="d-flex flex-wrap align-items-center gap-3 text-body-secondary my-3">
                <Link to={`/author/${post.user.id}`} className="text-decoration-none">
                    <i className="bi bi-person me-1"></i>{post.user.name}
                </Link>
                <span><i className="bi bi-calendar me-1"></i>{format(new Date(post.published_at), 'MMM dd, yyyy')}</span>
                <span><i className="bi bi-clock me-1"></i>{post.read_time} min read</span>
                <span><i className="bi bi-eye me-1"></i>{post.views}</span>
                {isAuthenticated && (
                    <button className="btn btn-sm btn-outline-primary ms-auto" onClick={toggleBookmark}>
                        <i className={`bi bi-bookmark${bookmarked ? '-fill' : ''} me-1`}></i>{bookmarked ? 'Saved' : 'Save'}
                    </button>
                )}
            </div>

            {post.featured_image && (
                <img src={`/storage/${post.featured_image}`} alt={post.title} className="img-fluid rounded mb-4 w-100" />
            )}

            <div className="post-content" dangerouslySetInnerHTML={{ __html: html }} />

            <div className="mt-4 d-flex flex-wrap gap-2">
                {post.tags.map((t) => (
                    <Link key={t.id} to={`/tag/${t.slug}`} className="badge text-bg-secondary text-decoration-none">#{t.name}</Link>
                ))}
            </div>

            <div className="d-flex align-items-center gap-2 my-4">
                <span className="text-body-secondary">Share:</span>
                <a className="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer" href={`https://www.facebook.com/sharer/sharer.php?u=${url}`}><i className="bi bi-facebook"></i></a>
                <a className="btn btn-sm btn-outline-dark" target="_blank" rel="noopener noreferrer" href={`https://twitter.com/intent/tweet?url=${url}&text=${text}`}><i className="bi bi-twitter-x"></i></a>
                <a className="btn btn-sm btn-outline-success" target="_blank" rel="noopener noreferrer" href={`https://wa.me/?text=${text}%20${url}`}><i className="bi bi-whatsapp"></i></a>
                <button className="btn btn-sm btn-outline-secondary" onClick={() => { navigator.clipboard.writeText(window.location.href); toast.success('Link copied'); }}>
                    <i className="bi bi-link-45deg"></i>
                </button>
            </div>

            <div className="card border-0 bg-body-tertiary mb-5">
                <div className="card-body d-flex gap-3 align-items-center">
                    {post.user.avatar_url
                        ? <img src={post.user.avatar_url} alt="" className="rounded-circle" width="64" height="64" style={{ objectFit: 'cover' }} />
                        : <i className="bi bi-person-circle fs-1"></i>}
                    <div>
                        <h6 className="mb-1">{post.user.name}</h6>
                        <p className="mb-0 text-body-secondary">{post.user.bio || 'Author at this blog.'}</p>
                    </div>
                </div>
            </div>

            <CommentSection slug={post.slug} postId={post.id} />

            {data.related.length > 0 && (
                <section className="mt-5">
                    <h2 className="h4 mb-3">Related posts</h2>
                    <PostGrid posts={data.related} loading={false} />
                </section>
            )}
        </article>
    );
}
```

### 17.5 Static Pages and 404

`resources/js/pages/public/About.jsx`

```jsx
export default function About() {
    return (
        <div className="container py-5" style={{ maxWidth: 760 }}>
            <title>About</title>
            <h1 className="mb-4">About us</h1>
            <p className="lead">Write your story here: who runs the blog, what topics it covers and why it exists.</p>
        </div>
    );
}
```

`resources/js/pages/public/Contact.jsx`

```jsx
export default function Contact() {
    return (
        <div className="container py-5" style={{ maxWidth: 760 }}>
            <title>Contact</title>
            <h1 className="mb-4">Contact</h1>
            <p>Reach us at <a href="mailto:hello@blogify.com">hello@blogify.com</a>.</p>
        </div>
    );
}
```

`resources/js/pages/NotFound.jsx`

```jsx
import { Link } from 'react-router-dom';

export default function NotFound() {
    return (
        <div className="container py-5 text-center">
            <title>Page not found</title>
            <h1 className="display-1 fw-bold">404</h1>
            <p className="lead">The page you are looking for does not exist.</p>
            <Link to="/" className="btn btn-primary">Back to home</Link>
        </div>
    );
}
```

### 17.6 Auth Pages

`resources/js/pages/auth/Login.jsx`

```jsx
import { useState } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

export default function Login() {
    const { login } = useAuth();
    const [form, bind] = useForm({ email: '', password: '', remember: false });
    const [errors, setErrors] = useState({});
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErrors({});
        try {
            await login(form);
            toast.success('Welcome back!');
        } catch (err) {
            const fieldErrors = err.response?.data?.errors;
            if (fieldErrors) setErrors(fieldErrors); else toast.error(errorMessage(err));
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <title>Login</title>
            <h1 className="h4 mb-4">Sign in</h1>
            <Field label="Email" name="email" type="email" value={form.email} onChange={bind} error={errors.email?.[0]} required autoFocus />
            <Field label="Password" name="password" type="password" value={form.password} onChange={bind} error={errors.password?.[0]} required />
            <div className="d-flex justify-content-between mb-3">
                <div className="form-check">
                    <input className="form-check-input" type="checkbox" id="remember" name="remember" checked={form.remember} onChange={bind} />
                    <label className="form-check-label" htmlFor="remember">Remember me</label>
                </div>
                <Link to="/forgot-password">Forgot password?</Link>
            </div>
            <button className="btn btn-primary w-100" disabled={busy}>{busy ? 'Signing in...' : 'Sign in'}</button>
            <p className="text-center mt-3 mb-0">No account? <Link to="/register">Sign up</Link></p>
        </form>
    );
}
```

`resources/js/pages/auth/Register.jsx`

```jsx
import { useState } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

export default function Register() {
    const { register } = useAuth();
    const [form, bind] = useForm({ name: '', email: '', password: '', password_confirmation: '' });
    const [errors, setErrors] = useState({});
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErrors({});
        try {
            await register(form);
            toast.success('Account created! Check your inbox to verify your email.');
        } catch (err) {
            const fieldErrors = err.response?.data?.errors;
            if (fieldErrors) setErrors(fieldErrors); else toast.error(errorMessage(err));
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <title>Register</title>
            <h1 className="h4 mb-4">Create an account</h1>
            <Field label="Name" name="name" value={form.name} onChange={bind} error={errors.name?.[0]} required autoFocus />
            <Field label="Email" name="email" type="email" value={form.email} onChange={bind} error={errors.email?.[0]} required />
            <Field label="Password" name="password" type="password" value={form.password} onChange={bind} error={errors.password?.[0]} required />
            <Field label="Confirm password" name="password_confirmation" type="password" value={form.password_confirmation} onChange={bind} required />
            <button className="btn btn-primary w-100" disabled={busy}>{busy ? 'Creating...' : 'Sign up'}</button>
            <p className="text-center mt-3 mb-0">Already registered? <Link to="/login">Sign in</Link></p>
        </form>
    );
}
```

`resources/js/pages/auth/ForgotPassword.jsx`

```jsx
import { useState } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { errorMessage, getCsrfCookie } from '@/bootstrap/axios';
import Field from '@/components/common/Field';

export default function ForgotPassword() {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setError('');
        try {
            await getCsrfCookie();
            await api.post('/forgot-password', { email });
            toast.success('Password reset link sent. Check your email.');
        } catch (err) {
            setError(errorMessage(err));
        } finally {
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <title>Forgot password</title>
            <h1 className="h4 mb-4">Forgot your password?</h1>
            <Field label="Email" name="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} error={error} required autoFocus />
            <button className="btn btn-primary w-100" disabled={busy}>{busy ? 'Sending...' : 'Send reset link'}</button>
            <p className="text-center mt-3 mb-0"><Link to="/login">Back to login</Link></p>
        </form>
    );
}
```

`resources/js/pages/auth/ResetPassword.jsx`

```jsx
import { useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { errorMessage, getCsrfCookie } from '@/bootstrap/axios';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

export default function ResetPassword() {
    const { token } = useParams();
    const [params] = useSearchParams();
    const navigate = useNavigate();
    const [form, bind] = useForm({ email: params.get('email') || '', password: '', password_confirmation: '' });
    const [errors, setErrors] = useState({});
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErrors({});
        try {
            await getCsrfCookie();
            await api.post('/reset-password', { ...form, token });
            toast.success('Password reset. You can sign in now.');
            navigate('/login');
        } catch (err) {
            const fieldErrors = err.response?.data?.errors;
            if (fieldErrors) setErrors(fieldErrors); else toast.error(errorMessage(err));
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <title>Reset password</title>
            <h1 className="h4 mb-4">Reset password</h1>
            <Field label="Email" name="email" type="email" value={form.email} onChange={bind} error={errors.email?.[0]} required />
            <Field label="New password" name="password" type="password" value={form.password} onChange={bind} error={errors.password?.[0]} required />
            <Field label="Confirm password" name="password_confirmation" type="password" value={form.password_confirmation} onChange={bind} required />
            <button className="btn btn-primary w-100" disabled={busy}>{busy ? 'Saving...' : 'Reset password'}</button>
            <p className="text-center mt-3 mb-0"><Link to="/login">Back to login</Link></p>
        </form>
    );
}
```

`resources/js/pages/auth/VerifyEmail.jsx`

```jsx
import { useState } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';

export default function VerifyEmail() {
    const { user, fetchUser } = useAuth();
    const [busy, setBusy] = useState(false);

    const resend = async () => {
        setBusy(true);
        try {
            await api.post('/email/verification-notification');
            toast.success('Verification link sent.');
        } catch (err) {
            toast.error(errorMessage(err));
        } finally {
            setBusy(false);
        }
    };

    return (
        <div className="container py-5 text-center" style={{ maxWidth: 560 }}>
            <title>Verify email</title>
            {user.email_verified_at ? (
                <>
                    <h1 className="h3">Your email is verified</h1>
                    <Link to="/" className="btn btn-primary mt-3">Continue</Link>
                </>
            ) : (
                <>
                    <h1 className="h3">Verify your email</h1>
                    <p className="text-body-secondary">We sent a link to <strong>{user.email}</strong>. In development, the link is written to <code>storage/logs/laravel.log</code>.</p>
                    <button className="btn btn-primary me-2" onClick={resend} disabled={busy}>Resend link</button>
                    <button className="btn btn-outline-secondary" onClick={fetchUser}>I have verified</button>
                </>
            )}
        </div>
    );
}
```

---

## Step 18: Build Admin Dashboard & Management Modules

All admin pages live in `resources/js/pages/admin/`. The API already enforces every permission. The UI only hides what the user cannot use.

### 18.1 Dashboard `Dashboard.jsx`

```jsx
import { format, subMonths } from 'date-fns';
import { Doughnut, Line } from 'react-chartjs-2';
import {
    ArcElement, CategoryScale, Chart as ChartJS, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip,
} from 'chart.js';
import { Link } from 'react-router-dom';
import useFetch from '@/hooks/useFetch';
import Spinner from '@/components/common/Spinner';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Tooltip, Legend, Filler);

const CARDS = [
    ['posts', 'Posts', 'file-earmark-text', 'primary'],
    ['published', 'Published', 'check-circle', 'success'],
    ['views', 'Views', 'eye', 'info'],
    ['comments', 'Comments', 'chat-dots', 'warning'],
    ['pending_comments', 'Pending comments', 'hourglass-split', 'danger'],
    ['users', 'Users', 'people', 'secondary'],
    ['subscribers', 'Subscribers', 'envelope', 'dark'],
];

export default function Dashboard() {
    const { data, loading } = useFetch('/api/admin/dashboard');
    if (loading || !data) return <Spinner />;

    const months = Array.from({ length: 6 }, (_, i) => subMonths(new Date(), 5 - i));
    const lineData = {
        labels: months.map((m) => format(m, 'MMM yyyy')),
        datasets: [{
            label: 'Posts created',
            data: months.map((m) => data.posts_per_month[format(m, 'yyyy-MM')] ?? 0),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,.15)',
            fill: true,
            tension: 0.3,
        }],
    };
    const doughnutData = {
        labels: data.posts_by_category.map((c) => c.name),
        datasets: [{ data: data.posts_by_category.map((c) => c.total), backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14'] }],
    };

    return (
        <>
            <title>Dashboard</title>
            <h1 className="h3 mb-4">Dashboard</h1>

            <div className="row g-3 mb-4">
                {CARDS.map(([key, label, icon, color]) => (
                    <div key={key} className="col-6 col-md-4 col-xl-3">
                        <div className="card border-0 shadow-sm"><div className="card-body d-flex align-items-center gap-3">
                            <i className={`bi bi-${icon} fs-2 text-${color}`}></i>
                            <div><div className="fs-4 fw-semibold">{data.stats[key]}</div><div className="text-body-secondary small">{label}</div></div>
                        </div></div>
                    </div>
                ))}
            </div>

            <div className="row g-4 mb-4">
                <div className="col-lg-8"><div className="card border-0 shadow-sm"><div className="card-body">
                    <h2 className="h6">Posts per month</h2><Line data={lineData} />
                </div></div></div>
                <div className="col-lg-4"><div className="card border-0 shadow-sm"><div className="card-body">
                    <h2 className="h6">Posts by category</h2><Doughnut data={doughnutData} />
                </div></div></div>
            </div>

            <div className="row g-4">
                <div className="col-lg-7"><div className="card border-0 shadow-sm"><div className="card-body">
                    <h2 className="h6">Recent posts</h2>
                    <table className="table table-sm align-middle mb-0">
                        <tbody>
                            {data.recent_posts.map((p) => (
                                <tr key={p.id}>
                                    <td><Link to={`/admin/posts/${p.id}/edit`}>{p.title}</Link></td>
                                    <td className="text-body-secondary">{p.user?.name}</td>
                                    <td><span className="badge text-bg-secondary">{p.status}</span></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div></div></div>
                <div className="col-lg-5"><div className="card border-0 shadow-sm"><div className="card-body">
                    <h2 className="h6">Recent comments</h2>
                    <ul className="list-unstyled mb-0">
                        {data.recent_comments.map((c) => (
                            <li key={c.id} className="mb-2">
                                <strong>{c.name || 'Member'}</strong> on <em>{c.post?.title}</em>
                                <div className="text-body-secondary small text-truncate">{c.content}</div>
                            </li>
                        ))}
                    </ul>
                </div></div></div>
            </div>
        </>
    );
}
```

### 18.2 Post Management

`posts/Index.jsx`

```jsx
import { useState } from 'react';
import { Link } from 'react-router-dom';
import { format } from 'date-fns';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import Spinner from '@/components/common/Spinner';
import Pagination from '@/components/common/Pagination';

const BADGE = { published: 'success', draft: 'secondary', scheduled: 'info' };

export default function PostsIndex() {
    const { hasPermission } = useAuth();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const [filters, setFilters] = useState({ search: '', status: '', category_id: '' });
    const { data, loading, reload } = useFetch('/api/admin/posts', { ...filters, page });
    const { data: categories } = useFetch('/api/admin/categories');
    const canPublish = hasPermission(['manage posts', 'publish posts']);

    const setFilter = (name, value) => { setFilters((f) => ({ ...f, [name]: value })); setPage(1); };

    const run = async (request, message) => {
        try { await request(); toast.success(message); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    return (
        <>
            <title>Posts</title>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1 className="h3 mb-0">Posts</h1>
                <Link to="/admin/posts/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1"></i>New post</Link>
            </div>

            <div className="card border-0 shadow-sm mb-3"><div className="card-body row g-2">
                <form className="col-md-6" onSubmit={(e) => { e.preventDefault(); setFilter('search', search); }}>
                    <input className="form-control" placeholder="Search title and press Enter..." value={search} onChange={(e) => setSearch(e.target.value)} />
                </form>
                <div className="col-md-3">
                    <select className="form-select" value={filters.status} onChange={(e) => setFilter('status', e.target.value)}>
                        <option value="">All statuses</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                <div className="col-md-3">
                    <select className="form-select" value={filters.category_id} onChange={(e) => setFilter('category_id', e.target.value)}>
                        <option value="">All categories</option>
                        {categories?.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                </div>
            </div></div>

            {loading ? <Spinner /> : (
                <div className="card border-0 shadow-sm"><div className="table-responsive">
                    <table className="table align-middle mb-0">
                        <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Date</th><th className="text-end">Actions</th></tr></thead>
                        <tbody>
                            {data.data.map((p) => (
                                <tr key={p.id}>
                                    <td>{p.title}</td>
                                    <td>{p.category?.name}</td>
                                    <td>{p.user?.name}</td>
                                    <td><span className={`badge text-bg-${BADGE[p.status]}`}>{p.status}</span></td>
                                    <td>{format(new Date(p.created_at), 'MMM dd, yyyy')}</td>
                                    <td className="text-end text-nowrap">
                                        <Link to={`/admin/posts/${p.id}/edit`} className="btn btn-sm btn-outline-primary me-1">Edit</Link>
                                        {canPublish && (p.status === 'published'
                                            ? <button className="btn btn-sm btn-outline-warning me-1" onClick={() => run(() => api.post(`/api/admin/posts/${p.id}/unpublish`), 'Unpublished')}>Unpublish</button>
                                            : <button className="btn btn-sm btn-outline-success me-1" onClick={() => run(() => api.post(`/api/admin/posts/${p.id}/publish`), 'Published')}>Publish</button>)}
                                        <button className="btn btn-sm btn-outline-danger" onClick={() => window.confirm('Delete this post?') && run(() => api.delete(`/api/admin/posts/${p.id}`), 'Deleted')}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                            {!data.data.length && <tr><td colSpan="6" className="text-center text-body-secondary py-4">No posts found.</td></tr>}
                        </tbody>
                    </table>
                </div></div>
            )}

            <div className="mt-3"><Pagination page={data?.current_page ?? 1} lastPage={data?.last_page ?? 1} onChange={setPage} /></div>
        </>
    );
}
```

`posts/Editor.jsx` (create and edit)

```jsx
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { format } from 'date-fns';
import ReactQuill from 'react-quill-new';
import 'react-quill-new/dist/quill.snow.css';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

const EMPTY = {
    title: '', category_id: '', excerpt: '', content: '', status: 'draft', published_at: '',
    is_featured: false, is_trending: false, meta_title: '', meta_description: '', meta_keywords: '', tags: [],
};

export default function PostEditor() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { hasPermission } = useAuth();
    const canPublish = hasPermission(['manage posts', 'publish posts']);
    const [form, bind, setForm] = useForm(EMPTY);
    const [image, setImage] = useState(null);
    const [currentImage, setCurrentImage] = useState(null);
    const [errors, setErrors] = useState({});
    const [busy, setBusy] = useState(false);
    const { data: categories } = useFetch('/api/admin/categories');
    const { data: tags } = useFetch('/api/admin/tags');

    useEffect(() => {
        if (!id) return;
        api.get(`/api/admin/posts/${id}`).then(({ data: p }) => {
            setCurrentImage(p.featured_image);
            setForm({
                title: p.title, category_id: p.category_id, excerpt: p.excerpt ?? '', content: p.content, status: p.status,
                published_at: p.published_at ? format(new Date(p.published_at), "yyyy-MM-dd'T'HH:mm") : '',
                is_featured: !!p.is_featured, is_trending: !!p.is_trending,
                meta_title: p.meta_title ?? '', meta_description: p.meta_description ?? '', meta_keywords: p.meta_keywords ?? '',
                tags: p.tags.map((t) => t.id),
            });
        }).catch((err) => { toast.error(errorMessage(err)); navigate('/admin/posts'); });
    }, [id, navigate, setForm]);

    const toggleTag = (tagId) => setForm((f) => ({
        ...f, tags: f.tags.includes(tagId) ? f.tags.filter((t) => t !== tagId) : [...f.tags, tagId],
    }));

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErrors({});

        const body = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (key === 'tags') value.forEach((t) => body.append('tags[]', t));
            else if (typeof value === 'boolean') body.append(key, value ? 1 : 0);
            else if (key === 'published_at' && value) body.append(key, new Date(value).toISOString());
            else body.append(key, value ?? '');
        });
        if (image) body.append('featured_image', image);
        if (id) body.append('_method', 'PUT'); // multipart cannot be sent with PUT

        try {
            await api.post(id ? `/api/admin/posts/${id}` : '/api/admin/posts', body);
            toast.success(id ? 'Post updated' : 'Post created');
            navigate('/admin/posts');
        } catch (err) {
            setErrors(err.response?.data?.errors || {});
            toast.error(errorMessage(err));
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <title>{id ? 'Edit post' : 'New post'}</title>
            <h1 className="h3 mb-4">{id ? 'Edit post' : 'New post'}</h1>

            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
                        <Field label="Title" name="title" value={form.title} onChange={bind} error={errors.title?.[0]} required />
                        <Field label="Excerpt" name="excerpt" as="textarea" rows="2" value={form.excerpt} onChange={bind} error={errors.excerpt?.[0]} />
                        <label className="form-label">Content</label>
                        <ReactQuill theme="snow" value={form.content} onChange={(content) => setForm((f) => ({ ...f, content }))} />
                        {errors.content && <div className="text-danger small mt-1">{errors.content[0]}</div>}
                    </div></div>

                    <div className="card border-0 shadow-sm"><div className="card-body">
                        <h2 className="h6">SEO</h2>
                        <Field label="Meta title" name="meta_title" value={form.meta_title} onChange={bind} />
                        <Field label="Meta description" name="meta_description" as="textarea" rows="2" value={form.meta_description} onChange={bind} />
                        <Field label="Meta keywords" name="meta_keywords" value={form.meta_keywords} onChange={bind} />
                    </div></div>
                </div>

                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
                        <Field label="Status" name="status" as="select" value={form.status} onChange={bind} error={errors.status?.[0]} disabled={!canPublish}>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="scheduled">Scheduled</option>
                        </Field>
                        {form.status !== 'draft' && (
                            <Field label="Publish date" name="published_at" type="datetime-local" value={form.published_at} onChange={bind} error={errors.published_at?.[0]} />
                        )}
                        <Field label="Category" name="category_id" as="select" value={form.category_id} onChange={bind} error={errors.category_id?.[0]} required>
                            <option value="">Select category</option>
                            {categories?.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </Field>

                        <label className="form-label">Tags</label>
                        <div className="d-flex flex-wrap gap-2 mb-3">
                            {tags?.map((t) => (
                                <button type="button" key={t.id} onClick={() => toggleTag(t.id)}
                                    className={`btn btn-sm ${form.tags.includes(t.id) ? 'btn-primary' : 'btn-outline-secondary'}`}>{t.name}</button>
                            ))}
                        </div>

                        <div className="form-check"><input className="form-check-input" type="checkbox" id="is_featured" name="is_featured" checked={form.is_featured} onChange={bind} /><label className="form-check-label" htmlFor="is_featured">Featured</label></div>
                        <div className="form-check mb-3"><input className="form-check-input" type="checkbox" id="is_trending" name="is_trending" checked={form.is_trending} onChange={bind} /><label className="form-check-label" htmlFor="is_trending">Trending</label></div>

                        <label className="form-label">Featured image</label>
                        {currentImage && !image && <img src={`/storage/${currentImage}`} alt="" className="img-fluid rounded mb-2" />}
                        <input type="file" accept="image/*" className={`form-control ${errors.featured_image ? 'is-invalid' : ''}`} onChange={(e) => setImage(e.target.files[0])} />
                        {errors.featured_image && <div className="invalid-feedback">{errors.featured_image[0]}</div>}
                    </div></div>

                    <button className="btn btn-primary w-100" disabled={busy}>{busy ? 'Saving...' : 'Save post'}</button>
                </div>
            </div>
        </form>
    );
}
```

### 18.3 Category & Tag Management

`resources/js/components/admin/TaxonomyManager.jsx` powers both pages.

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';
import Spinner from '@/components/common/Spinner';

const EMPTY = { name: '', description: '', status: true };

export default function TaxonomyManager({ title, endpoint, hasDescription = false, hasStatus = false }) {
    const { data: items, loading, reload } = useFetch(endpoint);
    const [form, bind, setForm] = useForm(EMPTY);
    const [editing, setEditing] = useState(null);
    const [errors, setErrors] = useState({});

    const reset = () => { setForm(EMPTY); setEditing(null); setErrors({}); };

    const startEdit = (item) => {
        setEditing(item.id);
        setForm({ name: item.name, description: item.description ?? '', status: !!item.status });
    };

    const submit = async (e) => {
        e.preventDefault();
        try {
            if (editing) await api.put(`${endpoint}/${editing}`, form);
            else await api.post(endpoint, form);
            toast.success('Saved');
            reset();
            reload();
        } catch (err) {
            setErrors(err.response?.data?.errors || {});
            toast.error(errorMessage(err));
        }
    };

    const remove = async (item) => {
        if (!window.confirm(`Delete "${item.name}"?`)) return;
        try { await api.delete(`${endpoint}/${item.id}`); toast.success('Deleted'); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    return (
        <>
            <title>{title}</title>
            <h1 className="h3 mb-4">{title}</h1>
            <div className="row g-4">
                <div className="col-lg-4">
                    <form onSubmit={submit} className="card border-0 shadow-sm"><div className="card-body">
                        <h2 className="h6">{editing ? 'Edit' : 'Add new'}</h2>
                        <Field label="Name" name="name" value={form.name} onChange={bind} error={errors.name?.[0]} required />
                        {hasDescription && <Field label="Description" name="description" as="textarea" rows="3" value={form.description} onChange={bind} error={errors.description?.[0]} />}
                        {hasStatus && (
                            <div className="form-check mb-3">
                                <input className="form-check-input" type="checkbox" id="status" name="status" checked={form.status} onChange={bind} />
                                <label className="form-check-label" htmlFor="status">Active</label>
                            </div>
                        )}
                        <button className="btn btn-primary me-2">Save</button>
                        {editing && <button type="button" className="btn btn-outline-secondary" onClick={reset}>Cancel</button>}
                    </div></form>
                </div>
                <div className="col-lg-8">
                    {loading ? <Spinner /> : (
                        <div className="card border-0 shadow-sm"><div className="table-responsive">
                            <table className="table align-middle mb-0">
                                <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th className="text-end">Actions</th></tr></thead>
                                <tbody>
                                    {items.map((item) => (
                                        <tr key={item.id}>
                                            <td>{item.name}{hasStatus && !item.status && <span className="badge text-bg-secondary ms-2">inactive</span>}</td>
                                            <td className="text-body-secondary">{item.slug}</td>
                                            <td>{item.posts_count}</td>
                                            <td className="text-end text-nowrap">
                                                <button className="btn btn-sm btn-outline-primary me-1" onClick={() => startEdit(item)}>Edit</button>
                                                <button className="btn btn-sm btn-outline-danger" onClick={() => remove(item)}>Delete</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div></div>
                    )}
                </div>
            </div>
        </>
    );
}
```

`Categories.jsx`

```jsx
import TaxonomyManager from '@/components/admin/TaxonomyManager';

export default function Categories() {
    return <TaxonomyManager title="Categories" endpoint="/api/admin/categories" hasDescription hasStatus />;
}
```

`Tags.jsx`

```jsx
import TaxonomyManager from '@/components/admin/TaxonomyManager';

export default function Tags() {
    return <TaxonomyManager title="Tags" endpoint="/api/admin/tags" />;
}
```

### 18.4 Comment Management `Comments.jsx`

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import { format } from 'date-fns';
import api, { errorMessage } from '@/bootstrap/axios';
import useFetch from '@/hooks/useFetch';
import Spinner from '@/components/common/Spinner';
import Pagination from '@/components/common/Pagination';

const BADGE = { pending: 'warning', approved: 'success', rejected: 'danger' };

export default function Comments() {
    const [status, setStatus] = useState('');
    const [page, setPage] = useState(1);
    const [replyTo, setReplyTo] = useState(null);
    const [reply, setReply] = useState('');
    const { data, loading, reload } = useFetch('/api/admin/comments', { status, page });

    const run = async (request, message) => {
        try { await request(); toast.success(message); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    const sendReply = async (comment) => {
        await run(() => api.post(`/api/admin/comments/${comment.id}/reply`, { content: reply }), 'Reply posted');
        setReplyTo(null);
        setReply('');
    };

    return (
        <>
            <title>Comments</title>
            <h1 className="h3 mb-4">Comments</h1>
            <div className="btn-group mb-3">
                {['', 'pending', 'approved', 'rejected'].map((s) => (
                    <button key={s} className={`btn btn-outline-primary ${status === s ? 'active' : ''}`} onClick={() => { setStatus(s); setPage(1); }}>
                        {s || 'All'}
                    </button>
                ))}
            </div>

            {loading ? <Spinner /> : (
                <div className="card border-0 shadow-sm"><div className="table-responsive">
                    <table className="table align-middle mb-0">
                        <thead><tr><th>Author</th><th>Comment</th><th>Post</th><th>Status</th><th className="text-end">Actions</th></tr></thead>
                        <tbody>
                            {data.data.map((c) => (
                                <tr key={c.id}>
                                    <td>{c.user?.name || c.name}<div className="text-body-secondary small">{c.email}</div></td>
                                    <td style={{ maxWidth: 360 }}>
                                        {c.content}
                                        <div className="text-body-secondary small">{format(new Date(c.created_at), 'MMM dd, yyyy HH:mm')}</div>
                                        {replyTo === c.id && (
                                            <div className="mt-2">
                                                <textarea className="form-control mb-2" rows="2" value={reply} onChange={(e) => setReply(e.target.value)} />
                                                <button className="btn btn-sm btn-primary me-1" onClick={() => sendReply(c)} disabled={!reply.trim()}>Send</button>
                                                <button className="btn btn-sm btn-outline-secondary" onClick={() => setReplyTo(null)}>Cancel</button>
                                            </div>
                                        )}
                                    </td>
                                    <td>{c.post?.title}</td>
                                    <td><span className={`badge text-bg-${BADGE[c.status]}`}>{c.status}</span></td>
                                    <td className="text-end text-nowrap">
                                        {c.status !== 'approved' && <button className="btn btn-sm btn-outline-success me-1" onClick={() => run(() => api.post(`/api/admin/comments/${c.id}/approve`), 'Approved')}>Approve</button>}
                                        {c.status !== 'rejected' && <button className="btn btn-sm btn-outline-warning me-1" onClick={() => run(() => api.post(`/api/admin/comments/${c.id}/reject`), 'Rejected')}>Reject</button>}
                                        <button className="btn btn-sm btn-outline-primary me-1" onClick={() => setReplyTo(c.id)}>Reply</button>
                                        <button className="btn btn-sm btn-outline-danger" onClick={() => window.confirm('Delete this comment?') && run(() => api.delete(`/api/admin/comments/${c.id}`), 'Deleted')}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                            {!data.data.length && <tr><td colSpan="5" className="text-center text-body-secondary py-4">No comments.</td></tr>}
                        </tbody>
                    </table>
                </div></div>
            )}
            <div className="mt-3"><Pagination page={data?.current_page ?? 1} lastPage={data?.last_page ?? 1} onChange={setPage} /></div>
        </>
    );
}
```

### 18.5 Media Library `Media.jsx`

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import useFetch from '@/hooks/useFetch';
import Spinner from '@/components/common/Spinner';
import Pagination from '@/components/common/Pagination';

export default function Media() {
    const [page, setPage] = useState(1);
    const { data, loading, reload } = useFetch('/api/admin/media', { page });

    const upload = async (e) => {
        const file = e.target.files[0];
        e.target.value = '';
        if (!file) return;

        const body = new FormData();
        body.append('file', file);
        try { await api.post('/api/admin/media', body); toast.success('Uploaded'); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    const remove = async (item) => {
        if (!window.confirm('Delete this file?')) return;
        try { await api.delete(`/api/admin/media/${item.id}`); toast.success('Deleted'); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    const copy = (item) => {
        navigator.clipboard.writeText(`${window.location.origin}${item.url}`);
        toast.success('URL copied');
    };

    return (
        <>
            <title>Media</title>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1 className="h3 mb-0">Media library</h1>
                <label className="btn btn-primary mb-0">
                    <i className="bi bi-upload me-1"></i>Upload
                    <input type="file" accept="image/*" hidden onChange={upload} />
                </label>
            </div>

            {loading ? <Spinner /> : (
                <div className="row g-3">
                    {data.data.map((m) => (
                        <div key={m.id} className="col-6 col-md-3 col-xl-2">
                            <div className="card border-0 shadow-sm h-100">
                                <img src={m.url} alt={m.alt_text || m.file_name} className="card-img-top" style={{ height: 120, objectFit: 'cover' }} loading="lazy" />
                                <div className="card-body p-2">
                                    <div className="small text-truncate" title={m.file_name}>{m.file_name}</div>
                                    <div className="d-flex gap-1 mt-1">
                                        <button className="btn btn-sm btn-outline-primary flex-fill" onClick={() => copy(m)}>Copy</button>
                                        <button className="btn btn-sm btn-outline-danger" onClick={() => remove(m)}><i className="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                    {!data.data.length && <p className="text-body-secondary">No files yet.</p>}
                </div>
            )}
            <div className="mt-4"><Pagination page={data?.current_page ?? 1} lastPage={data?.last_page ?? 1} onChange={setPage} /></div>
        </>
    );
}
```

### 18.6 User Management `Users.jsx`

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';
import Spinner from '@/components/common/Spinner';
import Pagination from '@/components/common/Pagination';

const EMPTY = { name: '', email: '', password: '', status: 'active', roles: ['user'] };

export default function Users() {
    const { hasRole } = useAuth();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const { data, loading, reload } = useFetch('/api/admin/users', { search, page });
    const { data: roles } = useFetch('/api/admin/roles');
    const [form, bind, setForm] = useForm(EMPTY);
    const [editing, setEditing] = useState(null);
    const [errors, setErrors] = useState({});

    const assignable = roles?.filter((r) => r.name !== 'super-admin' || hasRole('super-admin')) ?? [];
    const reset = () => { setForm(EMPTY); setEditing(null); setErrors({}); };

    const startEdit = (u) => {
        setEditing(u.id);
        setForm({ name: u.name, email: u.email, password: '', status: u.status, roles: u.roles.map((r) => r.name) });
    };

    const toggleRole = (name) => setForm((f) => ({
        ...f, roles: f.roles.includes(name) ? f.roles.filter((r) => r !== name) : [...f.roles, name],
    }));

    const submit = async (e) => {
        e.preventDefault();
        setErrors({});
        try {
            if (editing) await api.put(`/api/admin/users/${editing}`, form);
            else await api.post('/api/admin/users', form);
            toast.success('Saved');
            reset();
            reload();
        } catch (err) {
            setErrors(err.response?.data?.errors || {});
            toast.error(errorMessage(err));
        }
    };

    const remove = async (u) => {
        if (!window.confirm(`Delete ${u.name}?`)) return;
        try { await api.delete(`/api/admin/users/${u.id}`); toast.success('Deleted'); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    return (
        <>
            <title>Users</title>
            <h1 className="h3 mb-4">Users</h1>
            <div className="row g-4">
                <div className="col-lg-4">
                    <form onSubmit={submit} className="card border-0 shadow-sm"><div className="card-body">
                        <h2 className="h6">{editing ? 'Edit user' : 'Add user'}</h2>
                        <Field label="Name" name="name" value={form.name} onChange={bind} error={errors.name?.[0]} required />
                        <Field label="Email" name="email" type="email" value={form.email} onChange={bind} error={errors.email?.[0]} required />
                        <Field label={editing ? 'New password (optional)' : 'Password'} name="password" type="password" value={form.password} onChange={bind} error={errors.password?.[0]} required={!editing} />
                        <Field label="Status" name="status" as="select" value={form.status} onChange={bind}>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </Field>
                        <label className="form-label">Roles</label>
                        <div className="mb-3">
                            {assignable.map((r) => (
                                <div className="form-check" key={r.id}>
                                    <input className="form-check-input" type="checkbox" id={`role-${r.id}`} checked={form.roles.includes(r.name)} onChange={() => toggleRole(r.name)} />
                                    <label className="form-check-label" htmlFor={`role-${r.id}`}>{r.name}</label>
                                </div>
                            ))}
                            {errors['roles.0'] && <div className="text-danger small">{errors['roles.0'][0]}</div>}
                        </div>
                        <button className="btn btn-primary me-2">Save</button>
                        {editing && <button type="button" className="btn btn-outline-secondary" onClick={reset}>Cancel</button>}
                    </div></form>
                </div>

                <div className="col-lg-8">
                    <input className="form-control mb-3" placeholder="Search name or email..." value={search} onChange={(e) => { setSearch(e.target.value); setPage(1); }} />
                    {loading ? <Spinner /> : (
                        <div className="card border-0 shadow-sm"><div className="table-responsive">
                            <table className="table align-middle mb-0">
                                <thead><tr><th>Name</th><th>Roles</th><th>Status</th><th className="text-end">Actions</th></tr></thead>
                                <tbody>
                                    {data.data.map((u) => (
                                        <tr key={u.id}>
                                            <td>{u.name}<div className="text-body-secondary small">{u.email}</div></td>
                                            <td>{u.roles.map((r) => <span key={r.id} className="badge text-bg-primary me-1">{r.name}</span>)}</td>
                                            <td><span className={`badge text-bg-${u.status === 'active' ? 'success' : 'danger'}`}>{u.status}</span></td>
                                            <td className="text-end text-nowrap">
                                                <button className="btn btn-sm btn-outline-primary me-1" onClick={() => startEdit(u)}>Edit</button>
                                                <button className="btn btn-sm btn-outline-danger" onClick={() => remove(u)}>Delete</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div></div>
                    )}
                    <div className="mt-3"><Pagination page={data?.current_page ?? 1} lastPage={data?.last_page ?? 1} onChange={setPage} /></div>
                </div>
            </div>
        </>
    );
}
```

### 18.7 Role & Permission Management `Roles.jsx`

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

const CORE = ['super-admin', 'admin', 'editor', 'author', 'user'];
const EMPTY = { name: '', permissions: [] };

export default function Roles() {
    const { hasPermission } = useAuth();
    const { data: roles, reload } = useFetch('/api/admin/roles');
    const { data: permissions, reload: reloadPermissions } = useFetch('/api/admin/permissions');
    const [form, bind, setForm] = useForm(EMPTY);
    const [editing, setEditing] = useState(null);
    const [newPermission, setNewPermission] = useState('');
    const [errors, setErrors] = useState({});

    const reset = () => { setForm(EMPTY); setEditing(null); setErrors({}); };

    const startEdit = (r) => {
        setEditing(r);
        setForm({ name: r.name, permissions: r.permissions.map((p) => p.name) });
    };

    const togglePermission = (name) => setForm((f) => ({
        ...f, permissions: f.permissions.includes(name) ? f.permissions.filter((p) => p !== name) : [...f.permissions, name],
    }));

    const submit = async (e) => {
        e.preventDefault();
        setErrors({});
        try {
            if (editing) await api.put(`/api/admin/roles/${editing.id}`, form);
            else await api.post('/api/admin/roles', form);
            toast.success('Saved');
            reset();
            reload();
        } catch (err) {
            setErrors(err.response?.data?.errors || {});
            toast.error(errorMessage(err));
        }
    };

    const remove = async (r) => {
        if (!window.confirm(`Delete role "${r.name}"?`)) return;
        try { await api.delete(`/api/admin/roles/${r.id}`); toast.success('Deleted'); reload(); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    const addPermission = async (e) => {
        e.preventDefault();
        try { await api.post('/api/admin/permissions', { name: newPermission }); setNewPermission(''); reloadPermissions(); toast.success('Permission added'); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    return (
        <>
            <title>Roles & Permissions</title>
            <h1 className="h3 mb-4">Roles & Permissions</h1>
            <div className="row g-4">
                <div className="col-lg-5">
                    <form onSubmit={submit} className="card border-0 shadow-sm mb-4"><div className="card-body">
                        <h2 className="h6">{editing ? `Edit role: ${editing.name}` : 'New role'}</h2>
                        <Field label="Name" name="name" value={form.name} onChange={bind} error={errors.name?.[0]} disabled={editing && CORE.includes(editing.name)} required />
                        <label className="form-label">Permissions</label>
                        <div className="mb-3" style={{ maxHeight: 280, overflowY: 'auto' }}>
                            {permissions?.map((p) => (
                                <div className="form-check" key={p.id}>
                                    <input className="form-check-input" type="checkbox" id={`perm-${p.id}`} checked={form.permissions.includes(p.name)} onChange={() => togglePermission(p.name)} />
                                    <label className="form-check-label" htmlFor={`perm-${p.id}`}>{p.name}</label>
                                </div>
                            ))}
                        </div>
                        <button className="btn btn-primary me-2">Save</button>
                        {editing && <button type="button" className="btn btn-outline-secondary" onClick={reset}>Cancel</button>}
                    </div></form>

                    {hasPermission('manage permissions') && (
                        <form onSubmit={addPermission} className="input-group">
                            <input className="form-control" placeholder="New permission name" value={newPermission} onChange={(e) => setNewPermission(e.target.value)} required />
                            <button className="btn btn-outline-primary">Add</button>
                        </form>
                    )}
                </div>

                <div className="col-lg-7">
                    <div className="card border-0 shadow-sm"><div className="table-responsive">
                        <table className="table align-middle mb-0">
                            <thead><tr><th>Role</th><th>Users</th><th>Permissions</th><th></th></tr></thead>
                            <tbody>
                                {roles?.map((r) => (
                                    <tr key={r.id}>
                                        <td className="fw-semibold">{r.name}</td>
                                        <td>{r.users_count}</td>
                                        <td>{r.name === 'super-admin' ? 'All (full access)' : r.permissions.length}</td>
                                        <td className="text-end text-nowrap">
                                            {r.name !== 'super-admin' && <button className="btn btn-sm btn-outline-primary me-1" onClick={() => startEdit(r)}>Edit</button>}
                                            {!CORE.includes(r.name) && <button className="btn btn-sm btn-outline-danger" onClick={() => remove(r)}>Delete</button>}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div></div>
                </div>
            </div>
        </>
    );
}
```

### 18.8 Site Settings `Settings.jsx`

```jsx
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

export default function Settings() {
    const { data, reload } = useFetch('/api/public/settings');
    const [form, bind, setForm] = useForm({ site_name: '', site_description: '', footer_text: '' });
    const [logo, setLogo] = useState(null);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (data) setForm({ site_name: data.site_name ?? '', site_description: data.site_description ?? '', footer_text: data.footer_text ?? '' });
    }, [data, setForm]);

    const submit = async (e) => {
        e.preventDefault();
        setErrors({});
        const body = new FormData();
        Object.entries(form).forEach(([k, v]) => body.append(k, v ?? ''));
        if (logo) body.append('site_logo', logo);

        try { await api.post('/api/admin/settings', body); toast.success('Settings saved'); reload(); }
        catch (err) { setErrors(err.response?.data?.errors || {}); toast.error(errorMessage(err)); }
    };

    return (
        <>
            <title>Settings</title>
            <h1 className="h3 mb-4">Site settings</h1>
            <form onSubmit={submit} className="card border-0 shadow-sm" style={{ maxWidth: 640 }}><div className="card-body">
                <Field label="Site name" name="site_name" value={form.site_name} onChange={bind} error={errors.site_name?.[0]} required />
                <Field label="Description" name="site_description" as="textarea" rows="2" value={form.site_description} onChange={bind} />
                <Field label="Footer text" name="footer_text" value={form.footer_text} onChange={bind} />
                <label className="form-label">Logo</label>
                {data?.site_logo && <img src={`/storage/${data.site_logo}`} alt="" height="48" className="d-block mb-2" />}
                <input type="file" accept="image/*" className="form-control mb-3" onChange={(e) => setLogo(e.target.files[0])} />
                <button className="btn btn-primary">Save settings</button>
            </div></form>
        </>
    );
}
```

---

## Step 19: Comments, Bookmarks & Newsletter

### Comments

- Authenticated users comment immediately (status `approved`). Guests must give a name and email, and their comments wait in `pending` until a moderator approves them.
- Replies are one level deep and must belong to the same post (validated on the server).
- Guest emails are hidden from public responses (`$hidden` on `Comment`).
- Throttle: 10 comments per minute per client.

`resources/js/components/public/CommentSection.jsx`

```jsx
import { useState } from 'react';
import { formatDistanceToNow } from 'date-fns';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';

function CommentItem({ comment, onReply }) {
    return (
        <div className="d-flex gap-3 mb-3">
            <i className="bi bi-person-circle fs-3"></i>
            <div className="flex-grow-1">
                <strong>{comment.user?.name || comment.name}</strong>{' '}
                <small className="text-body-secondary">{formatDistanceToNow(new Date(comment.created_at), { addSuffix: true })}</small>
                <p className="mb-1" style={{ whiteSpace: 'pre-wrap' }}>{comment.content}</p>
                {onReply && <button className="btn btn-link btn-sm p-0" onClick={() => onReply(comment)}>Reply</button>}
                {comment.replies?.map((r) => <div key={r.id} className="mt-3"><CommentItem comment={r} /></div>)}
            </div>
        </div>
    );
}

export default function CommentSection({ slug, postId }) {
    const { isAuthenticated } = useAuth();
    const { data: comments, loading, reload } = useFetch(`/api/public/posts/${slug}/comments`);
    const [form, bind, setForm] = useForm({ name: '', email: '', content: '' });
    const [parent, setParent] = useState(null);
    const [errors, setErrors] = useState({});

    const submit = async (e) => {
        e.preventDefault();
        setErrors({});
        try {
            const { data } = await api.post('/api/public/comments', { ...form, post_id: postId, parent_id: parent?.id });
            toast.success(data.message);
            setForm((f) => ({ ...f, content: '' }));
            setParent(null);
            reload();
        } catch (err) {
            setErrors(err.response?.data?.errors || {});
            toast.error(errorMessage(err));
        }
    };

    return (
        <section>
            <h2 className="h4 mb-4">Comments {comments && `(${comments.length})`}</h2>

            <form onSubmit={submit} className="mb-5">
                {parent && (
                    <div className="alert alert-secondary py-2 d-flex justify-content-between">
                        Replying to {parent.user?.name || parent.name}
                        <button type="button" className="btn-close" onClick={() => setParent(null)} aria-label="Cancel reply"></button>
                    </div>
                )}
                {!isAuthenticated && (
                    <div className="row">
                        <div className="col-md-6"><Field label="Name" name="name" value={form.name} onChange={bind} error={errors.name?.[0]} required /></div>
                        <div className="col-md-6"><Field label="Email" name="email" type="email" value={form.email} onChange={bind} error={errors.email?.[0]} required /></div>
                    </div>
                )}
                <Field label="Comment" name="content" as="textarea" rows="3" value={form.content} onChange={bind} error={errors.content?.[0]} required />
                <button className="btn btn-primary">Post comment</button>
            </form>

            {loading ? null : comments.map((c) => <CommentItem key={c.id} comment={c} onReply={setParent} />)}
            {!loading && !comments.length && <p className="text-body-secondary">Be the first to comment.</p>}
        </section>
    );
}
```

### Bookmarks

- `POST /api/profile/posts/{post}/bookmark` toggles a bookmark (only published posts).
- `GET /api/profile/bookmark-ids` lets the post page show the saved state.
- `GET /api/profile/bookmarks` feeds the "Bookmarks" section of the profile page below.

### Profile Page `resources/js/pages/Profile.jsx`

Available to every logged-in user at `/profile` (not just admins).

```jsx
import { useState } from 'react';
import toast from 'react-hot-toast';
import api, { errorMessage } from '@/bootstrap/axios';
import { useAuth } from '@/context/AuthContext';
import useFetch from '@/hooks/useFetch';
import useForm from '@/hooks/useForm';
import Field from '@/components/common/Field';
import PostGrid from '@/components/public/PostGrid';

export default function Profile() {
    const { user, fetchUser } = useAuth();
    const [info, bindInfo] = useForm({
        name: user.name, email: user.email, bio: user.bio ?? '',
        website: user.website ?? '', facebook: user.facebook ?? '', twitter: user.twitter ?? '', instagram: user.instagram ?? '',
    });
    const [pass, bindPass, setPass] = useForm({ current_password: '', password: '', password_confirmation: '' });
    const [errors, setErrors] = useState({});
    const [passErrors, setPassErrors] = useState({});
    const bookmarks = useFetch('/api/profile/bookmarks');

    const saveInfo = async (e) => {
        e.preventDefault();
        setErrors({});
        try { await api.put('/api/profile', info); await fetchUser(); toast.success('Profile updated'); }
        catch (err) { setErrors(err.response?.data?.errors || {}); toast.error(errorMessage(err)); }
    };

    const savePassword = async (e) => {
        e.preventDefault();
        setPassErrors({});
        try {
            await api.put('/api/profile/password', pass);
            setPass({ current_password: '', password: '', password_confirmation: '' });
            toast.success('Password updated');
        } catch (err) { setPassErrors(err.response?.data?.errors || {}); toast.error(errorMessage(err)); }
    };

    const saveAvatar = async (e) => {
        const file = e.target.files[0];
        e.target.value = '';
        if (!file) return;
        const body = new FormData();
        body.append('avatar', file);
        try { await api.post('/api/profile/avatar', body); await fetchUser(); toast.success('Avatar updated'); }
        catch (err) { toast.error(errorMessage(err)); }
    };

    return (
        <div className="container py-5">
            <title>My profile</title>
            <h1 className="h3 mb-4">My profile</h1>

            <div className="row g-4">
                <div className="col-lg-7">
                    <form onSubmit={saveInfo} className="card border-0 shadow-sm"><div className="card-body">
                        <div className="d-flex align-items-center gap-3 mb-3">
                            {user.avatar_url
                                ? <img src={user.avatar_url} alt="" className="rounded-circle" width="72" height="72" style={{ objectFit: 'cover' }} />
                                : <i className="bi bi-person-circle display-4"></i>}
                            <label className="btn btn-sm btn-outline-secondary mb-0">Change avatar<input type="file" accept="image/*" hidden onChange={saveAvatar} /></label>
                        </div>
                        <Field label="Name" name="name" value={info.name} onChange={bindInfo} error={errors.name?.[0]} required />
                        <Field label="Email" name="email" type="email" value={info.email} onChange={bindInfo} error={errors.email?.[0]} required />
                        <Field label="Bio" name="bio" as="textarea" rows="3" value={info.bio} onChange={bindInfo} error={errors.bio?.[0]} />
                        <Field label="Website" name="website" value={info.website} onChange={bindInfo} error={errors.website?.[0]} />
                        <Field label="Facebook URL" name="facebook" value={info.facebook} onChange={bindInfo} error={errors.facebook?.[0]} />
                        <Field label="Twitter URL" name="twitter" value={info.twitter} onChange={bindInfo} error={errors.twitter?.[0]} />
                        <Field label="Instagram URL" name="instagram" value={info.instagram} onChange={bindInfo} error={errors.instagram?.[0]} />
                        <button className="btn btn-primary">Save profile</button>
                    </div></form>
                </div>

                <div className="col-lg-5">
                    <form onSubmit={savePassword} className="card border-0 shadow-sm"><div className="card-body">
                        <h2 className="h6">Change password</h2>
                        <Field label="Current password" name="current_password" type="password" value={pass.current_password} onChange={bindPass} error={passErrors.current_password?.[0]} required />
                        <Field label="New password" name="password" type="password" value={pass.password} onChange={bindPass} error={passErrors.password?.[0]} required />
                        <Field label="Confirm password" name="password_confirmation" type="password" value={pass.password_confirmation} onChange={bindPass} required />
                        <button className="btn btn-primary">Update password</button>
                    </div></form>
                </div>
            </div>

            <h2 className="h4 mt-5 mb-3">Bookmarks</h2>
            <PostGrid posts={bookmarks.data?.data} loading={bookmarks.loading} />
        </div>
    );
}
```

### Newsletter

- The footer form posts to `POST /api/public/newsletter/subscribe` (throttled to 5 per minute). Duplicates are ignored with `firstOrCreate`.
- Emails are stored in the `newsletters` table. Export or list them from the database or add an admin page when you need one.

---

## Step 20: SEO, Media Library & Settings

### SEO

- Each post has `meta_title`, `meta_description` and `meta_keywords` fields. `BlogDetail.jsx` renders them as `<title>` and `<meta>` tags. React 19 hoists these into `<head>` automatically, so no helmet library is needed.
- `app.blade.php` has a default description and title for crawlers that do not run JavaScript.
- Slugs are generated on the server and stay stable after the first save.

> **Important:** a client-rendered SPA is indexed well by Google, but social crawlers (Facebook, X, WhatsApp) do not run JavaScript and will only see the Blade defaults. For full SEO and rich link previews, add server-side rendering or prerendering (for example Inertia SSR, a prerender service, or a Blade route for `/blog/{slug}` that injects the post's meta tags).

### Media Library

- Uploads go to `storage/app/public/media` through `POST /api/admin/media` (images only, 4 MB, validated by MIME type).
- The admin page lists, copies the URL and deletes files. Deleting also removes the file from disk.
- Run `php artisan storage:link` once so `/storage/...` URLs work.

```bash
php artisan storage:link
```

### Settings

A key/value table for site name, description, logo and footer text.

- `GET /api/public/settings` is cached forever (`Cache::rememberForever`) and cleared whenever settings are saved.
- The public layout reads it for the brand name and footer. The admin Settings page saves it.

---

## Step 21: Database Seeders (Sample Data & Default Admin)

```bash
php artisan make:seeder RolePermissionSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder TagSeeder
php artisan make:seeder PostSeeder
php artisan make:seeder SettingSeeder
```

### 21.1 `RolePermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view dashboard', 'manage posts', 'create posts', 'edit posts', 'delete posts', 'publish posts',
            'manage categories', 'manage tags', 'manage comments', 'manage media', 'manage users',
            'manage roles', 'manage permissions', 'manage settings',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        Role::firstOrCreate(['name' => 'super-admin'])->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions([
            'view dashboard', 'manage posts', 'manage categories', 'manage tags',
            'manage comments', 'manage media', 'manage users', 'manage settings',
        ]);

        Role::firstOrCreate(['name' => 'editor'])->syncPermissions([
            'view dashboard', 'manage posts', 'manage categories', 'manage tags',
            'manage comments', 'manage media',
        ]);

        Role::firstOrCreate(['name' => 'author'])->syncPermissions([
            'view dashboard', 'create posts', 'edit posts', 'manage media',
        ]);

        // Regular readers: no admin permissions
        Role::firstOrCreate(['name' => 'user']);
    }
}
```

### 21.2 `UserSeeder.php`

`forceCreate` is used because `email_verified_at` is intentionally not mass assignable. The `hashed` cast hashes the password.

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Super Admin', 'superadmin@blogify.com', 'super-admin'],
            ['Admin', 'admin@blogify.com', 'admin'],
            ['Editor', 'editor@blogify.com', 'editor'],
            ['John Author', 'author@blogify.com', 'author'],
            ['Jane Reader', 'user@blogify.com', 'user'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::forceCreate([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'email_verified_at' => now(),
                'status' => 'active',
            ])->assignRole($role);
        }
    }
}
```

### 21.3 `CategorySeeder.php` and `TagSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Technology', 'Lifestyle', 'Travel', 'Business', 'Health'] as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => "Latest articles about {$name}."]
            );
        }
    }
}
```

```php
<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Laravel', 'React', 'PHP', 'JavaScript', 'Tutorial', 'News', 'Tips', 'Guide'] as $name) {
            Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
```

### 21.4 `PostSeeder.php`

Uses Faker through the `fake()` helper (a dev dependency, so seed in development only).

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::role(['author', 'admin'])->get();
        $categories = Category::all();
        $tags = Tag::all();

        for ($i = 1; $i <= 15; $i++) {
            $title = rtrim(fake()->unique()->sentence(6), '.');

            $post = Post::create([
                'user_id' => $authors->random()->id,
                'category_id' => $categories->random()->id,
                'title' => $title,
                'slug' => Post::makeSlug($title),
                'excerpt' => fake()->paragraph(2),
                'content' => '<p>'.implode('</p><p>', fake()->paragraphs(5)).'</p>',
                'status' => 'published',
                'published_at' => now()->subDays($i),
                'is_featured' => $i <= 3,
                'is_trending' => $i % 4 === 0,
                'views' => fake()->numberBetween(10, 5000),
            ]);

            $post->tags()->sync($tags->random(3)->pluck('id'));
        }
    }
}
```

### 21.5 `SettingSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Blogify',
            'site_description' => 'Modern Laravel & React Blogging Platform',
            'site_logo' => null,
            'footer_text' => '© '.now()->year.' Blogify. All rights reserved.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
```

React escapes text, so the footer must contain the real `©` character, not the `&copy;` HTML entity.

### 21.6 `DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
```

---

## Step 22: Run Migrations, Seed & Start Development

```bash
# Public storage symlink
php artisan storage:link

# Fresh database with sample data
php artisan migrate:fresh --seed
```

**Start the servers (separate terminals):**

```bash
# Terminal 1 - Laravel (serves the app and API)
php artisan serve
```

```bash
# Terminal 2 - Vite (assets + hot reload)
npm run dev
```

```bash
# Terminal 3 (optional) - publishes scheduled posts
php artisan schedule:work
```

**Access:**

| URL | Description |
| :-- | :-- |
| `http://localhost:8000/` | Public blog (SPA served by Laravel) |
| `http://localhost:8000/login` | Login |
| `http://localhost:8000/admin/dashboard` | Admin dashboard |
| `http://localhost:5173/` | Vite dev server only. Do not browse to it directly |

**Default credentials** (password for all: `password`):

| Role | Email |
| :-- | :-- |
| Super Admin | superadmin@blogify.com |
| Admin | admin@blogify.com |
| Editor | editor@blogify.com |
| Author | author@blogify.com |
| User (no admin access) | user@blogify.com |

Change these before any deployment. With `MAIL_MAILER=log`, verification and reset links are written to `storage/logs/laravel.log`.

---

## Step 23: Using Ready-Made HTML Bootstrap Templates (Blog & Admin)

Use this step when you already have a **blog HTML layout** (public site) and an **admin dashboard HTML layout** (both static Bootstrap templates) and want them to run on this Laravel + React project. The API, auth, roles and data code from Steps 1-22 stay the same. Only the markup and styling change.

### 23.1 Choose a Setup

| Part | Option | Use when |
| :-- | :-- | :-- |
| Blog (public) | **A. React** (JSX components, no page reload) | You want the SPA behaviour from this guide |
| Admin | **A. React admin SPA** | You want CRUD without page reloads |
| Admin | **B. Laravel Blade admin** (server-rendered) | You want classic pages: normal forms, redirects, flash messages |

The public blog is always React in this guide. Pick **one** of A or B for the admin.

> A template ships its own CSS and JS. Two templates loaded into one page conflict (duplicate Bootstrap, clashing class names). Keep the blog template and the admin template **on separate pages**, as described below.

### 23.2 Prepare the Template Files

Put each template's static assets under `public/template/` and do not run them through Vite:

```text
public/
└── template/
    ├── blog/    # css/, js/, img/, fonts/, vendor/ from the blog template
    └── admin/   # css/, js/, img/, fonts/, vendor/ from the admin template
```

- Keep the template's folder structure so relative paths such as `url(../fonts/x.woff2)` inside its CSS keep working.
- Keep the original `.html` files outside `public/` (for example `template-source/`) as your reference while converting.
- Delete demo-only assets, analytics snippets and CDN scripts you do not use.
- Check the template licence allows use in your project.

If the template includes its own Bootstrap, **do not** also import `bootstrap/dist/css/bootstrap.min.css` for that page. If it uses Bootstrap Icons, Font Awesome or similar, either keep its bundled copy or install the npm package, but not both.

### 23.3 Two Entry Points (Blog Page and Admin Page)

Split the app into two bundles so each page loads only its own template CSS.

`vite.config.js`:

```js
laravel({
    input: ['resources/js/public.jsx', 'resources/js/admin.jsx'],
    refresh: true,
}),
```

Rename `resources/js/main.jsx` to `resources/js/public.jsx` and remove the Bootstrap CSS imports if the blog template brings its own:

```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { Toaster } from 'react-hot-toast';
import PublicRouter from './routes/PublicRouter';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import './assets/css/public.css'; // your small overrides only

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <ThemeProvider>
            <AuthProvider>
                <PublicRouter />
                <Toaster position="top-right" />
            </AuthProvider>
        </ThemeProvider>
    </React.StrictMode>
);
```

Create `resources/js/admin.jsx`:

```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { Toaster } from 'react-hot-toast';
import AdminRouter from './routes/AdminRouter';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import './assets/css/admin.css'; // your small overrides only

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <ThemeProvider>
            <AuthProvider>
                <AdminRouter />
                <Toaster position="top-right" />
            </AuthProvider>
        </ThemeProvider>
    </React.StrictMode>
);
```

Split the router from Step 16.3:

- `PublicRouter.jsx`: `AppRouter.jsx` with the whole `AdminRoute` block and the admin lazy imports removed. Keep the public, auth and profile routes.
- `AdminRouter.jsx`: only the admin routes. Do not use `basename`, because the pages already link to `/admin/...`.

```jsx
import { lazy, Suspense } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import AdminRoute from './AdminRoute';
import AdminLayout from '@/layouts/AdminLayout';
import Spinner from '@/components/common/Spinner';
import NotFound from '@/pages/NotFound';

const Dashboard = lazy(() => import('@/pages/admin/Dashboard'));
const PostsIndex = lazy(() => import('@/pages/admin/posts/Index'));
const PostEditor = lazy(() => import('@/pages/admin/posts/Editor'));
const Categories = lazy(() => import('@/pages/admin/Categories'));
const Tags = lazy(() => import('@/pages/admin/Tags'));
const Media = lazy(() => import('@/pages/admin/Media'));
const Comments = lazy(() => import('@/pages/admin/Comments'));
const Users = lazy(() => import('@/pages/admin/Users'));
const Roles = lazy(() => import('@/pages/admin/Roles'));
const Settings = lazy(() => import('@/pages/admin/Settings'));

export default function AdminRouter() {
    return (
        <BrowserRouter>
            <Suspense fallback={<Spinner fullPage />}>
                <Routes>
                    <Route element={<AdminRoute />}>
                        <Route path="/admin" element={<Navigate to="/admin/dashboard" replace />} />
                        <Route element={<AdminLayout />}>
                            <Route path="/admin/dashboard" element={<Dashboard />} />
                            <Route path="/admin/posts" element={<PostsIndex />} />
                            <Route path="/admin/posts/create" element={<PostEditor />} />
                            <Route path="/admin/posts/:id/edit" element={<PostEditor />} />
                            <Route path="/admin/categories" element={<Categories />} />
                            <Route path="/admin/tags" element={<Tags />} />
                            <Route path="/admin/media" element={<Media />} />
                            <Route path="/admin/comments" element={<Comments />} />
                            <Route path="/admin/users" element={<Users />} />
                            <Route path="/admin/roles" element={<Roles />} />
                            <Route path="/admin/settings" element={<Settings />} />
                        </Route>
                    </Route>
                    <Route path="*" element={<NotFound />} />
                </Routes>
            </Suspense>
        </BrowserRouter>
    );
}
```

Two Blade views, one per template. `resources/views/app.blade.php` (blog):

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Blogify') }}</title>
    <link rel="stylesheet" href="{{ asset('template/blog/css/style.css') }}">
    @viteReactRefresh
    @vite('resources/js/public.jsx')
</head>
<body>
    <div id="root"></div>
</body>
</html>
```

`resources/views/admin.blade.php` (admin):

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | {{ config('app.name', 'Blogify') }}</title>
    <link rel="stylesheet" href="{{ asset('template/admin/css/style.css') }}">
    @viteReactRefresh
    @vite('resources/js/admin.jsx')
</head>
<body>
    <div id="root"></div>
</body>
</html>
```

`routes/web.php`. The admin shell requires a logged-in staff user, and the admin route must come **before** the public catch-all:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*')
    ->middleware(['auth', 'role:super-admin|admin|editor|author']);

Route::view('/{any?}', 'app')->where('any', '(?!api/|sanctum/|storage/|template/|admin(/|$)|up$).*');
```

Because the blog and admin are now separate pages, going between them is a normal full page load. Everything inside each part still has no page reload. Update the links that cross the boundary:

| File | Change |
| :-- | :-- |
| `PublicLayout.jsx` | Dashboard link becomes `<a className="dropdown-item" href="/admin/dashboard">` |
| `GuestRoute.jsx` | For admins with no saved `from`, run `window.location.assign('/admin/dashboard')` and return `<Spinner fullPage />` |
| `AdminRoute.jsx` | Replace both `<Navigate>` redirects with `window.location.replace('/login')` and `window.location.replace('/')`, returning `<Spinner fullPage />` |
| `AdminLayout.jsx` | "View site" and "Profile" become `<a href="/">` and `<a href="/profile">`. Logout ends with `window.location.assign('/login')` instead of `navigate('/login')` |

### 23.4 Convert the Blog Template to React

Work one HTML file at a time. Start with the shell, then the pages.

**HTML to JSX rules**

| HTML | JSX |
| :-- | :-- |
| `class="..."` | `className="..."` |
| `for="id"` | `htmlFor="id"` |
| `<img ...>`, `<input ...>`, `<br>`, `<hr>` | self-closing: `<img ... />` |
| `style="color:red; margin-top:10px"` | `style={{ color: 'red', marginTop: 10 }}` |
| `<!-- comment -->` | `{/* comment */}` |
| `stroke-width`, `fill-rule`, `tabindex` | `strokeWidth`, `fillRule`, `tabIndex` |
| `<a href="single.html">` | `<Link to="/blog/slug">` |
| `<a href="https://...">` | keep `<a>` (add `rel="noopener noreferrer"` for `target="_blank"`) |
| `src="img/a.jpg"` | `src="/template/blog/img/a.jpg"` |
| `onclick="..."` | `onClick={...}` handler |
| `<html>`, `<head>`, `<body>`, `<script>` tags | remove (see below) |

An online "HTML to JSX" converter does the mechanical part. Review the result by hand.

**Map template sections to project files**

| Template section / page | React file | Data from |
| :-- | :-- | :-- |
| Header / navbar | `layouts/PublicLayout.jsx` | settings, categories, auth state |
| Footer | `layouts/PublicLayout.jsx` | settings, newsletter form |
| Hero / slider | `pages/public/Home.jsx` | `/api/public/posts/featured` |
| Blog card | `components/public/PostCard.jsx` | any post list |
| Blog list + sidebar | `pages/public/PostArchive.jsx` | `/api/public/posts`, categories, tags |
| Single post | `pages/public/BlogDetail.jsx` | `/api/public/posts/{slug}` |
| Comments block | `components/public/CommentSection.jsx` | `/api/public/posts/{slug}/comments` |
| Login / register | `pages/auth/*` | Fortify endpoints |

**Example: static card to dynamic card**

Template HTML:

```html
<div class="col-lg-4 col-md-6">
  <div class="blog-card">
    <img src="img/blog-1.jpg" class="img-fluid" alt="">
    <span class="category">Technology</span>
    <h3><a href="single.html">Post title</a></h3>
    <p>Short excerpt...</p>
    <div class="meta">By John &middot; Oct 12, 2026</div>
  </div>
</div>
```

`components/public/PostCard.jsx` using the template's own class names:

```jsx
import { Link } from 'react-router-dom';
import { format } from 'date-fns';

export default function PostCard({ post }) {
    const image = post.featured_image
        ? `/storage/${post.featured_image}`
        : '/template/blog/img/blog-1.jpg'; // template placeholder

    return (
        <div className="blog-card">
            <img src={image} className="img-fluid" alt={post.title} loading="lazy" />
            <span className="category">{post.category.name}</span>
            <h3><Link to={`/blog/${post.slug}`}>{post.title}</Link></h3>
            <p>{post.excerpt}</p>
            <div className="meta">By {post.user?.name} &middot; {format(new Date(post.published_at), 'MMM dd, yyyy')}</div>
        </div>
    );
}
```

Keep the template's grid column classes in **one** place. `PostGrid.jsx` already wraps each card in `col-md-6 col-lg-4`. Change it to the template's column classes (for example `col-lg-4 col-md-6`) and keep the card itself free of a column wrapper.

**Header and footer**

Paste the template's navbar markup into `PublicLayout.jsx` as JSX, then replace the static parts with dynamic ones:

- brand text with `settings?.site_name`
- the category links with a `categories.map(...)` loop of `<NavLink>`
- Login/Register buttons with the `isAuthenticated` branch already in Step 16.2
- the footer newsletter input with `<NewsletterForm />`

If the template's navbar relies on Bootstrap's JavaScript (dropdowns, collapse), keep the React state toggles from Step 16.2 instead of loading `bootstrap.bundle.js`. Using both on the same elements breaks the toggles.

**Template JavaScript**

Do not load the template's `main.js`. It runs before React renders and binds to elements that do not exist yet. Replace each behaviour instead:

| Template script | React replacement |
| :-- | :-- |
| Mobile menu / sidebar toggle | `useState` (already used in the layouts) |
| Owl Carousel / Slick / Swiper slider | `swiper` (`npm install swiper`) |
| AOS / WOW scroll animation | `aos`, initialised once in `PublicLayout` with `useEffect` |
| Lightbox / GLightbox | `yet-another-react-lightbox` |
| Back-to-top button | a small component with a scroll listener |
| jQuery form validation | server validation errors via the `Field` component |

If you must keep a jQuery plugin, install `jquery`, import it inside a component, initialise it in `useEffect`, and destroy it in the cleanup function.

**Colours and dark mode**

`ThemeContext` sets `data-bs-theme`, which only works if the template uses Bootstrap 5.3 or newer. If it does not, remove the theme toggle or map it to the template's own dark class on `document.body`.

### 23.5 Option A: Convert the Admin Template to React

The admin pages from Step 18 already contain the logic. You replace their markup with the template's classes.

1. Copy the template's wrapper (sidebar, topbar, content area) into `layouts/AdminLayout.jsx` as JSX.
2. Keep the permission-filtered `MENU` array. Render each item with the template's menu markup.
3. Replace template page content one screen at a time:

| Template page | Project page |
| :-- | :-- |
| Dashboard (cards, charts) | `pages/admin/Dashboard.jsx` |
| Tables / data table | `posts/Index.jsx`, `Users.jsx`, `Comments.jsx`, `Media.jsx` |
| Forms / editor | `posts/Editor.jsx`, `TaxonomyManager.jsx`, `Settings.jsx` |
| Profile | `pages/Profile.jsx` |
| Modals / alerts | keep `react-hot-toast` and `window.confirm`, or add a small modal component |

4. Keep the data code (`useFetch`, `api`, `useForm`). Only change `className` values and wrapper elements.

Example `AdminLayout.jsx` skeleton with a generic template structure (adjust class names to your template):

```jsx
import { useEffect, useState } from 'react';
import { Link, NavLink, Outlet } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';

const MENU = [
    { to: '/admin/dashboard', icon: 'speedometer2', label: 'Dashboard', permission: 'view dashboard' },
    { to: '/admin/posts', icon: 'file-earmark-text', label: 'Posts', permission: ['manage posts', 'create posts'] },
    // ...same items as Step 16.2
    { to: '/profile', icon: 'person-circle', label: 'Profile', external: true },
];

export default function AdminLayout() {
    const { user, hasPermission, logout } = useAuth();
    const [collapsed, setCollapsed] = useState(false);

    // Many templates collapse the sidebar with a class on <body>
    useEffect(() => {
        document.body.classList.toggle('sidebar-collapse', collapsed);
        return () => document.body.classList.remove('sidebar-collapse');
    }, [collapsed]);

    const handleLogout = async () => {
        await logout();
        toast.success('Logged out');
        window.location.assign('/login');
    };

    return (
        <div className="wrapper">
            <aside className="main-sidebar">
                <a href="/" className="brand-link">Blogify</a>
                <nav className="sidebar-menu">
                    <ul>
                        {MENU.filter((m) => !m.permission || hasPermission(m.permission)).map((m) => (
                            <li key={m.to}>
                                {m.external
                                    ? <a href={m.to} className="nav-link"><i className={`bi bi-${m.icon}`}></i> {m.label}</a>
                                    : <NavLink to={m.to} className="nav-link"><i className={`bi bi-${m.icon}`}></i> {m.label}</NavLink>}
                            </li>
                        ))}
                    </ul>
                </nav>
            </aside>

            <div className="content-wrapper">
                <header className="main-header">
                    <button className="sidebar-toggle" onClick={() => setCollapsed(!collapsed)} aria-label="Toggle sidebar">
                        <i className="bi bi-list"></i>
                    </button>
                    <span>{user?.name}</span>
                    <button className="btn btn-sm btn-outline-danger" onClick={handleLogout}>Logout</button>
                </header>
                <section className="content"><Outlet /></section>
            </div>
        </div>
    );
}
```

**Admin template JavaScript.** Do not load the template's `app.js`. Replace its widgets:

| Template widget | Replacement |
| :-- | :-- |
| Charts (Chart.js / ApexCharts / Morris) | `react-chartjs-2` (already used) or `react-apexcharts` |
| DataTables | the server-side pagination and search already built in Step 18 |
| Select2 / date pickers | native `select` and `datetime-local`, or `react-select` |
| Rich text editor | `react-quill-new` (already used) |
| Sidebar tree menu | React state (`useState`) per menu group |
| Toasts / alerts | `react-hot-toast` |

### 23.6 Option B: Admin as a Laravel Blade Panel

Use this if you want the admin template to run as plain server-rendered pages. Skip 23.3's `admin.jsx`, `AdminRouter.jsx` and `admin.blade.php`. Keep the React blog from 23.4. This option does not use the `/api/admin/*` routes. The controllers below talk to the models directly.

**1. Layout and partials**

Copy the template to `public/template/admin/`. Convert its HTML into `resources/views/layouts/admin.blade.php` plus partials:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('template/admin/css/style.css') }}">
    @stack('styles')
</head>
<body>
    @include('panel.partials.sidebar')
    <div class="content-wrapper">
        @include('panel.partials.topbar')
        <main class="content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('template/admin/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template/admin/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

Rules for converting the template into Blade:

- Every `href="css/x.css"`, `src="js/x.js"`, `src="img/x.png"` becomes `{{ asset('template/admin/...') }}`.
- Sidebar links become named routes: `href="{{ route('panel.posts.index') }}"`.
- Active menu state: `class="nav-link {{ request()->routeIs('panel.posts.*') ? 'active' : '' }}"`.
- Hide menu items by permission: `@can('manage users') ... @endcan`.
- Repeating markup (table rows, cards) becomes `@foreach` / `@forelse` loops.
- Every form needs `@csrf`, and `@method('PUT')` / `@method('DELETE')` for update and delete.
- Print user data with `{{ }}` (escaped). Use `{!! !!}` only for sanitized HTML.

**2. Routes** (add above the SPA catch-all in `routes/web.php`)

```php
use App\Http\Controllers\Panel\DashboardController as PanelDashboardController;
use App\Http\Controllers\Panel\PostController as PanelPostController;

Route::middleware(['auth', 'role:super-admin|admin|editor|author'])
    ->prefix('panel')
    ->name('panel.')
    ->group(function () {
        Route::get('/', [PanelDashboardController::class, 'index'])->name('dashboard');
        Route::resource('posts', PanelPostController::class)->except('show');
        // categories, tags, comments, users ... follow the same pattern
    });

Route::view('/{any?}', 'app')->where('any', '(?!api/|sanctum/|storage/|template/|panel(/|$)|up$).*');
```

**3. Controller** (thin, reuses the models and `PostPolicy` from Step 13)

```php
<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Post::class);

        $posts = Post::with(['category:id,name', 'user:id,name'])
            ->when(! $request->user()->can('manage posts'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->query('search'), fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('panel.posts.index', compact('posts'));
    }

    public function create()
    {
        Gate::authorize('create', Post::class);

        return view('panel.posts.form', ['post' => new Post(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Post::class);

        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Post::create($data + [
            'slug' => Post::makeSlug($data['title']),
            'user_id' => $request->user()->id,
            'status' => 'draft',
        ]);

        return redirect()->route('panel.posts.index')->with('success', 'Post created.');
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
```

Blade validation errors: `@error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror` and `value="{{ old('title', $post->title) }}"`. Add `edit` and `update` the same way as `store`.

**4. List view** `resources/views/panel/posts/index.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Posts</h1>
        <a href="{{ route('panel.posts.create') }}" class="btn btn-primary">New post</a>
    </div>

    <table class="table">
        <thead><tr><th>Title</th><th>Category</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->category?->name }}</td>
                    <td>{{ $post->status }}</td>
                    <td class="text-end">
                        @can('delete', $post)
                            <form action="{{ route('panel.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No posts found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $posts->links() }}
@endsection
```

For Bootstrap-styled pagination add this to `AppServiceProvider::boot()`:

```php
use Illuminate\Pagination\Paginator;

Paginator::useBootstrapFive();
```

**5. Login flow.** Fortify views are disabled, so `auth` redirects guests to `/login`, which is the React login page. After a successful login, React must send staff users to the Blade panel with a full page load (it is outside React Router). In `GuestRoute.jsx`:

```jsx
if (canAccessAdmin && !location.state?.from) {
    window.location.assign('/panel');
    return <Spinner fullPage />;
}
```

For logout from the panel, use a form that posts to Fortify's `/logout` (`@csrf`), or add a small button that calls the logout endpoint and then goes to `/`.

### 23.7 Common Problems

| Problem | Fix |
| :-- | :-- |
| Page looks unstyled | CSS path is wrong. Use `/template/...` (absolute) in JSX and `{{ asset('template/...') }}` in Blade |
| Fonts or icons missing | Keep the template's `fonts/` folder next to its CSS and do not move files |
| Menus and dropdowns do nothing | Template JS is not loaded (correct for React). Rebuild the toggle with React state |
| Menus break after loading `bootstrap.bundle.js` | Do not mix Bootstrap's JS and React state on the same element. Use one |
| Two Bootstraps / clashing styles | Load only the CSS of the current page. Keep the blog and admin templates in separate entry points |
| Styles bleed from blog into admin (or back) | Both are in the same page. Use the two-entry setup in 23.3 |
| `Invalid DOM property class` warning | Replace `class` with `className` |
| `Style prop expects a mapping` error | Convert `style="..."` strings to objects |
| Template slider/animation does not start | Initialise it in `useEffect` after render, or use the React package version |
| Blade page shows 419 on forms | Missing `@csrf` in the form |
| Panel redirects guests to a blank page | Make sure `redirectGuestsTo('/login')` is set and the React `/login` route exists |
| `/panel` or `/admin` shows the blog 404 | The catch-all regex must exclude `panel` or `admin`, and the panel/admin routes must be declared first |

---

## Project File Structure

```text
laravel-react-blog/
├── app/
│   ├── Actions/Fortify/{CreateNewUser,ResetUserPassword,PasswordValidationRules}.php
│   ├── Http/Controllers/Api/
│   │   ├── Admin/{Dashboard,Post,Category,Tag,Comment,Media,User,Role,Permission,Setting}Controller.php
│   │   ├── Public/{Post,Category,Comment,Newsletter}Controller.php
│   │   └── ProfileController.php
│   ├── Models/
│   │   ├── Concerns/HasSlug.php
│   │   └── {User,Post,Category,Tag,Comment,Media,Newsletter,Setting}.php
│   ├── Policies/PostPolicy.php
│   └── Providers/{AppServiceProvider,FortifyServiceProvider}.php
├── bootstrap/{app.php,providers.php}
├── config/{sanctum,fortify,permission}.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── assets/css/app.css
│   │   ├── bootstrap/axios.js
│   │   ├── components/{common,public,admin}/
│   │   ├── context/{AuthContext,ThemeContext}.jsx
│   │   ├── hooks/{useFetch,useForm}.js
│   │   ├── layouts/{PublicLayout,AuthLayout,AdminLayout}.jsx
│   │   ├── pages/{public,auth,admin}/ + Profile.jsx, NotFound.jsx
│   │   ├── routes/{AppRouter,ProtectedRoute,GuestRoute,AdminRoute}.jsx
│   │   └── main.jsx
│   └── views/app.blade.php
├── routes/{api.php,web.php,console.php}
└── vite.config.js
```

---

## Production Build & Deployment

### Build

```bash
npm run build
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize        # caches config, routes, views and events
```

### `.env` Production

The SPA and API stay on one domain, so `SESSION_SAME_SITE=lax` is correct. `None` is only needed for cross-site setups.

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### Scheduler (cron)

```text
* * * * * cd /path/to/laravel-react-blog && php artisan schedule:run >> /dev/null 2>&1
```

### Checklist

- [ ] HTTPS enabled
- [ ] `APP_DEBUG=false` and a strong `APP_KEY`
- [ ] Seeded default passwords changed or removed
- [ ] `SANCTUM_STATEFUL_DOMAINS` matches the production host
- [ ] File permissions (`storage`, `bootstrap/cache`)
- [ ] Web server serves `public/` and falls back to `index.php`
- [ ] Real mail driver configured (verification and reset emails)
- [ ] Queue worker if queued mail or jobs are used
- [ ] Cron running `schedule:run`
- [ ] Database backups

---

## Security Best Practices

| Item | Notes |
| :-- | :-- |
| Auth | Sanctum session cookies. No tokens in localStorage |
| CSRF | `getCsrfCookie()` runs before auth POSTs. Axios retries once on 419 |
| RBAC | Enforced on the API with permission middleware and `PostPolicy` |
| Privilege escalation | Only a super admin can assign or modify the super-admin role. Core roles cannot be deleted |
| Validation | Server-side validation on every endpoint |
| File uploads | Image-only MIME validation, size limits, stored under generated names |
| XSS | Post HTML is sanitized with DOMPurify. Everything else is escaped by React |
| SQL injection | Eloquent / query builder with bindings only |
| Rate limiting | Login (Fortify), comments (10/min), newsletter (5/min) |
| Data exposure | Guest emails and two-factor columns are hidden from JSON. Public author data uses a column whitelist |
| Banned users | Fortify `authenticateUsing` blocks non-active accounts |
| Passwords | `Password::defaults()` plus hashing via the `hashed` cast |

---

## Performance Optimization

- Admin pages are lazy loaded with `React.lazy()`, so public visitors never download them
- All list endpoints are paginated and select only the columns they need (post lists skip `content`)
- Relationships are eager loaded with `with()` and `withCount()` to avoid N+1 queries
- Settings are cached with `Cache::rememberForever` and cleared on update
- Database indexes on `posts(status, published_at)` and `comments(post_id, status)`
- Images use `loading="lazy"`. Compress uploads before publishing
- Vite minifies and hashes assets in `npm run build`
- `php artisan optimize` in production
- Consider a CDN for `/storage` and `/build`

---

## Troubleshooting

| Issue | Solution |
| :-- | :-- |
| `419` CSRF mismatch | Open the app at `http://localhost:8000`, clear cookies, and confirm `getCsrfCookie()` runs before login |
| `401` on `/api/user` for guests | Expected. The auth context treats it as "not logged in" |
| `401` after logging in | `SANCTUM_STATEFUL_DOMAINS` must include the exact host and port you use. Run `php artisan config:clear` |
| Login redirects or "Route [login] not defined" | `redirectGuestsTo('/login')` is missing in `bootstrap/app.php` |
| `GET /login` returns Fortify's view error | Set `'views' => false` in `config/fortify.php` |
| Blank page, Vite errors | Keep `npm run dev` running, or run `npm run build`. Make sure `resources/js/app.js` is deleted and the Blade file uses `main.jsx` |
| `__dirname is not defined` | Use the `fileURLToPath` alias in `vite.config.js` (ES module) |
| "Table already exists" on migrate | A duplicate `create_*_table` migration exists. Delete it. Models must be created without `-m` |
| Foreign key errors on migrate | Migration files are not in dependency order. Rename the timestamps |
| Images not showing | Run `php artisan storage:link` |
| `404` on page refresh | Confirm the catch-all route in `routes/web.php` and that you browse via port 8000 |
| Permission denied (403) | Run `php artisan permission:cache-reset`, re-seed roles, then log in again |
| Route model binding fails for media | "media" singularizes to "medium". The guide declares the media routes by hand for this reason |
| `PUT` with a file upload does nothing | Send `POST` with `_method=PUT`. PHP does not parse multipart `PUT` bodies |

---

## Useful Commands

```bash
# Laravel
php artisan route:list --path=api
php artisan permission:cache-reset
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan tinker
php artisan schedule:work

# React/Vite
npm run dev
npm run build

# Storage
php artisan storage:link
```

---

**Extend it with:** an RSS feed, `sitemap.xml`, an admin newsletter subscribers page, related posts by tags, social login, and SSR or prerendering for full SEO.

*Last Updated: October 2026 | Laravel 13 | React 19 | Bootstrap 5.3 | Sanctum + Fortify + Spatie Permission*
