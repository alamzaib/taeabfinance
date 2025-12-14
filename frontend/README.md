# Taeab Frontend

Next.js frontend application for Taeab platform.

## Getting Started

### Prerequisites

- Node.js 18.x or later
- npm or yarn

### Installation

1. Install dependencies:
```bash
npm install
```

2. Run the development server:
```bash
npm run dev
```

3. Open [http://localhost:3000](http://localhost:3000) in your browser to see the application.

## Pages

- **Homepage** (`/`) - Landing page with hero section, features, and call-to-action
- **Login** (`/login`) - User login page
- **Register** (`/register`) - User registration page
- **Dashboard** (`/dashboard`) - Main dashboard with financial overview
- **Packages** (`/packages`) - Subscription packages page
- **Billing** (`/billing`) - Billing and payment management
- **Payment Success** (`/payment/success`) - Payment confirmation page
- **Payment Cancel** (`/payment/cancel`) - Payment cancellation page

## Tech Stack

- **Next.js 14** - React framework with App Router
- **TypeScript** - Type safety
- **Tailwind CSS** - Utility-first CSS framework
- **React 18** - UI library

## Project Structure

```
frontend/
├── app/
│   ├── page.tsx              # Homepage
│   ├── login/
│   │   └── page.tsx          # Login page
│   ├── register/
│   │   └── page.tsx          # Register page
│   ├── dashboard/
│   │   └── page.tsx          # Dashboard page
│   ├── packages/
│   │   └── page.tsx          # Packages page
│   ├── billing/
│   │   └── page.tsx          # Billing page
│   ├── payment/
│   │   ├── success/
│   │   │   └── page.tsx      # Payment success page
│   │   └── cancel/
│   │       └── page.tsx      # Payment cancel page
│   ├── layout.tsx            # Root layout
│   └── globals.css           # Global styles
├── package.json
├── tsconfig.json
├── tailwind.config.ts
└── next.config.js
```

## Build for Production

```bash
npm run build
npm start
```

## Development

The app uses Next.js App Router. Pages are automatically routed based on the file structure in the `app` directory.

