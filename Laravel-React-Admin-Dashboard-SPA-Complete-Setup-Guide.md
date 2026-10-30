# Laravel 13 + React Admin Dashboard SPA - Complete Setup Guide (A to Z)

> A fully integrated, modern, and production-ready Laravel 13 + React Admin Dashboard with session-based authentication, RBAC, and a trending Bootstrap 5.3 UI. 

---

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Project Architecture](#project-architecture)
- [Step 1: Create Laravel 13 Project](#step-1-create-laravel-13-project)
- [Step 2: Install Laravel Sanctum (Session-Based SPA Auth)](#step-2-install-laravel-sanctum-session-based-spa-auth)
- [Step 3: Install Laravel Fortify](#step-3-install-laravel-fortify)
- [Step 4: Install Spatie Laravel Permission](#step-4-install-spatie-laravel-permission)
- [Step 5: Environment & Database Configuration](#step-5-environment--database-configuration)
- [Step 6: Install & Setup React with Vite](#step-6-install--setup-react-with-vite)
- [Step 7: Install Frontend Dependencies](#step-7-install-frontend-dependencies)
- [Step 8: Configure Vite for React](#step-8-configure-vite-for-react)
- [Step 9: Configure Sanctum, CORS & Middleware](#step-9-configure-sanctum-cors--middleware)
- [Step 10: Configure Fortify Features](#step-10-configure-fortify-features)
- [Step 11: Setup Spatie Permission & User Model](#step-11-setup-spatie-permission--user-model)
- [Step 12: Create API Routes, Controllers & Resources](#step-12-create-api-routes-controllers--resources)
- [Step 13: Setup SPA View & Web Routes](#step-13-setup-spa-view--web-routes)
- [Step 14: Setup Axios, CSRF & Auth Context](#step-14-setup-axios-csrf--auth-context)
- [Step 15: Create React Router Structure & Protected Routes](#step-15-create-react-router-structure--protected-routes)
- [Step 16: Build Authentication Pages](#step-16-build-authentication-pages)
- [Step 17: Build Admin Layout (Sidebar, Navbar, Theme)](#step-17-build-admin-layout-sidebar-navbar-theme)
- [Step 18: Build Dashboard with Analytics & Charts](#step-18-build-dashboard-with-analytics--charts)
- [Step 19: User, Role & Permission Management Modules](#step-19-user-role--permission-management-modules)
- [Step 20: Profile Management, Notifications & Settings](#step-20-profile-management-notifications--settings)
- [Step 21: Migrations, Seeders & Running the App](#step-21-migrations-seeders--running-the-app)
- [Project File Structure Overview](#project-file-structure-overview)
- [Production Build & Deployment](#production-build--deployment)
- [Security Best Practices](#security-best-practices)
- [Troubleshooting Guide](#troubleshooting-guide)
- [Useful Artisan & NPM Commands](#useful-artisan--npm-commands)

---

## Project Overview

This guide will help you build a scalable, enterprise-level Admin Dashboard using Laravel 13 as the REST API backend and React as the frontend SPA. The project utilizes session-based authentication with Laravel Sanctum and Fortify, fine-grained access control with Spatie Permission, and a clean, responsive Bootstrap 5.3 UI complete with light/dark mode, analytics, charts, CRUD modules, and more.

---

## Tech Stack

| Layer | Technology | Version | Purpose |
| :-- | :-- | :-- | :-- |
| Backend | [Laravel](https://laravel.com/) | 13.x | API, Authentication, Authorization & Business Logic |
| Frontend | [React](https://react.dev/) | ^19.x | Component-based SPA |
| Auth (Sessions) | [Laravel Sanctum](https://laravel.com/docs/sanctum) | ^4.x | Cookie-based SPA Authentication & CSRF |
| Auth Logic | [Laravel Fortify](https://laravel.com/docs/fortify) | ^1.x | Login, Register, Reset Password, Email Verification |
| RBAC | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) | ^6.x | Roles, Permissions & Middleware |
| Routing | [React Router DOM](https://reactrouter.com/) | ^7.x | Client-side routing & route protection |
| HTTP Client | [Axios](https://axios-http.com/) | ^1.7.x | API requests with credentials |
| UI Framework | [Bootstrap](https://getbootstrap.com/) | ^5.3.x | Responsive grid, components & utilities |
| Icons | [Bootstrap Icons](https://icons.getbootstrap.com/) | ^1.11.x | Icon set |
| Charts | [Chart.js](https://www.chartjs.org/) & [react-chartjs-2](https://react-chartjs-2.js.org/) | Latest | Interactive dashboard charts |
| Date/Utils | [date-fns](https://date-fns.org/), [classnames](https://www.npmjs.com/package/classnames), [react-hot-toast](https://react-hot-toast.com/) | Latest | UX utilities & notifications |

---

## Prerequisites

Ensure the following are installed on your machine:

| Software | Recommended Version | Notes |
| :-- | :-- | :-- |
| [PHP](https://www.php.net/) | >= 8.2 | Required for Laravel 13 |
| [Composer](https://getcomposer.org/) | >= 2.7 | PHP dependency manager |
| [Node.js](https://nodejs.org/) | >= 18 LTS | For React & Vite |
| [NPM](https://www.npmjs.com/) or [Yarn](https://yarnpkg.com/) or [PNPM](https://pnpm.io/) | Latest | Package manager |
| [MySQL](https://www.mysql.com/) / [MariaDB](https://mariadb.org/) | >= 10.4 | Database |
| [Git](https://git-scm.com/) | Latest | Version control |
| Local Server | [Laragon](https://laragon.org/), [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/) or [Laravel Herd](https://herd.laravel.com/) | Recommended for Windows |

---

## Project Architecture

```text
┌─────────────────────────┐        Cookies + CSRF        ┌─────────────────────────┐
│       React SPA         │  ─────────────────────────►  │       Laravel 13 API    │
│  (Bootstrap 5.3 UI)     │        JSON Requests        │  Sanctum + Fortify +    │
│  React Router + Axios   │  ◄─────────────────────────  │  Spatie Permission      │
└─────────────────────────┘                              └─────────────┬───────────┘
                                                                       │
                                                                     MySQL
                                                                       │
                                                                   Migrations
                                                                   Models/Policies
```

- **Frontend:** React SPA served by Vite, communicates with Laravel via API endpoints.
- **Authentication:** Sanctum issues HTTP-only cookies after CSRF verification. Fortify handles the auth logic.
- **Authorization:** Spatie checks roles/permissions on API routes and is mirrored on the UI.
- **State:** React Context API (or Zustand) used for Auth, Theme, and global app state. 

---

## Step 1: Create Laravel 13 Project

Open your terminal in `C:\wamp64\www\` or your preferred directory.

```bash
# Create a new Laravel 13 project
composer create-project laravel/laravel laravel-react-admin "13.*"

# Navigate into the project
cd laravel-react-admin
```

---

## Step 2: Install Laravel Sanctum (Session-Based SPA Auth)

```bash
# Install Sanctum
composer require laravel/sanctum

# Publish config and migrations
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

---

## Step 3: Install Laravel Fortify

```bash
# Install Fortify
composer require laravel/fortify

# Publish Fortify assets (actions, config, migrations)
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"

# Run Fortify migrations
php artisan migrate
```

---

## Step 4: Install Spatie Laravel Permission

```bash
# Install Spatie Permission
composer require spatie/laravel-permission

# Publish config and migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate
```

---

## Step 5: Environment & Database Configuration

### 5.1 Update `.env`

```env
APP_NAME="ReactAdminDashboard"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_react_admin
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="admin@reactadmindashboard.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5.2 Generate Application Key

```bash
php artisan key:generate
```

### 5.3 Create Database

Create a database named `laravel_react_admin` in your MySQL client (phpMyAdmin, TablePlus, HeidiSQL etc.)

---

## Step 6: Install & Setup React with Vite

Laravel 13 ships with Vite. Install the React plugin.

```bash
npm install react react-dom
npm install @vitejs/plugin-react --save-dev
```

---

## Step 7: Install Frontend Dependencies

```bash
# Routing
npm install react-router-dom

# HTTP Client
npm install axios

# Bootstrap 5.3
npm install bootstrap @popperjs/core bootstrap-icons

# Charts
npm install chart.js react-chartjs-2

# Utilities
npm install react-hot-toast date-fns classnames

# Optional: Zustand for global state (recommended)
npm install zustand
```

---

## Step 8: Configure Vite for React

### 8.1 Update `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/main.jsx'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: true,
    },
});
```

### 8.2 Update `package.json` Scripts (Ensure these exist)

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

---

## Step 9: Configure Sanctum, CORS & Middleware

### 9.1 Update `config/sanctum.php`

Ensure stateful domains are correct as per `.env`.

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1,localhost:5173,127.0.0.1:5173')),
```

### 9.2 Update `config/cors.php`

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'forgot-password', 'reset-password', 'email/verify/*', 'email/verification-notification'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 9.3 Update `bootstrap/app.php` (Laravel 11/13)

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
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

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

### 9.4 Register Fortify Provider

Add to `bootstrap/providers.php`

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
```

---

## Step 10: Configure Fortify Features

### 10.1 Update `app/Providers/FortifyServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                return response()->json(['message' => 'Login successful'], 200);
            }
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return response()->json(['message' => 'Registration successful'], 201);
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return response()->json(['message' => 'Logged out successfully'], 200);
            }
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttle = Str::transliterate(Str::lower($request->input(Fortify::username())));
            return Limit::perMinute(5)->by($throttle.'|'.$request->ip());
        });
    }
}
```

### 10.2 Update `config/fortify.php`

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
],
```

---

## Step 11: Setup Spatie Permission & User Model

### 11.1 Update `app/Models/User.php`

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
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### 11.2 Add Columns to Users Table (Optional but Recommended)

Create a migration to extend users table:

```bash
php artisan make:migration add_profile_fields_to_users_table --table=users
```

`database/migrations/xxxx_add_profile_fields_to_users_table.php`

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
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'status']);
        });
    }
};
```

---

## Step 12: Create API Routes, Controllers & Resources

### 12.1 Update `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DashboardController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Authenticated user
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('roles.permissions', 'permissions');
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
    });

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Admin Management (Protected by role/permission)
    Route::middleware(['role:super-admin|admin'])->prefix('admin')->group(function () {
        // Users
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::post('users/{user}/remove-role', [UserController::class, 'removeRole']);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

        // Roles
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions']);

        // Permissions
        Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'show']);
    });
});
```

### 12.2 Generate Controllers

```bash
php artisan make:controller Api/UserController --api
php artisan make:controller Api/RoleController --api
php artisan make:controller Api/PermissionController --api
php artisan make:controller Api/ProfileController
php artisan make:controller Api/DashboardController
```

### 12.3 Example `app/Http/Controllers/Api/UserController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles.permissions');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->role($request->role);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'roles' => ['array'],
            'status' => ['in:active,inactive,banned']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status ?? 'active',
        ]);

        if ($request->roles) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user->load('roles'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles.permissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'roles' => ['array'],
            'status' => ['in:active,inactive,banned']
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'status']));

        if (isset($request->roles)) {
            $user->syncRoles($request->roles);
        }

        if ($request->filled('password')) {
            $request->validate(['password' => Rules\Password::defaults()]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user->load('roles'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => ['required', 'string']]);
        $user->assignRole($request->role);
        return response()->json(['message' => 'Role assigned successfully', 'user' => $user->load('roles')]);
    }

    public function removeRole(Request $request, User $user)
    {
        $request->validate(['role' => ['required', 'string']]);
        $user->removeRole($request->role);
        return response()->json(['message' => 'Role removed successfully', 'user' => $user->load('roles')]);
    }

    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        return response()->json(['message' => 'Status updated', 'status' => $user->status]);
    }
}
```

### 12.4 Example `app/Http/Controllers/Api/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'recent_users' => User::with('roles')->latest()->take(5)->get(),
            'users_by_month' => User::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )->groupBy('month')->orderBy('month')->get(),
        ]);
    }
}
```

---

## Step 13: Setup SPA View & Web Routes

### 13.1 Create `resources/views/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'React Admin Dashboard') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body>
    <div id="root"></div>
</body>
</html>
```

### 13.2 Update `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
```

---

## Step 14: Setup Axios, CSRF & Auth Context

### 14.1 Create `resources/js/bootstrap/axios.js`

```js
import axios from 'axios';

axios.defaults.baseURL = '/';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// CSRF Cookie helper
export const getCsrfCookie = async () => {
    await axios.get('/sanctum/csrf-cookie');
};

export default axios;
```

### 14.2 Create Auth Context `resources/js/context/AuthContext.jsx`

```jsx
import { createContext, useContext, useEffect, useState } from 'react';
import axios, { getCsrfCookie } from '@/bootstrap/axios';
import toast from 'react-hot-toast';

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error('useAuth must be used within AuthProvider');
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [permissions, setPermissions] = useState([]);
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchUser = async () => {
        try {
            const res = await axios.get('/api/user');
            setUser(res.data.user);
            setRoles(res.data.roles || []);
            setPermissions(res.data.permissions || []);
        } catch (error) {
            setUser(null);
            setRoles([]);
            setPermissions([]);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUser();
    }, []);

    const login = async (credentials) => {
        await getCsrfCookie();
        await axios.post('/login', credentials);
        await fetchUser();
        toast.success('Logged in successfully');
    };

    const register = async (data) => {
        await getCsrfCookie();
        await axios.post('/register', data);
        await fetchUser();
        toast.success('Account created successfully');
    };

    const logout = async () => {
        await axios.post('/logout');
        setUser(null);
        setRoles([]);
        setPermissions([]);
        toast.success('Logged out successfully');
    };

    const hasRole = (role) => {
        if (Array.isArray(role)) {
            return role.some(r => roles.includes(r));
        }
        return roles.includes(role);
    };

    const hasPermission = (permission) => {
        if (Array.isArray(permission)) {
            return permission.some(p => permissions.includes(p));
        }
        return permissions.includes(permission);
    };

    const can = (permission) => hasPermission(permission);

    return (
        <AuthContext.Provider value={{
            user,
            roles,
            permissions,
            loading,
            login,
            register,
            logout,
            fetchUser,
            hasRole,
            hasPermission,
            can,
            isAuthenticated: !!user
        }}>
            {children}
        </AuthContext.Provider>
    );
};
```

### 14.3 Create Theme Context `resources/js/context/ThemeContext.jsx`

```jsx
import { createContext, useContext, useEffect, useState } from 'react';

const ThemeContext = createContext();

export const useTheme = () => useContext(ThemeContext);

export const ThemeProvider = ({ children }) => {
    const [theme, setTheme] = useState(localStorage.getItem('theme') || 'light');

    useEffect(() => {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
    }, [theme]);

    const toggleTheme = () => {
        setTheme(prev => prev === 'light' ? 'dark' : 'light');
    };

    return (
        <ThemeContext.Provider value={{ theme, toggleTheme }}>
            {children}
        </ThemeContext.Provider>
    );
};
```

---

## Step 15: Create React Router Structure & Protected Routes

### 15.1 Create `resources/js/routes/ProtectedRoute.jsx`

```jsx
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

const ProtectedRoute = ({ requiredRole, requiredPermission }) => {
    const { isAuthenticated, loading, hasRole, hasPermission } = useAuth();

    if (loading) return <Spinner fullScreen />;

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    if (requiredRole && !hasRole(requiredRole)) {
        return <Navigate to="/admin/dashboard" replace />;
    }

    if (requiredPermission && !hasPermission(requiredPermission)) {
        return <Navigate to="/admin/dashboard" replace />;
    }

    return <Outlet />;
};

export default ProtectedRoute;
```

### 15.2 Create `resources/js/routes/GuestRoute.jsx`

```jsx
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

const GuestRoute = () => {
    const { isAuthenticated, loading } = useAuth();

    if (loading) return <Spinner fullScreen />;

    if (isAuthenticated) {
        return <Navigate to="/admin/dashboard" replace />;
    }

    return <Outlet />;
};

export default GuestRoute;
```

### 15.3 Create `resources/js/routes/AppRouter.jsx`

```jsx
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import GuestRoute from './GuestRoute';
import ProtectedRoute from './ProtectedRoute';

// Layouts
import AuthLayout from '@/layouts/AuthLayout';
import AdminLayout from '@/layouts/AdminLayout';

// Auth Pages
import Login from '@/pages/auth/Login';
import Register from '@/pages/auth/Register';
import ForgotPassword from '@/pages/auth/ForgotPassword';
import ResetPassword from '@/pages/auth/ResetPassword';
import VerifyEmail from '@/pages/auth/VerifyEmail';

// Admin Pages
import Dashboard from '@/pages/admin/Dashboard';
import UsersIndex from '@/pages/admin/users/Index';
import UsersCreate from '@/pages/admin/users/Create';
import UsersEdit from '@/pages/admin/users/Edit';
import RolesIndex from '@/pages/admin/roles/Index';
import PermissionsIndex from '@/pages/admin/permissions/Index';
import Profile from '@/pages/admin/profile/Index';
import Settings from '@/pages/admin/settings/Index';
import NotFound from '@/pages/NotFound';

const AppRouter = () => {
    return (
        <BrowserRouter>
            <Routes>
                <Route element={<GuestRoute />}>
                    <Route element={<AuthLayout />}>
                        <Route path="/login" element={<Login />} />
                        <Route path="/register" element={<Register />} />
                        <Route path="/forgot-password" element={<ForgotPassword />} />
                        <Route path="/reset-password/:token" element={<ResetPassword />} />
                    </Route>
                </Route>

                <Route element={<ProtectedRoute />}>
                    <Route path="/email/verify" element={<VerifyEmail />} />
                    <Route element={<AdminLayout />}>
                        <Route path="/admin/dashboard" element={<Dashboard />} />
                        <Route path="/admin/profile" element={<Profile />} />
                        <Route path="/admin/settings" element={<Settings />} />
                        
                        <Route element={<ProtectedRoute requiredRole={['super-admin', 'admin']} />}>
                            <Route path="/admin/users" element={<UsersIndex />} />
                            <Route path="/admin/users/create" element={<UsersCreate />} />
                            <Route path="/admin/users/:id/edit" element={<UsersEdit />} />
                            <Route path="/admin/roles" element={<RolesIndex />} />
                            <Route path="/admin/permissions" element={<PermissionsIndex />} />
                        </Route>
                    </Route>
                </Route>

                <Route path="/" element={<Navigate to="/admin/dashboard" replace />} />
                <Route path="*" element={<NotFound />} />
            </Routes>
        </BrowserRouter>
    );
};

export default AppRouter;
```

### 15.4 Create `resources/js/main.jsx`

```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import AppRouter from './routes/AppRouter';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import { Toaster } from 'react-hot-toast';

// Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Custom CSS
import './assets/css/app.css';

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <ThemeProvider>
            <AuthProvider>
                <AppRouter />
                <Toaster position="top-right" toastOptions={{
                    duration: 4000,
                    style: { background: '#333', color: '#fff' },
                }} />
            </AuthProvider>
        </ThemeProvider>
    </React.StrictMode>
);
```

---

## Step 16: Build Authentication Pages

### 16.1 Create `resources/js/pages/auth/Login.jsx`

```jsx
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import toast from 'react-hot-toast';

const Login = () => {
    const { login } = useAuth();
    const navigate = useNavigate();
    const [form, setForm] = useState({ email: '', password: '', remember: false });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setForm(prev => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});
        
        try {
            await login(form);
            navigate('/admin/dashboard');
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            } else {
                toast.error('Invalid credentials');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="card shadow-lg border-0">
            <div className="card-body p-5">
                <div className="text-center mb-4">
                    <h4 className="fw-bold">Welcome Back</h4>
                    <p className="text-muted">Sign in to continue to Admin Dashboard</p>
                </div>
                <form onSubmit={handleSubmit}>
                    <div className="mb-3">
                        <label className="form-label">Email Address</label>
                        <input type="email" name="email" className={`form-control ${errors.email ? 'is-invalid' : ''}`} value={form.email} onChange={handleChange} required />
                        {errors.email && <div className="invalid-feedback">{errors.email[0]}</div>}
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Password</label>
                        <input type="password" name="password" className={`form-control ${errors.password ? 'is-invalid' : ''}`} value={form.password} onChange={handleChange} required />
                        {errors.password && <div className="invalid-feedback">{errors.password[0]}</div>}
                    </div>
                    <div className="mb-3 form-check">
                        <input type="checkbox" className="form-check-input" name="remember" id="remember" checked={form.remember} onChange={handleChange} />
                        <label className="form-check-label" htmlFor="remember">Remember me</label>
                    </div>
                    <button type="submit" className="btn btn-primary w-100" disabled={loading}>
                        {loading ? <span className="spinner-border spinner-border-sm me-2"></span> : null}
                        Sign In
                    </button>
                </form>
                <div className="text-center mt-3">
                    <Link to="/forgot-password" className="text-decoration-none">Forgot Password?</Link>
                </div>
                <div className="text-center mt-2">
                    <span className="text-muted">Don't have an account?</span> <Link to="/register" className="text-decoration-none">Register</Link>
                </div>
            </div>
        </div>
    );
};

export default Login;
```

*(Create similar components for Register, ForgotPassword, ResetPassword, and VerifyEmail following the same pattern.)*

### 16.2 Create `resources/js/layouts/AuthLayout.jsx`

```jsx
import { Outlet } from 'react-router-dom';

const AuthLayout = () => {
    return (
        <div className="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
            <div style={{ width: '450px' }}>
                <div className="text-center mb-4">
                    <h2 className="text-white fw-bold">React Admin Dashboard</h2>
                    <p className="text-white-50">Laravel 13 + React SPA</p>
                </div>
                <Outlet />
            </div>
        </div>
    );
};

export default AuthLayout;
```

Add styles in `resources/js/assets/css/app.css`:

```css
:root {
    --sidebar-width: 260px;
    --header-height: 70px;
}

[data-bs-theme="dark"] {
    --bs-body-bg: #0f1026;
    --bs-body-color: #e4e6eb;
    --bs-card-bg: #1c1e2f;
    --bs-border-color: #2d2f45;
}

.auth-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: var(--sidebar-width);
    transition: all 0.3s ease;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar.collapsed {
    width: 80px;
}

.main-content {
    margin-left: var(--sidebar-width);
    width: calc(100% - var(--sidebar-width));
    transition: all 0.3s ease;
}

.main-content.expanded {
    margin-left: 80px;
    width: calc(100% - 80px);
}

.header {
    height: var(--header-height);
    position: sticky;
    top: 0;
    z-index: 999;
}

.content {
    padding: 1.5rem;
    min-height: calc(100vh - var(--header-height));
}
```

---

## Step 17: Build Admin Layout (Sidebar, Navbar, Theme)

### 17.1 Create `resources/js/layouts/AdminLayout.jsx`

```jsx
import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from '@/components/layout/Sidebar';
import Header from '@/components/layout/Header';

const AdminLayout = () => {
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    const toggleSidebar = () => {
        setSidebarCollapsed(!sidebarCollapsed);
    };

    return (
        <div className="admin-wrapper">
            <Sidebar collapsed={sidebarCollapsed} />
            <div className={`main-content ${sidebarCollapsed ? 'expanded' : ''}`}>
                <Header toggleSidebar={toggleSidebar} />
                <main className="content">
                    <Outlet />
                </main>
            </div>
        </div>
    );
};

export default AdminLayout;
```

### 17.2 Create `resources/js/components/layout/Sidebar.jsx`

```jsx
import { NavLink } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';

const Sidebar = ({ collapsed }) => {
    const { user, hasRole, hasPermission } = useAuth();

    const menuItems = [
        { title: 'Dashboard', icon: 'bi-speedometer2', path: '/admin/dashboard', show: true },
        { title: 'Users', icon: 'bi-people', path: '/admin/users', show: hasRole(['super-admin', 'admin']) || hasPermission('manage users') },
        { title: 'Roles', icon: 'bi-shield-lock', path: '/admin/roles', show: hasRole(['super-admin', 'admin']) || hasPermission('manage roles') },
        { title: 'Permissions', icon: 'bi-key', path: '/admin/permissions', show: hasRole(['super-admin', 'admin']) || hasPermission('manage permissions') },
        { title: 'Profile', icon: 'bi-person', path: '/admin/profile', show: true },
        { title: 'Settings', icon: 'bi-gear', path: '/admin/settings', show: true },
    ];

    return (
        <aside className={`sidebar bg-body-tertiary border-end ${collapsed ? 'collapsed' : ''}`}>
            <div className="sidebar-header d-flex align-items-center justify-content-center border-bottom" style={{ height: '70px' }}>
                <h5 className="mb-0 fw-bold text-primary">
                    <i className="bi bi-grid-1x2-fill me-2"></i>
                    {!collapsed && <span>ReactAdmin</span>}
                </h5>
            </div>
            <nav className="sidebar-nav p-2">
                <ul className="nav flex-column">
                    {menuItems.filter(item => item.show).map((item, index) => (
                        <li className="nav-item" key={index}>
                            <NavLink to={item.path} className="nav-link d-flex align-items-center py-2 px-3 rounded mb-1">
                                <i className={`bi ${item.icon} ${collapsed ? '' : 'me-3'}`}></i>
                                {!collapsed && <span>{item.title}</span>}
                            </NavLink>
                        </li>
                    ))}
                </ul>
            </nav>
        </aside>
    );
};

export default Sidebar;
```

### 17.3 Create `resources/js/components/layout/Header.jsx`

```jsx
import { useAuth } from '@/context/AuthContext';
import { useTheme } from '@/context/ThemeContext';
import { Link } from 'react-router-dom';

const Header = ({ toggleSidebar }) => {
    const { user, logout } = useAuth();
    const { theme, toggleTheme } = useTheme();

    return (
        <header className="header bg-body border-bottom d-flex align-items-center justify-content-between px-4">
            <div className="d-flex align-items-center">
                <button className="btn btn-link text-body" onClick={toggleSidebar}>
                    <i className="bi bi-list fs-4"></i>
                </button>
            </div>
            <div className="d-flex align-items-center gap-3">
                <button className="btn btn-link text-body" onClick={toggleTheme}>
                    <i className={`bi ${theme === 'light' ? 'bi-moon-stars' : 'bi-sun'} fs-5`}></i>
                </button>
                <div className="dropdown">
                    <button className="btn btn-link text-body dropdown-toggle text-decoration-none d-flex align-items-center" data-bs-toggle="dropdown">
                        <div className="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style={{ width: '40px', height: '40px' }}>
                            {user?.name?.charAt(0).toUpperCase()}
                        </div>
                        <span className="d-none d-md-inline">{user?.name}</span>
                    </button>
                    <ul className="dropdown-menu dropdown-menu-end">
                        <li><Link className="dropdown-item" to="/admin/profile"><i className="bi bi-person me-2"></i>Profile</Link></li>
                        <li><Link className="dropdown-item" to="/admin/settings"><i className="bi bi-gear me-2"></i>Settings</Link></li>
                        <li><hr className="dropdown-divider" /></li>
                        <li><button className="dropdown-item" onClick={logout}><i className="bi bi-box-arrow-right me-2"></i>Logout</button></li>
                    </ul>
                </div>
            </div>
        </header>
    );
};

export default Header;
```

---

## Step 18: Build Dashboard with Analytics & Charts

### 18.1 Create `resources/js/pages/admin/Dashboard.jsx`

```jsx
import { useEffect, useState } from 'react';
import axios from '@/bootstrap/axios';
import { Line, Doughnut } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

const Dashboard = () => {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            const res = await axios.get('/api/dashboard/stats');
            setStats(res.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const lineData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'New Users',
            data: stats?.users_by_month?.map(m => m.count) || [],
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
        }],
    };

    const doughnutData = {
        labels: ['Active', 'Inactive', 'Banned'],
        datasets: [{
            data: [stats?.active_users || 0, (stats?.total_users - stats?.active_users) || 0, 0],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
        }],
    };

    if (loading) return <div className="text-center py-5"><div className="spinner-border"></div></div>;

    return (
        <div>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h4 className="mb-0 fw-bold">Dashboard Overview</h4>
                <nav aria-label="breadcrumb">
                    <ol className="breadcrumb mb-0">
                        <li className="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>

            <div className="row g-4 mb-4">
                <div className="col-xl-3 col-md-6">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="d-flex justify-content-between">
                                <div>
                                    <p className="text-muted mb-1">Total Users</p>
                                    <h3 className="mb-0 fw-bold">{stats?.total_users}</h3>
                                </div>
                                <div className="bg-primary bg-opacity-10 p-3 rounded">
                                    <i className="bi bi-people text-primary fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-xl-3 col-md-6">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="d-flex justify-content-between">
                                <div>
                                    <p className="text-muted mb-1">Active Users</p>
                                    <h3 className="mb-0 fw-bold">{stats?.active_users}</h3>
                                </div>
                                <div className="bg-success bg-opacity-10 p-3 rounded">
                                    <i className="bi bi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-xl-3 col-md-6">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="d-flex justify-content-between">
                                <div>
                                    <p className="text-muted mb-1">Total Roles</p>
                                    <h3 className="mb-0 fw-bold">{stats?.total_roles}</h3>
                                </div>
                                <div className="bg-warning bg-opacity-10 p-3 rounded">
                                    <i className="bi bi-shield-lock text-warning fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-xl-3 col-md-6">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="d-flex justify-content-between">
                                <div>
                                    <p className="text-muted mb-1">Permissions</p>
                                    <h3 className="mb-0 fw-bold">{stats?.total_permissions}</h3>
                                </div>
                                <div className="bg-info bg-opacity-10 p-3 rounded">
                                    <i className="bi bi-key text-info fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="row g-4">
                <div className="col-xl-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-transparent border-0 py-3">
                            <h5 className="mb-0">User Registrations</h5>
                        </div>
                        <div className="card-body">
                            <Line data={lineData} options={{ responsive: true, plugins: { legend: { display: false } } }} />
                        </div>
                    </div>
                </div>
                <div className="col-xl-4">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-transparent border-0 py-3">
                            <h5 className="mb-0">User Status</h5>
                        </div>
                        <div className="card-body">
                            <Doughnut data={doughnutData} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
```

---

## Step 19: User, Role & Permission Management Modules

### 19.1 Users Index `resources/js/pages/admin/users/Index.jsx`

```jsx
import { useEffect, useState } from 'react';
import axios from '@/bootstrap/axios';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';

const UsersIndex = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [pagination, setPagination] = useState({});

    useEffect(() => {
        fetchUsers();
    }, [search]);

    const fetchUsers = async (page = 1) => {
        try {
            const res = await axios.get(`/api/admin/users?page=${page}&search=${search}`);
            setUsers(res.data.data);
            setPagination(res.data);
        } catch (error) {
            toast.error('Failed to fetch users');
        } finally {
            setLoading(false);
        }
    };

    const deleteUser = async (id) => {
        if (!confirm('Are you sure you want to delete this user?')) return;
        try {
            await axios.delete(`/api/admin/users/${id}`);
            toast.success('User deleted successfully');
            fetchUsers();
        } catch (error) {
            toast.error('Failed to delete user');
        }
    };

    const toggleStatus = async (id) => {
        try {
            await axios.post(`/api/admin/users/${id}/toggle-status`);
            toast.success('Status updated');
            fetchUsers();
        } catch (error) {
            toast.error('Failed to update status');
        }
    };

    return (
        <div>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h4 className="mb-0 fw-bold">User Management</h4>
                <Link to="/admin/users/create" className="btn btn-primary">
                    <i className="bi bi-plus-circle me-2"></i>Add User
                </Link>
            </div>

            <div className="card border-0 shadow-sm">
                <div className="card-header bg-transparent border-0 py-3">
                    <div className="row align-items-center">
                        <div className="col-md-4">
                            <input type="text" className="form-control" placeholder="Search users..." value={search} onChange={(e) => setSearch(e.target.value)} />
                        </div>
                    </div>
                </div>
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.map(user => (
                                    <tr key={user.id}>
                                        <td>
                                            <div className="d-flex align-items-center">
                                                <div className="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style={{ width: '35px', height: '35px' }}>
                                                    {user.name.charAt(0).toUpperCase()}
                                                </div>
                                                <span>{user.name}</span>
                                            </div>
                                        </td>
                                        <td>{user.email}</td>
                                        <td>
                                            {user.roles?.map(role => (
                                                <span key={role.id} className="badge bg-primary me-1">{role.name}</span>
                                            ))}
                                        </td>
                                        <td>
                                            <span className={`badge bg-${user.status === 'active' ? 'success' : user.status === 'inactive' ? 'warning' : 'danger'}`}>
                                                {user.status}
                                            </span>
                                        </td>
                                        <td>{new Date(user.created_at).toLocaleDateString()}</td>
                                        <td>
                                            <div className="btn-group">
                                                <Link to={`/admin/users/${user.id}/edit`} className="btn btn-sm btn-outline-primary">
                                                    <i className="bi bi-pencil"></i>
                                                </Link>
                                                <button className="btn btn-sm btn-outline-warning" onClick={() => toggleStatus(user.id)}>
                                                    <i className="bi bi-toggle-on"></i>
                                                </button>
                                                <button className="btn btn-sm btn-outline-danger" onClick={() => deleteUser(user.id)}>
                                                    <i className="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UsersIndex;
```

*(Follow similar CRUD patterns for Roles and Permissions with modal forms for assigning permissions to roles.)*

---

## Step 20: Profile Management, Notifications & Settings

### 20.1 Profile Page `resources/js/pages/admin/profile/Index.jsx`

```jsx
import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import axios from '@/bootstrap/axios';
import toast from 'react-hot-toast';

const Profile = () => {
    const { user, fetchUser } = useAuth();
    const [activeTab, setActiveTab] = useState('profile');
    const [profileForm, setProfileForm] = useState({
        name: user?.name || '',
        email: user?.email || '',
        phone: user?.phone || '',
    });
    const [passwordForm, setPasswordForm] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updateProfile = async (e) => {
        e.preventDefault();
        try {
            await axios.put('/api/profile', profileForm);
            await fetchUser();
            toast.success('Profile updated successfully');
        } catch (error) {
            toast.error('Failed to update profile');
        }
    };

    const updatePassword = async (e) => {
        e.preventDefault();
        try {
            await axios.put('/api/profile/password', passwordForm);
            setPasswordForm({ current_password: '', password: '', password_confirmation: '' });
            toast.success('Password updated successfully');
        } catch (error) {
            toast.error('Failed to update password');
        }
    };

    return (
        <div>
            <h4 className="mb-4 fw-bold">Profile Settings</h4>
            <div className="row">
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body text-center">
                            <div className="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style={{ width: '100px', height: '100px', fontSize: '2rem' }}>
                                {user?.name?.charAt(0).toUpperCase()}
                            </div>
                            <h5 className="mb-1">{user?.name}</h5>
                            <p className="text-muted mb-2">{user?.email}</p>
                            {user?.roles?.map(role => (
                                <span key={role.id} className="badge bg-primary">{role.name}</span>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-transparent border-0">
                            <ul className="nav nav-tabs card-header-tabs">
                                <li className="nav-item">
                                    <button className={`nav-link ${activeTab === 'profile' ? 'active' : ''}`} onClick={() => setActiveTab('profile')}>Profile Information</button>
                                </li>
                                <li className="nav-item">
                                    <button className={`nav-link ${activeTab === 'password' ? 'active' : ''}`} onClick={() => setActiveTab('password')}>Change Password</button>
                                </li>
                            </ul>
                        </div>
                        <div className="card-body">
                            {activeTab === 'profile' && (
                                <form onSubmit={updateProfile}>
                                    <div className="mb-3">
                                        <label className="form-label">Full Name</label>
                                        <input type="text" className="form-control" value={profileForm.name} onChange={(e) => setProfileForm({...profileForm, name: e.target.value})} />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Email Address</label>
                                        <input type="email" className="form-control" value={profileForm.email} onChange={(e) => setProfileForm({...profileForm, email: e.target.value})} />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Phone Number</label>
                                        <input type="text" className="form-control" value={profileForm.phone} onChange={(e) => setProfileForm({...profileForm, phone: e.target.value})} />
                                    </div>
                                    <button type="submit" className="btn btn-primary">Update Profile</button>
                                </form>
                            )}
                            {activeTab === 'password' && (
                                <form onSubmit={updatePassword}>
                                    <div className="mb-3">
                                        <label className="form-label">Current Password</label>
                                        <input type="password" className="form-control" value={passwordForm.current_password} onChange={(e) => setPasswordForm({...passwordForm, current_password: e.target.value})} />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">New Password</label>
                                        <input type="password" className="form-control" value={passwordForm.password} onChange={(e) => setPasswordForm({...passwordForm, password: e.target.value})} />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Confirm New Password</label>
                                        <input type="password" className="form-control" value={passwordForm.password_confirmation} onChange={(e) => setPasswordForm({...passwordForm, password_confirmation: e.target.value})} />
                                    </div>
                                    <button type="submit" className="btn btn-primary">Change Password</button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Profile;
```

---

## Step 21: Migrations, Seeders & Running the App

### 21.1 Create Role & Permission Seeder

```bash
php artisan make:seeder RolePermissionSeeder
```

`database/seeders/RolePermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage users', 'create users', 'edit users', 'delete users',
            'manage roles', 'create roles', 'edit roles', 'delete roles',
            'manage permissions', 'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['manage users', 'manage roles', 'view dashboard']);
        $userRole->givePermissionTo(['view dashboard']);

        $sa = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@reactadmin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $sa->assignRole('super-admin');

        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@reactadmin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $adminUser->assignRole('admin');

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@reactadmin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $user->assignRole('user');
    }
}
```

Update `database/seeders/DatabaseSeeder.php`

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
        ]);
    }
}
```

### 21.2 Run Migrations & Seed

```bash
# Fresh migrate with seeder (use carefully)
php artisan migrate --seed

# Or run separately
php artisan migrate
php artisan db:seed
```

### 21.3 Start Development Servers

Open two terminals in the project root:

**Terminal 1 - Laravel Backend:**

```bash
php artisan serve
```

**Terminal 2 - Vite Frontend:**

```bash
npm run dev
```

### 21.4 Access the Application

| Environment | URL |
| :-- | :-- |
| Frontend (Vite) | `http://localhost:5173` |
| Backend (Laravel API) | `http://localhost:8000` |
| App via Laravel (if built) | `http://localhost:8000` |

**Default Credentials:**

| Role | Email | Password |
| :-- | :-- | :-- |
| Super Admin | superadmin@reactadmin.com | password |
| Admin | admin@reactadmin.com | password |
| User | user@reactadmin.com | password |

---

## Project File Structure Overview

```text
laravel-react-admin/
├── app/
│   ├── Actions/Fortify/        # Fortify actions
│   ├── Http/Controllers/Api/   # API Controllers
│   ├── Models/User.php         # User model with HasRoles
│   └── Providers/FortifyServiceProvider.php
├── bootstrap/app.php           # Middleware, aliases & routing
├── config/
│   ├── cors.php               # CORS config
│   ├── fortify.php            # Fortify features
│   ├── permission.php         # Spatie config
│   └── sanctum.php            # Sanctum config
├── database/
│   ├── migrations/            # DB migrations
│   └── seeders/               # RolePermissionSeeder
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── assets/css/app.css
│   │   ├── bootstrap/axios.js
│   │   ├── components/layout/  # Header, Sidebar
│   │   ├── context/           # AuthContext, ThemeContext
│   │   ├── layouts/           # AdminLayout, AuthLayout
│   │   ├── pages/
│   │   │   ├── admin/         # Dashboard, Users, Roles, Profile
│   │   │   └── auth/          # Login, Register, etc.
│   │   ├── routes/            # AppRouter, ProtectedRoute
│   │   └── main.jsx
│   └── views/app.blade.php    # SPA entry
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # SPA fallback
└── vite.config.js            # Vite + React config
```

---

## Production Build & Deployment

### 1. Build Frontend Assets

```bash
npm run build
```

This will compile and optimize all React/JS/CSS into `public/build/`.

### 2. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

### 3. Update Production `.env`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=None

DB_CONNECTION=mysql
# ... your production DB credentials

MAIL_MAILER=smtp
# ... your SMTP credentials
```

### 4. Deployment Checklist

- [ ] Point web server document root to `public/`
- [ ] Set correct permissions for `storage/` and `bootstrap/cache/` (chmod -R 775)
- [ ] Install dependencies: `composer install --no-dev --optimize-autoloader`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run seeders if needed: `php artisan db:seed --force`
- [ ] Configure SSL (HTTPS) - Required for secure SameSite cookies
- [ ] Update CORS for your production domain
- [ ] Set up queue worker if using queued emails/notifications
- [ ] Configure cron for scheduled tasks if needed

### 5. Nginx Example (Optional)

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;
    root /var/www/laravel-react-admin/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Security Best Practices

| Area | Recommendation |
| :-- | :-- |
| Authentication | Always use Sanctum SPA with `withCredentials: true`. Never store tokens in localStorage. |
| CSRF | Ensure `sanctum/csrf-cookie` is called before POST/PUT/PATCH/DELETE auth actions. |
| Authorization | Enforce checks on both API (policies/middleware) and frontend (route guards/UI hiding). |
| Passwords | Use Laravel's default bcrypt/hashed casting. Enforce strong passwords. |
| Email Verification | Enable and enforce for sensitive actions in production. |
| CORS | Restrict `allowed_origins` to your actual frontend domain only. |
| Sessions | Use `SameSite=Lax`/`None` appropriately with `Secure=true` over HTTPS. |
| Validation | Validate all inputs on the backend. Frontend validation is for UX only. |
| File Uploads | Validate mime types, extensions, and store outside of public if sensitive. |
| Rate Limiting | Utilize Fortify's rate limiters and consider adding throttle to custom API routes. |
| Updates | Keep Laravel, React, and all packages up to date. |

---

## Troubleshooting Guide

| Issue | Cause | Solution |
| :-- | :-- | :-- |
| `419 CSRF token mismatch` | CSRF cookie not set | Call `axios.get('/sanctum/csrf-cookie')` before login/register/forgot-password. |
| `401 Unauthenticated` on API calls | Session not sent or domain mismatch | Set `axios.defaults.withCredentials = true`, verify `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN`. |
| CORS errors in browser | Incorrect origins/headers | Update `config/cors.php` with correct frontend URL and `supports_credentials: true`. |
| Routes show 404 on refresh | Missing SPA fallback | Ensure `routes/web.php` has `/{any}` route returning `app` view. |
| Vite not hot-reloading | Port conflict or CORS | Check `vite.config.js` server config and ensure port 5173 is free. |
| Permissions not working | Cache not cleared | Run `php artisan permission:cache-reset` and ensure roles/permissions are assigned. |
| User can access protected UI without role | Only checking on UI | Always enforce role/permission on API routes. Never rely solely on frontend guards. |
| Dark/Light mode not persisting | LocalStorage issue | Ensure `ThemeContext` saves to `localStorage` and applies `data-bs-theme`. |
| Mixed content on HTTPS | HTTP assets | Use `@vite()` and ensure `APP_URL` is HTTPS in production. |

---

## Useful Artisan & NPM Commands

### Laravel Artisan

```bash
# Clear all caches
php artisan optimize:clear

# Permission cache
php artisan permission:cache-reset
php artisan permission:show

# Route inspection
php artisan route:list --path=api

# Tinker
php artisan tinker
```

### NPM

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Production build
npm run build

# Preview build locally (if needed)
npm run preview
```

### Spatie Permission Tinker Examples

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Create permission
Permission::create(['name' => 'publish posts']);

// Create role and assign
$role = Role::create(['name' => 'editor']);
$role->givePermissionTo('publish posts');

// Assign role to user
User::find(1)->assignRole('editor');

// Check permissions
auth()->user()->can('publish posts');
```

---

**Built for scalability, security, and modern UI/UX. Ready to extend with modules like Products, Orders, Reports, Notifications, and more.**

*Last Updated: October 2026 | Laravel 13 | React 19 | Bootstrap 5.3 | Sanctum + Fortify + Spatie Permission*
