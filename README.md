# 🚀 AStudio Task Manager

> A modern, Docker-powered project management and time tracking application built with Laravel 12.

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Powered-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-Cache_Ready-DC382D?style=for-the-badge&logo=redis&logoColor=white)

</div>

## 🎯 Overview

AStudio Task Manager is a comprehensive project management and time tracking solution designed for modern development teams. It combines robust project management capabilities with detailed time tracking features, all wrapped in a secure and scalable API-first architecture.

## ✨ Core Features

### 🏗️ Project Management
- **Project Lifecycle Management**
  - Create and manage projects with dynamic status tracking
  - Support for PENDING → ACTIVE → INACTIVE transitions
  - Soft deletion for data integrity
- **Custom Attributes**
  - Flexible EAV (Entity-Attribute-Value) system
  - Extensible project metadata
  - Dynamic filtering capabilities

### ⏱️ Time Tracking
- **Detailed Time Entries**
  - Task-specific logging
  - Date and duration tracking
  - Project association
  - User attribution
- **Reporting & Analytics**
  - Per-project time summaries
  - User activity tracking
  - Historical data management

### 🔐 Security & Authentication
- **OAuth2 Implementation**
  - Secure token-based authentication
  - Passport integration
  - Refresh token support
- **Fine-grained Authorization**
  - Role-based access control
  - Project-level permissions
  - Policy-driven security

## 🏗️ Technical Architecture

### Backend Stack
```
Laravel 12.x ─┬── PHP 8.2+
              ├── MySQL 8.0
              ├── Redis Cache
              └── OAuth2 (Passport)
```

### Infrastructure
```
Docker ─┬── Nginx (Web Server)
        ├── PHP-FPM
        ├── MySQL
        ├── Redis
        ├── Mailpit (Mail Testing)
        └── Adminer (DB Management)
```

## 🛠️ Development Tools

### Core Tools
- **Laravel Telescope** - Advanced debugging
- **Laravel Pint** - Code style enforcement
- **PHPUnit** - Comprehensive testing
- **Laravel Actions** - Business logic encapsulation

### Quality Assurance
- Automated testing pipeline
- Code style enforcement
- Static analysis tools
- Development environment parity

## 🔌 API Endpoints

### Authentication
```
POST   /api/login     - User authentication
POST   /api/register  - New user registration
POST   /api/logout    - Session termination
GET    /api/user      - Current user info
```

### Project Management
```
GET    /api/projects      - List projects
POST   /api/projects      - Create project
GET    /api/projects/{id} - Project details
PUT    /api/projects/{id} - Update project
DELETE /api/projects/{id} - Remove project
```

### Time Tracking
```
GET    /api/timesheets      - List time entries
POST   /api/timesheets      - Create time entry
GET    /api/timesheets/{id} - Entry details
PUT    /api/timesheets/{id} - Update entry
DELETE /api/timesheets/{id} - Remove entry
```
### For Request and Response Examples, Please Check Postman Collection
## 🚀 Prerequisites

- Docker and Docker Compose
- Git
- Composer (for local development)
- Node.js and npm (for frontend assets)

## ⚡ Quick Start

1. Clone the repository:
   ```bash
   git clone https://github.com/ahmedbally/astudio-task
   cd astudio_task
   ```

2. Start the Docker environment:
   ```bash
   docker-compose up -d
   ```

3. Import Postman Collection:
    ```
    ASTUDIO TASK.postman_collection.json
    ```

4. Database Dump:
    ```
    dump.sql
    ```

5. Test Credentials:
   ```
   Email: user@astudio.com
   Password: password
   ```

## 🌐 Service Access Points

| Service  | URL                  | Description          |
|----------|---------------------|---------------------|
| App      | http://localhost:8000 | Main Application    |
| Mailpit  | http://localhost:8025 | Email Testing UI    |
| Adminer  | http://localhost:8080 | Database Management |

### Adminer Login
- System: MySQL
- Server: db
- Username: root
- Password: root
- Database: astudio_task

## 📦 Dependencies

### Backend (PHP)
- PHP 8.2+
- Laravel Framework 12.x
- Laravel Passport
- Laravel Actions
- League OAuth2 Client

### Development
- Laravel Telescope
- Laravel Pail
- Laravel Pint
