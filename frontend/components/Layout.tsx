"use client";

import React, { memo } from "react";
import dynamic from "next/dynamic";

// Lazy load Navigation to reduce initial bundle size
const Navigation = dynamic(() => import("./Navigation"), {
  ssr: false,
  loading: () => (
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
  ),
});

interface LayoutProps {
  children: React.ReactNode;
  showNavigation?: boolean;
}

function LayoutComponent({ children, showNavigation = true }: LayoutProps) {
  return (
    <div className="min-h-screen bg-gray-50">
      {showNavigation && (
        <div className="fixed top-0 w-full z-50">
          <Navigation />
        </div>
      )}
      <main className={showNavigation ? "pt-16" : ""}>{children}</main>
    </div>
  );
}

export default memo(LayoutComponent);

