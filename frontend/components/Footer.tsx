"use client";

import Link from "next/link";
import { useAuth } from "@/contexts/AuthContext";
import Logo from "./Logo";

export default function Footer() {
  const { isAuthenticated } = useAuth();

  return (
    <footer className="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        <div className="grid md:grid-cols-4 gap-8">
          <div>
            <Logo className="mb-4" />
            <p className="text-gray-400">Your trusted partner for smart investing, secure savings, and passive earnings.</p>
          </div>
          <div>
            <h4 className="font-semibold mb-4 text-white">Invest</h4>
            <ul className="space-y-2 text-gray-400">
              <li>
                <Link href="/packages" className="hover:text-primary-400 transition">
                  Investment Plans
                </Link>
              </li>
              <li>
                <Link 
                  href={isAuthenticated === true ? "/dashboard" : "/login"} 
                  className="hover:text-primary-400 transition"
                >
                  Portfolio
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h4 className="font-semibold mb-4 text-white">Company</h4>
            <ul className="space-y-2 text-gray-400">
              <li>
                <Link href="/about" className="hover:text-primary-400 transition">
                  About Us
                </Link>
              </li>
              <li>
                <Link href="/contact" className="hover:text-primary-400 transition">
                  Contact
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h4 className="font-semibold mb-4 text-white">Account</h4>
            <ul className="space-y-2 text-gray-400">
              <li>
                <Link href="/login" className="hover:text-primary-400 transition">
                  Login
                </Link>
              </li>
              <li>
                <Link href="/register" className="hover:text-primary-400 transition">
                  Register
                </Link>
              </li>
            </ul>
          </div>
        </div>
        <div className="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
          <p>&copy; 2024 Taeab. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
}

