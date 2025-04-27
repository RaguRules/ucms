# Courts Management System API Documentation

## Overview

This document provides comprehensive documentation for the Courts Management System API. The API follows RESTful principles and provides endpoints for authentication, case management, staff management, and user management.

## Base URL

All API endpoints are prefixed with:

```
/api/v1
```

## Authentication

The API uses JWT (JSON Web Token) for authentication. To access protected endpoints, you must include the JWT token in the Authorization header of your requests.

```
Authorization: Bearer {your_token}
```

### Authentication Endpoints

#### Register a New User

- **URL**: `/auth/register`
- **Method**: POST
- **Auth Required**: No
- **Parameters**:
  - `username` (string, required): User's unique username
  - `email` (string, required): User's email address
  - `password` (string, required): User's password (min 8 characters)
  - `full_name` (string, required): User's full name
  - `role` (string, required): User's role (user, staff, admin)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User registered successfully",
    "data": {
      "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "full_name": "John Doe",
        "role": "user",
        "status": "active",
        "created_at": "2025-04-20 09:00:00"
      },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
  }
  ```
- **Error Response**: 400 Bad Request, 409 Conflict

#### Login

- **URL**: `/auth/login`
- **Method**: POST
- **Auth Required**: No
- **Parameters**:
  - `username` (string, required): User's username or email
  - `password` (string, required): User's password
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Login successful",
    "data": {
      "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "full_name": "John Doe",
        "role": "user",
        "status": "active",
        "last_login": "2025-04-20 09:00:00",
        "created_at": "2025-04-20 09:00:00"
      },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized

#### Get User Profile

- **URL**: `/auth/profile`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Profile retrieved successfully",
    "data": {
      "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "full_name": "John Doe",
        "role": "user",
        "status": "active",
        "last_login": "2025-04-20 09:00:00",
        "created_at": "2025-04-20 09:00:00"
      }
    }
  }
  ```
- **Error Response**: 401 Unauthorized

#### Request Password Reset

- **URL**: `/auth/password/reset-request`
- **Method**: POST
- **Auth Required**: No
- **Parameters**:
  - `email` (string, required): User's email address
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Password reset instructions sent to your email"
  }
  ```
- **Error Response**: 400 Bad Request

#### Reset Password

- **URL**: `/auth/password/reset`
- **Method**: POST
- **Auth Required**: No
- **Parameters**:
  - `token` (string, required): Reset token from email
  - `password` (string, required): New password
  - `password_confirmation` (string, required): Confirm new password
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Password reset successfully"
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized

#### Logout

- **URL**: `/auth/logout`
- **Method**: POST
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Logout successful"
  }
  ```
- **Error Response**: 401 Unauthorized

## Case Management

### Case Endpoints

#### Get All Cases

- **URL**: `/cases`
- **Method**: GET
- **Auth Required**: Yes
- **Query Parameters**:
  - `page` (integer, optional): Page number (default: 1)
  - `limit` (integer, optional): Items per page (default: 10)
  - `search` (string, optional): Search term
  - `status` (string, optional): Filter by status
  - `type` (string, optional): Filter by type
  - `date_from` (date, optional): Filter by filing date from
  - `date_to` (date, optional): Filter by filing date to
  - `judge_id` (integer, optional): Filter by judge
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Cases retrieved successfully",
    "data": {
      "data": [
        {
          "id": 1,
          "case_number": "CIV-2025-001",
          "title": "Smith vs. Johnson",
          "description": "Civil dispute over property boundaries",
          "type": "civil",
          "status": "active",
          "filing_date": "2025-04-01",
          "plaintiff": "John Smith",
          "defendant": "Robert Johnson",
          "judge_id": 5,
          "priority": "medium",
          "created_at": "2025-04-01 10:00:00",
          "updated_at": null
        },
        // More cases...
      ],
      "pagination": {
        "total": 50,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 5,
        "has_more": true
      }
    }
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Case Types

