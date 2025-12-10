# REST API Setup Summary

## Backend (Laravel)

### ✅ Completed Tasks

1. **API Routes** (`backoffice/routes/api.php`)
   - Created versioned API routes with `v1` prefix
   - Public routes: `/api/v1/login`, `/api/v1/register`, `/api/v1/packages`
   - Protected routes: `/api/v1/user`, `/api/v1/logout`, `/api/v1/billing`

2. **Controllers**
   - `AuthController` (`backoffice/app/Http/Controllers/Api/V1/AuthController.php`)
     - Handles login, register, user info, logout, and billing
   - `PackageController` (`backoffice/app/Http/Controllers/Api/V1/PackageController.php`)
     - Returns available subscription packages

3. **Authentication**
   - Installed Laravel Sanctum for API token authentication
   - Updated User model to use `HasApiTokens` trait
   - Configured CORS middleware in `bootstrap/app.php`

4. **Database**
   - Ran migrations for Sanctum personal access tokens table

## Frontend (Next.js)

### ✅ Completed Tasks

1. **Axios Installation**
   - Installed axios package for HTTP requests

2. **API Service** (`frontend/lib/api.ts`)
   - Created centralized API service with:
     - Base URL configuration (`https://developer.taeab.com`)
     - Automatic token injection from localStorage
     - Request/response interceptors
     - Error handling (401 redirects to login)

3. **Updated Pages**
   - **Login** (`frontend/app/login/page.tsx`) - Connects to `/api/v1/login`
   - **Register** (`frontend/app/register/page.tsx`) - Connects to `/api/v1/register`
   - **Packages** (`frontend/app/packages/page.tsx`) - Fetches from `/api/v1/packages`
   - **Billing** (`frontend/app/billing/page.tsx`) - Fetches from `/api/v1/billing`

## API Endpoints

### Base URL
```
https://developer.taeab.com/api/v1
```

### Available Endpoints

#### Public Endpoints
- `POST /api/v1/login` - User login
- `POST /api/v1/register` - User registration
- `GET /api/v1/packages` - Get subscription packages

#### Protected Endpoints (Require Bearer Token)
- `GET /api/v1/user` - Get authenticated user
- `POST /api/v1/logout` - Logout user
- `GET /api/v1/billing` - Get billing information

## Usage Example

### Frontend API Call
```typescript
import { authAPI, packageAPI } from '@/lib/api';

// Login
const response = await authAPI.login('user@example.com', 'password');
localStorage.setItem('auth_token', response.data.token);

// Get packages
const packages = await packageAPI.getPackages();
console.log(packages.data.packages);
```

### Direct API Call (using axios)
```javascript
axios.get('https://developer.taeab.com/api/v1/packages')
  .then(response => console.log(response.data))
  .catch(error => console.error(error));
```

## Configuration

### Backend
- API routes are registered in `bootstrap/app.php`
- Sanctum is configured for token authentication
- CORS is enabled for API routes

### Frontend
- API base URL can be configured via `NEXT_PUBLIC_API_URL` environment variable
- Default: `https://developer.taeab.com`
- Tokens are stored in localStorage and automatically included in requests

## Next Steps

1. **Backend**
   - Update `.env` file with proper database configuration
   - Configure CORS allowed origins if needed
   - Add more API endpoints as needed

2. **Frontend**
   - Set `NEXT_PUBLIC_API_URL` in `.env.local` if using different API URL
   - Add error handling UI components
   - Implement token refresh logic if needed

3. **Testing**
   - Test all API endpoints
   - Verify authentication flow
   - Test error handling

## Files Created/Modified

### Backend
- `backoffice/routes/api.php` (created)
- `backoffice/app/Http/Controllers/Api/V1/AuthController.php` (created)
- `backoffice/app/Http/Controllers/Api/V1/PackageController.php` (created)
- `backoffice/bootstrap/app.php` (modified - added API routes and CORS)
- `backoffice/app/Models/User.php` (modified - added HasApiTokens trait)
- `backoffice/composer.json` (modified - added laravel/sanctum)

### Frontend
- `frontend/lib/api.ts` (created)
- `frontend/app/login/page.tsx` (modified - added API integration)
- `frontend/app/register/page.tsx` (modified - added API integration)
- `frontend/app/packages/page.tsx` (modified - added API integration)
- `frontend/app/billing/page.tsx` (modified - added API integration)
- `frontend/package.json` (modified - added axios)

