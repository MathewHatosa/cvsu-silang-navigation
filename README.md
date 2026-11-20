# CvSU Silang Campus Navigation System

An interactive web-based campus navigation and facility management system designed for **Cavite State University - Silang Campus**. The system enables students, faculty, and visitors to locate campus buildings, classrooms, offices, and facilities, while providing campus administrators with tools to manage campus location data and review navigation trends.

---

## 📌 Features

- **Interactive Campus Map**: Visual navigation interface displaying buildings, coordinates, and facility boundaries.
- **Search & Directory**: Search for buildings, specific rooms, and academic/administrative departments.
- **Facility & Office Locator**: Quick access to clinic, faculty offices, comfort rooms, and student facilities.
- **Admin Management Dashboard (`/admin`)**:
  - Add and delete campus buildings and descriptions.
  - Manage rooms associated with specific buildings.
  - Manage campus facilities and GPS coordinates.
  - View real-time navigation history and popular destinations.
- **RESTful API Endpoints**: Internal endpoints providing campus GeoJSON/location data for frontend rendering.

---

## 🛠️ Tech Stack

- **Backend Framework**: [Laravel 12](https://laravel.com/) (PHP ^8.2)
- **Frontend / Styling**: Blade Templates, [Tailwind CSS v4](https://tailwindcss.com/), [Vite](https://vitejs.dev/)
- **Database**: MySQL / MariaDB (via XAMPP, Laragon, or standalone MySQL)
- **Map / Scripting**: Vanilla JavaScript, Leaflet / Custom map coordinate engine

---

## 📋 System Requirements

Ensure the following prerequisites are installed on your machine:
- **PHP** >= 8.2 (with `pdo_mysql`, `openssl`, `mbstring`, `fileinfo` extensions enabled)
- **Composer** (PHP dependency manager)
- **Node.js** (v18.x or later) & **npm**
- **MySQL / MariaDB** (e.g., via [XAMPP](https://www.apachefriends.org/) or [Laragon](https://laragon.org/))

---

## 🚀 Installation & Setup Guide

### 1. Clone or Open the Project
Open a terminal in the project root folder:
```powershell
cd cvsu-silang-navigation-main
```

### 2. Environment Configuration
Duplicate `.env.example` into `.env`:
```powershell
# Windows (PowerShell / CMD)
copy .env.example .env

# macOS / Linux
cp .env.example .env
```

### 3. Install Backend Dependencies
```bash
composer install
```

### 4. Install Frontend Dependencies
```bash
npm install
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Automated Database Setup (One Command)
Make sure your **MySQL** service is started (e.g., via XAMPP Control Panel or Laragon). Then run:
```bash
php artisan db:setup
```

**What this automated command does:**
1. Connects to MySQL using your `.env` configuration.
2. Automatically creates the database (`campus_navigation_db`) if it doesn't already exist.
3. Runs all migrations (including the native campus navigation schema).
4. Seeds all buildings, departments, rooms, and facilities automatically via `CampusNavigationSeeder`.

> [!TIP]
> You can also use standard Laravel artisan commands:
> - Run seeders only: `php artisan db:seed`
> - Fresh reset + migrations + seeds: `php artisan migrate:fresh --seed`
> - Or use our all-in-one helper: `php artisan db:setup --fresh`

---

## 💻 Running the Application

You can start the development server using either of the following approaches:

### Option A: Single Command (Recommended)
Laravel 12 comes configured with concurrent task running:
```bash
composer run dev
```
*This simultaneously starts the Laravel server (`php artisan serve`), queue listener, logging pail, and the Vite dev server.*

---

### Option B: Running in Two Separate Terminals

#### Terminal 1 — Backend Server
```bash
php artisan serve
```
*Runs the PHP server at `http://127.0.0.1:8000`.*

#### Terminal 2 — Frontend Assets Bundler
```bash
npm run dev
```
*Compiles Tailwind CSS and hot-reloads frontend assets via Vite.*

---

## 🌐 Application URLs

Once running, access the system in your web browser:

| Page | URL | Description |
| :--- | :--- | :--- |
| **Campus Map** | `http://127.0.0.1:8000/` | Main interactive campus navigation map |
| **Admin Panel** | `http://127.0.0.1:8000/admin` | Campus data and navigation management |
| **Campus Data API** | `http://127.0.0.1:8000/api/campus-data` | JSON output of all buildings, rooms, and facilities |

---

## 🗄️ Database Structure

- `buildings` — Building ID, building name, latitude, longitude, description.
- `rooms` — Room ID, foreign key `building_id`, room number, room name.
- `departments` — Department ID, foreign key `building_id`, department name.
- `facilities` — Facility ID, facility name, latitude, longitude.
- `navigation_history` — Search and destination log tracking popularity of locations.

---

## 🔧 Troubleshooting

- **`SQLSTATE[HY000] [1049] Unknown database 'campus_navigation_db'`**:
  Make sure you imported `database/campus_navigation_db.sql` into MySQL before running the app.
- **`Vite manifest not found`**:
  Make sure `npm run dev` is running in a terminal, or build production assets with `npm run build`.
- **`No application encryption key has been specified`**:
  Run `php artisan key:generate`.
