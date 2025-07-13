# Admin Dashboard Installation and Usage Guide

## Overview
This document provides instructions for installing and using the Unified Courts Management System Admin Dashboard. The dashboard provides role-based access for different user types (Administrator, Hon. Judge, The Registrar, Interpreter, Other Staff, Lawyer, Police) with appropriate permissions and features for each role.

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Existing Unified Courts Management System database

### Installation Steps

1. **Copy Files**: Copy the entire `admin_dashboard` directory to your web server's document root.

2. **Configure Database Connection**: 
   - Open `config/database.php`
   - Update the database connection parameters to match your environment:
     ```php
     $host = "localhost";
     $username = "your_db_username";
     $password = "your_db_password";
     $database = "courtsmanagement";
     ```

3. **Set Permissions**: Ensure the web server has appropriate read/write permissions for the dashboard directory.

4. **Access the Dashboard**: Navigate to `http://your-server/admin_dashboard/` in your web browser.

## Features

### Authentication
- Secure login system with role-based access control
- Password management
- Session handling

### Dashboard
- Role-specific dashboard with relevant statistics and quick actions
- Light/dark mode toggle
- Responsive design for all device sizes

### User Management
- Profile management for all users
- Staff management (Administrator only)
- Registration approval system (Administrator only)

### Case Management
- View and manage cases based on role permissions
- Filter and search functionality
- Detailed case views with related information

### Role-Based Access
The dashboard implements a comprehensive role-based access control system with the following roles:

1. **Administrator (R01)**
   - Full access to all system features
   - User management
   - System configuration
   - Reports generation

2. **Hon. Judge (R02)**
   - View and edit cases
   - Create judgements and orders
   - Issue warrants
   - Manage personal notes

3. **The Registrar (R03)**
   - Register and manage cases
   - Manage appeals and motions
   - Handle case activities and notifications
   - Process fines

4. **Interpreter (R04)**
   - View assigned cases
   - View case activities
   - Add and manage notes

5. **Other Staff (R05)**
   - View cases and activities
   - View notifications
   - Manage personal profile

6. **Lawyer (R06)**
   - View assigned cases
   - Submit motions
   - Add case notes

7. **Police (R07)**
   - View cases
   - Manage warrants
   - Update warrant status

## Integration with Existing System
The dashboard integrates seamlessly with the existing Unified Courts Management System through:

- Shared database access
- User role mapping
- Consistent data handling
- Synchronized user management

## Customization

### Theme Customization
- Edit `assets/css/dashboard.css` to modify the dashboard appearance
- Edit `assets/css/login.css` to modify the login page appearance

### Adding New Features
1. Create a new page file in the `pages` directory
2. Add the page to the sidebar menu in `includes/sidebar.php`
3. Add appropriate permissions in `includes/rbac.php`

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/database.php`
   - Ensure the database server is running

2. **Permission Denied**
   - Check if the user has appropriate role permissions
   - Verify RBAC settings in `includes/rbac.php`

3. **Page Not Found**
   - Ensure the page file exists in the `pages` directory
   - Check if the page is properly included in `index.php`

### Running Tests
A test script is included to verify the dashboard functionality:

1. Navigate to the dashboard directory in your terminal
2. Run `php test.php` to execute tests
3. Review the test results for any issues

## Support
For additional support or questions, please contact the system administrator.
