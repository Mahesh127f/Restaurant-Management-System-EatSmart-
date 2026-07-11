# EatSmart — Restaurant Management System
**Project Name: EatSmart**

## Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Local Server:** XAMPP / WAMP / LAMP

---

## Project Structure
```
eatsmart/
├── index.php               ← Homepage
├── menu.php                ← Full menu with filter & search
├── reservation.php         ← Table booking with time slots
├── waste_deals.php         ← Food waste reduction deals
├── checkout.php            ← Order checkout
├── my_orders.php           ← Order tracking (live status)
├── my_reservations.php     ← Customer reservations
├── login.php               ← Login
├── register.php            ← Register
├── css/
│   ├── style.css           ← Main stylesheet
│   └── admin.css           ← Admin panel stylesheet
├── js/
│   └── main.js             ← Cart, chatbot, utilities
├── php/
│   ├── config.php          ← DB config & helpers
│   ├── auth.php            ← Login/logout handler
│   ├── get_order_status.php← Order status API
│   └── get_taken_slots.php ← Reservation slots API
├── includes/
│   ├── header.php          ← Shared nav + cart + chatbot
│   └── footer.php          ← Shared footer
├── admin/
│   ├── index.php           ← Admin dashboard
│   ├── orders.php          ← Order management
│   ├── kitchen.php         ← Kitchen display board
│   ├── menu.php            ← Menu manager (add/edit/delete)
│   ├── reservations.php    ← Reservation management
│   ├── waste_deals.php     ← Waste deals manager
│   ├── reports.php         ← Sales report + AI demand prediction
│   ├── customers.php       ← Customer list
│   └── sidebar.php         ← Admin sidebar include
├── images/
│   └── menu/
│       ├── butter_chicken.jpg
│       ├── butter_naan.jpg
│       ├── chicken_65.jpg
│       ├── chichen_biryani.jpg
│       ├── dal_makhani.jpg
│       ├── egg_fried_rice.jpg
│       ├── fresh_lime_soda.jpg
│       ├── garlic_naan.jpg
│       ├── gulab_jamun.jpg
│       ├── kulfi.jpg
│       ├── mango_lassi.jpg
│       ├── masala_chai.jpg
│       ├── mutton_rogan_josh.jpg
│       ├── palak_paneer.jpg
│       ├── paneer_butter_masala.jpg
│       ├── paneer_tikka.jpg
│       ├── rasgulla.jpg
│       ├── seekh_kabab.jpg
│       ├── tandoori_roti.jpg
│       ├── veg_biryani.jpg
│       ├── veg_spring_rolls.jpg
└── database.sql            ← Full database with seed data

```

---

## Setup Instructions

### Step 1 — Install XAMPP
Download and install XAMPP from https://www.apachefriends.org/
Start **Apache** and **MySQL** services.

### Step 2 — Copy Project
Copy the `eatsmart` folder to `C:/xampp/htdocs/` (Windows) or `/var/www/html/` (Linux/Mac)

### Step 3 — Create Database
1. Open http://localhost/phpmyadmin
2. Click **New** → name it `eatsmart` → click **Create**
3. Click the `eatsmart` database → go to **Import** tab
4. Upload `database.sql` → click **Go**

### Step 4 — Configure Database
Open `php/config.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password (blank for XAMPP default)
define('DB_NAME', 'eatsmart');
define('SITE_URL', 'http://localhost/eatsmart');
```

### Step 5 — Open in Browser
Visit: **http://localhost/eatsmart**

---

## Demo Accounts

| Role     | Email                    | Password |
|----------|--------------------------|----------|
| Admin    | admin@eatsmart.com       | password |
| Kitchen  | kitchen@eatsmart.com     | password |
| Customer | rahul@gmail.com          | password |
| Customer | priya@gmail.com          | password |

---

## Features Implemented

### Customer Features
- ✅ User Registration & Login
- ✅ Full Menu with category filter & search
- ✅ Add to Cart (localStorage, works without login)
- ✅ Pre-Order food before arrival
- ✅ Table Reservation with time slot system
- ✅ Live Order Status Tracking (auto-refresh every 8s)
- ✅ Waste Reduction Deals with countdown timers
- ✅ Loyalty Points (1 point per ₹10 spent)
- ✅ My Orders page
- ✅ My Reservations page
- ✅ AI Chatbot (rule-based, handles 15+ query types)

### Admin Features
- ✅ Dashboard with live stats & charts
- ✅ Full Order Management (update status)
- ✅ Kitchen Display Board (auto-refreshes every 15s)
- ✅ Menu Manager (add/edit/hide items)
- ✅ Reservation Manager (confirm/cancel/complete)
- ✅ Waste Deals Manager (create/deactivate deals)
- ✅ Sales Reports with date filtering
- ✅ Customer database view

### AI Features
- ✅ Popular Dish Recommendations (by order count)
- ✅ Demand Prediction by day of week
- ✅ Rule-based Chatbot with 15+ intents
- ✅ Top dish bar charts on dashboard

---

## Notes for Submission
- All passwords are hashed using PHP `password_hash()` (bcrypt)
- Sessions used for authentication
- SQL injection protected via prepared statements
- Input sanitized with `htmlspecialchars` + `strip_tags`
- Cart stored in `localStorage` (no server needed for browsing)

Built with ❤️ in India By MAHESH YADAV— EatSmart, 2026
