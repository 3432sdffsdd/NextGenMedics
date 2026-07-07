# WhatsApp setup for NextGen Medics LMS

Real WhatsApp messages use **Meta WhatsApp Cloud API** (free for development).

## Easiest way (recommended)

1. Double-click:
   ```
   d:\LMS\backend\setup-whatsapp.bat
   ```
2. It opens Meta Developer in your browser.
3. Follow the on-screen steps and paste your **Phone Number ID** and **Access Token**.
4. Enter your phone number for a test message.
5. Restart the API (`start-lms.bat`).

---

## Manual setup in Meta (step by step)

### Step 1 — Create a Meta app

1. Go to [https://developers.facebook.com/apps/](https://developers.facebook.com/apps/)
2. Log in with Facebook.
3. Click **Create App**.
4. Choose **Other** → **Next**.
5. Choose **Business** → **Next**.
6. App name: `NextGen Medics LMS` → **Create app**.

### Step 2 — Add WhatsApp

1. In your app dashboard, find **WhatsApp** and click **Set up**.
2. Open **WhatsApp** → **API Setup** in the left menu.

### Step 3 — Copy credentials

On the API Setup page, copy:

| Field | Put in `.env` as |
|--------|------------------|
| Phone number ID | `WHATSAPP_PHONE_NUMBER_ID` |
| Temporary access token | `WHATSAPP_ACCESS_TOKEN` |

### Step 4 — Add test phone numbers

Meta only allows messages to **verified test numbers** until your app is approved.

1. On the same API Setup page, find **To** (Send messages).
2. Click **Manage phone number list** or add recipient.
3. Enter your phone (e.g. `+92 321 8902931`).
4. Confirm the code WhatsApp sends you.

Repeat for each teacher/student phone you want to test.

### Step 5 — Edit `backend/.env`

```env
WHATSAPP_ENABLED=true
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxx...
CRON_SECRET=ngm-cron-local-dev
```

### Step 6 — Test

```bat
cd C:\xampp\php
php.exe -f d:\LMS\backend\scripts\test-whatsapp.php 03218902931
```

You should receive a WhatsApp message within a few seconds.

### Step 7 — Restart LMS

```bat
d:\LMS\start-lms.bat
```

---

## Student & teacher phone numbers

In the LMS admin panel, when creating users, fill the **Phone** field (e.g. `03218902931`).

Each number must also be added as a test recipient in Meta until you complete **Business Verification** for production.

---

## Production (later)

- Replace the **temporary token** with a **permanent System User token** in Meta Business Settings.
- Complete Meta **Business Verification** to message any number without adding them as test recipients.
- Use a dedicated WhatsApp Business number.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| No message received | Add phone in Meta → WhatsApp → API Setup → test recipients |
| Token expired | Generate new token in Meta API Setup (temp tokens ~24h) |
| Log only, no WhatsApp | Set `WHATSAPP_ENABLED=true` and restart API |
| Error in log | See `backend/storage/logs/whatsapp.log` |

---

## Without WhatsApp API

If you skip Meta setup, class reminders still work as **in-app notifications** (bell icon). WhatsApp attempts are logged to `whatsapp.log` only.
