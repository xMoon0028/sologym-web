# SoloGym Web • Promotional & Infographic Platform

Official promotional, infographic, and APK distribution website for **SoloGym** (by MoonDev) built with **Laravel 13 & PHP 8.3**.

---

## 🚀 Quick GitHub Setup

Follow these steps in your terminal to push this project to your GitHub repository:

### 1. Create a New Repository on GitHub
1. Go to [github.com/new](https://github.com/new).
2. Name the repository: `sologym-web` (or any name you prefer).
3. Set visibility to **Public** (recommended for Render free tier).
4. Do **not** initialize with README or .gitignore (we already have them configured).
5. Click **Create repository**.

### 2. Push Your Local Code to GitHub
Open your terminal and run:

```bash
cd /Users/admin/.gemini/antigravity/scratch/solo_gym_web

# Link to your new GitHub repository
git remote add origin https://github.com/YOUR_GITHUB_USERNAME/sologym-web.git

# Rename branch to main (if not already)
git branch -M main

# Push all files (including APK package & Render config)
git push -u origin main
```

---

## 🌐 Deploying to Render.com (Free Tier)

There are two easy ways to deploy this website on Render:

### Method 1: Automatic Blueprint (Recommended • 1-Click)
1. Go to [dashboard.render.com](https://dashboard.render.com/).
2. Click **New +** > **Blueprint**.
3. Select your `sologym-web` GitHub repository.
4. Render will automatically read [`render.yaml`](render.yaml) and configure:
   - **Environment:** Docker (PHP 8.3)
   - **Plan:** Free
   - **Environment Variables:** `APP_KEY`, `APP_ENV=production`, `DB_CONNECTION=sqlite`.
5. Click **Apply**. Your website will be live in 2-3 minutes!

### Method 2: Manual Web Service
1. On [Render Dashboard](https://dashboard.render.com/), click **New +** > **Web Service**.
2. Connect your GitHub repository: `sologym-web`.
3. Choose **Docker** as the Runtime.
4. Select the **Free** instance type.
5. In **Environment Variables**, add:
   - `APP_NAME`: `SoloGym`
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`
   - `APP_KEY`: *(Generate one using `php artisan key:generate --show` or leave Render to generate)*
   - `DB_CONNECTION`: `sqlite`
   - `DB_DATABASE`: `/var/www/html/database/database.sqlite`
6. Click **Create Web Service**.

---

## 📱 Features Included
* **Hero App Mockup:** Realistic in-app sample view of SoloGym with Coach Rex, next workout card, and body comp metrics.
* **Direct APK Download:** `GET /download/apk` distributes `SoloGym-v1.0.0.apk` directly to Android users with checksum verification.
* **Interactive Mascot Showcase:** Switch between the 4 authentic personas (**Coach Rex**, **Coach Maya**, **Coach Iron**, **Coach Zen**) and 6 emotional states.
* **Interactive Olympic Plate Calculator:** Real-time color-coded Olympic plate visualization.
* **Interactive US Navy Body Fat Calculator:** Real-time Lean Mass vs Fat Mass calculation.
* **Evidence-Based Literature Section:** Schoenfeld, Helms, Contreras, Rippetoe, and ACSM guidelines.
* **100% Offline & Mobile-Optimized:** Built with high-contrast cyber dark mode.

---

&copy; 2026 SoloGym by [MoonDev](https://xmoon0028.github.io/)
