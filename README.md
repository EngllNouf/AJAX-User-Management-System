# 🧑‍💻 User Management System (PHP + MySQL + AJAX)

A simple **web-based system** built with PHP, MySQL, HTML, CSS, and JavaScript (AJAX) that allows secure login, user management (add, edit, delete), and full activity logging — all without reloading the page.

---

## 🚀 Features

### 🔐 Login Page

* Secure login using username and password.
* Passwords are **hashed** in the database.
* Login verification is done via **AJAX** (no page reload).
* Displays specific error messages for:

  * User not found
  * Incorrect password
  * Database error

### 👥 Users Page

* Shows all registered users in a responsive table.
* Supports **Add**, **Edit**, and **Delete** operations.
* All actions are handled with AJAX — the page never reloads.
* Every operation (add, edit, delete) is recorded in logs.

### 🧾 Logs Page

* Displays all actions performed by users:

  * Login
  * Add user
  * Edit user
  * Delete user
* Shows which user performed the action and when.

---

## 🗄️ Folder Structure

```
USER-MANAGEMENT-SYSTEM/
│
├── backend/
│   ├── add_user.php
│   ├── delete_user.php
│   ├── edit_user.php
│   ├── get_logs.php
│   ├── get_users.php
│   └── login.php
│
├── frontend/
│   ├── index.html
│   ├── Users.html
│   ├── Logs.html
│   ├── script.js
│   ├── Users.js
│   ├── Logs.js
│   ├── style.css
│   └── Users.css
│
├── Recording 2025-11-02 142817.mp4
└── database.sql
```

---

## ⚙️ How to Run Locally

### Requirements:

* [XAMPP](https://www.apachefriends.org/)
* PHP 8+
* MySQL

### Steps:

1. Move the folder `USER-MANAGEMENT-SYSTEM` to:

   ```
   C:\xampp\htdocs\
   ```
2. Open **XAMPP Control Panel** → Start **Apache** and **MySQL**.
3. Go to **phpMyAdmin** → Import the file:

   ```
   database.sql
   ```
4. Open the project in your browser:

   ```
   http://localhost/USER-MANAGEMENT-SYSTEM/frontend/
   ```
5. Login using test credentials:

   ```
   Username: any test user
   Password: 123456
   ```

   *(All passwords are stored hashed for security.)*

---

## 💡 Tech Stack

| Layer        | Technologies Used            |
| ------------ | ---------------------------- |
| Frontend     | HTML, CSS, JavaScript (AJAX) |
| Backend      | PHP (Procedural)             |
| Database     | MySQL                        |
| Local Server | XAMPP                        |

---

## 🧰 Database Information

### Tables:

1. **users** — Stores all user data (name, age, email, password hash).
2. **logs** — Stores every operation (login, add, edit, delete) with timestamp and username.

To import:

```bash
database.sql
```

---

## 🖼️ Screenshots

### 🔐 Login Page

*(Add your screenshot here)*

### 👥 Users Page

*(Add your screenshot here)*

### 🧾 Logs Page

*(Add your screenshot here)*

---

## 🎥 Demo Video

📽️ [Recording 2025-11-02 142817.mp4](Recording%202025-11-02%20142817.mp4)

---

## 🧑‍💻 Developer

**Noof Mohammed**
GitHub: [@yourusername](https://github.com/yourusername)

---

## 📝 Notes

* All backend responses are in **JSON** format for AJAX integration.
* Error handling covers:

  * Invalid requests
  * Database connection errors
  * Empty fields
* Demo users share the same password: `123456` (hashed in DB).
* Easy to extend with authentication sessions or role management.
