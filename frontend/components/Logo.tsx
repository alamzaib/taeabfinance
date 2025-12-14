"use client";

import Image from "next/image";
import Link from "next/link";
import { useState, memo } from "react";

function LogoComponent({ className = "" }: { className?: string }) {
  const [imageError, setImageError] = useState(false);

  return (
    <Link href="/" className={`flex items-center gap-2 ${className}`}>
      {!imageError && (
        <div className="relative w-10 h-10 flex-shrink-0">
          <Image
            src="/images/logo.png"
            alt="Taeab Finance Logo"
            width={40}
            height={40}
            className="object-contain"
            priority
            onError={() => setImageError(true)}
          />
        </div>
      )}
      <span 
        className="text-3xl font-black tracking-tight"
        style={{
          fontFamily: "'Inter', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif",
          background: "linear-gradient(135deg, #0d9488 0%, #14b8a6 50%, #2dd4bf 100%)",
          WebkitBackgroundClip: "text",
          WebkitTextFillColor: "transparent",
          backgroundClip: "text",
          letterSpacing: "-0.02em",
        }}
      >
        TAEAB
      </span>
    </Link>
  );
}

export default memo(LogoComponent);

