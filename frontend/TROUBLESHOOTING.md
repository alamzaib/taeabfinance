# Troubleshooting API Connection Issues

## Network Error when fetching packages

If you're seeing a "Network Error" when trying to fetch packages, check the following:

### 1. Verify Backend Server is Running
- Make sure Laravel backend is running
- Check if you can access: `http://backoffice.taeab.local/api/v1/packages` directly in your browser
- Should return JSON data

### 2. Check API URL Configuration
The frontend uses `http://backoffice.taeab.local` as the default API URL. 

To change it, create a `.env.local` file in the `frontend` directory:
```
NEXT_PUBLIC_API_URL=http://backoffice.taeab.local
```

Or if your Laravel runs on a different port:
```
NEXT_PUBLIC_API_URL=http://localhost:8000
```

### 3. CORS Configuration
The backend CORS is configured in `backoffice/config/cors.php`. Make sure your frontend URL is in the `allowed_origins` array.

Common frontend URLs:
- `http://localhost:3000` (Next.js default)
- `http://localhost:3001`
- `http://127.0.0.1:3000`

### 4. Check Browser Console
Open browser DevTools (F12) and check:
- **Console tab**: Look for CORS errors or network errors
- **Network tab**: Check if the request is being made and what the response is

### 5. Verify Domain Resolution
If using `backoffice.taeab.local`, make sure:
- It's added to your `hosts` file (Windows: `C:\Windows\System32\drivers\etc\hosts`)
- Points to `127.0.0.1` or your server IP

### 6. Test API Endpoint Directly
Try accessing these URLs directly in your browser:
- `http://backoffice.taeab.local/api/v1/packages`
- `http://localhost:8000/api/v1/packages` (if using Laravel's built-in server)

### 7. Check Laravel Logs
Check `backoffice/storage/logs/laravel.log` for any errors.

### 8. Restart Servers
Sometimes a simple restart helps:
```bash
# Restart Laravel (if using artisan serve)
php artisan serve

# Restart Next.js
npm run dev
```

## Common Solutions

### Solution 1: Use localhost instead of custom domain
If `backoffice.taeab.local` doesn't work, try using `localhost:8000`:

1. Update `frontend/.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost:8000
```

2. Make sure Laravel is running on port 8000:
```bash
cd backoffice
php artisan serve
```

### Solution 2: Add your frontend URL to CORS
Edit `backoffice/config/cors.php` and add your frontend URL to `allowed_origins` array.

### Solution 3: Check if packages exist in database
Run the seeder to create sample packages:
```bash
cd backoffice
php artisan db:seed --class=PackageSeeder
```

Or create packages via the admin panel at `http://backoffice.taeab.local/packages`

