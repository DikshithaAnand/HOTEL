# 🏨 Hotelia API

### Smart Hotel Reservation & Management Platform

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit_12-blue?style=for-the-badge&logo=php&logoColor=white)](https://phpunit.de)
[![API](https://img.shields.io/badge/API-RESTful_v1-orange?style=for-the-badge)](https://swagger.io)

# Hotelia PMS — Smart Hotel Reservation & Management Platform

A web-based hotel management platform designed to simplify hotel operations, manage reservations, and organize booking-related information through a centralized application.

Hotelia PMS aims to reduce manual work, improve reservation management, and provide a structured way to manage hotel operations.

---

## Table of Contents

- [Overview](#overview)
- [Objectives](#objectives)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation and Setup](#installation-and-setup)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Email Testing](#email-testing)
- [Cloud Integration](#cloud-integration)
- [Testing](#testing)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)
- [Future Enhancements](#future-enhancements)
- [License](#license)

---

## Overview

**Hotelia PMS** is a hotel property management application developed using Laravel and modern web technologies.

The application provides a foundation for managing hotel-related information and reservation workflows through a centralized web interface. It combines server-side application logic, database operations, and integration capabilities to support a more organized hotel management process.

The project also includes development and integration components for testing email functionality and exploring cloud-based booking synchronization.

### Problem Statement

Managing hotel reservations and operational information through disconnected or manual processes can lead to duplicated work, inconsistent records, and difficulties tracking booking information.

Hotelia PMS addresses this problem by providing a centralized software platform that can support reservation-related workflows and improve the organization of hotel management activities.

### Proposed Solution

The proposed system uses a Laravel-based web application with database integration and optional cloud synchronization components.

The application is designed to provide a maintainable foundation that can be extended with additional hotel management functionality as requirements evolve.

---

## Objectives

The primary objectives of Hotelia PMS are:

- Centralize hotel-related information and reservation workflows.
- Reduce repetitive manual data management.
- Improve the organization and accessibility of booking records.
- Provide a structured backend for hotel management operations.
- Support reliable database interactions.
- Enable development-time email testing.
- Explore integration with cloud-based booking synchronization services.
- Maintain a modular application structure that supports future enhancements.

---

## Key Features

The project is organized around the following application and integration capabilities.

### 1. Hotel Management Application

- Laravel-based web application.
- Organized application routes and backend logic.
- Database-backed application functionality.
- Structured configuration and environment management.

### 2. Reservation Management Foundation

- Support for reservation-related application workflows.
- Centralized handling of booking information.
- Database integration for storing and retrieving application records.

The exact reservation capabilities depend on the modules and workflows enabled in the deployed application.

### 3. Database Integration

- Laravel database configuration.
- Support for local development using SQLite.
- Database migrations for managing schema changes.
- Structured database access through Laravel.

### 4. Email Testing

- Local email testing using Mailpit.
- Ability to inspect captured test emails without delivering them to real recipients.
- Support for testing email-related application workflows during development.

### 5. Cloud Integration

The project includes a Zoho Catalyst integration component for exploring cloud-based booking synchronization.

The integration configuration includes:

- Booking synchronization endpoint configuration.
- Booking read endpoint configuration.
- A synchronization enable/disable setting.
- A shared-secret configuration for authenticated requests.

Cloud synchronization must be configured and tested separately before being considered operational in a deployment.

### 6. Maintainable Project Structure

- Separation of application logic, configuration, routes, and resources.
- Dependency management through Composer and npm.
- Environment-specific configuration.
- Support for automated testing through the Laravel testing setup.

---

## Technology Stack

| Category | Technology |
|---|---|
| Backend | PHP |
| Backend Framework | Laravel |
| Frontend | HTML5, CSS3, JavaScript, Blade templates |
| Database | SQLite for local development |
| Dependency Management | Composer |
| Frontend Package Management | npm |
| Frontend Build Tool | Vite |
| Email Testing | Mailpit |
| Cloud Integration | Zoho Catalyst |
| Version Control | Git |
| Source Code Hosting | GitHub |

**Note:** The technologies listed describe the project's development stack and integration components. The functionality available in a particular deployment depends on its implementation and configuration.

---

## System Architecture

The application follows a web application architecture.

```text
                 User
                  |
                  v
          Web Browser Interface
                  |
                  v
           Laravel Application
           /        |        \
          /         |         \
         v          v          v
    Application  Database   Email Services
      Logic      (SQLite)   (Mailpit for
                             local testing)
                             
                  |
                  v
          Cloud Integration
                  |
                  v
            Zoho Catalyst
       (When configured and enabled)
```

### Architecture Components

**Frontend Layer**

Provides the web interface through which users interact with the application.

**Application Layer**

Laravel handles application routing, backend processing, validation, and database interactions.

**Database Layer**

Stores application data using the configured database connection. SQLite is used for local development.

**Email Testing Layer**

Mailpit captures development emails so their contents can be inspected during testing.

**Cloud Integration Layer**

The Zoho Catalyst integration provides a configured pathway for cloud-based booking synchronization and booking data retrieval.

---

## Project Structure

The following directories and files form the main project structure.

```text
Hotel/
├── app/
│   ├── Http/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
│   └── app.php
│
├── config/
│   └── Application configuration files
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│
├── docs/
│   └── Project documentation
│
├── functions/
│   └── Cloud integration functions
│
├── public/
│   └── Public application assets
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   └── Application-generated files
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env.example
├── .gitignore
├── artisan
├── catalyst.json
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── README.md
└── vite.config.js
```

*This is a representative structure. Individual files and subdirectories may vary depending on the modules present in your local copy.*

---

## Prerequisites

Install the following software before running the application:

- PHP with the extensions required by the Laravel project.
- Composer.
- Node.js and npm.
- Git.
- A compatible database configuration.
- A modern web browser.

Optional components:

- Mailpit for local email testing.
- A Zoho Catalyst account and configured cloud functions if cloud synchronization is required.

Check the installed versions:

```powershell
php --version
composer --version
node --version
npm --version
git --version
```

---

## Installation and Setup

### Step 1: Clone the Repository

Clone the repository from GitHub:

```powershell
git clone https://github.com/YOUR_USERNAME/Hotel.git
```

Navigate into the project directory:

```powershell
cd Hotel
```

Replace `YOUR_USERNAME` with your GitHub username.

### Step 2: Install PHP Dependencies

Run:

```powershell
composer install
```

Composer installs the PHP packages defined in `composer.lock`.

### Step 3: Install Frontend Dependencies

Run:

```powershell
npm install
```

This installs the frontend dependencies defined in `package-lock.json`.

### Step 4: Configure the Environment

Create a local environment file:

```powershell
Copy-Item .env.example .env
```

Open `.env` and configure the application environment and database settings.

### Step 5: Generate the Application Key

Run:

```powershell
php artisan key:generate
```

Laravel generates an application encryption key and updates the local environment configuration.

### Step 6: Prepare the Database

For a local SQLite setup, create the database file if it does not already exist:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

Ensure that the `.env` file uses the intended SQLite database configuration.

For example:

```dotenv
DB_CONNECTION=sqlite
```

If the project uses additional database configuration, preserve the settings required by the application.

### Step 7: Run Database Migrations

Run:

```powershell
php artisan migrate
```

This creates or updates the database schema using the available migrations.

If the application requires seed data, run the appropriate project seeder only after confirming that it is safe to use with your current database.

### Step 8: Build Frontend Assets

For a production-style frontend build, run:

```powershell
npm run build
```

During development, Vite can instead be started using:

```powershell
npm run dev
```

### Step 9: Start the Laravel Server

Run:

```powershell
php artisan serve
```

Open the local address shown in the terminal, typically:

http://127.0.0.1:8000

---

## Environment Configuration

The `.env` file contains machine-specific settings for the application.

Common Laravel settings include:

```dotenv
APP_NAME="Hotelia PMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
```

Use these values as a starting point, adapting them to the existing application configuration.

### Cloud Integration Variables

The project may also use variables such as:

```dotenv
ZOHO_CATALYST_BOOKING_URL=
ZOHO_CATALYST_BOOKINGS_READ_URL=
ZOHO_CATALYST_SYNC_ENABLED=false
BOOKING_SYNC_SECRET=
```

Configure these variables only when the corresponding cloud integration is required.

**Important:**

- Never commit `.env` to GitHub.
- Never publish actual API keys, passwords, tokens, or shared secrets.
- Keep real secret values in your local environment or an appropriate deployment secret manager.
- Use `.env.example` to document required variable names without including private values.
- Keep cloud synchronization disabled until the endpoints, authentication, and expected behavior have been verified.

---

## Database Setup

Hotelia PMS uses SQLite for local development.

The database file is expected at:

```text
database/database.sqlite
```

### Common Database Commands

Run migrations:

```powershell
php artisan migrate
```

Check migration status:

```powershell
php artisan migrate:status
```

Clear cached Laravel configuration:

```powershell
php artisan config:clear
```

### Database Safety

The local SQLite database is intended for development and should not be committed to the repository.

Back up important data before changing the database schema or running database reset commands.

---

## Running the Application

For local development, use separate terminals for the following processes when required.

**Terminal 1 — Laravel backend**

```powershell
php artisan serve
```

**Terminal 2 — Vite development server**

```powershell
npm run dev
```

**Terminal 3 — Mailpit**

Start Mailpit using your local installation or configured development environment if email testing is required.

Once the required services are running, access the Laravel application through the local address displayed by the development server.

---

## Email Testing

Hotelia PMS supports local email testing using Mailpit.

Mailpit captures development emails so developers can inspect email content without sending test messages to real recipients.

### Typical Testing Workflow

1. Start the Mailpit service.
2. Configure Laravel's mail settings to use the local Mailpit SMTP service.
3. Trigger the application's email-related workflow.
4. Open the Mailpit web interface.
5. Inspect the captured message, recipient, subject, and content.

Mailpit's default local interface is commonly available at:

http://localhost:8025

The actual address depends on the local Mailpit configuration.

**Note:** Mailpit is intended for development and testing. Production email delivery requires an appropriately configured mail service.

---

## Cloud Integration

The project includes a Zoho Catalyst integration component for booking-related operations.

The integration configuration supports endpoint settings and a synchronization enable/disable flag.

### Integration Configuration

Before enabling the integration:

1. Deploy the required cloud function.
2. Configure the booking endpoint.
3. Configure the booking read endpoint if required.
4. Store the shared secret securely.
5. Verify authentication and request validation.
6. Test booking retrieval and synchronization independently.
7. Enable synchronization only after confirming the intended behavior.

### Integration Safety

Cloud synchronization is disabled by default in the example configuration:

```dotenv
ZOHO_CATALYST_SYNC_ENABLED=false
```

Keep it disabled until the integration has been configured and tested.

**Important:** The existence of cloud function files and configuration does not, by itself, guarantee that a cloud deployment is active or that booking synchronization is working.

---

## Testing

The project includes a Laravel testing structure.

Run the available automated tests with:

```powershell
php artisan test
```

Alternatively, run PHPUnit directly:

```powershell
vendor/bin/phpunit
```

### Suggested Manual Testing

In addition to automated tests, verify:

- The application starts successfully.
- The configured database connection works.
- Migrations complete successfully.
- Relevant application pages load.
- Reservation-related workflows behave as expected.
- Database records are stored and retrieved correctly.
- Email-related workflows can be tested through Mailpit.
- Cloud integration authentication works when configured.
- Invalid requests and error conditions are handled appropriately.

Only describe a feature as tested or verified after running the corresponding test successfully.

---

## Security Considerations

Security is important for hotel management applications because booking systems may process personal and operational information.

Recommended practices include:

- Keep `.env` out of version control.
- Never commit credentials or shared secrets.
- Validate user input on the server.
- Use appropriate authentication and authorization.
- Apply authorization checks to sensitive hotel and reservation operations.
- Protect cloud endpoints with appropriate authentication.
- Avoid exposing stack traces and sensitive details in production.
- Use HTTPS in production deployments.
- Keep PHP and project dependencies updated.
- Back up production databases securely.
- Restrict access to development utilities and maintenance scripts.
- Review access permissions before deploying the application publicly.

For production, configure:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

Use a properly generated application key and production-specific database, email, and cloud settings.

---

## Troubleshooting

### Composer Dependency Errors

Run:

```powershell
composer install
```

If installation fails, review the PHP version, required extensions, and error message.

### Frontend Assets Not Loading

Run:

```powershell
npm install
npm run build
```

During development, use `npm run dev` when the application expects the Vite development server.

### Database Connection Errors

Check that:

- The SQLite file exists.
- The configured database path is correct.
- The database file is accessible.
- Required migrations have been executed.
- The environment configuration is correct.

After changing configuration, run:

```powershell
php artisan config:clear
```

### Application Key Missing

Run:

```powershell
php artisan key:generate
```

Ensure the local `.env` file exists before generating the key.

### Email Not Appearing in Mailpit

Check that:

- Mailpit is running.
- Laravel's SMTP settings match the Mailpit configuration.
- The application is using the intended mail configuration.
- The email workflow is being triggered successfully.

### Cloud Synchronization Not Working

Check that:

- The cloud function is deployed.
- The configured endpoint is correct.
- The required environment variables are present.
- Authentication headers match the server-side validation.
- The synchronization setting is enabled only when appropriate.
- The cloud function logs do not report errors.

---

## Future Enhancements

Potential future improvements include:

- Enhanced reservation lifecycle management.
- Room inventory and availability management.
- Guest profile management.
- Reservation search and filtering.
- Booking status notifications.
- Role-based access control.
- Hotel performance dashboards.
- Payment gateway integration.
- Improved reporting and analytics.
- Automated testing for critical workflows.
- Production deployment and monitoring.
- More robust cloud-based booking synchronization.
- Integration with external hotel booking platforms.

These are potential enhancements and should not be interpreted as features already implemented.

---

## License

This project includes a `LICENSE` file in the repository.

Refer to that file for the applicable license terms before reusing, modifying, or distributing the project.

---

## Author

**Hotelia PMS — Smart Hotel Reservation & Management Platform**

Developed as a web application project using Laravel and related technologies.

For source code, setup instructions, and project updates, refer to this GitHub repository.
