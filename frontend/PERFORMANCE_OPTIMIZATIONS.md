# Performance Optimizations

This document outlines the performance optimizations implemented to eliminate flickering and improve page load times.

## Key Optimizations

### 1. **Persistent Layout with Context API**
- Created `AuthContext` to share authentication state globally
- Single source of truth for auth state prevents multiple API calls
- Layout component persists across page navigations
- Navigation component uses shared context instead of individual checks

### 2. **Code Splitting & Lazy Loading**
- Navigation component is dynamically imported with `next/dynamic`
- Reduces initial bundle size
- Shows skeleton loader during hydration to prevent flicker

### 3. **React Memoization**
- `Navigation` component wrapped with `React.memo`
- `Logo` component wrapped with `React.memo`
- `Layout` component wrapped with `React.memo`
- Prevents unnecessary re-renders

### 4. **Optimized Auth Checking**
- Auth check happens once at app level via `AuthProvider`
- Uses `useRef` to prevent multiple simultaneous checks
- Caches auth state in context
- Pages consume cached state instead of re-checking

### 5. **Skeleton Loading States**
- Navigation shows skeleton during initial load
- Prevents layout shift and flickering
- Smooth transition from loading to loaded state

## Architecture Changes

### Before:
```
Each Page → Individual Navigation → Individual Auth Check → API Call
```

### After:
```
RootLayout → AuthProvider → Single Auth Check → Context
    ↓
All Pages → Layout → Navigation (from Context) → No API Call
```

## Benefits

1. **No Flickering**: Persistent layout and cached auth state prevent UI jumps
2. **Faster Load Times**: Code splitting reduces initial bundle size
3. **Better UX**: Skeleton loaders provide visual feedback
4. **Reduced API Calls**: Single auth check instead of per-page checks
5. **Smoother Navigation**: Components persist across route changes

## File Structure

```
frontend/
├── contexts/
│   └── AuthContext.tsx      # Global auth state provider
├── components/
│   ├── Layout.tsx           # Persistent layout wrapper
│   ├── Navigation.tsx       # Memoized navigation component
│   └── Logo.tsx             # Memoized logo component
└── app/
    └── layout.tsx           # Root layout with AuthProvider
```

## Usage

All pages now use the `Layout` component:

```tsx
import Layout from "@/components/Layout";

export default function MyPage() {
  return (
    <Layout>
      {/* Page content */}
    </Layout>
  );
}
```

Pages can access auth state via:

```tsx
import { useAuth } from "@/contexts/AuthContext";

export default function MyPage() {
  const { isAuthenticated, user, loading } = useAuth();
  // ...
}
```

## Future Optimizations

- [ ] Add React Query for API caching
- [ ] Implement route prefetching
- [ ] Add service worker for offline support
- [ ] Optimize images with Next.js Image component
- [ ] Add bundle analyzer to identify large dependencies

