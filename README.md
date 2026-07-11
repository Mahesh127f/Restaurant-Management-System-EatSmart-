# 🍽️ EatSmart – Restaurant Management System

A modern web-based Restaurant Management System built using **PHP, MySQL, HTML, CSS, and JavaScript**. EatSmart simplifies restaurant operations by allowing customers to browse menus, place orders, reserve tables, and track their orders, while providing administrators with powerful tools to manage the restaurant efficiently.

---

## 🌐 Live Demo

**Website:** https://eatsmart.great-site.net

---

## 📌 Features

### 👤 Customer Features

- User Registration & Login
- Browse Food Menu
- Search & Category Filter
- Food Images
- Add to Cart
- Secure Checkout
- Table Reservation
- Live Order Tracking
- Order History
- Reservation History
- Waste Reduction Deals
- Loyalty Points System
- AI Chatbot Assistance

---

### 👨‍💼 Admin Features

- Admin Dashboard
- Manage Customers
- Manage Orders
- Kitchen Display Board
- Manage Menu
- Manage Reservations
- Manage Waste Deals
- Sales Reports
- Demand Prediction Dashboard

---

### 🤖 AI Features

- Popular Dish Recommendation
- Weekly Demand Prediction
- Rule-based Restaurant Chatbot
- Dashboard Analytics

---

# 🛠️ Tech Stack

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP 8+

### Database

- MySQL

### Server

- Apache
- XAMPP (Local Development)
- InfinityFree (Deployment)

---

# 📂 Project Structure

```text
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

# 🚀 Installation (Local)

## 1. Clone Repository

```bash
git clone https://github.com/Mahesh127f/Restaurant-Management-System_EatSmart.git
```

---

## 2. Move Project

Copy the folder into

```
xampp/htdocs/
```

---

## 3. Start XAMPP

Start

- Apache
- MySQL

---

## 4. Create Database

Open

```
http://localhost/phpmyadmin
```

Create database

```
eatsmart
```

Import

```
database.sql
```

---

## 5. Configure Database

Open

```
php/config.php
```

Use

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eatsmart');

define('SITE_URL', 'http://localhost/eatsmart');
```

---

## 6. Run Project

```
http://localhost/eatsmart
```

---

# ☁️ Deployment on InfinityFree

### 1. Create Hosting Account

Create a free hosting account on InfinityFree.

---

### 2. Create MySQL Database

Example:

```text
Host:
sql308.infinityfree.com

Database:
if0_42386102_eatsmart

Username:
if0_42386102

Password:
********
```

---

### 3. Import Database

Open phpMyAdmin

Import

```
database.sql
```

---

### 4. Configure Database

Update

```
php/config.php
```

```php
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_USER', 'if0_42386102');
define('DB_PASS', 'YOUR_PASSWORD');
define('DB_NAME', 'if0_42386102_eatsmart');

define('SITE_URL', 'https://eatsmart.great-site.net');
```

---

### 5. Upload Files

Upload all project files to

```
htdocs/
```

Do **not** upload

```
database.sql
```

---

### 6. UTF-8 Emoji Support

Run the following SQL if category emojis appear as `?`:

```sql
ALTER DATABASE if0_42386102_eatsmart
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

ALTER TABLE categories
CONVERT TO CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Also verify the database connection uses:

```php
$conn->set_charset('utf8mb4');
```

---

# 👤 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@eatsmart.com | password |
| Kitchen | kitchen@eatsmart.com | password |
| Customer | mahesh@gmail.com | password |
| Customer | priya@gmail.com | password |

---

# 🔒 Security Features

- Password Hashing (bcrypt)
- Prepared SQL Statements
- Session Authentication
- Input Validation
- XSS Protection
- SQL Injection Protection

---

# 📸 Screenshots

You can add screenshots here:

- Home Page
- Menu
- Login
- Cart
- Reservation
- Admin Dashboard
- Kitchen Dashboard
- Reports

---

# 🚀 Future Enhancements

- Online Payment Gateway
- Email Notifications
- SMS Notifications
- QR Code Menu
- PWA Support
- Real AI Chatbot Integration
- Multi-Restaurant Support

---

# 👨‍💻 Author

**Mahesh Yadav**

B.Tech Information Technology

Amity University, Noida

GitHub

https://github.com/Mahesh127f

---

## ⭐ If you like this project, consider giving it a star on GitHub!
