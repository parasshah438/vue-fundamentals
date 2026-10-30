# Laravel 13 + Vue 3 Admin SPA - Complete Setup Guide (A to Z)

> A fully trending, production-ready admin dashboard built with Laravel 13, Vue 3 (Composition API), Sanctum, Fortify, Spatie Permission, Vue Router, Axios, and Bootstrap 5.3

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Project Architecture](#project-architecture)
- [Step 1: Create Laravel 13 Project](#step-1-create-laravel-13-project)
- [Step 2: Install Laravel Sanctum (Session-based SPA Auth)](#step-2-install-laravel-sanctum-session-based-spa-auth)
- [Step 3: Install Laravel Fortify](#step-3-install-laravel-fortify)
- [Step 4: Install Spatie Laravel Permission](#step-4-install-spatie-laravel-permission)
- [Step 5: Setup Database & Environment](#step-5-setup-database--environment)
- [Step 6: Install & Setup Vue 3 with Vite](#step-6-install--setup-vue-3-with-vite)
- [Step 7: Install Frontend Dependencies (Vue Router, Axios, Bootstrap)](#step-7-install-frontend-dependencies-vue-router-axios-bootstrap)
- [Step 8: Configure Vite for Vue & API Proxy](#step-8-configure-vite-for-vue--api-proxy)
- [Step 9: Setup Sanctum for SPA Authentication](#step-9-setup-sanctum-for-spa-authentication)
- [Step 10: Configure Fortify Features](#step-10-configure-fortify-features)
- [Step 11: Setup Spatie Permission Models & Migrations](#step-11-setup-spatie-permission-models--migrations)
- [Step 12: Create User Model with Roles/Permissions](#step-12-create-user-model-with-rolespermissions)
- [Step 13: Setup API Routes & Controllers](#step-13-setup-api-routes--controllers)
- [Step 14: Create Vue 3 SPA Structure](#step-14-create-vue-3-spa-structure)
- [Step 15: Setup Axios with Sanctum CSRF](#step-15-setup-axios-with-sanctum-csrf)
- [Step 16: Build Auth Pages (Login/Register/Forgot/Verify)](#step-16-build-auth-pages-loginregisterforgotverify)
- [Step 17: Build Admin Dashboard Layout](#step-17-build-admin-dashboard-layout)
- [Step 18: Setup Vue Router with Guards](#step-18-setup-vue-router-with-guards)
- [Step 19: Role & Permission Management UI](#step-19-role--permission-management-ui)
- [Step 20: Run Migrations, Seeders & Start Development](#step-20-run-migrations-seeders--start-development)
- [File Structure Overview](#file-structure-overview)
- [Production Build & Deployment](#production-build--deployment)
- [Security Best Practices](#security-best-practices)
- [Troubleshooting](#troubleshooting)
- [Useful Commands](#useful-commands)

---

## Project Overview

This guide walks you through building a modern admin SPA with:

- **Session-based SPA Auth** using Laravel Sanctum (cookie-based, no tokens)
- **Full auth flow** via Laravel Fortify (login, register, forgot password, reset password, email verification)
- **RBAC** using Spatie Laravel Permission (roles, permissions, middleware)
- **SPA routing** with Vue Router 4
- **API communication** with Axios (with CSRF protection)
- **Modern UI** with Bootstrap 5.3, icons, and responsive admin layout
- **Clean architecture** separating API/backend and Vue SPA frontend

---

## Tech Stack

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| Backend | Laravel | 13.x | API, Auth, Business Logic |
| Authentication | Laravel Sanctum | ^4.x | SPA cookie authentication |
| Auth UI/Actions | Laravel Fortify | ^1.x | Auth endpoints & features |
| Authorization | Spatie Permission | ^6.x | Roles & Permissions |
| Frontend | Vue.js | ^3.5.x | SPA Framework (Composition API) |
| Routing | Vue Router | ^4.x | Client-side routing |
| HTTP Client | Axios | ^1.7.x | API requests |
| Build Tool | Vite | ^6.x | Asset bundling & HMR |
| UI Framework | Bootstrap | ^5.3.x | Responsive UI components |
| Icons | Bootstrap Icons | ^1.11.x | Icon set |

---

## Prerequisites

Before starting, ensure you have the following installed:

- [PHP 8.2+](https://www.php.net/downloads)
- [Composer 2.x](https://getcomposer.org/download/)
- [Node.js 18+ & NPM](https://nodejs.org/)
- [MySQL/MariaDB](https://www.mysql.com/) or [SQLite](https://www.sqlite.org/)
- [Git](https://git-scm.com/)
- [Laravel CLI](https://laravel.com/docs/13/installation) (optional but recommended)
- Local server like [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or [Valet](https://laravel.com/docs/13/valet)

---

## Project Architecture

```
┌─────────────────┐     Cookies/CSRF     ┌──────────────────┐
│   Vue 3 SPA     │◄───────────────────►│  Laravel 13 API   │
│  (Bootstrap UI) │   JSON API Requests  │  Sanctum + Fortify │
└─────────────────┘                      │  Spatie Permission │
         │                              └──────────────────┘
         │                                        │
      Vue Router                              MySQL/DB
         │                                        │
      Axios Calls                            Migrations/Models
```

**Flow:** Vue SPA runs on same domain (or configured) → Uses Sanctum's `sanctum/csrf-cookie` → Auth via Fortify endpoints → All protected API routes check auth + permissions → Returns JSON to Vue.

---

## Step 1: Create Laravel 13 Project

Open your terminal in `C:\wamp64\www\` (or your project directory).

```bash
# Create new Laravel project
composer create-project laravel/laravel laravel-vue-admin-spa "13.*"

# Navigate into project
cd laravel-vue-admin-spa
```

---

## Step 2: Install Laravel Sanctum (Session-based SPA Auth)

Laravel 13 may have Sanctum, but let's ensure it's installed and published.

```bash
# Install Sanctum
composer require laravel/sanctum

# Publish Sanctum configuration and migrations
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

---

## Step 3: Install Laravel Fortify

Fortify provides the backend authentication logic without UI scaffolding.

```bash
# Install Fortify
composer require laravel/fortify

# Publish Fortify actions, views (optional) and config
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"

# Run Fortify migrations
php artisan migrate
```

---

## Step 4: Install Spatie Laravel Permission

```bash
# Install Spatie Permission package
composer require spatie/laravel-permission

# Publish migration and config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate
```

---

## Step 5: Setup Database & Environment

Edit your `.env` file:

```env
APP_NAME="VueAdminSPA"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
SESSION_DOMAIN=localhost
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_vue_admin_spa
DB_USERNAME=root
DB_PASSWORD=

# For email verification (use mailtrap/mailhog in dev)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="admin@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Generate app key:

```bash
php artisan key:generate
```

Create the database `laravel_vue_admin_spa` in phpMyAdmin or via CLI.

---

## Step 6: Install & Setup Vue 3 with Vite

Laravel 13 comes with Vite. Install Vue plugin and dependencies.

```bash
# Install Vue 3 and Vite plugin
npm install vue@latest @vitejs/plugin-vue
```

---

## Step 7: Install Frontend Dependencies (Vue Router, Axios, Bootstrap)

```bash
# Vue Router
npm install vue-router@4

# Axios
npm install axios

# Bootstrap 5.3 and Popper
npm install bootstrap @popperjs/core

# Bootstrap Icons
npm install bootstrap-icons

# Optional: Pinia for state management (recommended for larger apps)
npm install pinia
```

---

## Step 8: Configure Vite for Vue & API Proxy

Update `vite.config.js` in the root:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
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

Update `resources/js/app.js`:

```js
import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Bootstrap CSS & JS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import '../css/app.css';

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');
```

---

## Step 9: Setup Sanctum for SPA Authentication

### 9.1 Configure `config/sanctum.php`

Ensure `stateful` domains include your frontend:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

### 9.2 Update `app/Http/Kernel.php` (Laravel 11/13 structure)

In Laravel 11+, middleware is in `bootstrap/app.php`. For Laravel 13, check `bootstrap/app.php`:

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

### 9.3 Update `config/cors.php`

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'forgot-password', 'reset-password', 'email/verification-notification', 'email/verify/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

## Step 10: Configure Fortify Features

### 10.1 Register Fortify Service Provider

In `config/app.php` or better use `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
```

Create provider if not exists: `php artisan fortify:install` would do it, but we published. Also ensure `App\Providers\FortifyServiceProvider` exists.

### 10.2 Update `app/Providers/FortifyServiceProvider.php`

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
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Custom responses for SPA
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
            $request->session()->put('login.throttle', $throttle);
            return Limit::perMinute(5)->by($throttle.'|$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->id.'|$request->ip());
        });
    }
}
```

### 10.3 Enable Fortify Features in `config/fortify.php`

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    // Features::twoFactorAuthentication([
    //     'confirm' => true,
    //     'confirmPassword' => true,
    // ]),
],
```

Also ensure `home` route is set (Fortify will redirect after auth, but for SPA we handle):

```php
'home' => '/dashboard',
```

Note: For SPA, we're not using server-side views; endpoints are what matter.

---

## Step 11: Setup Spatie Permission Models & Migrations

The migration is published. Verify `database/migrations/xxxx_create_permission_tables.php` exists.

Also publish config:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-config"
```

Config is in `config/permission.php` - defaults are fine.

---

## Step 12: Create User Model with Roles/Permissions

Update `app/Models/User.php`:

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

---

## Step 13: Setup API Routes & Controllers

### 13.1 Update `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions')
        ]);
    });
    
    // Admin routes with permissions
    Route::middleware(['role:admin|super-admin'])->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
        
        Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::post('users/{user}/revoke-role', [UserController::class, 'revokeRole']);
        Route::post('roles/{role}/give-permission', [RoleController::class, 'givePermission']);
        Route::post('roles/{role}/revoke-permission', [RoleController::class, 'revokePermission']);
    });
});
```

### 13.2 Create API Controllers

```bash
php artisan make:controller Api/UserController --api
php artisan make:controller Api/RoleController --api
php artisan make:controller Api/PermissionController --api
```

Example `app/Http/Controllers/Api/RoleController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);
        
        if (isset($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function givePermission(Request $request, Role $role)
    {
        $request->validate(['permission' => 'required']);
        $role->givePermissionTo($request->permission);
        return response()->json($role->load('permissions'));
    }

    public function revokePermission(Request $request, Role $role)
    {
        $request->validate(['permission' => 'required']);
        $role->revokePermissionTo($request->permission);
        return response()->json($role->load('permissions'));
    }
}
```

### 13.3 Update `routes/web.php` for SPA fallback

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
```

### 13.4 Create SPA View

Create `resources/views/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel Vue Admin SPA') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

---

## Step 14: Create Vue 3 SPA Structure

Create the following directory structure:

```bash
mkdir -p resources/js/{components,layouts,pages/{auth,admin},router,stores,utils}
```

### 14.1 Create `resources/js/App.vue`

```vue
<template>
  <router-view />
</template>

<script setup>
</script>

<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}
</style>
```

### 14.2 Create Router

`resources/js/router/index.js`:

```js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { guest: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/pages/auth/ForgotPassword.vue'),
    meta: { guest: true }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: () => import('@/pages/auth/ResetPassword.vue'),
    meta: { guest: true }
  },
  {
    path: '/email/verify',
    name: 'verify-email',
    component: () => import('@/pages/auth/VerifyEmail.vue'),
    meta: { auth: true }
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { auth: true },
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/pages/admin/Dashboard.vue')
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/pages/admin/Users.vue'),
        meta: { permission: 'manage users' }
      },
      {
        path: 'roles',
        name: 'roles',
        component: () => import('@/pages/admin/Roles.vue'),
        meta: { permission: 'manage roles' }
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('@/pages/admin/Permissions.vue'),
        meta: { permission: 'manage permissions' }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFound.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  if (authStore.user === null) {
    await authStore.fetchUser();
  }

  if (to.meta.auth && !authStore.isAuthenticated) {
    return next({ name: 'login' });
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }

  if (to.meta.permission && authStore.isAuthenticated) {
    if (!authStore.hasPermission(to.meta.permission) && !authStore.hasRole(['admin', 'super-admin'])) {
      return next({ name: 'dashboard' });
    }
  }

  next();
});

export default router;
```

### 14.3 Create Auth Store with Pinia

`resources/js/stores/auth.js`:

```js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.user,
    hasRole: (state) => (roles) => {
      if (!state.user || !state.user.roles) return false;
      const roleNames = Array.isArray(roles) ? roles : [roles];
      return state.user.roles.some(role => roleNames.includes(role.name));
    },
    hasPermission: (state) => (permissions) => {
      if (!state.user) return false;
      const perms = Array.isArray(permissions) ? permissions : [permissions];
      
      // Check direct permissions
      if (state.user.permissions && state.user.permissions.some(p => perms.includes(p.name))) {
        return true;
      }
      
      // Check role permissions
      if (state.user.roles) {
        for (let role of state.user.roles) {
          if (role.permissions && role.permissions.some(p => perms.includes(p.name))) {
            return true;
          }
        }
      }
      
      return false;
    }
  },
  
  actions: {
    async fetchCsrfCookie() {
      await axios.get('/sanctum/csrf-cookie');
    },
    
    async fetchUser() {
      this.loading = true;
      try {
        const response = await axios.get('/api/user');
        this.user = response.data.user;
      } catch (error) {
        this.user = null;
      } finally {
        this.loading = false;
      }
    },
    
    async login(credentials) {
      await this.fetchCsrfCookie();
      const response = await axios.post('/login', credentials);
      await this.fetchUser();
      return response;
    },
    
    async register(data) {
      await this.fetchCsrfCookie();
      const response = await axios.post('/register', data);
      await this.fetchUser();
      return response;
    },
    
    async logout() {
      await axios.post('/logout');
      this.user = null;
    },
    
    async forgotPassword(email) {
      await this.fetchCsrfCookie();
      return await axios.post('/forgot-password', { email });
    },
    
    async resetPassword(data) {
      await this.fetchCsrfCookie();
      return await axios.post('/reset-password', data);
    },
    
    async resendVerification() {
      return await axios.post('/email/verification-notification');
    }
  }
});
```

---

## Step 15: Setup Axios with Sanctum CSRF

Create `resources/js/bootstrap.js`:

```js
import axios from 'axios';

window.axios = axios;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.baseURL = '/';

// Add response interceptor for auth errors
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default axios;
```

---

## Step 16: Build Auth Pages (Login/Register/Forgot/Verify)

### 16.1 `resources/js/pages/auth/Login.vue`

```vue
<template>
  <div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg" style="width: 400px;">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h3 class="fw-bold">Admin Login</h3>
          <p class="text-muted">Sign in to your dashboard</p>
        </div>
        
        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input 
              type="email" 
              class="form-control" 
              id="email" 
              v-model="form.email"
              required
              autofocus
            >
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              v-model="form.password"
              required
            >
          </div>
          
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" v-model="form.remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          
          <div v-if="errors" class="alert alert-danger">
            <ul class="mb-0">
              <li v-for="(error, key) in errors" :key="key">{{ error[0] }}</li>
            </ul>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Sign In
          </button>
          
          <div class="text-center">
            <router-link to="/forgot-password" class="text-decoration-none">Forgot Password?</router-link>
          </div>
          <div class="text-center mt-2">
            <span class="text-muted">Don't have an account?</span>
            <router-link to="/register" class="text-decoration-none">Register</router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
  remember: false
});

const errors = ref(null);
const loading = ref(false);

const handleLogin = async () => {
  loading.value = true;
  errors.value = null;
  
  try {
    await authStore.login(form.value);
    router.push({ name: 'dashboard' });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      errors.value = { general: ['Invalid credentials'] };
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
```

### 16.2 `resources/js/pages/auth/Register.vue`

```vue
<template>
  <div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg" style="width: 450px;">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h3 class="fw-bold">Create Account</h3>
          <p class="text-muted">Register for admin access</p>
        </div>
        
        <form @submit.prevent="handleRegister">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" v-model="form.name" required>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" v-model="form.email" required>
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" v-model="form.password" required>
          </div>
          
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" v-model="form.password_confirmation" required>
          </div>
          
          <div v-if="errors" class="alert alert-danger">
            <ul class="mb-0">
              <li v-for="(error, key) in errors" :key="key">{{ error[0] }}</li>
            </ul>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Register
          </button>
          
          <div class="text-center">
            <span class="text-muted">Already have an account?</span>
            <router-link to="/login" class="text-decoration-none">Login</router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const errors = ref(null);
const loading = ref(false);

const handleRegister = async () => {
  loading.value = true;
  errors.value = null;
  
  try {
    await authStore.register(form.value);
    router.push({ name: 'verify-email' });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      errors.value = { general: ['Registration failed'] };
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
```

### 16.3 `resources/js/pages/auth/ForgotPassword.vue`

```vue
<template>
  <div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg" style="width: 400px;">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h3 class="fw-bold">Reset Password</h3>
          <p class="text-muted">Enter your email to receive reset link</p>
        </div>
        
        <div v-if="status" class="alert alert-success">{{ status }}</div>
        
        <form @submit.prevent="handleForgotPassword" v-if="!status">
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" v-model="email" required>
          </div>
          
          <div v-if="errors" class="alert alert-danger">
            <ul class="mb-0">
              <li v-for="(error, key) in errors" :key="key">{{ error[0] }}</li>
            </ul>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Send Reset Link
          </button>
        </form>
        
        <div class="text-center">
          <router-link to="/login" class="text-decoration-none">Back to Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const email = ref('');
const errors = ref(null);
const status = ref(null);
const loading = ref(false);

const handleForgotPassword = async () => {
  loading.value = true;
  errors.value = null;
  status.value = null;
  
  try {
    const response = await authStore.forgotPassword(email.value);
    status.value = response.data.status || 'Password reset link sent to your email!';
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      errors.value = { general: ['Something went wrong'] };
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
```

### 16.4 `resources/js/pages/auth/VerifyEmail.vue`

```vue
<template>
  <div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg" style="width: 500px;">
      <div class="card-body p-5 text-center">
        <i class="bi bi-envelope-check text-primary" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mt-3">Verify Your Email</h3>
        <p class="text-muted">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>
        
        <div v-if="status" class="alert alert-success">{{ status }}</div>
        <div v-if="errors" class="alert alert-danger">{{ errors }}</div>
        
        <button @click="resend" class="btn btn-primary me-2" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          Resend Verification Email
        </button>
        <button @click="logout" class="btn btn-outline-secondary">Logout</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const status = ref(null);
const errors = ref(null);

const resend = async () => {
  loading.value = true;
  status.value = null;
  errors.value = null;
  
  try {
    const response = await authStore.resendVerification();
    status.value = response.data.status || 'Verification link sent!';
  } catch (error) {
    errors.value = 'Failed to resend verification email';
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped>
.auth-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
```

---

## Step 17: Build Admin Dashboard Layout

### 17.1 `resources/js/layouts/AdminLayout.vue`

```vue
<template>
  <div class="admin-wrapper">
    <!-- Sidebar -->
    <nav class="sidebar bg-dark text-white" :class="{ 'collapsed': sidebarCollapsed }">
      <div class="sidebar-header p-3 border-bottom border-secondary">
        <h5 class="mb-0">
          <i class="bi bi-speedometer2 me-2"></i>
          <span v-if="!sidebarCollapsed">VueAdmin</span>
        </h5>
      </div>
      
      <ul class="nav flex-column mt-3">
        <li class="nav-item">
          <router-link to="/dashboard" class="nav-link text-white">
            <i class="bi bi-house-door me-2"></i>
            <span v-if="!sidebarCollapsed">Dashboard</span>
          </router-link>
        </li>
        
        <li class="nav-item" v-if="authStore.hasRole(['admin', 'super-admin']) || authStore.hasPermission('manage users')">
          <router-link to="/users" class="nav-link text-white">
            <i class="bi bi-people me-2"></i>
            <span v-if="!sidebarCollapsed">Users</span>
          </router-link>
        </li>
        
        <li class="nav-item" v-if="authStore.hasRole(['admin', 'super-admin']) || authStore.hasPermission('manage roles')">
          <router-link to="/roles" class="nav-link text-white">
            <i class="bi bi-shield-lock me-2"></i>
            <span v-if="!sidebarCollapsed">Roles</span>
          </router-link>
        </li>
        
        <li class="nav-item" v-if="authStore.hasRole(['admin', 'super-admin']) || authStore.hasPermission('manage permissions')">
          <router-link to="/permissions" class="nav-link text-white">
            <i class="bi bi-key me-2"></i>
            <span v-if="!sidebarCollapsed">Permissions</span>
          </router-link>
        </li>
      </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <header class="header bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center">
        <button class="btn btn-link text-dark" @click="toggleSidebar">
          <i class="bi bi-list fs-4"></i>
        </button>
        
        <div class="dropdown">
          <button class="btn btn-link text-dark dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-2"></i>
            {{ authStore.user?.name }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </header>
      
      <!-- Page Content -->
      <main class="content p-4">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const logout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped>
.admin-wrapper {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 250px;
  transition: width 0.3s;
  flex-shrink: 0;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar .nav-link {
  padding: 0.75rem 1.5rem;
  transition: background 0.2s;
}

.sidebar .nav-link:hover {
  background: rgba(255,255,255,0.1);
}

.sidebar .nav-link.router-link-active {
  background: rgba(255,255,255,0.2);
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  background-color: #f8f9fa;
}

.header {
  height: 60px;
}

.content {
  flex: 1;
}
</style>
```

### 17.2 `resources/js/pages/admin/Dashboard.vue`

```vue
<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Dashboard</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div>
    
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Total Users</p>
                <h3 class="mb-0">0</h3>
              </div>
              <div class="bg-primary bg-opacity-10 p-3 rounded">
                <i class="bi bi-people text-primary fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Roles</p>
                <h3 class="mb-0">0</h3>
              </div>
              <div class="bg-success bg-opacity-10 p-3 rounded">
                <i class="bi bi-shield-lock text-success fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Permissions</p>
                <h3 class="mb-0">0</h3>
              </div>
              <div class="bg-warning bg-opacity-10 p-3 rounded">
                <i class="bi bi-key text-warning fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Active</p>
                <h3 class="mb-0">1</h3>
              </div>
              <div class="bg-info bg-opacity-10 p-3 rounded">
                <i class="bi bi-check-circle text-info fs-4"></i>
              </div>
            </div>
          </div>
        </tag>
      </div>
    </div>
    
    <div class="row mt-4">
      <div class"col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">Welcome, {{ authStore.user?.name }}!</p>
          </div>
          <div class="card-body">
            <p class="mb-0">You are logged in as <strong>{{ authStore.user?.email }}</strong></p>
            <p class="mb-0 mt-2">Roles: <span class="badge bg-primary me-1" v-for="role in authStore.user?.roles" :key="role.id">{{ role.name }}</span></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
const authStore = useAuthStore();
</script>
```

### 17.3 `resources/js/pages/NotFound.vue`

```vue
<template>
  <div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">
      <h1 class="display-1 fw-bold">404</h1>
      <p class="fs-3"> <span class="text-danger">Oops!</span Page not found</p>
      <p class="lead">The page you are looking for doesn't exist.</p>
      <router-link to="/dashboard" class="btn btn-primary">Go Home</router-link>
    </div>
  </div>
</template>
```

---

## Step 18: Setup Vue Router with Guards

As shown in Step 14.2, router guards check authentication and permissions. Ensure imports use `@` alias or relative paths. In Vite config, you can add alias:

Update `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
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

---

## Step 19: Role & Permission Management UI

### 19.1 `resources/js/pages/admin/Roles.vue` (Basic Structure)

```vue
<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Role Management</h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" @click="openCreate">
        <i class="bi bi-plus-circle me-2"></i>Add Role
      </button>
    </div>
    
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Permissions</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(role, index) in roles" :key="role.id">
                <td>{{ index + 1 }}</td>
                <td>{{ role.name }}</td>
                <td>
                  <span class="badge bg-info me-1" v-for="perm in role.permissions" :key="perm.id">
                    {{ perm.name }}
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-1" @click="openEdit(role)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteRole(role.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editMode ? 'Edit Role' : 'Create Role' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Role Name</label>
              <input type="text" class="form-control" v-model="form.name">
            </div>
            <div class="mb-3">
              <label class="form-label">Permissions</label>
              <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                <div class="form-check" v-for="perm in permissions" :key="perm.id">
                  <input class="form-check-input" type="checkbox" :value="perm.name" v-model="form.permissions" :id="'perm-'+perm.id">
                  <label class="form-check-label" :for="'perm-'+perm.id">{{ perm.name }}</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="saveRole" data-bs-dismiss="modal">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const roles = ref([]);
const permissions = ref([]);
const editMode = ref(false);
const currentId = ref(null);
const form = ref({
  name: '',
  permissions: []
});

const fetchRoles = async () => {
  const response = await axios.get('/api/admin/roles');
  roles.value = response.data;
};

const fetchPermissions = async () => {
  const response = await axios.get('/api/admin/permissions');
  permissions.value = response.data;
};

const openCreate = () => {
  editMode.value = false;
  currentId.value = null;
  form.value = { name: '', permissions: [] };
};

const openEdit = (role) => {
  editMode.value = true;
  currentId.value = role.id;
  form.value = {
    name: role.name,
    permissions: role.permissions.map(p => p.name)
  };
  const modal = new Modal(document.getElementById('roleModal'));
  modal.show();
};

const saveRole = async () => {
  if (editMode.value) {
    await axios.put(`/api/admin/roles/${currentId.value}`, form.value);
  } else {
    await axios.post('/api/admin/roles', form.value);
  }
  await fetchRoles();
};

const deleteRole = async (id) => {
  if (confirm('Are you sure you want to delete this role?')) {
    await axios.delete(`/api/admin/roles/${id}`);
    await fetchRoles();
  }
};

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
```

---

## Step 20: Run Migrations, Seeders & Start Development

### 20.1 Create a Database Seeder for Roles/Permissions

Create seeder:

```bash
php artisan make:seeder RolePermissionSeeder
```

`database/seeders/RolePermissionSeeder.php`:

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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage permissions',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        // Assign all permissions to super-admin
        $superAdmin->givePermissionTo(Permission::all());

        // Assign specific permissions to admin
        $admin->givePermissionTo(['manage users', 'manage roles', 'view dashboard']);

        // Assign view permission to user
        $user->givePermissionTo(['view dashboard']);

        // Create super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdminUser->assignRole('super-admin');

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');
    }
}
```

Update `database/seeders/DatabaseSeeder.php`:

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

### 20.2 Run Migrations & Seed

```bash
# Run all migrations
php artisan migrate

# Run seeder
php artisan db:seed
```

### 20.3 Compile Assets & Start Servers

Open two terminal windows in the project root:

**Terminal 1 - Laravel Backend:**

```bash
php artisan serve
```

**Terminal 2 - Vite Frontend:**

```bash
npm run dev
```

### 20.4 Access the Application

- **Frontend (Vue SPA):** http://localhost:5173 (or served via Laravel)
- **Backend API:** http://localhost:8000
- **Default Super Admin:** superadmin@example.com / password
- **Default Admin:** admin@example.com / password

---

## File Structure Overview

```
laravel-vue-admin-spa/
├── app/
│   ├── Actions/Fortify/        # Fortify actions
│   ├── Http/Controllers/Api/   # API controllers
│   ├── Models/User.php         # User with HasRoles
│   └── Providers/             # Service providers
├── bootstrap/
│   └── app.php                # Middleware & routing config
├── config/
│   ├── cors.php               # CORS for SPA
│   ├── fortify.php            # Fortify features
│   ├── permission.php         # Spatie config
│   └── sanctum.php            # Sanctum config
├── database/
│   ├── migrations/            # All migrations
│   └── seeders/               # RolePermissionSeeder
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── App.vue
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── layouts/AdminLayout.vue
│   │   ├── pages/
│   │   │   ├── auth/          # Login, Register, etc.
│   │   │   └── admin/         # Dashboard, Users, Roles
│   │   ├── router/index.js    # Vue Router with guards
│   │   └── stores/auth.js     # Pinia auth store
│   └── views/app.blade.php    # SPA entry point
├── routes/
│   ├── api.php                # API routes
│   └── web.php                # SPA fallback route
└── vite.config.js             # Vite + Vue config
```

---

## Production Build & Deployment

### Build Assets

```bash
npm run build
```

This generates optimized files in `public/build/`.

### Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Environment for Production

Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=None
```

### Deployment Tips

- Set proper file permissions (`storage/`, `bootstrap/cache/`)
- Use HTTPS (required for secure cookies)
- Configure web server (Nginx/Apache) to serve `public/` directory
- Ensure CORS is configured for your production domain
- Run migrations on production: `php artisan migrate --force`
- Consider using queue workers for emails (password resets, verification)

---

## Security Best Practices

- [x] Use Sanctum SPA authentication (HTTP-only cookies)
- [x] Enable CSRF protection (automatically handled)
- [x] Implement role/permission middleware
- [x] Use password hashing (Laravel default)
- [x] Enable email verification
- [x] Rate limiting on auth routes (Fortify handles)
- [x] Validate all inputs on backend
- [x] Use HTTPS in production
- [x] Keep dependencies updated
- [x] Don't expose sensitive data in API responses

---

## Troubleshooting

| Issue | Cause | Solution |
|---|---|---|
| CORS errors | Wrong origins in config | Check `config/cors.php` and `SANCTUM_STATEFUL_DOMAINS` |
| 419 CSRF Token Mismatch | Missing CSRF cookie | Call `sanctum/csrf-cookie` before auth requests |
| 401 Unauthenticated | Not logged in or session expired | Ensure `withCredentials: true` in Axios, check session config |
| Routes not found in SPA | Missing fallback route | Ensure `routes/web.php` has catch-all route |
| Vite assets not loading | Not running dev server or not built | Run `npm run dev` or `npm run build` |
| Permission denied | Missing role/permission | Check user roles/permissions via seeder or DB |
| Email verification not working | MAIL config incorrect | Use `log` driver in dev or configure SMTP |

---

## Useful Commands

```bash
# Clear caches
php artisan optimize:clear

# Generate API documentation (optional)
php artisan route:list

# Create new permission
php artisan permission:create-permission "edit posts"

# Create new role
php artisan permission:create-role "editor"

# Tinker for testing
php artisan tinker

# Check user roles
User::find(1)->getRoleNames()

# Give permission to role
$role = Role::findByName('editor');
$role->givePermissionTo('edit posts');
```

---

**Created with ❤️ for building modern Laravel + Vue admin SPAs.**

*Last Updated: October 2026 | Laravel 13 | Vue 3 | Bootstrap 5.3*
