# Production Architecture Guide: Next.js Public Site + React (Vite) Admin + Laravel 13 API + MySQL

> A production-oriented setup for a blogging platform with three separate applications: a **Next.js** public website (server-rendered for SEO), a **React + Vite** admin dashboard (SPA), and a **Laravel 13** REST API with **MySQL**. It builds on `Laravel-React-Blogging-Website-Complete-Setup-Guide.md`: the Laravel API, roles, policies, migrations, seeders and the admin pages from that guide are reused. This guide covers what changes.

---

## Table of Contents

- [Why This Architecture](#why-this-architecture)
- [Architecture Overview](#architecture-overview)
- [Part 1: Prepare the Laravel API](#part-1-prepare-the-laravel-api)
- [Part 2: Next.js Public Website](#part-2-nextjs-public-website)
- [Part 3: React (Vite) Admin Dashboard](#part-3-react-vite-admin-dashboard)
- [Part 4: Local Development](#part-4-local-development)
- [Part 5: Production Deployment](#part-5-production-deployment)
- [Part 6: SEO Checklist](#part-6-seo-checklist)
- [Part 7: Security Checklist](#part-7-security-checklist)
- [Troubleshooting](#troubleshooting)
- [What to Reuse from the Main Guide](#what-to-reuse-from-the-main-guide)

---

## Why This Architecture

| Application | Technology | Suitable for production? | Why |
| :-- | :-- | :-- | :-- |
| Public website | Next.js | Yes | Server-rendered or statically generated pages. Search engines and social link previews see full HTML. Per-post `generateMetadata`, sitemap and RSS are simple. Incremental static regeneration (ISR) keeps pages fast |
| Admin dashboard | React + Vite SPA | Yes | SEO does not matter. An SPA suits CRUD, charts and a rich-text editor |
| Backend | Laravel 13 REST API | Yes | One place for authentication, permissions, validation and business logic, shared by both front ends |
| Database | MySQL | Yes | Proven, well supported |

**Trade-offs to accept**

- Three deployments: a Node server (Next.js), static files (admin) and PHP (Laravel). Each needs CI/CD and monitoring.
- Cross-origin authentication for the admin (CORS, cookie domain, Sanctum stateful domains).
- Published content must be pushed to Next.js (a revalidation webhook), otherwise pages show stale content until the cache expires.

If you do not want a Node server, Laravel with Blade or Inertia SSR for the public pages gives the same SEO with two deployments.

---

## Architecture Overview

```text
                     ┌──────────────────────────────┐
 Readers ──────────► │ Next.js  (example.com)       │
                     │ SSR / ISR, sitemap, RSS      │──── server fetch ───┐
                     └──────────────┬───────────────┘                     │
                                    │ browser: comments, newsletter,      ▼
                                    │ view counter (no login)    ┌─────────────────────┐
                                    └──────────────────────────► │ Laravel 13 API      │──► MySQL
                                                                 │ (api.example.com)   │
 Staff ──────────► ┌───────────────────────────────┐             │ Sanctum, Fortify,   │
                   │ React + Vite SPA              │ ──────────► │ Spatie, Policies    │
                   │ (admin.example.com)           │ cookies/CORS│                     │
                   └───────────────────────────────┘             └──────────┬──────────┘
                                                                            │ publish / edit
                                                                            ▼
                                                              POST /api/revalidate (Next.js)
```

**Domains (production)**

| App | URL | Notes |
| :-- | :-- | :-- |
| Public site | `https://example.com` | Next.js server (Node) behind Nginx |
| Admin | `https://admin.example.com` | Static build (`dist/`) |
| API | `https://api.example.com` | Laravel (PHP-FPM) |

**Ports (local)**

| App | URL |
| :-- | :-- |
| Laravel API | `http://localhost:8000` |
| Next.js | `http://localhost:3000` |
| Admin (Vite) | `http://localhost:5173` |

**Who logs in**

- Only **staff** log in, and only to the admin app. The public site has no login: readers browse, comment as guests (moderated) and subscribe to the newsletter.
- Reader accounts and bookmarks from the main guide are not used here. Disable `Features::registration()` in `config/fortify.php` if only staff accounts exist, and create staff in the admin Users page.

---

## Part 1: Prepare the Laravel API

Start from the finished Laravel backend of the main guide (Steps 1-14, 21). The React code inside Laravel is no longer needed.

### 1.1 Remove the SPA from Laravel

- Delete `resources/views/app.blade.php`, `resources/js/`, and any `public/template/` files.
- Optionally remove the unused Node tooling: Laravel no longer builds front-end assets.
- Replace `routes/web.php`. The web routes now only need the health response (Fortify registers its own POST routes):

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['name' => config('app.name'), 'status' => 'ok']));
```

### 1.2 Environment (`.env`)

Local:

```env
APP_URL=http://localhost:8000
ASSET_URL=http://localhost:8000

FRONTEND_URL=http://localhost:3000
ADMIN_URL=http://localhost:5173
FRONTEND_REVALIDATE_SECRET=replace-with-a-long-random-string

CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173

SESSION_DRIVER=database
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=sync
```

Production values are in [Part 5](#part-5-production-deployment).

> Only the **admin** origin goes into `SANCTUM_STATEFUL_DOMAINS`. Requests from stateful origins must carry a CSRF token. The public site's requests (comments, newsletter) are therefore left out, so they stay stateless and need no CSRF handling.

Add the two custom URLs to config. In `config/app.php`:

```php
'admin_url' => env('ADMIN_URL'),
```

In `config/services.php`:

```php
'frontend' => [
    'url' => env('FRONTEND_URL'),
    'revalidate_secret' => env('FRONTEND_REVALIDATE_SECRET'),
],
```

### 1.3 CORS

Publish the CORS config and restrict it to your front ends:

```bash
php artisan config:publish cors
```

`config/cors.php`:

```php
<?php

return [
    'paths' => [
        'api/*', 'sanctum/csrf-cookie',
        'login', 'logout', 'forgot-password', 'reset-password',
        'email/verification-notification',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', ''))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

`supports_credentials` is required for the admin's cookie login. With credentials, origins must be listed explicitly (no `*`).

### 1.4 Redirects and Fortify URLs

`bootstrap/app.php`: guests hitting an auth-protected web route (for example the e-mail verification link) go to the admin login:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
    $middleware->redirectGuestsTo(fn () => rtrim(config('app.admin_url'), '/').'/login');

    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

`config/fortify.php`: send users to the admin after e-mail verification:

```php
'views' => false,
'home' => env('ADMIN_URL', 'http://localhost:5173').'/admin/dashboard',
```

`FortifyServiceProvider::boot()`: the password-reset link opens the admin app:

```php
ResetPassword::createUrlUsing(fn ($user, string $token) =>
    rtrim(config('app.admin_url'), '/').'/reset-password/'.$token.'?email='.urlencode($user->getEmailForPasswordReset())
);
```

### 1.5 Absolute File URLs

The apps are on different origins, so relative `/storage/...` paths no longer work. Return absolute URLs from the API.

`User` model (replace the accessor):

```php
public function getAvatarUrlAttribute(): ?string
{
    $avatar = $this->attributes['avatar'] ?? null;

    return $avatar ? asset('storage/'.$avatar) : null;
}
```

`Media` model:

```php
public function getUrlAttribute(): string
{
    return asset('storage/'.$this->file_path);
}
```

`Post` model: add an accessor and append it:

```php
protected $appends = ['featured_image_url'];

public function getFeaturedImageUrlAttribute(): ?string
{
    $image = $this->attributes['featured_image'] ?? null;

    return $image ? asset('storage/'.$image) : null;
}
```

### 1.6 Public Endpoints for Next.js

**Views are counted by the browser, not by the page fetch.** If the post page is cached, a server-side increment would only run on revalidation. Remove the `increment` lines from `Public\PostController::show()` and add a separate endpoint:

```php
// Public\PostController

public function view(string $slug)
{
    Post::published()->where('slug', $slug)->toBase()->increment('views');

    return response()->noContent();
}
```

In `show()`, delete `Post::whereKey(...)->toBase()->increment('views');` and `$post->views++;`.

**Sitemap data.** Create `app/Http/Controllers/Api/Public/SitemapController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

class SitemapController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'posts' => Post::published()->orderByDesc('published_at')->limit(5000)->get(['slug', 'updated_at']),
            'categories' => Category::where('status', true)->get(['slug', 'updated_at']),
            'tags' => Tag::get(['slug', 'updated_at']),
        ]);
    }
}
```

**Routes.** In the `public` group of `routes/api.php` add:

```php
use App\Http\Controllers\Api\Public\SitemapController;

Route::get('sitemap', SitemapController::class);
Route::post('posts/{slug}/view', [PublicPostController::class, 'view'])->middleware('throttle:30,1');
```

The existing `/api/public/*` and `/api/admin/*` routes are unchanged.

### 1.7 Push Changes to Next.js (Revalidation)

When content changes, Laravel tells Next.js which pages to rebuild.

**Job** `app/Jobs/RevalidateFrontend.php`:

```php
<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

class RevalidateFrontend implements ShouldQueue
{
    use Queueable;

    /** @param  string[]  $paths   exact pages, e.g. /blog/my-post
     *  @param  string[]  $layouts layout roots to refresh everything under, e.g. / */
    public function __construct(public array $paths = [], public array $layouts = []) {}

    public function handle(): void
    {
        $url = config('services.frontend.url');

        if (! $url) {
            return;
        }

        try {
            Http::withHeaders(['x-revalidate-secret' => config('services.frontend.revalidate_secret')])
                ->timeout(10)
                ->post(rtrim($url, '/').'/api/revalidate', [
                    'paths' => array_values(array_unique($this->paths)),
                    'layouts' => array_values(array_unique($this->layouts)),
                ])
                ->throw();
        } catch (Throwable $e) {
            // Pages still refresh on their own through the time-based ISR fallback
            report($e);
        }
    }
}
```

**Post observer** `app/Observers/PostObserver.php`:

```php
<?php

namespace App\Observers;

use App\Jobs\RevalidateFrontend;
use App\Models\Post;

class PostObserver
{
    public function saved(Post $post): void
    {
        $this->refresh($post);
    }

    public function deleted(Post $post): void
    {
        $this->refresh($post);
    }

    private function refresh(Post $post): void
    {
        $post->loadMissing('category:id,slug');

        $paths = ['/', '/blog', '/blog/'.$post->slug, '/sitemap.xml', '/feed.xml'];

        if ($post->category) {
            $paths[] = '/category/'.$post->category->slug;
        }

        RevalidateFrontend::dispatch($paths);
    }
}
```

**Category and tag observer** `app/Observers/TaxonomyObserver.php` (navigation and sidebars show them on every page):

```php
<?php

namespace App\Observers;

use App\Jobs\RevalidateFrontend;
use Illuminate\Database\Eloquent\Model;

class TaxonomyObserver
{
    public function saved(Model $model): void
    {
        RevalidateFrontend::dispatch([], ['/']);
    }

    public function deleted(Model $model): void
    {
        RevalidateFrontend::dispatch([], ['/']);
    }
}
```

**Register** in `AppServiceProvider::boot()`:

```php
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Observers\PostObserver;
use App\Observers\TaxonomyObserver;

Post::observe(PostObserver::class);
Category::observe(TaxonomyObserver::class);
Tag::observe(TaxonomyObserver::class);
```

**Settings** (site name, footer): in `Admin\SettingController::update()`, after `Cache::forget('settings');` add:

```php
\App\Jobs\RevalidateFrontend::dispatch([], ['/']);
```

**Scheduled posts.** A bulk `update()` does not fire model events, so replace the scheduler in `routes/console.php` with one that saves each model:

```php
Schedule::call(function () {
    Post::where('status', 'scheduled')
        ->where('published_at', '<=', now())
        ->get()
        ->each
        ->update(['status' => 'published']);
})->everyMinute()->name('publish-scheduled-posts');
```

View counts use `toBase()->increment()`, which does not fire events, so reading a post never triggers a rebuild.

> Tag changes made with `$post->tags()->sync()` do not fire a `Post` event. Tag archive pages refresh on the ISR timer (5 minutes). If you need it faster, dispatch `RevalidateFrontend` with the tag paths in the admin `PostController` after the sync.

### 1.8 Optional Cleanup

- Disable `Features::registration()` in `config/fortify.php` when only staff log in.
- The bookmark routes in `ProfileController` are unused here. Remove them if you do not plan reader accounts.

---

## Part 2: Next.js Public Website

Uses the App Router. Next.js 15 or newer, Node.js 20.19+ recommended.

### 2.1 Create the Project

```bash
npx create-next-app@latest blogify-web
```

Answer the prompts: **JavaScript**, **ESLint** yes, **Tailwind** no, **`src/` directory** yes, **App Router** yes, import alias `@/*`.

```bash
cd blogify-web
npm install bootstrap bootstrap-icons sanitize-html
```

`.env.local`:

```env
# Server-side calls (use the internal URL if the API is on the same network)
API_URL=http://localhost:8000
# Browser calls (comments, newsletter, view counter)
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_SITE_URL=http://localhost:3000
# Must match FRONTEND_REVALIDATE_SECRET in Laravel
REVALIDATE_SECRET=replace-with-a-long-random-string
```

### 2.2 Project Structure

```text
blogify-web/src/
├── app/
│   ├── layout.jsx
│   ├── page.jsx                      # home
│   ├── not-found.jsx
│   ├── sitemap.js
│   ├── robots.js
│   ├── feed.xml/route.js             # RSS
│   ├── api/revalidate/route.js       # webhook called by Laravel
│   ├── blog/page.jsx                 # list + search
│   ├── blog/[slug]/page.jsx         # single post
│   ├── category/[slug]/page.jsx
│   └── tag/[slug]/page.jsx
├── components/
│   ├── BootstrapJS.jsx
│   ├── PostGrid.jsx
│   ├── PostArchive.jsx
│   ├── Pagination.jsx
│   ├── NewsletterForm.jsx
│   ├── CommentSection.jsx
│   └── ViewCounter.jsx
└── lib/
    ├── api.js
    └── format.js
```

### 2.3 API Helper and Formatting

`src/lib/api.js`: server-side fetch with ISR. `revalidate` is the time-based fallback if a webhook is missed.

```js
const API = process.env.API_URL;

export async function api(path, { revalidate = 300 } = {}) {
    const res = await fetch(`${API}/api/public${path}`, {
        headers: { Accept: 'application/json' },
        next: { revalidate },
    });

    if (res.status === 404) return null;
    if (!res.ok) throw new Error(`API ${res.status} for ${path}`);

    return res.json();
}
```

`src/lib/format.js`:

```js
export const formatDate = (value) =>
    new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
```

### 2.4 Root Layout

`src/components/BootstrapJS.jsx`: loads Bootstrap's JavaScript on the client for the mobile navbar toggle.

```jsx
'use client';

import { useEffect } from 'react';

export default function BootstrapJS() {
    useEffect(() => {
        import('bootstrap/dist/js/bootstrap.bundle.min.js');
    }, []);

    return null;
}
```

`src/app/layout.jsx`:

```jsx
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import Link from 'next/link';
import { api } from '@/lib/api';
import BootstrapJS from '@/components/BootstrapJS';
import NewsletterForm from '@/components/NewsletterForm';

export async function generateMetadata() {
    const settings = (await api('/settings')) ?? {};
    const name = settings.site_name || 'Blogify';

    return {
        metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL),
        title: { default: name, template: `%s | ${name}` },
        description: settings.site_description,
        openGraph: { siteName: name, type: 'website' },
        alternates: { types: { 'application/rss+xml': '/feed.xml' } },
    };
}

export default async function RootLayout({ children }) {
    const [settings, categories] = await Promise.all([api('/settings'), api('/categories')]);
    const siteName = settings?.site_name || 'Blogify';

    return (
        <html lang="en">
            <body className="d-flex flex-column min-vh-100">
                <nav className="navbar navbar-expand-lg bg-body-tertiary border-bottom sticky-top">
                    <div className="container">
                        <Link className="navbar-brand fw-bold" href="/">{siteName}</Link>
                        <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
                            <span className="navbar-toggler-icon"></span>
                        </button>
                        <div className="collapse navbar-collapse" id="mainNav">
                            <ul className="navbar-nav me-auto">
                                <li className="nav-item"><Link className="nav-link" href="/blog">Blog</Link></li>
                                {categories?.slice(0, 5).map((c) => (
                                    <li key={c.id} className="nav-item">
                                        <Link className="nav-link" href={`/category/${c.slug}`}>{c.name}</Link>
                                    </li>
                                ))}
                            </ul>
                            <form action="/blog" role="search" className="d-flex">
                                <input name="q" type="search" className="form-control form-control-sm me-2" placeholder="Search..." aria-label="Search" />
                                <button className="btn btn-sm btn-outline-primary" type="submit"><i className="bi bi-search"></i></button>
                            </form>
                        </div>
                    </div>
                </nav>

                <main className="flex-grow-1">{children}</main>

                <footer className="bg-body-tertiary border-top mt-5 py-5">
                    <div className="container">
                        <div className="row g-4">
                            <div className="col-md-6">
                                <h5>{siteName}</h5>
                                <p className="text-body-secondary">{settings?.site_description}</p>
                            </div>
                            <div className="col-md-6">
                                <h6>Newsletter</h6>
                                <NewsletterForm />
                            </div>
                        </div>
                        <hr />
                        <p className="text-center text-body-secondary mb-0">{settings?.footer_text}</p>
                    </div>
                </footer>

                <BootstrapJS />
            </body>
        </html>
    );
}
```

`src/app/not-found.jsx`:

```jsx
import Link from 'next/link';

export default function NotFound() {
    return (
        <div className="container py-5 text-center">
            <h1 className="display-1 fw-bold">404</h1>
            <p className="lead">The page you are looking for does not exist.</p>
            <Link href="/" className="btn btn-primary">Back to home</Link>
        </div>
    );
}
```

### 2.5 Shared Components

`src/components/PostGrid.jsx` (server component):

```jsx
import Link from 'next/link';
import { formatDate } from '@/lib/format';

export default function PostGrid({ posts }) {
    if (!posts?.length) {
        return <p className="text-center text-body-secondary py-5">No posts found.</p>;
    }

    return (
        <div className="row g-4">
            {posts.map((post) => (
                <div key={post.id} className="col-md-6 col-lg-4">
                    <article className="card border-0 shadow-sm h-100">
                        <Link href={`/blog/${post.slug}`}>
                            {post.featured_image_url ? (
                                // eslint-disable-next-line @next/next/no-img-element
                                <img src={post.featured_image_url} alt={post.title} loading="lazy" className="card-img-top" style={{ height: 220, objectFit: 'cover' }} />
                            ) : (
                                <div className="bg-body-secondary d-flex align-items-center justify-content-center" style={{ height: 220 }}>
                                    <i className="bi bi-image fs-1 text-secondary"></i>
                                </div>
                            )}
                        </Link>
                        <div className="card-body">
                            <Link href={`/category/${post.category.slug}`} className="badge bg-primary text-decoration-none mb-2">{post.category.name}</Link>
                            <h2 className="h5 card-title">
                                <Link href={`/blog/${post.slug}`} className="text-decoration-none text-body">{post.title}</Link>
                            </h2>
                            <p className="card-text text-body-secondary">{post.excerpt}</p>
                        </div>
                        <div className="card-footer bg-transparent border-0 d-flex justify-content-between">
                            <small className="text-body-secondary">{post.user?.name} &middot; {formatDate(post.published_at)}</small>
                            <small className="text-body-secondary"><i className="bi bi-eye me-1"></i>{post.views}</small>
                        </div>
                    </article>
                </div>
            ))}
        </div>
    );
}
```

`src/components/Pagination.jsx`: plain links, so every page is crawlable.

```jsx
import Link from 'next/link';

export default function Pagination({ basePath, page, lastPage, query = {} }) {
    if (!lastPage || lastPage <= 1) return null;

    const href = (p) => {
        const qs = new URLSearchParams({ ...query, page: String(p) }).toString();
        return `${basePath}?${qs}`;
    };

    return (
        <nav aria-label="Pagination" className="mt-4">
            <ul className="pagination justify-content-center">
                <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                    <Link className="page-link" href={href(Math.max(1, page - 1))} rel="prev">Previous</Link>
                </li>
                <li className="page-item disabled"><span className="page-link">Page {page} of {lastPage}</span></li>
                <li className={`page-item ${page >= lastPage ? 'disabled' : ''}`}>
                    <Link className="page-link" href={href(Math.min(lastPage, page + 1))} rel="next">Next</Link>
                </li>
            </ul>
        </nav>
    );
}
```

`src/components/PostArchive.jsx`: one archive layout for the blog, category and tag pages.

```jsx
import Link from 'next/link';
import { api } from '@/lib/api';
import PostGrid from './PostGrid';
import Pagination from './Pagination';

export default async function PostArchive({ title, filters, basePath, query = {} }) {
    const params = new URLSearchParams(Object.entries(filters).filter(([, value]) => value));
    const [posts, categories, tags] = await Promise.all([
        api(`/posts?${params}`, { revalidate: 120 }),
        api('/categories'),
        api('/tags'),
    ]);

    return (
        <div className="container py-5">
            <h1 className="h2 mb-4">{title}</h1>
            <div className="row g-4">
                <div className="col-lg-9">
                    <PostGrid posts={posts?.data} />
                    <Pagination basePath={basePath} page={posts?.current_page ?? 1} lastPage={posts?.last_page ?? 1} query={query} />
                </div>
                <aside className="col-lg-3">
                    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
                        <h2 className="h6">Categories</h2>
                        <ul className="list-unstyled mb-0">
                            {categories?.map((c) => (
                                <li key={c.id} className="d-flex justify-content-between">
                                    <Link href={`/category/${c.slug}`}>{c.name}</Link>
                                    <span className="text-body-secondary">{c.posts_count}</span>
                                </li>
                            ))}
                        </ul>
                    </div></div>
                    <div className="card border-0 shadow-sm"><div className="card-body">
                        <h2 className="h6">Popular tags</h2>
                        <div className="d-flex flex-wrap gap-1">
                            {tags?.map((t) => (
                                <Link key={t.id} href={`/tag/${t.slug}`} className="badge text-bg-secondary text-decoration-none">{t.name}</Link>
                            ))}
                        </div>
                    </div></div>
                </aside>
            </div>
        </div>
    );
}
```

### 2.6 Pages

`src/app/page.jsx` (home):

```jsx
import Link from 'next/link';
import { api } from '@/lib/api';
import PostGrid from '@/components/PostGrid';

export const revalidate = 300;

export default async function Home() {
    const [featured, trending, latest] = await Promise.all([
        api('/posts/featured'),
        api('/posts/trending'),
        api('/posts/latest'),
    ]);
    const hero = featured?.[0];

    return (
        <>
            <section className="bg-body-tertiary py-5">
                <div className="container py-4 text-center">
                    {hero ? (
                        <>
                            <span className="badge bg-primary mb-3">Featured</span>
                            <h1 className="display-5 fw-bold">{hero.title}</h1>
                            <p className="lead text-body-secondary mx-auto" style={{ maxWidth: 700 }}>{hero.excerpt}</p>
                            <Link href={`/blog/${hero.slug}`} className="btn btn-primary btn-lg">Read article</Link>
                        </>
                    ) : (
                        <h1 className="display-5 fw-bold">Welcome to the blog</h1>
                    )}
                </div>
            </section>

            <div className="container">
                <section className="mt-5">
                    <h2 className="h3 mb-4">Trending</h2>
                    <PostGrid posts={trending} />
                </section>
                <section className="mt-5">
                    <div className="d-flex justify-content-between align-items-center mb-4">
                        <h2 className="h3 mb-0">Latest posts</h2>
                        <Link href="/blog">View all</Link>
                    </div>
                    <PostGrid posts={latest} />
                </section>
            </div>
        </>
    );
}
```

`src/app/blog/page.jsx` (list and search). In Next.js 15+, `searchParams` and `params` are promises:

```jsx
import PostArchive from '@/components/PostArchive';

export const metadata = { title: 'Blog', alternates: { canonical: '/blog' } };

export default async function BlogPage({ searchParams }) {
    const { page = '1', q = '' } = await searchParams;

    return (
        <PostArchive
            title={q ? `Search results for "${q}"` : 'All posts'}
            filters={{ page, search: q }}
            basePath="/blog"
            query={q ? { q } : {}}
        />
    );
}
```

`src/app/category/[slug]/page.jsx`:

```jsx
import { notFound } from 'next/navigation';
import { api } from '@/lib/api';
import PostArchive from '@/components/PostArchive';

export async function generateMetadata({ params }) {
    const { slug } = await params;
    const category = await api(`/categories/${slug}`);

    if (!category) return {};

    return {
        title: category.name,
        description: category.description || undefined,
        alternates: { canonical: `/category/${slug}` },
    };
}

export default async function CategoryPage({ params, searchParams }) {
    const { slug } = await params;
    const { page = '1' } = await searchParams;
    const category = await api(`/categories/${slug}`);

    if (!category) notFound();

    return <PostArchive title={category.name} filters={{ category: slug, page }} basePath={`/category/${slug}`} />;
}
```

`src/app/tag/[slug]/page.jsx` follows the same pattern with `filters={{ tag: slug, page }}`. The API has no single-tag endpoint, so the title comes from the slug:

```jsx
import PostArchive from '@/components/PostArchive';

export async function generateMetadata({ params }) {
    const { slug } = await params;

    return { title: `Tag: ${slug.replace(/-/g, ' ')}`, alternates: { canonical: `/tag/${slug}` } };
}

export default async function TagPage({ params, searchParams }) {
    const { slug } = await params;
    const { page = '1' } = await searchParams;

    return <PostArchive title={`Tag: ${slug.replace(/-/g, ' ')}`} filters={{ tag: slug, page }} basePath={`/tag/${slug}`} />;
}
```

### 2.7 Single Post Page

`src/components/ViewCounter.jsx`: counts one view per browser session:

```jsx
'use client';

import { useEffect } from 'react';

export default function ViewCounter({ slug }) {
    useEffect(() => {
        const key = `viewed:${slug}`;
        if (sessionStorage.getItem(key)) return;
        sessionStorage.setItem(key, '1');

        fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/public/posts/${slug}/view`, { method: 'POST' }).catch(() => {});
    }, [slug]);

    return null;
}
```

`src/app/blog/[slug]/page.jsx`:

```jsx
import Link from 'next/link';
import { notFound } from 'next/navigation';
import sanitizeHtml from 'sanitize-html';
import { api } from '@/lib/api';
import { formatDate } from '@/lib/format';
import PostGrid from '@/components/PostGrid';
import CommentSection from '@/components/CommentSection';
import ViewCounter from '@/components/ViewCounter';

export const revalidate = 300;

// Pre-render the newest posts at build time. Others render on first request and are then cached.
export async function generateStaticParams() {
    try {
        const data = await api('/sitemap', { revalidate: 600 });

        return (data?.posts ?? []).slice(0, 100).map((post) => ({ slug: post.slug }));
    } catch {
        return [];
    }
}

export async function generateMetadata({ params }) {
    const { slug } = await params;
    const data = await api(`/posts/${slug}`);

    if (!data) return {};

    const { post } = data;
    const title = post.meta_title || post.title;
    const description = post.meta_description || post.excerpt;

    return {
        title,
        description,
        keywords: post.meta_keywords || undefined,
        alternates: { canonical: `/blog/${slug}` },
        openGraph: {
            type: 'article',
            title,
            description,
            publishedTime: post.published_at,
            authors: [post.user.name],
            images: post.featured_image_url ? [post.featured_image_url] : [],
        },
        twitter: { card: 'summary_large_image' },
    };
}

export default async function PostPage({ params }) {
    const { slug } = await params;
    const data = await api(`/posts/${slug}`);

    if (!data) notFound();

    const { post, related } = data;

    // Post HTML comes from a rich-text editor. Sanitize it on the server before rendering.
    const html = sanitizeHtml(post.content, {
        allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'h1', 'h2', 'span', 'u', 's']),
        allowedAttributes: {
            ...sanitizeHtml.defaults.allowedAttributes,
            img: ['src', 'alt', 'width', 'height'],
            '*': ['class'],
        },
        allowedSchemes: ['http', 'https', 'mailto'],
    });

    const jsonLd = {
        '@context': 'https://schema.org',
        '@type': 'BlogPosting',
        headline: post.title,
        description: post.meta_description || post.excerpt,
        image: post.featured_image_url || undefined,
        datePublished: post.published_at,
        dateModified: post.updated_at,
        author: { '@type': 'Person', name: post.user.name },
        mainEntityOfPage: `${process.env.NEXT_PUBLIC_SITE_URL}/blog/${slug}`,
    };

    return (
        <article className="container py-5" style={{ maxWidth: 860 }}>
            <script
                type="application/ld+json"
                dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd).replace(/</g, '\\u003c') }}
            />

            <Link href={`/category/${post.category.slug}`} className="badge bg-primary text-decoration-none mb-3">{post.category.name}</Link>
            <h1 className="display-6 fw-bold">{post.title}</h1>

            <div className="d-flex flex-wrap gap-3 text-body-secondary my-3">
                <span><i className="bi bi-person me-1"></i>{post.user.name}</span>
                <span><i className="bi bi-calendar me-1"></i>{formatDate(post.published_at)}</span>
                <span><i className="bi bi-clock me-1"></i>{post.read_time} min read</span>
            </div>

            {post.featured_image_url && (
                // eslint-disable-next-line @next/next/no-img-element
                <img src={post.featured_image_url} alt={post.title} className="img-fluid rounded mb-4 w-100" />
            )}

            <div className="post-content" dangerouslySetInnerHTML={{ __html: html }} />

            <div className="mt-4 d-flex flex-wrap gap-2">
                {post.tags.map((tag) => (
                    <Link key={tag.id} href={`/tag/${tag.slug}`} className="badge text-bg-secondary text-decoration-none">#{tag.name}</Link>
                ))}
            </div>

            <hr className="my-5" />
            <CommentSection slug={post.slug} postId={post.id} />

            {related.length > 0 && (
                <section className="mt-5">
                    <h2 className="h4 mb-3">Related posts</h2>
                    <PostGrid posts={related} />
                </section>
            )}

            <ViewCounter slug={post.slug} />
        </article>
    );
}
```

Add basic content styles in `src/app/globals.css` (and import it in `layout.jsx` if your generated layout does not already):

```css
.post-content { font-size: 1.1rem; line-height: 1.8; }
.post-content img { max-width: 100%; height: auto; }
```

### 2.8 Comments and Newsletter (Client Components)

Comments load in the browser so a new approved comment shows immediately without rebuilding the page. Guests only: name and email are required, and comments are moderated.

`src/components/CommentSection.jsx`:

```jsx
'use client';

import { useCallback, useEffect, useState } from 'react';

const API = process.env.NEXT_PUBLIC_API_URL;
const JSON_HEADERS = { 'Content-Type': 'application/json', Accept: 'application/json' };

function Comment({ comment, onReply }) {
    return (
        <div className="mb-3">
            <strong>{comment.user?.name || comment.name}</strong>{' '}
            <small className="text-body-secondary">{new Date(comment.created_at).toLocaleDateString()}</small>
            <p className="mb-1" style={{ whiteSpace: 'pre-wrap' }}>{comment.content}</p>
            {onReply && <button className="btn btn-link btn-sm p-0" onClick={() => onReply(comment)}>Reply</button>}
            {comment.replies?.map((reply) => (
                <div key={reply.id} className="ms-4 mt-3"><Comment comment={reply} /></div>
            ))}
        </div>
    );
}

export default function CommentSection({ slug, postId }) {
    const [comments, setComments] = useState(null);
    const [form, setForm] = useState({ name: '', email: '', content: '' });
    const [parent, setParent] = useState(null);
    const [errors, setErrors] = useState({});
    const [notice, setNotice] = useState(null);

    const load = useCallback(async () => {
        const res = await fetch(`${API}/api/public/posts/${slug}/comments`, { headers: { Accept: 'application/json' } });
        if (res.ok) setComments(await res.json());
    }, [slug]);

    useEffect(() => { load(); }, [load]);

    const change = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const submit = async (e) => {
        e.preventDefault();
        setErrors({});
        setNotice(null);

        const res = await fetch(`${API}/api/public/comments`, {
            method: 'POST',
            headers: JSON_HEADERS,
            body: JSON.stringify({ ...form, post_id: postId, parent_id: parent?.id }),
        });
        const data = await res.json();

        if (!res.ok) {
            setErrors(data.errors ?? {});
            setNotice({ type: 'danger', text: data.message ?? 'Could not post your comment.' });
            return;
        }

        setNotice({ type: 'success', text: data.message });
        setForm({ ...form, content: '' });
        setParent(null);
        load();
    };

    return (
        <section>
            <h2 className="h4 mb-4">Comments {comments && `(${comments.length})`}</h2>

            <form onSubmit={submit} className="mb-5">
                {notice && <div className={`alert alert-${notice.type}`}>{notice.text}</div>}
                {parent && (
                    <div className="alert alert-secondary py-2 d-flex justify-content-between">
                        Replying to {parent.user?.name || parent.name}
                        <button type="button" className="btn-close" onClick={() => setParent(null)} aria-label="Cancel reply"></button>
                    </div>
                )}
                <div className="row">
                    <div className="col-md-6 mb-3">
                        <input name="name" className={`form-control ${errors.name ? 'is-invalid' : ''}`} placeholder="Name" value={form.name} onChange={change} required />
                        {errors.name && <div className="invalid-feedback">{errors.name[0]}</div>}
                    </div>
                    <div className="col-md-6 mb-3">
                        <input name="email" type="email" className={`form-control ${errors.email ? 'is-invalid' : ''}`} placeholder="Email (not published)" value={form.email} onChange={change} required />
                        {errors.email && <div className="invalid-feedback">{errors.email[0]}</div>}
                    </div>
                </div>
                <textarea name="content" rows="3" className={`form-control mb-3 ${errors.content ? 'is-invalid' : ''}`} placeholder="Your comment" value={form.content} onChange={change} required />
                {errors.content && <div className="invalid-feedback d-block mb-2">{errors.content[0]}</div>}
                <button className="btn btn-primary">Post comment</button>
            </form>

            {comments?.map((comment) => <Comment key={comment.id} comment={comment} onReply={setParent} />)}
            {comments?.length === 0 && <p className="text-body-secondary">Be the first to comment.</p>}
        </section>
    );
}
```

`src/components/NewsletterForm.jsx`:

```jsx
'use client';

import { useState } from 'react';

export default function NewsletterForm() {
    const [email, setEmail] = useState('');
    const [notice, setNotice] = useState(null);
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);

        try {
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/public/newsletter/subscribe`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ email }),
            });
            const data = await res.json();

            setNotice({ type: res.ok ? 'success' : 'danger', text: data.message ?? data.errors?.email?.[0] ?? 'Something went wrong.' });
            if (res.ok) setEmail('');
        } catch {
            setNotice({ type: 'danger', text: 'Network error. Please try again.' });
        } finally {
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit}>
            <div className="input-group">
                <input type="email" className="form-control" placeholder="Your email" value={email} onChange={(e) => setEmail(e.target.value)} required />
                <button className="btn btn-primary" disabled={busy}>Subscribe</button>
            </div>
            {notice && <div className={`small mt-2 text-${notice.type}`}>{notice.text}</div>}
        </form>
    );
}
```

### 2.9 Revalidation Webhook

`src/app/api/revalidate/route.js`: Laravel calls this after publishing or editing. The shared secret blocks everyone else.

```js
import { revalidatePath } from 'next/cache';

export async function POST(request) {
    if (request.headers.get('x-revalidate-secret') !== process.env.REVALIDATE_SECRET) {
        return Response.json({ message: 'Unauthorized' }, { status: 401 });
    }

    const { paths = [], layouts = [] } = await request.json();

    paths.forEach((path) => revalidatePath(path));
    layouts.forEach((path) => revalidatePath(path, 'layout'));

    return Response.json({ revalidated: true, paths, layouts });
}
```

`revalidatePath('/', 'layout')` refreshes every page under the root layout. Use it only for changes that appear everywhere (categories, tags, site settings).

### 2.10 Sitemap, Robots and RSS

`src/app/sitemap.js`:

```js
import { api } from '@/lib/api';

export default async function sitemap() {
    const site = process.env.NEXT_PUBLIC_SITE_URL;
    const data = (await api('/sitemap', { revalidate: 600 })) ?? { posts: [], categories: [], tags: [] };

    return [
        { url: site, changeFrequency: 'daily', priority: 1 },
        { url: `${site}/blog`, changeFrequency: 'daily', priority: 0.8 },
        ...data.posts.map((p) => ({ url: `${site}/blog/${p.slug}`, lastModified: p.updated_at, priority: 0.7 })),
        ...data.categories.map((c) => ({ url: `${site}/category/${c.slug}`, lastModified: c.updated_at, priority: 0.5 })),
        ...data.tags.map((t) => ({ url: `${site}/tag/${t.slug}`, lastModified: t.updated_at, priority: 0.4 })),
    ];
}
```

`src/app/robots.js`:

```js
export default function robots() {
    const site = process.env.NEXT_PUBLIC_SITE_URL;

    return {
        rules: [{ userAgent: '*', allow: '/', disallow: ['/api/'] }],
        sitemap: `${site}/sitemap.xml`,
    };
}
```

`src/app/feed.xml/route.js`:

```js
import { api } from '@/lib/api';

export const revalidate = 600;

const escapeXml = (value = '') =>
    value.replace(/[<>&'"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;' }[c]));

export async function GET() {
    const site = process.env.NEXT_PUBLIC_SITE_URL;
    const settings = (await api('/settings')) ?? {};
    const posts = (await api('/posts/latest', { revalidate: 600 })) ?? [];

    const items = posts.map((post) => `
        <item>
            <title>${escapeXml(post.title)}</title>
            <link>${site}/blog/${post.slug}</link>
            <guid>${site}/blog/${post.slug}</guid>
            <pubDate>${new Date(post.published_at).toUTCString()}</pubDate>
            <description>${escapeXml(post.excerpt ?? '')}</description>
        </item>`).join('');

    const xml = `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>${escapeXml(settings.site_name || 'Blogify')}</title>
        <link>${site}</link>
        <description>${escapeXml(settings.site_description || '')}</description>${items}
    </channel>
</rss>`;

    return new Response(xml, { headers: { 'Content-Type': 'application/rss+xml; charset=utf-8' } });
}
```

### 2.11 Notes

- **Build needs the API.** Pages like the home page are generated at build time, so the API must be reachable during `next build`. Deploy the API first, or build on a machine that can reach it.
- **Images.** The examples use plain `<img>` for simplicity. In production you can switch to `next/image` and list the API host under `images.remotePatterns` in `next.config.mjs`.
- **Draft previews** (viewing unpublished posts) are not included. Add a signed preview token on the API and Next.js Draft Mode when you need it.
- **Version note.** In Next.js 16 `revalidateTag` takes a second argument. This guide uses `revalidatePath`, which is the same across versions.

---

## Part 3: React (Vite) Admin Dashboard

The admin app reuses the pages from the main guide (Steps 15, 16 and 18). Only the setup and a few cross-origin details change.

### 3.1 Create the Project

```bash
npm create vite@latest blogify-admin -- --template react
cd blogify-admin
npm install react-router-dom axios bootstrap bootstrap-icons chart.js react-chartjs-2 date-fns react-hot-toast react-quill-new
```

`vite.config.js`:

```js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [react()],
    resolve: {
        alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
    },
    server: { port: 5173, strictPort: true },
});
```

`jsconfig.json` (editor support for the `@` alias):

```json
{
    "compilerOptions": {
        "baseUrl": ".",
        "paths": { "@/*": ["src/*"] }
    }
}
```

`.env`:

```env
VITE_API_URL=http://localhost:8000
VITE_SITE_URL=http://localhost:3000
```

`index.html`: keep the admin out of search results:

```html
<meta name="robots" content="noindex, nofollow">
```

### 3.2 Copy Files from the Main Guide

Copy these into `src/`, keeping the same folder names:

| From the main guide | Notes |
| :-- | :-- |
| Step 15 `bootstrap/axios.js` | One change (below) |
| Step 15 `context/AuthContext.jsx`, `ThemeContext.jsx` | Unchanged |
| Step 15 `hooks/useFetch.js`, `useForm.js` | Unchanged |
| Step 15.6 `components/common/*` (Spinner, Field, Pagination) | Unchanged |
| Step 16 `layouts/AuthLayout.jsx`, `layouts/AdminLayout.jsx` | Small edits (below) |
| Step 16 `routes/ProtectedRoute` is not needed | Use the guards below |
| Step 17.6 `pages/auth/Login`, `ForgotPassword`, `ResetPassword` | Unchanged |
| Step 17.5 `pages/NotFound.jsx` | Unchanged |
| Step 18 `components/admin/TaxonomyManager.jsx` and all `pages/admin/*` | Small edits (below) |
| Step 19 `pages/Profile.jsx` | Delete the Bookmarks section and its imports |

Import the CSS in `src/main.jsx`:

```jsx
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
```

and keep the sidebar styles from Step 16.5 in `src/assets/css/app.css`.

### 3.3 Axios Pointing at the API

`src/bootstrap/axios.js`: only `baseURL` changes. `withXSRFToken: true` makes Axios send the CSRF header on cross-origin requests.

```js
import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
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

export const errorMessage = (error) => {
    const errors = error.response?.data?.errors;
    if (errors) return Object.values(errors).flat()[0];
    return error.response?.data?.message || 'Something went wrong. Please try again.';
};

export default api;
```

All existing calls such as `api.get('/api/admin/posts')` and `api.post('/login')` keep working because they are relative to `baseURL`.

### 3.4 Router and Guards

Keep the `/admin/...` URL prefix from the main guide so every file works unchanged. The app lives at `https://admin.example.com/admin/dashboard`, and `/` redirects there.

`src/routes/GuestRoute.jsx`:

```jsx
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

export default function GuestRoute() {
    const { isAuthenticated, loading } = useAuth();
    const location = useLocation();

    if (loading) return <Spinner fullPage />;
    if (!isAuthenticated) return <Outlet />;

    return <Navigate to={location.state?.from?.pathname || '/admin/dashboard'} replace />;
}
```

`src/routes/AdminRoute.jsx`: a logged-in user without a staff role sees a message instead of being redirected in a loop.

```jsx
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';
import Spinner from '@/components/common/Spinner';

function Forbidden() {
    const { logout } = useAuth();

    return (
        <div className="container py-5 text-center" style={{ maxWidth: 520 }}>
            <h1 className="h3">No access</h1>
            <p className="text-body-secondary">Your account does not have access to the admin area.</p>
            <button className="btn btn-outline-danger" onClick={async () => { await logout(); toast.success('Logged out'); }}>
                Log out
            </button>
        </div>
    );
}

export default function AdminRoute() {
    const { isAuthenticated, canAccessAdmin, loading } = useAuth();
    const location = useLocation();

    if (loading) return <Spinner fullPage />;
    if (!isAuthenticated) return <Navigate to="/login" replace state={{ from: location }} />;

    return canAccessAdmin ? <Outlet /> : <Forbidden />;
}
```

`src/routes/AppRouter.jsx`:

```jsx
import { lazy, Suspense } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import GuestRoute from './GuestRoute';
import AdminRoute from './AdminRoute';
import AuthLayout from '@/layouts/AuthLayout';
import AdminLayout from '@/layouts/AdminLayout';
import Spinner from '@/components/common/Spinner';
import Login from '@/pages/auth/Login';
import ForgotPassword from '@/pages/auth/ForgotPassword';
import ResetPassword from '@/pages/auth/ResetPassword';
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
const Profile = lazy(() => import('@/pages/Profile'));

export default function AppRouter() {
    return (
        <BrowserRouter>
            <Suspense fallback={<Spinner fullPage />}>
                <Routes>
                    <Route path="/" element={<Navigate to="/admin/dashboard" replace />} />

                    <Route element={<GuestRoute />}>
                        <Route element={<AuthLayout />}>
                            <Route path="/login" element={<Login />} />
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
                            <Route path="/admin/profile" element={<Profile />} />
                        </Route>
                    </Route>

                    <Route path="*" element={<NotFound />} />
                </Routes>
            </Suspense>
        </BrowserRouter>
    );
}
```

`src/main.jsx`:

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

### 3.5 Edits to Copied Files

| File | Change |
| :-- | :-- |
| `AdminLayout.jsx` | Menu item `Profile`: change `to: '/profile'` to `to: '/admin/profile'`. "View site" `<Link to="/">` becomes `<a href={import.meta.env.VITE_SITE_URL} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-secondary">View site</a>` |
| `posts/Editor.jsx` | Use the absolute image URL: `setCurrentImage(p.featured_image_url)` and `<img src={currentImage} ...>` (remove the `/storage/` prefix) |
| `Media.jsx` | `item.url` is now absolute. Change the copy handler to `navigator.clipboard.writeText(item.url)` |
| `Settings.jsx` | Logo: `src={`${import.meta.env.VITE_API_URL}/storage/${data.site_logo}`}` |
| `Profile.jsx` | Delete the Bookmarks section, the `PostGrid` import and the `bookmarks` fetch |
| `Login.jsx` | No change |

Everything that uses `user.avatar_url` already receives an absolute URL from the API.

### 3.6 Build

```bash
npm run build      # outputs dist/
```

---

## Part 4: Local Development

Run three servers in three terminals.

```bash
# 1. Laravel API
cd laravel-react-blog
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve

# 2. Next.js public site
cd blogify-web
npm run dev

# 3. Admin
cd blogify-admin
npm run dev
```

Optional fourth terminal for scheduled posts: `php artisan schedule:work`.

**Checks**

1. Open `http://localhost:5173` and log in as `admin@blogify.com` / `password` (seeded in the main guide).
2. Create and publish a post. With `QUEUE_CONNECTION=sync` the revalidation call runs immediately.
3. Open `http://localhost:3000/blog`. The post should be listed.
4. Test the webhook directly (PowerShell: use `curl.exe`):

```bash
curl -X POST http://localhost:3000/api/revalidate \
  -H "x-revalidate-secret: replace-with-a-long-random-string" \
  -H "Content-Type: application/json" \
  -d '{"paths":["/"]}'
```

5. Post a guest comment on the public site. It appears as "pending" in the admin Comments page. Approve it, and it appears on the post.
6. For the real production behaviour, test with `npm run build && npm run start` in `blogify-web`. `npm run dev` does not cache like production.

Cookies on `localhost` are shared across ports, which is why the cookie login works between `localhost:5173` and `localhost:8000`. Always use `localhost` consistently (not `127.0.0.1` in one place).

---

## Part 5: Production Deployment

### 5.1 DNS and Server Layout

| Hostname | Points to | Serves |
| :-- | :-- | :-- |
| `example.com` (and `www`) | web server | Next.js (Node, port 3000 behind Nginx) |
| `admin.example.com` | web server | `blogify-admin/dist` (static) |
| `api.example.com` | web server | Laravel `public/` (PHP-FPM) |

All three share the registrable domain `example.com`, so they are the same site and the `SameSite=Lax` session cookie works across them.

### 5.2 Laravel `.env` (production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
ASSET_URL=https://api.example.com

FRONTEND_URL=https://example.com
ADMIN_URL=https://admin.example.com
FRONTEND_REVALIDATE_SECRET=<long random string, same as Next.js>

CORS_ALLOWED_ORIGINS=https://example.com,https://www.example.com,https://admin.example.com
SANCTUM_STATEFUL_DOMAINS=admin.example.com

SESSION_DRIVER=database
SESSION_DOMAIN=.example.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
```

`SESSION_DOMAIN=.example.com` is required. The CSRF cookie (`XSRF-TOKEN`) is set by the API but must be readable by JavaScript running on `admin.example.com`. A host-only cookie for `api.example.com` would not be.

If Laravel is behind a load balancer or reverse proxy, trust it so HTTPS URLs and client IPs are correct (needed for the comment and newsletter rate limits):

```php
$middleware->trustProxies(at: '*'); // narrow this to your proxy IPs where possible
```

Commands on each release:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Run a queue worker (supervisor/systemd) and the scheduler (cron):

```bash
php artisan queue:work --tries=3 --timeout=30
```

```text
* * * * * cd /var/www/blogify-api && php artisan schedule:run >> /dev/null 2>&1
```

### 5.3 Next.js (production)

`.env.production` (or the host's environment settings):

```env
API_URL=https://api.example.com
NEXT_PUBLIC_API_URL=https://api.example.com
NEXT_PUBLIC_SITE_URL=https://example.com
REVALIDATE_SECRET=<same secret as Laravel>
```

`NEXT_PUBLIC_*` values are baked in at build time, so set them **before** `npm run build`.

```bash
npm ci
npm run build
npm run start -- -p 3000
```

Keep it running with PM2:

```bash
pm2 start npm --name blogify-web -- start -- -p 3000
pm2 save
```

Alternatively deploy the Next.js app to a managed platform (for example Vercel). Then only the API and the admin run on your own server.

### 5.4 Admin (production)

`.env.production`:

```env
VITE_API_URL=https://api.example.com
VITE_SITE_URL=https://example.com
```

Build and upload `dist/` to the admin web root. Any static host works.

### 5.5 Nginx

API (`api.example.com`):

```nginx
server {
    listen 443 ssl http2;
    server_name api.example.com;
    root /var/www/blogify-api/public;
    index index.php;
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Admin (`admin.example.com`): single-page app fallback.

```nginx
server {
    listen 443 ssl http2;
    server_name admin.example.com;
    root /var/www/blogify-admin/dist;
    index index.html;

    location / {
        try_files $uri /index.html;
    }

    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Public site (`example.com`): proxy to Next.js.

```nginx
server {
    listen 443 ssl http2;
    server_name example.com www.example.com;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Add TLS certificates (for example Let's Encrypt) for all three hostnames and redirect HTTP to HTTPS.

### 5.6 Release Order

1. Deploy the **API** (migrations first).
2. Build and deploy the **admin**.
3. Build and deploy **Next.js** (the build calls the API).
4. Publish a test post and confirm it appears on the public site within seconds (revalidation) and that the sitemap includes it.

### 5.7 Checklist

- [ ] HTTPS on all three hostnames
- [ ] `APP_DEBUG=false`, strong `APP_KEY`, seeded passwords changed or removed
- [ ] `CORS_ALLOWED_ORIGINS` lists only your real front-end origins
- [ ] `SANCTUM_STATEFUL_DOMAINS` contains only the admin host
- [ ] `SESSION_DOMAIN=.example.com`, `SESSION_SECURE_COOKIE=true`
- [ ] Same revalidation secret in Laravel and Next.js, never committed to git
- [ ] Queue worker and scheduler running
- [ ] Real mail driver configured (password reset, e-mail verification)
- [ ] Database backups and `storage/app/public` backups
- [ ] Log rotation and error monitoring (for example Sentry) on all three apps
- [ ] Uptime check on `/up` (API) and the home page

---

## Part 6: SEO Checklist

- [ ] Every page has a unique `<title>` and meta description (`generateMetadata`)
- [ ] Canonical URLs set (`alternates.canonical`)
- [ ] Open Graph and Twitter tags on posts (image, title, description)
- [ ] `BlogPosting` JSON-LD on post pages
- [ ] `sitemap.xml` includes posts, categories and tags, and is submitted in Google Search Console
- [ ] `robots.txt` references the sitemap. The admin has `noindex` (and ideally is not linked from the public site)
- [ ] RSS feed at `/feed.xml`
- [ ] Pagination uses real links with `rel="prev"` / `rel="next"`
- [ ] Unknown slugs call `notFound()` and return a real 404 status
- [ ] Images have `alt` text and `loading="lazy"`. Use `next/image` for resizing if desired
- [ ] Core Web Vitals checked with Lighthouse on a production build
- [ ] 301 redirects planned for any previous URLs if migrating an existing site

---

## Part 7: Security Checklist

| Item | Notes |
| :-- | :-- |
| Admin auth | Sanctum session cookie plus CSRF token. No tokens in `localStorage` |
| CORS | Explicit origin list with credentials. Never `*` |
| Public writes | Comments and newsletter are throttled and validated on the API. Comments are moderated |
| Webhook | `/api/revalidate` rejects requests without the shared secret. Use a long random value and rotate it if leaked |
| HTML content | Sanitized on the server (`sanitize-html`) before rendering. JSON-LD output escapes `<` |
| Data exposure | Guest e-mails are hidden from public responses. Public author data uses a column whitelist |
| RBAC | Enforced in Laravel (policies, permission middleware). The admin UI only hides what the user cannot use |
| Uploads | Image-only validation, size limits. Consider resizing and virus scanning for public uploads |
| Secrets | `.env` files are not committed. `NEXT_PUBLIC_*` values are public by definition. Never put secrets in them |
| Headers | Add `X-Content-Type-Options`, `Referrer-Policy` and a Content-Security-Policy in Nginx or Next.js config |

---

## Troubleshooting

| Problem | Likely cause and fix |
| :-- | :-- |
| Admin login returns `419` | Origin not in `SANCTUM_STATEFUL_DOMAINS`, or the XSRF cookie is not readable. In production `SESSION_DOMAIN` must be `.example.com`. Locally use `localhost` everywhere and run `php artisan config:clear` |
| Admin login returns `401` right after login | Cookie not sent. Check `withCredentials`, `supports_credentials` in CORS, `SESSION_SECURE_COOKIE` on HTTPS, and that you use the same hostname consistently |
| CORS error in the browser | The origin is missing from `CORS_ALLOWED_ORIGINS`. Run `php artisan config:clear` after changing `.env` |
| Public comments return `419` | The public site's origin was added to `SANCTUM_STATEFUL_DOMAINS`. Remove it. Only the admin belongs there |
| Public site shows old content after publishing | Webhook did not reach Next.js. Check `FRONTEND_URL`, the secret on both sides, the queue worker and `storage/logs/laravel.log`. The 5-minute ISR timer eventually refreshes the page |
| `401 Unauthorized` from `/api/revalidate` | Secret differs between Laravel (`FRONTEND_REVALIDATE_SECRET`) and Next.js (`REVALIDATE_SECRET`) |
| Images broken on the public site or admin | The API returned a relative path. Check the `*_url` accessors, `APP_URL`/`ASSET_URL`, and that `php artisan storage:link` was run |
| `next build` fails with a fetch error | The API is not reachable during the build. Check `API_URL` and deploy the API first |
| `params` / `searchParams` error in Next.js | In Next.js 15+ they are promises. Use `const { slug } = await params;` |
| Views never increase | `NEXT_PUBLIC_API_URL` wrong or the `/view` route is missing. Check the browser network tab |
| Admin shows "No access" | The user has no staff role. Assign `admin`, `editor` or `author` in the Users page |
| Reset-password link opens the wrong site | `config('app.admin_url')` is not set. Add `ADMIN_URL` and the `admin_url` config key |
| Every view causes a rebuild | Views must use `toBase()->increment()` so model events do not fire |

---

## What to Reuse from the Main Guide

| Main guide | In this architecture |
| :-- | :-- |
| Steps 1-14 (Laravel setup, auth, migrations, models, API routes, controllers, policies) | Reuse as-is, then apply [Part 1](#part-1-prepare-the-laravel-api) |
| Step 15 (Axios, auth context, hooks) | Admin app only (Axios `baseURL` changes) |
| Step 16 (router, layouts, guards) | Admin app only (new guards in 3.4) |
| Step 17 (public React pages) | Replaced by the Next.js pages in [Part 2](#part-2-nextjs-public-website) |
| Step 18 (admin pages) | Admin app (small edits in 3.5) |
| Step 19 (comments, bookmarks, newsletter) | Comments and newsletter are client components in Next.js. Bookmarks are dropped |
| Step 20 (SEO) | Handled by Next.js metadata, sitemap and RSS |
| Step 21 (seeders) | Reuse as-is |
| Step 22 (run) | Replaced by [Part 4](#part-4-local-development) |
| Step 23 (HTML templates) | Apply the blog template to the Next.js app (JSX conversion rules are the same) and the admin template to the Vite admin |

**Project layout at a glance**

```text
blogify/
├── blogify-api/     Laravel 13 (API only)  → api.example.com
├── blogify-web/     Next.js public site    → example.com
└── blogify-admin/   React + Vite admin     → admin.example.com
```

*Last Updated: October 2026 | Laravel 13 | Next.js 15+ | React 19 | Vite | MySQL | Sanctum + Fortify + Spatie Permission*
