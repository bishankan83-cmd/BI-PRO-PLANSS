# TirePlan Pro — Tire Production Planning System
## Complete Setup Guide

---

## 📁 Project Structure

```
tire_planner/
├── index.php          ← Main application (open this in browser)
├── setup.sql          ← Run this ONCE to create the planning table
├── includes/
│   ├── db.php         ← Database config & connection
│   └── planning.php   ← Core scheduling engine (all business logic)
└── api/
    └── index.php      ← REST API endpoint (handles all AJAX calls)
```

---

## ⚙️ Setup Steps

### Step 1 — Edit Database Config
Open `includes/db.php` and set your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'planatir_task_managemen');
```

### Step 2 — Import Your Existing Tables
Import all 6 SQL files into your database (if not already done):
```bash
mysql -u root -p planatir_task_managemen < tire.sql
mysql -u root -p planatir_task_managemen < mold.sql
mysql -u root -p planatir_task_managemen < cavity.sql
mysql -u root -p planatir_task_managemen < tire_mold.sql
mysql -u root -p planatir_task_managemen < mold_press.sql
mysql -u root -p planatir_task_managemen < press_cavity.sql
```

### Step 3 — Run Setup SQL
```bash
mysql -u root -p planatir_task_managemen < setup.sql
```
This creates the `production_plan` table used to store all plans.

### Step 4 — Deploy to Web Server
Copy the entire `tire_planner/` folder to your web server root (e.g. `/var/www/html/`) and open `http://localhost/tire_planner/` in your browser.

> **Note:** Requires PHP 8.0+ with PDO and pdo_mysql extension enabled.

---

## 🚀 How to Use

### Dashboard
- See live stats: planned runs, in-progress, completed today, active molds, cavities, tire SKUs
- Quick view of upcoming production runs

### New Plan (3-Step Wizard)
1. **Step 1 — Select Tire**: Search by icode (e.g. `139001`) or description (e.g. `ACHIEVER`). Optionally filter by press.
2. **Step 2 — Choose Slot**: The system automatically calculates all available slots by finding which **mold is free** + which **cavity is free** on that mold's compatible press, then picks the **earliest start time** (max of mold ready + cavity ready). The "BEST" slot is highlighted first.
3. **Step 3 — Confirm**: Review the selected mold/press/cavity/time and save.

### Schedule View
- Filter plans by date range and status
- Update status (Planned → In Progress → Completed)
- Delete plans

---

## 🧠 Scheduling Logic

For each tire icode:
1. Look up all compatible molds from `tire_mold`
2. For each mold, get its `availability_date` from `mold` table
3. For each mold, find compatible presses via `mold_press`
4. For each press, get all its cavities via `press_cavity` + `cavity` table
5. **Effective Start = MAX(mold_ready, cavity_ready)**  — must wait for BOTH
6. **End = Start + time_taken (minutes)**
7. Sort all options by earliest start → show user the best slots

---

## 📊 Database Tables Used

| Table | Purpose |
|-------|---------|
| `tire` | Tire catalog with `time_taken` (minutes per cycle) |
| `mold` | Mold inventory with `availability_date` |
| `cavity` | Cavity inventory with `availability_date` |
| `tire_mold` | Which molds can make which tires |
| `mold_press` | Which presses a mold can run on |
| `press_cavity` | Which cavities belong to which press |
| `production_plan` | **NEW** — your saved production schedule |
