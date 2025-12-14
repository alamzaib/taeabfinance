"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { memo } from "react";
import { useAuth } from "@/contexts/AuthContext";
import Logo from "./Logo";

function NavigationComponent() {
  const router = useRouter();
  const { isAuthenticated, user, loading, logout } = useAuth();

  const handleLogout = async () => {
    await logout();
    router.push("/login");
  };

  // Show skeleton while loading to prevent flicker
  if (loading) {
    return (
      <nav className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="w-32 h-8 bg-gray-200 animate-pulse rounded"></div>
            <div className="flex items-center space-x-4">
              <div className="w-20 h-4 bg-gray-200 animate-pulse rounded"></div>
              <div className="w-20 h-4 bg-gray-200 animate-pulse rounded"></div>
            </div>
          </div>
        </div>
      </nav>
    );
  }

  return (
    <nav className="bg-white shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <Logo />
          <div className="flex items-center space-x-4">
            {isAuthenticated === true ? (
              <>
                <Link href="/dashboard" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Dashboard
                </Link>
                <Link href="/earnings" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Earnings
                </Link>
                <Link href="/packages" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Investment Plans
                </Link>
                <Link href="/billing" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Billing
                </Link>
                <Link href="/contact" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Contact
                </Link>
                {user && (
                  <span className="text-gray-700 text-sm hidden md:inline">Welcome, {user.name}!</span>
                )}
                <button
                  onClick={handleLogout}
                  className="text-gray-700 hover:text-primary-600 transition font-medium"
                >
                  Logout
                </button>
              </>
            ) : (
              <>
                <Link href="/packages" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Investment Plans
                </Link>
                <Link href="/contact" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Contact
                </Link>
                <Link href="/login" className="text-gray-700 hover:text-primary-600 transition font-medium">
                  Login
                </Link>
                <Link
                  href="/register"
                  className="gradient-green text-white px-4 py-2 rounded-lg hover:opacity-90 transition shadow-md font-semibold"
                >
                  Start Investing
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}

export default memo(NavigationComponent);

