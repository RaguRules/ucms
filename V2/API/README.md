# Courts Management System API - README

## Overview

This repository contains a modern, robust RESTful API for an Integrated Courts Management System. The API provides endpoints for authentication, case management, staff management, and user management, following industry best practices for security, performance, and code organization.

## Features

- **Authentication System**
  - User registration and login
  - JWT-based authentication
  - Password reset functionality
  - Role-based access control

- **Case Management**
  - Create, read, update, and delete cases
  - Case status tracking and history
  - Case filtering and pagination
  - Case type and status management

- **Staff Management**
  - Create, read, update, and delete staff records
  - Staff assignments to cases
  - Department and position management
  - Staff status tracking

- **User Management**
  - Create, read, update, and delete users
  - User role and permission management
  - Password management
  - User status control

## Technical Specifications

- **Architecture**: RESTful API following MVC pattern
- **Language**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Authentication**: JWT (JSON Web Tokens)
- **Documentation**: Comprehensive API documentation in Markdown

## Security Features

- Prepared statements to prevent SQL injection
- Input validation and sanitization
- CSRF protection
- Password hashing with bcrypt
- Role-based access control
- Rate limiting
- Secure file uploads

## Directory Structure

```
courts-api-system/
├── src/
│   ├── api/
│   │   └── v1/
│   │       ├── auth/
│   │       ├── cases/
│   │       ├── staff/
│   │       └── users/
│   ├── core/
│   │   ├── Auth.php
│   │   ├── Database.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   └── Validator.php
│   ├── middleware/
│   │   ├── AuthMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── models/
│   │   ├── Case.php
│   │   ├── Staff.php
│   │   └── User.php
│   └── config/
│       ├── auth.php
│       └── database.php
├── public/
│   └── uploads/
│       └── staff/
├── vendor/
├── .env
├── .env.example
├── api_documentation.md
├── composer.json
├── index.php
└── test.php
```

## Installation

1. Clone the repository
2. Run `composer install` to install dependencies
3. Copy `.env.example` to `.env` and configure your database settings
4. Import the database schema from `courtsmanagement.sql`
5. Configure your web server to point to the `public` directory
6. Ensure the `uploads` directory is writable by the web server

## API Documentation

Comprehensive API documentation is available in the `api_documentation.md` file. This documentation includes:

- Base URL information
- Authentication details
- Endpoint descriptions
- Request parameters
- Response formats
- Error handling
- HTTP status codes
- Rate limiting information

## Testing

A test script is included to verify the functionality of the API. To run the tests:

1. Start your web server
2. Update the base URL in `test.php` if needed
3. Run `php test.php` from the command line
4. Review the test results in the console and in `test_results.json`

## Security Considerations

- Always use HTTPS in production
- Regularly update dependencies
- Monitor logs for suspicious activity
- Implement additional security measures as needed (e.g., IP whitelisting, 2FA)
- Regularly backup the database

## Performance Optimization

- Database query optimization
- Response caching
- Rate limiting to prevent abuse
- Efficient error handling
- Optimized file uploads

## License

This project is licensed under the MIT License - see the LICENSE file for details.
