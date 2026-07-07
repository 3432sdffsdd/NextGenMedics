# Live Deployment Guide — NextGen Medics LMS

## What to upload

### 1. Backend
Upload the entire `backend/` folder **except**:
- `.env` — create/edit on server (never upload from local)
- `storage/cache/rate_limit/*.json` — temp files, skip
- `vendor/` — run `composer install` on server if you use Composer; this project works without it

**Must exist on server:**
```
backend/
  .env                    ← your live DB + JWT secrets
  public/                 ← web root (or subfolder)
  storage/uploads/        ← writable (755 or 775)
  storage/cache/          ← writable
```

### 2. Frontend
Upload **`frontend/dist/`** contents only (already built):
```
index.html
assets/
favicon.svg
mentors/
.htaccess
```

Point your domain (or subfolder) to these files.

---

## Step-by-step

### Step 1 — Backup database
In cPanel → phpMyAdmin → your database → **Export** → Go.  
Save the `.sql` file before anything else.

### Step 2 — Run SQL update
1. Open phpMyAdmin → select your database
2. Go to **SQL** tab
3. Open file: `backend/database/live-update-all.sql`
4. **Change line 22:** `USE YOUR_DB_NAME;` → your real database name  
   Example: `USE u123456_lms;`
5. Click **Go**

This adds all new tables/columns. It does **not** touch your students or teachers.

### Step 3 — Upload backend
- FTP/cPanel File Manager → upload `backend/` folder
- Create `backend/.env` on server:

```env
APP_ENV=production
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_db_user
DB_PASS=your_db_password
JWT_SECRET=long-random-string-here
JWT_REFRESH_SECRET=another-long-random-string
MAIL_FROM=noreply@yourdomain.com
MAIL_FROM_NAME=NextGen Medics
CRON_SECRET=random-cron-secret

# Optional — Study tools generation
AI_ENABLED=false
AI_API_KEY=
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-4o-mini
```

### Step 4 — Upload frontend
- Upload `frontend/dist/*` to your site root (or `public_html/`)
- Ensure `.htaccess` is uploaded (SPA routing)

### Step 5 — API URL
If frontend and backend are on the same domain:
- `frontend/.env.production` uses `VITE_API_URL=/backend/public`
- Rebuild locally if your path differs: `npm run build`

If API is on a subdomain, set in `.env.production` before build:
```
VITE_API_URL=https://api.yourdomain.com
```

### Step 6 — Permissions
```bash
chmod -R 775 backend/storage/uploads
chmod -R 775 backend/storage/cache
```

### Step 7 — Test
- Login as admin / teacher / student (your existing accounts)
- Teacher: upload material, create quiz, Word import, monthly schedule
- Student: view course, attempt quiz

---

## Do NOT run on live

| Script | Why |
|--------|-----|
| `database/install.php` | Wipes/resets database |
| `database/seed.sql` | Inserts demo users & courses |

**Safe on live:** `live-update-all.sql` only (or `migrate.php` if you have SSH + PHP CLI)

---

## SQL file summary

File: **`backend/database/live-update-all.sql`**

| Migration | What it adds |
|-----------|----------------|
| 001 | `course_class_schedule`, `class_reminder_log` |
| 002 | `batches`, `batch_students`, `class_schedule` |
| 003 | Study tools tables, flashcards, MCQs, streaks, discussion extras |
| 004 | `lecture_resources.uploaded_by` column |
| 005 | `schedule_month_uploads` (monthly Excel calendar) |
| 006 | `quizzes.show_review` column (quiz Word import + review mode) |

**Not included:** Any `INSERT INTO users` — your live students & teachers are preserved.

---

## Troubleshooting

**500 error on API** → Check `backend/.env` DB credentials and `storage/` permissions.

**Blank page after deploy** → Check browser console; verify `VITE_API_URL` matches your server layout.

**SQL error "Duplicate column"** → Safe to ignore if you already ran part of the script; columns use IF NOT EXISTS checks.

**Quiz import fails** → Ensure PHP `zip` extension is enabled (for `.docx` parsing).
