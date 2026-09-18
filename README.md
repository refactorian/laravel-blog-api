# Laravel Blog API

A production-ready REST API backend for a blog platform, built with Laravel and Sanctum authentication. Designed for Flutter mobile applications.

## Tech Stack

- **PHP 8.3+** / **Laravel 11**
- **Laravel Sanctum** — token-based API authentication (30-day token expiration)
- **MySQL / PostgreSQL** — relational database
- **PHPUnit** — automated testing (166 tests, 374 assertions)

## Quick Start

```bash
git clone <repo-url> && cd laravel-blog-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

The API is available at `http://localhost:8000/api/v1`.

## Authentication

All authenticated endpoints require a Bearer token:

```
Authorization: Bearer <your-token>
```

Get a token via `/register` or `/login`. Tokens expire after 30 days.

### Rate Limiting

Auth endpoints are rate-limited:
- Register / Login: 10 requests per minute
- Forgot / Reset password: 5 requests per minute

---

## API Endpoints

Base URL: `/api/v1`

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/auth/register` | No | Register a new user |
| `POST` | `/auth/login` | No | Login and get token |
| `POST` | `/auth/logout` | Yes | Invalidate current token |
| `GET` | `/auth/me` | Yes | Get authenticated user |
| `POST` | `/auth/forgot-password` | No | Send password reset link |
| `POST` | `/auth/reset-password` | No | Reset password with token |

### Profile

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/profile` | Yes | Get current user profile |
| `PUT` | `/profile` | Yes | Update profile (name, bio, avatar) |
| `PUT` | `/profile/password` | Yes | Change password |

### Posts

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/posts` | No | List posts (paginated, filterable, sortable) |
| `GET` | `/posts/{slug}` | No | Get single post by slug |
| `POST` | `/posts` | Yes | Create a new post |
| `PUT` | `/posts/{id}` | Yes | Update a post |
| `DELETE` | `/posts/{id}` | Yes | Delete a post (soft delete) |
| `POST` | `/posts/{id}/publish` | Yes | Publish a draft post |
| `POST` | `/posts/{id}/unpublish` | Yes | Unpublish a post |
| `GET` | `/posts/{id}/related` | No | Get related posts |
| `POST` | `/posts/{id}/read` | Yes | Mark post as read |

**Query Parameters for `GET /posts`:**

| Parameter | Example | Description |
|-----------|---------|-------------|
| `search` | `?search=laravel` | Search in title, excerpt, content |
| `category` | `?category=technology` | Filter by category slug |
| `tag` | `?tag=flutter` | Filter by tag slug |
| `author` | `?author=1` | Filter by user ID |
| `sort` | `?sort=-published_at` | Sort (prefix `-` for desc) |
| `page` | `?page=1` | Page number |
| `per_page` | `?per_page=20` | Results per page (max 100) |

### Comments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/posts/{id}/comments` | Yes | List comments for a post |
| `POST` | `/posts/{id}/comments` | Yes | Create a comment or reply |
| `PUT` | `/comments/{id}` | Yes | Update a comment |
| `DELETE` | `/comments/{id}` | Yes | Delete a comment |

### Likes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/posts/{id}/like` | Yes | Like a post |
| `DELETE` | `/posts/{id}/like` | Yes | Unlike a post |
| `GET` | `/posts/{id}/like` | Yes | Check like status and count |

### Bookmarks

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/bookmarks` | Yes | List user's bookmarks |
| `POST` | `/posts/{id}/bookmark` | Yes | Bookmark a post |
| `DELETE` | `/posts/{id}/bookmark` | Yes | Remove bookmark |
| `GET` | `/posts/{id}/bookmark` | Yes | Check bookmark status |

### Categories

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/categories` | No | List all categories with post counts |
| `GET` | `/categories/{slug}` | No | Get category details |
| `POST` | `/categories` | Yes | Create category (author/admin) |
| `PUT` | `/categories/{id}` | Yes | Update category (author/admin) |
| `DELETE` | `/categories/{id}` | Yes | Delete category (admin only) |

### Tags

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/tags` | No | List all tags with post counts |
| `GET` | `/tags/{slug}` | No | Get tag details |
| `POST` | `/tags` | Yes | Create tag (author/admin) |
| `PUT` | `/tags/{id}` | Yes | Update tag (author/admin) |
| `DELETE` | `/tags/{id}` | Yes | Delete tag (admin only) |

### Users

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/users/{id}` | No | Get public user profile |
| `GET` | `/users/{id}/posts` | No | Get user's published posts |
| `GET` | `/users/{id}/followers` | No | List user's followers |
| `GET` | `/users/{id}/following` | No | List users this user follows |
| `POST` | `/users/{id}/follow` | Yes | Follow a user |
| `DELETE` | `/users/{id}/follow` | Yes | Unfollow a user |

### Search

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/search?q=laravel` | No | Search posts (default) |
| `GET` | `/search?q=john&type=users` | No | Search users |

### Home / Discovery

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/home` | No | Featured, latest, popular posts, categories, trending tags |

### Feed

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/feed` | Yes | Personalized feed from followed authors |

### Media

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/media` | Yes | Upload image (JPEG, PNG, WebP, max 10MB) |
| `DELETE` | `/media/{id}` | Yes | Delete uploaded media |

### Notifications

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/notifications` | Yes | List notifications |
| `POST` | `/notifications/{id}/read` | Yes | Mark notification as read |
| `POST` | `/notifications/read-all` | Yes | Mark all as read |

### Reading History

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/reading-history` | Yes | List reading history |
| `DELETE` | `/reading-history` | Yes | Clear reading history |

---

## Response Format

**Success:**

```json
{
    "success": true,
    "message": "Posts retrieved successfully.",
    "data": {}
}
```

**Validation Error:**

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

## User Roles

| Role | Permissions |
|------|-------------|
| `user` | Like, bookmark, comment, follow, read history |
| `author` | Create/edit/delete own posts, manage categories & tags |
| `admin` | Full access to all resources |

## Database

```bash
php artisan migrate:fresh --seed
```

This creates:
- 1 admin user (`admin@example.com`)
- 5 author users
- 10 regular users
- 10 categories, 20 tags
- 25 published posts with tags
- Comments, likes, bookmarks, and follows

## Testing

```bash
php artisan test
```

166 tests covering authentication, posts, categories, tags, comments, likes, bookmarks, follows, search, notifications, reading history, media, feed, users, and home endpoints.

## License

MIT