- **URL**: `/cases/types`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case types retrieved successfully",
    "data": [
      "civil",
      "criminal",
      "family",
      "probate",
      "juvenile",
      "traffic",
      "small_claims",
      "administrative"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Case Statuses

- **URL**: `/cases/statuses`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case statuses retrieved successfully",
    "data": [
      "pending",
      "active",
      "scheduled",
      "continued",
      "dismissed",
      "closed",
      "appealed",
      "archived"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Case Details

- **URL**: `/cases/{id}`
- **Method**: GET
- **Auth Required**: Yes
- **URL Parameters**:
  - `id` (integer, required): Case ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case retrieved successfully",
    "data": {
      "case": {
        "id": 1,
        "case_number": "CIV-2025-001",
        "title": "Smith vs. Johnson",
        "description": "Civil dispute over property boundaries",
        "type": "civil",
        "status": "active",
        "filing_date": "2025-04-01",
        "plaintiff": "John Smith",
        "defendant": "Robert Johnson",
        "judge_id": 5,
        "priority": "medium",
        "created_at": "2025-04-01 10:00:00",
        "updated_at": null
      },
      "history": [
        {
          "id": 1,
          "case_id": 1,
          "status": "pending",
          "notes": "Case filed",
          "created_at": "2025-04-01 10:00:00"
        },
        {
          "id": 2,
          "case_id": 1,
          "status": "active",
          "notes": "Case accepted",
          "created_at": "2025-04-05 14:30:00"
        }
      ]
    }
  }
  ```
- **Error Response**: 401 Unauthorized, 404 Not Found

#### Get Case History

- **URL**: `/cases/{id}/history`
- **Method**: GET
- **Auth Required**: Yes
- **URL Parameters**:
  - `id` (integer, required): Case ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case history retrieved successfully",
    "data": [
      {
        "id": 1,
        "case_id": 1,
        "status": "pending",
        "notes": "Case filed",
        "created_at": "2025-04-01 10:00:00"
      },
      {
        "id": 2,
        "case_id": 1,
        "status": "active",
        "notes": "Case accepted",
        "created_at": "2025-04-05 14:30:00"
      }
    ]
  }
  ```
- **Error Response**: 401 Unauthorized, 404 Not Found

#### Create Case

- **URL**: `/cases`
- **Method**: POST
- **Auth Required**: Yes (admin or staff role)
- **Parameters**:
  - `case_number` (string, required): Unique case number
  - `title` (string, required): Case title
  - `description` (string, required): Case description
  - `type` (string, required): Case type
  - `status` (string, required): Case status
  - `filing_date` (date, required): Filing date (YYYY-MM-DD)
  - `plaintiff` (string, required): Plaintiff name
  - `defendant` (string, required): Defendant name
  - `judge_id` (integer, optional): Judge ID
  - `priority` (string, optional): Case priority (low, medium, high)
- **Success Response**: 201 Created
  ```json
  {
    "status": "success",
    "message": "Case created successfully",
    "data": {
      "id": 1,
      "case_number": "CIV-2025-001",
      "title": "Smith vs. Johnson",
      "description": "Civil dispute over property boundaries",
      "type": "civil",
      "status": "pending",
      "filing_date": "2025-04-01",
      "plaintiff": "John Smith",
      "defendant": "Robert Johnson",
      "judge_id": 5,
      "priority": "medium",
      "created_at": "2025-04-20 09:30:00",
      "updated_at": null
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden

#### Update Case

- **URL**: `/cases/{id}`
- **Method**: PUT
- **Auth Required**: Yes (admin or staff role)
- **URL Parameters**:
  - `id` (integer, required): Case ID
- **Parameters**: Same as Create Case (all optional)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case updated successfully",
    "data": {
      "id": 1,
      "case_number": "CIV-2025-001",
      "title": "Smith vs. Johnson",
      "description": "Updated description",
      "type": "civil",
      "status": "active",
      "filing_date": "2025-04-01",
      "plaintiff": "John Smith",
      "defendant": "Robert Johnson",
      "judge_id": 5,
      "priority": "high",
      "created_at": "2025-04-01 10:00:00",
      "updated_at": "2025-04-20 09:45:00"
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Delete Case

- **URL**: `/cases/{id}`
- **Method**: DELETE
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): Case ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case deleted successfully"
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Update Case Status

- **URL**: `/cases/{id}/status`
- **Method**: PUT
- **Auth Required**: Yes (admin or staff role)
- **URL Parameters**:
  - `id` (integer, required): Case ID
- **Parameters**:
  - `status` (string, required): New status
  - `notes` (string, optional): Status change notes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Case status updated successfully",
    "data": {
      "id": 1,
      "case_number": "CIV-2025-001",
      "title": "Smith vs. Johnson",
      "description": "Civil dispute over property boundaries",
      "type": "civil",
      "status": "closed",
      "filing_date": "2025-04-01",
      "plaintiff": "John Smith",
      "defendant": "Robert Johnson",
      "judge_id": 5,
      "priority": "medium",
      "created_at": "2025-04-01 10:00:00",
      "updated_at": "2025-04-20 10:00:00"
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

## Staff Management

### Staff Endpoints

#### Get All Staff

- **URL**: `/staff`
- **Method**: GET
- **Auth Required**: Yes
- **Query Parameters**:
  - `page` (integer, optional): Page number (default: 1)
  - `limit` (integer, optional): Items per page (default: 10)
  - `search` (string, optional): Search term
  - `department` (string, optional): Filter by department
  - `position` (string, optional): Filter by position
  - `status` (string, optional): Filter by status
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff members retrieved successfully",
    "data": {
      "data": [
        {
          "id": 1,
          "first_name": "Jane",
          "last_name": "Smith",
          "email": "jane.smith@courts.gov",
          "phone": "555-123-4567",
          "nic": "1234567890",
          "address": "123 Main St, Anytown, USA",
          "department": "civil",
          "position": "judge",
          "join_date": "2020-01-15",
          "photo": "/uploads/staff/1650123456_jane_smith.jpg",
          "status": "active",
          "created_at": "2020-01-15 09:00:00",
          "updated_at": null
        },
        // More staff members...
      ],
      "pagination": {
        "total": 25,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 3,
        "has_more": true
      }
    }
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Staff Departments

- **URL**: `/staff/departments`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Departments retrieved successfully",
    "data": [
      "administration",
      "civil",
      "criminal",
      "family",
      "probate",
      "juvenile",
      "traffic",
      "records",
      "it",
      "security"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Staff Positions

- **URL**: `/staff/positions`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Positions retrieved successfully",
    "data": [
      "judge",
      "clerk",
      "bailiff",
      "court_reporter",
      "administrator",
      "attorney",
      "paralegal",
      "it_specialist",
      "security_officer",
      "records_manager"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Staff Statuses

- **URL**: `/staff/statuses`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Statuses retrieved successfully",
    "data": [
      "active",
      "on_leave",
      "suspended",
      "retired",
      "terminated"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get Staff Details

- **URL**: `/staff/{id}`
- **Method**: GET
- **Auth Required**: Yes
- **URL Parameters**:
  - `id` (integer, required): Staff ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff member retrieved successfully",
    "data": {
      "id": 1,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@courts.gov",
      "phone": "555-123-4567",
      "nic": "1234567890",
      "address": "123 Main St, Anytown, USA",
      "department": "civil",
      "position": "judge",
      "join_date": "2020-01-15",
      "photo": "/uploads/staff/1650123456_jane_smith.jpg",
      "status": "active",
      "created_at": "2020-01-15 09:00:00",
      "updated_at": null
    }
  }
  ```
- **Error Response**: 401 Unauthorized, 404 Not Found

#### Get Staff Assignments

- **URL**: `/staff/{id}/assignments`
- **Method**: GET
- **Auth Required**: Yes
- **URL Parameters**:
  - `id` (integer, required): Staff ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff assignments retrieved successfully",
    "data": [
      {
        "id": 1,
        "staff_id": 1,
        "case_id": 5,
        "role": "judge",
        "start_date": "2025-04-01",
        "end_date": null,
        "created_at": "2025-04-01 10:00:00",
        "case_number": "CIV-2025-005",
        "title": "Adams vs. Wilson",
        "status": "active"
      },
      // More assignments...
    ]
  }
  ```
- **Error Response**: 401 Unauthorized, 404 Not Found

#### Create Staff

- **URL**: `/staff`
- **Method**: POST
- **Auth Required**: Yes (admin role)
- **Parameters**:
  - `first_name` (string, required): First name
  - `last_name` (string, required): Last name
  - `email` (string, required): Email address
  - `phone` (string, required): Phone number
  - `nic` (string, required): National ID Card number
  - `address` (string, required): Address
  - `department` (string, required): Department
  - `position` (string, required): Position
  - `join_date` (date, required): Join date (YYYY-MM-DD)
  - `photo` (file, optional): Staff photo
  - `status` (string, optional): Status (default: active)
- **Success Response**: 201 Created
  ```json
  {
    "status": "success",
    "message": "Staff member created successfully",
    "data": {
      "id": 26,
      "first_name": "Michael",
      "last_name": "Johnson",
      "email": "michael.johnson@courts.gov",
      "phone": "555-987-6543",
      "nic": "0987654321",
      "address": "456 Oak St, Anytown, USA",
      "department": "criminal",
      "position": "attorney",
      "join_date": "2025-04-15",
      "photo": "/uploads/staff/1650123789_michael_johnson.jpg",
      "status": "active",
      "created_at": "2025-04-20 10:30:00",
      "updated_at": null
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden

#### Update Staff

- **URL**: `/staff/{id}`
- **Method**: PUT
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): Staff ID
- **Parameters**: Same as Create Staff (all optional)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff member updated successfully",
    "data": {
      "id": 1,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane.smith@courts.gov",
      "phone": "555-123-9999",
      "nic": "1234567890",
      "address": "789 Pine St, Anytown, USA",
      "department": "civil",
      "position": "judge",
      "join_date": "2020-01-15",
      "photo": "/uploads/staff/1650123456_jane_smith.jpg",
      "status": "active",
      "created_at": "2020-01-15 09:00:00",
      "updated_at": "2025-04-20 10:45:00"
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Delete Staff

- **URL**: `/staff/{id}`
- **Method**: DELETE
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): Staff ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff member deleted successfully"
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Assign Staff to Case

- **URL**: `/staff/{id}/assignments`
- **Method**: POST
- **Auth Required**: Yes (admin or manager role)
- **URL Parameters**:
  - `id` (integer, required): Staff ID
- **Parameters**:
  - `case_id` (integer, required): Case ID
  - `role` (string, required): Role in the case
  - `start_date` (date, required): Start date (YYYY-MM-DD)
  - `end_date` (date, optional): End date (YYYY-MM-DD)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff assigned to case successfully",
    "data": [
      {
        "id": 5,
        "staff_id": 1,
        "case_id": 10,
        "role": "judge",
        "start_date": "2025-04-20",
        "end_date": null,
        "created_at": "2025-04-20 11:00:00",
        "case_number": "CIV-2025-010",
        "title": "Brown vs. Davis",
        "status": "pending"
      },
      // More assignments...
    ]
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Remove Staff from Case

- **URL**: `/staff/{id}/assignments/{case_id}`
- **Method**: DELETE
- **Auth Required**: Yes (admin or manager role)
- **URL Parameters**:
  - `id` (integer, required): Staff ID
  - `case_id` (integer, required): Case ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Staff removed from case successfully"
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden, 404 Not Found

## User Management

### User Endpoints

#### Get All Users

- **URL**: `/users`
- **Method**: GET
- **Auth Required**: Yes (admin role)
- **Query Parameters**:
  - `page` (integer, optional): Page number (default: 1)
  - `limit` (integer, optional): Items per page (default: 10)
  - `search` (string, optional): Search term
  - `role` (string, optional): Filter by role
  - `status` (string, optional): Filter by status
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Users retrieved successfully",
    "data": {
      "data": [
        {
          "id": 1,
          "username": "admin",
          "email": "admin@courts.gov",
          "full_name": "System Administrator",
          "role": "admin",
          "status": "active",
          "last_login": "2025-04-20 08:00:00",
          "created_at": "2020-01-01 00:00:00",
          "updated_at": null
        },
        // More users...
      ],
      "pagination": {
        "total": 15,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 2,
        "has_more": true
      }
    }
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden

#### Get User Roles

- **URL**: `/users/roles`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User roles retrieved successfully",
    "data": [
      "user",
      "staff",
      "manager",
      "admin"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get User Statuses

- **URL**: `/users/statuses`
- **Method**: GET
- **Auth Required**: Yes
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User statuses retrieved successfully",
    "data": [
      "active",
      "inactive",
      "suspended",
      "locked"
    ]
  }
  ```
- **Error Response**: 401 Unauthorized

#### Get User Details

- **URL**: `/users/{id}`
- **Method**: GET
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): User ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User retrieved successfully",
    "data": {
      "id": 2,
      "username": "johndoe",
      "email": "john@example.com",
      "full_name": "John Doe",
      "role": "staff",
      "status": "active",
      "last_login": "2025-04-19 15:30:00",
      "created_at": "2023-05-10 09:00:00",
      "updated_at": null
    }
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Create User

- **URL**: `/users`
- **Method**: POST
- **Auth Required**: Yes (admin role)
- **Parameters**:
  - `username` (string, required): Username
  - `email` (string, required): Email address
  - `password` (string, required): Password (min 8 characters)
  - `full_name` (string, required): Full name
  - `role` (string, required): Role
  - `status` (string, optional): Status (default: active)
- **Success Response**: 201 Created
  ```json
  {
    "status": "success",
    "message": "User created successfully",
    "data": {
      "id": 16,
      "username": "sarahwilson",
      "email": "sarah@example.com",
      "full_name": "Sarah Wilson",
      "role": "staff",
      "status": "active",
      "last_login": null,
      "created_at": "2025-04-20 11:30:00",
      "updated_at": null
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 409 Conflict

#### Update User

- **URL**: `/users/{id}`
- **Method**: PUT
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): User ID
- **Parameters**: Same as Create User (all optional)
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User updated successfully",
    "data": {
      "id": 2,
      "username": "johndoe",
      "email": "john.doe@example.com",
      "full_name": "John A. Doe",
      "role": "manager",
      "status": "active",
      "last_login": "2025-04-19 15:30:00",
      "created_at": "2023-05-10 09:00:00",
      "updated_at": "2025-04-20 11:45:00"
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict

#### Delete User

- **URL**: `/users/{id}`
- **Method**: DELETE
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): User ID
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User deleted successfully"
  }
  ```
- **Error Response**: 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Update User Status

- **URL**: `/users/{id}/status`
- **Method**: PUT
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): User ID
- **Parameters**:
  - `status` (string, required): New status
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "User status updated successfully",
    "data": {
      "id": 3,
      "username": "janedoe",
      "email": "jane@example.com",
      "full_name": "Jane Doe",
      "role": "user",
      "status": "suspended",
      "last_login": "2025-04-18 10:15:00",
      "created_at": "2023-06-20 14:00:00",
      "updated_at": "2025-04-20 12:00:00"
    }
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

#### Change User Password

- **URL**: `/users/{id}/password`
- **Method**: PUT
- **Auth Required**: Yes (admin role)
- **URL Parameters**:
  - `id` (integer, required): User ID
- **Parameters**:
  - `password` (string, required): New password
  - `password_confirmation` (string, required): Confirm new password
- **Success Response**: 200 OK
  ```json
  {
    "status": "success",
    "message": "Password changed successfully"
  }
  ```
- **Error Response**: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found

## Error Responses

All API endpoints return standardized error responses:

```json
{
  "status": "error",
  "message": "Error message"
}
```

For validation errors:

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "Error message for field"
    ]
  }
}
```

## HTTP Status Codes

- `200 OK`: Request succeeded
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Permission denied
- `404 Not Found`: Resource not found
- `409 Conflict`: Resource conflict (e.g., duplicate username)
- `422 Unprocessable Entity`: Validation failed
- `500 Internal Server Error`: Server error

## Rate Limiting

API requests are subject to rate limiting to prevent abuse. The current limits are:

- 60 requests per minute for authenticated users
- 30 requests per minute for unauthenticated users

When rate limit is exceeded, the API will return a `429 Too Many Requests` status code.
