import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Taeab Finance",
  description: "Financial management platform",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}

