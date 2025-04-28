# 📝 PHP Task Management API

A simple RESTful API to manage tasks — built in pure PHP (no framework)!

## 🚀 Features
- User Authentication using Bearer Tokens
- CRUD operations on Tasks
- Soft Delete and Restore Trashed Tasks
- Pagination Support for Task Listings
- Simple Routing System
- Secure Input Validation

## 📂 Folder Structure
```
/ (root)
|-- public/             # Main entry (index.php)
|-- src/                # Controllers, Models, Core files
|-- database/           # SQL scripts or database
|-- .gitignore
|-- README.md
```

## 📢 Requirements
- PHP >= 7.4
- MySQL or SQLite
- Apache/Nginx Server (or PHP built-in server)

## 📋 API Endpoints

| Method | Endpoint                      | Description |
| :----: | ----------------------------- | ----------- |
| POST   | `/api/register`                | Register user |
| POST   | `/api/login`                   | Login and get token |
| GET    | `/api/tasks`                   | Get all tasks (paginated) |
| POST   | `/api/tasks`                   | Create new task |
| GET    | `/api/tasks/{id}`               | Get specific task |
| PUT    | `/api/tasks/{id}`               | Update task |
| DELETE | `/api/tasks/{id}`               | Soft delete task |
| GET    | `/api/tasks/trashed`            | View trashed tasks |
| PUT    | `/api/tasks/restore/{id}`       | Restore task |

## 🛠️ Setup Instructions
1. Clone the repository:
    ```bash
    git clone https://github.com/your-username/php-task-api.git
    cd php-task-api
    ```
2. Setup your database and configure connection.
3. Serve with PHP built-in server:
    ```bash
    php -S localhost:8000 -t public
    ```
4. Test APIs using Postman!

## ✨ Badges
![PHP](https://img.shields.io/badge/Built%20With-PHP-blue)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📄 License
This project is licensed under the MIT License.