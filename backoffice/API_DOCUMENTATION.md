# API Documentation

## Base URL
```
https://developer.taeab.com/api/v1
```

## Authentication

Most endpoints require authentication using Bearer tokens. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### POST /login
Login and receive authentication token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

#### POST /register
Register a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

#### GET /user
Get authenticated user information.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

#### POST /logout
Logout and invalidate current token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

### Packages

#### GET /packages
Get all available subscription packages.

**Response:**
```json
{
  "success": true,
  "data": {
    "packages": [
      {
        "id": 1,
        "name": "Basic",
        "price": 9.99,
        "currency": "USD",
        "period": "month",
        "features": [
          "Track up to 50 transactions",
          "Basic budgeting tools",
          "Email support",
          "Mobile app access",
          "Monthly reports"
        ],
        "popular": false
      },
      {
        "id": 2,
        "name": "Professional",
        "price": 19.99,
        "currency": "USD",
        "period": "month",
        "features": [
          "Unlimited transactions",
          "Advanced budgeting & analytics",
          "Priority support",
          "Mobile app access",
          "Custom reports",
          "Export data",
          "Multi-account support"
        ],
        "popular": true
      },
      {
        "id": 3,
        "name": "Enterprise",
        "price": 49.99,
        "currency": "USD",
        "period": "month",
        "features": [
          "Everything in Professional",
          "Dedicated account manager",
          "24/7 phone support",
          "Custom integrations",
          "Team collaboration",
          "Advanced security",
          "API access"
        ],
        "popular": false
      }
    ]
  }
}
```

### Billing

#### GET /billing
Get billing information for authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_plan": "Professional",
    "amount": 19.99,
    "currency": "USD",
    "next_billing_date": "2024-02-15",
    "billing_history": []
  }
}
```

## Error Responses

All errors follow this format:

```json
{
  "message": "Error message here",
  "errors": {
    "field": ["Error message for field"]
  }
}
```

**HTTP Status Codes:**
- 200: Success
- 201: Created
- 401: Unauthorized
- 422: Validation Error
- 500: Server Error

## Frontend Integration

The frontend uses axios to connect to the API. Example usage:

```typescript
import { authAPI, packageAPI } from '@/lib/api';

// Login
const response = await authAPI.login(email, password);
localStorage.setItem('auth_token', response.data.token);

// Get packages
const packages = await packageAPI.getPackages();
```

The API service automatically includes the authentication token in requests when available in localStorage.

