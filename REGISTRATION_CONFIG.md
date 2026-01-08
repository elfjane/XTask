# User Registration Feature Configuration

This document explains how to configure the user registration feature in XTask.

## Overview

XTask provides a configurable user registration system that can be controlled via environment variables in both the frontend and backend. This allows administrators to enable or disable user registration based on their requirements.

## Configuration

### Backend Configuration

Edit your backend `.env` file:

```env
ALLOW_REGISTRATION=true
```

- **`true`**: Registration API endpoint is enabled. Users can create new accounts.
- **`false`**: Registration API endpoint returns a 403 error with the message "Registration is temporarily disabled."

### Frontend Configuration

Edit your frontend `.env` file:

```env
NUXT_PUBLIC_ALLOW_REGISTRATION=true
```

- **`true`**: Registration link and page are visible to users.
- **`false`**: Registration link is hidden from the login page.

## Usage Scenarios

### Scenario 1: Registration Fully Enabled
```env
# Backend .env
ALLOW_REGISTRATION=true

# Frontend .env
NUXT_PUBLIC_ALLOW_REGISTRATION=true
```
**Result**: Users can see the registration link and successfully create accounts.

### Scenario 2: Registration Fully Disabled
```env
# Backend .env
ALLOW_REGISTRATION=false

# Frontend .env
NUXT_PUBLIC_ALLOW_REGISTRATION=false
```
**Result**: Registration link is hidden, and the API endpoint is disabled.

### Scenario 3: Frontend Enabled, Backend Disabled
```env
# Backend .env
ALLOW_REGISTRATION=false

# Frontend .env
NUXT_PUBLIC_ALLOW_REGISTRATION=true
```
**Result**: Users can see the registration link and access the registration page, but when they attempt to register, they will receive an error message: "Registration is temporarily disabled. Please contact the administrator."

This scenario is useful when you want to inform users that registration is temporarily unavailable rather than hiding the feature completely.

### Scenario 4: Frontend Disabled, Backend Enabled
```env
# Backend .env
ALLOW_REGISTRATION=true

# Frontend .env
NUXT_PUBLIC_ALLOW_REGISTRATION=false
```
**Result**: Registration link is hidden from the UI, but the API endpoint remains functional. This can be useful if you want to allow registration only through direct API calls (e.g., for administrative purposes or third-party integrations).

## API Endpoint

### Register a New User

**Endpoint**: `POST /api/register`

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success Response (201)**:
```json
{
  "token": "1|abcdef123456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-01-08T17:30:00.000000Z",
    "updated_at": "2026-01-08T17:30:00.000000Z"
  }
}
```

**Error Response - Registration Disabled (403)**:
```json
{
  "message": "Registration is temporarily disabled."
}
```

**Error Response - Validation Error (422)**:
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

## Validation Rules

- **name**: Required, string, max 255 characters
- **email**: Required, valid email format, unique in database
- **password**: Required, minimum 8 characters, must match confirmation
- **password_confirmation**: Required, must match password

## Testing

Run the registration tests:

```bash
cd backend
php artisan test --filter RegisterTest
```

All tests should pass, covering scenarios including:
- Successful registration when enabled
- Registration blocked when disabled
- Validation errors
- Token generation and authentication

## Multi-language Support

The registration feature supports both English and Traditional Chinese:

- **English**: "Register", "Create Account", "Registration is temporarily disabled"
- **繁體中文**: "註冊", "建立帳號", "註冊功能暫時關閉"

Users can switch languages using the language selector in the header.
