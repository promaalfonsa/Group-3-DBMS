# Khadok – Food Delivery Management System

## Project Overview

Khadok is a web-based food delivery management system inspired by modern online food delivery platforms. The system connects customers, restaurants, delivery staff, and administrators through a centralized platform. Users can browse food items, place orders, track deliveries in real time, and provide ratings and reviews after completing purchases.

The project is developed using PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript.

---

# Main Features

## Customer Features

* User registration and login
* Browse restaurants and menus
* Search food items by name or description
* Add items to cart with quantity management
* Checkout system with delivery notes
* Order history and status tracking
* Live delivery tracking with map integration
* Restaurant and food rating/review system

## Restaurant Features

* Restaurant dashboard
* Add/edit/delete menu items
* Upload food images
* Manage incoming orders
* Accept or reject orders
* View ratings and reviews

## Delivery Staff Features

* Delivery dashboard
* View available orders
* Accept delivery requests
* Live location updating
* Delivery tracking system
* Mark orders as delivered

## Admin Features

* Manage users
* Manage restaurants
* Manage delivery staff
* View all orders
* Monitor platform activity
* Promotion management

---

# Technologies Used

| Technology          | Purpose                      |
| ------------------- | ---------------------------- |
| PHP                 | Backend Development          |
| MySQL               | Database Management          |
| HTML/CSS            | Frontend Structure & Styling |
| Bootstrap           | Responsive UI                |
| JavaScript          | Dynamic Features             |
| Leaflet.js          | Map Tracking                 |
| OpenStreetMap       | Map Tiles                    |
| XAMPP/OpenLiteSpeed | Local & Production Server    |

---

# Project Structure

```
khadok/
│
├── index.php
├── login.php
├── register.php
├── cart.php
├── checkout.php
├── orders.php
├── restaurant.php
├── setup.php
├── db.php
├── functions.php
│
├── admin_dashboard.php
├── restaurant_dashboard.php
├── driver_dashboard.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/
│
└── README.md
```

---

# Setup Instructions

## Requirements

* PHP 8+
* MySQL / MariaDB
* XAMPP / Laragon / OpenLiteSpeed
* Web Browser

---

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/GamerGiri/khadok.git
```

---

### 2. Move Project

Move the project folder to:

```txt
htdocs/
```

if using XAMPP.

---

### 3. Open Installer

Visit:

```txt
http://localhost/khadok/setup.php
```

---

### 4. Configure Database

Enter:

* Database Host
* Database Username
* Database Password
* Database Name

Then create admin account.

The installer automatically:

* Creates tables
* Creates admin account
* Generates db.php
* Locks installer

---

### 5. Login

After installation:

```txt
http://localhost/khadok/login.php
```

---

# Team Contributions



---

# E-R Diagram Description

The system consists of multiple entities connected through relationships.

## Main Entities

1. Users
2. Restaurants
3. Menu_Items
4. Orders
5. Order_Items
6. Deliveries
7. Ratings
8. Categories
9. Promotions
10. Driver_Locations

---

# Entity Relationships

## Users → Orders

* One user can place many orders.
* Relationship: One-to-Many

## Restaurants → Menu_Items

* One restaurant can have many menu items.
* Relationship: One-to-Many

## Orders → Order_Items

* One order contains multiple items.
* Relationship: One-to-Many

## Menu_Items → Order_Items

* One menu item can exist in many order items.
* Relationship: One-to-Many

## Orders → Deliveries

* One order can have one delivery.
* Relationship: One-to-One

## Users → Ratings

* Users can submit ratings.
* Relationship: One-to-Many

## Menu_Items → Ratings

* Menu items can receive many ratings.
* Relationship: One-to-Many

---

# Relational Schemas

## USERS

```sql
USERS(
    id PRIMARY KEY,
    name,
    email UNIQUE,
    password_hash,
    role,
    phone,
    created_at
)
```

---

## RESTAURANTS

```sql
RESTAURANTS(
    id PRIMARY KEY,
    user_id FOREIGN KEY REFERENCES USERS(id),
    name,
    address,
    city,
    phone,
    created_at
)
```

---

## CATEGORIES

```sql
CATEGORIES(
    id PRIMARY KEY,
    name UNIQUE
)
```

---

## MENU_ITEMS

```sql
MENU_ITEMS(
    id PRIMARY KEY,
    restaurant_id FOREIGN KEY REFERENCES RESTAURANTS(id),
    category_id FOREIGN KEY REFERENCES CATEGORIES(id),
    name,
    description,
    price,
    image_path,
    available,
    created_at
)
```

---

## ORDERS

```sql
ORDERS(
    id PRIMARY KEY,
    user_id FOREIGN KEY REFERENCES USERS(id),
    restaurant_id FOREIGN KEY REFERENCES RESTAURANTS(id),
    total,
    address,
    phone,
    payment_method,
    delivery_note,
    status,
    created_at
)
```

---

## ORDER_ITEMS

```sql
ORDER_ITEMS(
    id PRIMARY KEY,
    order_id FOREIGN KEY REFERENCES ORDERS(id),
    menu_item_id FOREIGN KEY REFERENCES MENU_ITEMS(id),
    qty,
    price
)
```

---

## DELIVERIES

```sql
DELIVERIES(
    id PRIMARY KEY,
    order_id FOREIGN KEY REFERENCES ORDERS(id),
    driver_id FOREIGN KEY REFERENCES USERS(id),
    assigned_at,
    status,
    latitude,
    longitude
)
```

---

## DRIVER_LOCATIONS

```sql
DRIVER_LOCATIONS(
    driver_id PRIMARY KEY FOREIGN KEY REFERENCES USERS(id),
    latitude,
    longitude,
    updated_at
)
```

---

## RATINGS

```sql
RATINGS(
    id PRIMARY KEY,
    user_id FOREIGN KEY REFERENCES USERS(id),
    restaurant_id FOREIGN KEY REFERENCES RESTAURANTS(id),
    menu_item_id FOREIGN KEY REFERENCES MENU_ITEMS(id),
    order_id FOREIGN KEY REFERENCES ORDERS(id),
    rating,
    comment,
    created_at
)
```

---

## PROMOTIONS

```sql
PROMOTIONS(
    id PRIMARY KEY,
    code UNIQUE,
    discount_percent,
    active,
    valid_from,
    valid_to
)
```

---

# Security Features

* Password hashing using PASSWORD_DEFAULT
* Session-based authentication
* Role-based access control
* SQL injection prevention using prepared statements
* Setup installer lock system

---

# Future Improvements

* Mobile application
* Online payment gateway
* AI food recommendations
* Push notifications
* Restaurant analytics dashboard
* Multi-language support

---

# Conclusion

Khadok is a complete database-driven food delivery management system that demonstrates practical implementation of relational databases, user management, transaction handling, and real-time delivery tracking. The project combines frontend and backend technologies with efficient database design to provide a scalable and user-friendly solution.
