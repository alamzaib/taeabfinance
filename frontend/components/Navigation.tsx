"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { authAPI } from "@/lib/api";
import Logo from "./Logo";

export default function Navigation() {
  const router = useRouter();
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userName, setUserName] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const checkAuth = async () => {
      const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;
      
      if (!token) {
        setIsAuthenticated(false);
        setLoading(false);
        return;
      }

      try {
        const response = await authAPI.getUser();
        if (response.success && response.data?.user) {
          setIsAuthenticated(true);
          setUserName(response.data.user.name);
        } else {
          setIsAuthenticated(false);
          localStorage.removeItem("auth_token");
        }
      } catch (error) {
        setIsAuthenticated(false);
        localStorage.removeItem("auth_token");
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  const handleLogout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      // Ignore logout errors
    } finally {
      localStorage.removeItem("auth_token");
      setIsAuthenticated(false);
      setUserName(null);
      router.push("/login");
    }
  };

  if (loading) {
    return null; // Don't show navigation while checking auth
  }

  return (
    <nav className="bg-white shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <Logo />
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
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
                {userName && (
                  <span className="text-gray-700 text-sm hidden md:inline">Welcome, {userName}!</span>
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
                    <Link href="/dashboard" className="text-gray-700 hover:text-primary-600 transition font-medium">
                      Dashboard
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

