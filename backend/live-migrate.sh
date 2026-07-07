#!/bin/bash
# ============================================================
# NextGen Medics LMS — LIVE server safe database update
# ============================================================
# SAFE: Does NOT delete teachers, students, courses, or enrollments.
# Only adds missing tables/columns from database/migrations/
#
# HOW TO RUN (SSH / cPanel Terminal):
#   cd /path/to/your/backend
#   chmod +x live-migrate.sh
#   ./live-migrate.sh
#
# Or without chmod:
#   bash live-migrate.sh
# ============================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo ""
echo "NextGen Medics — Live database update"
echo "====================================="
echo "Folder: $SCRIPT_DIR"
echo ""

if [ ! -f ".env" ]; then
  echo "ERROR: .env not found in backend folder."
  echo "Copy .env.example to .env and set your LIVE database credentials first."
  exit 1
fi

if [ ! -f "database/migrate.php" ]; then
  echo "ERROR: database/migrate.php not found."
  echo "Upload the full backend folder first, then run this script."
  exit 1
fi

# Find PHP (works on most Linux / cPanel servers)
PHP_BIN=""
for bin in php php83 php82 php81 php8 php74; do
  if command -v "$bin" >/dev/null 2>&1; then
    PHP_BIN="$bin"
    break
  fi
done

if [ -z "$PHP_BIN" ]; then
  echo "ERROR: PHP not found. Try: php database/migrate.php manually"
  exit 1
fi

echo "Using: $PHP_BIN"
echo ""
"$PHP_BIN" database/migrate.php
echo ""
echo "Live database update finished."
echo "Remember: also upload backend PHP files + frontend dist/ if you have not yet."
echo ""
