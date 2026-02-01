# 📘 Virtual Study Buddy

**Virtual Study Buddy** is a web-based platform built as a **Third Year Project** to help students organize and manage their study sessions, take notes, track tasks, and collaborate with others — all in one place.

This project is implemented using **PHP, HTML, CSS, and JavaScript**, offering a lightweight and user-friendly interface for academic productivity.

---

### 📌 Core Functionality
- **User Authentication** – Secure signup and login system.
- **Study Planner** – Plan study sessions and keep track of goals.
- **Notes Management** – Upload, view, and manage notes.
- **Tasks & To-Dos** – Add, update, and organize study tasks.
- **Forum / Discussion** – Post and interact with study discussions.
- **Dark Mode Support** – Optional theme toggle for comfortable reading.

### 🛠️ Tech Stack
| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL (via SQL scripts included) |
| Deployment | Standard web server with PHP support |

---

## 📁 Project Structure

``` bash
├── .gitignore
├── .htaccess
├── assets/
├── css/
├── js/
├── uploads/
├── dashboard.php
├── forum.php
├── login.php
├── signup.php
├── study_planner.php
├── studybuddy.php
├── db.php
└── setup_database.sql
```

## Setup Database

### 1. Create a new MySQL database (e.g., virtual_study_buddy)
   Import the schema using:

``` bash
   mysql -u your_username -p virtual_study_buddy < create_db.sql
```

### 2. Configure Connection
Open db.php and update the database credentials:

``` php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'virtual_study_buddy');
```
### 3. Serve the App
**Place the directory in your web server root (e.g., htdocs for XAMPP).**

Visit http://localhost/virtual_study_buddy/ in your browser.

**Note: This repository does not include the database schema or the OpenAI API key. You will need obtain your own API key to use the application.**

*you can get your api key from:*

https://platform.openai.com/api-keys
