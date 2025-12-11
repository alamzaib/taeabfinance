# Admin Panel Setup Guide

## Overview

The admin panel is located at `/backoffice` and provides comprehensive management tools for:
- User Management
- Roles & Permissions
- Packages
- Payments
- Refund Requests
- Support Tickets
- Logs
- Analytics

## Technologies Used

- **Laravel AdminLTE** - Admin panel UI framework
- **Spatie Role & Permission** - Role-based access control
- **Tabulator** - Advanced data tables
- **Chart.js** - Analytics charts

## Setup Instructions

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Create Admin User

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Default Admin Credentials:**
- Email: `admin@taeab.com`
- Password: `password`

⚠️ **Important:** Change the password immediately after first login!

### 3. Access Admin Panel

Navigate to: `http://your-domain/backoffice/login`

## Features

### User Management
- View all users in Tabulator table
- Create, edit, delete users
- Assign roles to users
- Toggle user status

### Roles & Permissions
- Create and manage roles
- Assign permissions to roles
- View role assignments

### Packages Management
- Create subscription packages
- Set pricing and features
- Mark packages as popular
- Toggle package active status

### Payments
- View all payment transactions
- Filter by status (pending, completed, failed, refunded)
- View payment details
- Process refunds

### Refund Requests
- View all refund requests
- Approve or reject refunds
- Add admin notes
- Track refund status

### Support Tickets
- View all support tickets
- Assign tickets to admins
- Update ticket status
- Reply to tickets
- Filter by priority and status

### Logs
- View system log files
- Download log files
- View log content

### Analytics
- Revenue by month chart
- User growth chart
- Payment status distribution
- Package popularity statistics

## Routes

All admin routes are prefixed with `/backoffice`:

- `/backoffice` - Dashboard
- `/backoffice/login` - Admin login
- `/backoffice/users` - User management
- `/backoffice/roles` - Roles & permissions
- `/backoffice/packages` - Packages management
- `/backoffice/payments` - Payments
- `/backoffice/refunds` - Refund requests
- `/backoffice/support-tickets` - Support tickets
- `/backoffice/logs` - System logs
- `/backoffice/analytics` - Analytics dashboard

## Middleware

The `AdminMiddleware` protects all admin routes and ensures only users with the `admin` role can access them.

## Tabulator Integration

All list views use Tabulator for:
- Sorting
- Filtering
- Pagination
- Search
- Responsive design

Data is loaded via AJAX from the controllers.

## Customization

### Adding New Menu Items

Edit `config/adminlte.php` and add items to the `menu` array:

```php
[
    'text' => 'Your Menu Item',
    'url' => '/backoffice/your-route',
    'icon' => 'fas fa-fw fa-icon',
],
```

### Adding Permissions

Create new permissions in the seeder or via Spatie:

```php
Permission::create(['name' => 'your-permission']);
```

Then check in views:

```php
@can('your-permission')
    // Content
@endcan
```

## Security Notes

1. Always use HTTPS in production
2. Change default admin password
3. Regularly review user roles and permissions
4. Monitor admin access logs
5. Use strong passwords for admin accounts

## Troubleshooting

### Can't access admin panel
- Ensure you have the `admin` role assigned
- Check middleware is registered correctly
- Verify routes are loaded

### Tabulator not loading
- Check browser console for errors
- Verify CDN links are accessible
- Ensure AJAX endpoints return JSON

### Charts not displaying
- Check Chart.js CDN is loaded
- Verify data format matches Chart.js requirements
- Check browser console for errors

