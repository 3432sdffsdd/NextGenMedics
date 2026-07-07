# NextGen Medics LMS API

Production-ready REST API backend for the NextGen Medics Learning Management System.

## Stack

- PHP 8.3+
- MySQL 8.0+ / MariaDB 10.5+
- Custom MVC architecture (no Laravel)
- JWT authentication
- cPanel / shared hosting compatible

## Project Structure

```
backend/
├── bootstrap.php          # PSR-4 autoloader (no Composer required)
├── config/config.php      # Application configuration
├── database/
│   ├── schema.sql         # Full database schema
│   ├── seed.sql           # Sample data
│   └── install.php        # One-command installer
├── docs/API.md            # Full API documentation
├── public/
│   ├── index.php          # Entry point (point web root here)
│   └── .htaccess          # URL rewriting + security headers
├── routes/api.php           # All API routes
├── src/
│   ├── Controllers/       # HTTP controllers
│   ├── Core/              # Router, JWT, Request, Response, Database
│   ├── Helpers/           # Password, FileUpload, Slug helpers
│   ├── Middleware/        # CORS, Auth, Role, RateLimit
│   ├── Repositories/      # Data access layer
│   └── Services/          # Business logic
└── storage/
    ├── uploads/           # User-uploaded files
    └── cache/             # Rate limit cache
```

## Quick Start (Local)

### 1. Configure environment

```bash
cp .env.example .env
# Edit .env with your database credentials and JWT secrets
```

### 2. Install database

```bash
php database/install.php
```

### 3. Point web server to `public/`

**Apache (XAMPP/WAMP):** Place project at `htdocs/LMS/backend/public`

**PHP built-in server (development):**
```bash
php -S localhost:8080 -t public
```

### 4. Test the API

```bash
curl http://localhost:8080/health
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nextgenmedics.com","password":"Admin@123"}'
```

## cPanel Deployment

1. Upload the entire `backend/` folder to your hosting account
2. Set document root to `backend/public` (via cPanel → Domains → Document Root)
3. Create MySQL database and user in cPanel
4. Copy `.env.example` to `.env` and fill in production values
5. Run `php database/install.php` via cPanel Terminal or SSH
6. Ensure `storage/uploads` and `storage/cache` are writable (755 or 775)
7. Add your frontend domain to `config/config.php` → `cors.allowed_origins`

## Frontend Integration

The existing React/Vite frontend connects via `frontend/src/services/api.js`:

```env
VITE_API_URL=https://yourdomain.com/api
```

Default local URL: `http://localhost/LMS/backend/public`

### Auth flow

```javascript
// Login returns: { token, refresh_token, expires_in, user }
POST /auth/login { email, password }

// Attach token to requests
Authorization: Bearer <token>

// Refresh when expired
POST /auth/refresh { refresh_token }
```

## Default Accounts

| Role    | Email                       | Password    |
|---------|-----------------------------|-------------|
| Admin   | admin@nextgenmedics.com     | Admin@123   |
| Teacher | teacher@nextgenmedics.com   | Teacher@123 |
| Student | student@nextgenmedics.com   | Student@123 |

Change these immediately in production.

## Security Checklist

- [ ] Set strong `JWT_SECRET` and `JWT_REFRESH_SECRET` in `.env`
- [ ] Change all default user passwords
- [ ] Restrict CORS to your frontend domain only
- [ ] Enable HTTPS
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Ensure `.env` is not web-accessible

## API Documentation

See [docs/API.md](docs/API.md) for the complete endpoint reference.
