# Deploying MAK Lost & Found to Render (free)

This app is containerized (Docker) and deploys to **Render**'s free web service.
It uses an **external MySQL** database, **Gmail SMTP** for OTP emails, and
**Cloudinary** for persistent image storage.

> Your local XAMPP setup is unchanged. These steps only affect the deployed copy.

---

## 0. What you'll create (free accounts)

| Service | Purpose | Sign up |
|---|---|---|
| GitHub repo `mak-lost-found-render` | Source Render deploys from | github.com/new |
| Render | Hosts the app (Docker web service) | render.com |
| Aiven (or Clever Cloud) | Free MySQL database | aiven.io |
| Cloudinary | Stores uploaded item photos | cloudinary.com |
| Gmail App Password | Sends OTP / notification emails | myaccount.google.com |

---

## 1. Generate an APP_KEY (once)

On your machine, in the project folder:

```bash
php artisan key:generate --show
```

Copy the whole `base64:...` string — you'll paste it into Render as `APP_KEY`.

---

## 2. Create the database (Aiven MySQL — free, persistent)

1. Sign in at https://aiven.io → **Create service** → **MySQL** → **Free plan**.
2. Pick a cloud/region close to you, create the service, wait until it's *Running*.
3. Open the service → **Overview** and note: **Host**, **Port**, **User**,
   **Password**, **Database name** (default `defaultdb`).
4. Aiven requires TLS. Click **Download** on the **CA Certificate** (`ca.pem`).
5. Put that file in the project at **`storage/certs/ca.pem`** (create the folder),
   then commit it (it's a public CA cert, not a secret). In production set:
   `MYSQL_ATTR_SSL_CA=/var/www/html/storage/certs/ca.pem`

> Prefer no TLS hassle? Use **Clever Cloud** → add a **MySQL** add-on (free dev
> plan). It gives host/port/user/password/db and needs **no** CA cert — leave
> `MYSQL_ATTR_SSL_CA` blank.

---

## 3. Create Cloudinary credentials

1. Sign up at https://cloudinary.com → **Dashboard**.
2. Copy **Cloud name**, **API Key**, **API Secret**.
   These become `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`.

---

## 4. Create a Gmail App Password

1. Enable **2-Step Verification** on your Google account.
2. https://myaccount.google.com/apppasswords → create one named "MAK Lost Found".
3. Copy the **16-character** password (remove spaces).
   - `MAIL_USERNAME` = your full Gmail address
   - `MAIL_PASSWORD` = that 16-char password
   - `MAIL_FROM_ADDRESS` = your Gmail address

---

## 5. Push to the new GitHub repo

Create an **empty** repo named `mak-lost-found-render` on GitHub (no README).
Then (commands you'll run — see the chat, I'll do the local git part for you):

```bash
git remote add render-origin https://github.com/<you>/mak-lost-found-render.git
git push render-origin main
```

---

## 6. Create the Render web service

**Option A — Blueprint (uses `render.yaml`):**
1. Render Dashboard → **New** → **Blueprint** → connect the repo.
2. Render reads `render.yaml` and creates the service. Fill every `sync:false`
   value (the secrets) when prompted.

**Option B — Manual:**
1. **New** → **Web Service** → connect the `mak-lost-found-render` repo.
2. Runtime: **Docker**. Plan: **Free**. Region: your choice.
3. Add the environment variables from `.env.production.example` (Section below).
4. **Create Web Service**.

### Required environment variables (Render → Environment)

```
APP_NAME=MAK Lost & Found
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...              # from step 1
APP_URL=https://YOUR-SERVICE.onrender.com
LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=...                     # from Aiven/Clever Cloud
DB_PORT=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MYSQL_ATTR_SSL_CA=/var/www/html/storage/certs/ca.pem   # Aiven only; blank otherwise

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your.gmail@gmail.com
MAIL_PASSWORD=your16charapppass
MAIL_FROM_ADDRESS=your.gmail@gmail.com
MAIL_FROM_NAME=MAK Lost & Found

CLOUDINARY_CLOUD_NAME=...
CLOUDINARY_API_KEY=...
CLOUDINARY_API_SECRET=...

WHATSAPP_PROVIDER=log
```

> After the first deploy, set `APP_URL` to the real `https://<name>.onrender.com`
> URL Render assigns, then trigger a redeploy (or **Manual Deploy → Clear build cache**).

---

## 7. First deploy

On deploy, the container automatically:
- runs `php artisan migrate --force` (creates all tables),
- runs `php artisan db:seed --force` (creates the 3 demo accounts),
- links storage, caches config + views, and starts Apache.

Watch **Logs** in Render. A healthy boot ends with `Starting Apache`.

---

## 8. Log in with the seeded accounts

No registration needed — these exist after the first deploy:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@mak.ac.ug` | `Admin@1234` |
| User | `john@mak.ac.ug` | `Password@123` |
| User | `mary@mak.ac.ug` | `Password@123` |

Then test **registration** with a fresh email to confirm the OTP email arrives.

---

## 9. Things to know about the free tier

- **Spin-down:** the service sleeps after ~15 min idle; the next request takes
  ~30–60s to wake. Sessions are in the database, so you stay logged in.
- **Ephemeral disk:** anything written to the container disk is lost on redeploy.
  That's why photos go to **Cloudinary** (persistent). Don't rely on local files.
- **Logs:** `LOG_CHANNEL=stderr` streams Laravel logs into Render's Logs tab.

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| 500 on every page | `APP_KEY` missing/invalid, or DB env wrong. Check Logs. |
| `SQLSTATE ... SSL` / can't connect | Set `MYSQL_ATTR_SSL_CA` to the committed `ca.pem` path (Aiven), or use a non-TLS provider. |
| OTP email never arrives | Wrong Gmail App Password, or 2FA not enabled. Check spam. |
| Photos vanish after redeploy | Cloudinary env not set — uploads fell back to local (ephemeral). |
| CSS/JS missing | Build stage failed — check Logs for the `npm run build` step. |
| Mixed-content / http links | Set `APP_URL` to the `https://` Render URL and redeploy. |
