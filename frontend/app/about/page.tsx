"use client";

import Layout from "@/components/Layout";
import Link from "next/link";

export default function AboutPage() {
  return (
    <Layout>
      <div className="min-h-screen bg-white">
        {/* Hero Section */}
        <section className="pt-32 pb-20 px-4 sm:px-6 lg:px-8 gradient-green-light">
          <div className="max-w-4xl mx-auto text-center">
            <h1 className="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                  About <span className="text-primary-600">Taeab</span>
            </h1>
            <p className="text-xl text-gray-700 mb-8">
              Your trusted partner for smart investing, secure savings, and passive earnings.
            </p>
          </div>
        </section>

        {/* Mission Section */}
        <section className="py-20 px-4 sm:px-6 lg:px-8 bg-white">
          <div className="max-w-4xl mx-auto">
            <h2 className="text-4xl font-bold text-gray-900 mb-6">Our Mission</h2>
            <p className="text-lg text-gray-700 leading-relaxed mb-6">
                  At Taeab, we believe that everyone deserves the opportunity to build wealth and achieve financial freedom.
              Our mission is to democratize access to smart investment opportunities, secure savings solutions, and passive income streams.
            </p>
            <p className="text-lg text-gray-700 leading-relaxed">
              We combine cutting-edge technology with expert financial guidance to help our clients make informed decisions 
              and grow their wealth with confidence. Whether you're just starting your investment journey or looking to diversify 
              your portfolio, we're here to support you every step of the way.
            </p>
          </div>
        </section>

        {/* Values Section */}
        <section className="py-20 px-4 sm:px-6 lg:px-8 gradient-green-light">
          <div className="max-w-7xl mx-auto">
            <h2 className="text-4xl font-bold text-gray-900 mb-12 text-center">Our Core Values</h2>
            <div className="grid md:grid-cols-3 gap-8">
              <div className="p-8 rounded-xl border-2 border-primary-100 bg-white">
                <div className="w-16 h-16 gradient-green rounded-xl flex items-center justify-center mb-6">
                  <svg
                    className="w-8 h-8 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                    />
                  </svg>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-3">Trust & Security</h3>
                <p className="text-gray-600 leading-relaxed">
                  Your financial security is our top priority. We use industry-leading security measures 
                  and transparent practices to protect your investments and personal information.
                </p>
              </div>
              <div className="p-8 rounded-xl border-2 border-primary-100 bg-white">
                <div className="w-16 h-16 gradient-green rounded-xl flex items-center justify-center mb-6">
                  <svg
                    className="w-8 h-8 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M13 10V3L4 14h7v7l9-11h-7z"
                    />
                  </svg>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-3">Innovation</h3>
                <p className="text-gray-600 leading-relaxed">
                  We leverage the latest technology and financial tools to provide you with the best investment 
                  opportunities and user experience possible.
                </p>
              </div>
              <div className="p-8 rounded-xl border-2 border-primary-100 bg-white">
                <div className="w-16 h-16 gradient-green rounded-xl flex items-center justify-center mb-6">
                  <svg
                    className="w-8 h-8 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                  </svg>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-3">Customer First</h3>
                <p className="text-gray-600 leading-relaxed">
                  Your success is our success. We're committed to providing exceptional customer service 
                  and personalized support to help you achieve your financial goals.
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Stats Section */}
        <section className="py-20 px-4 sm:px-6 lg:px-8 bg-white">
          <div className="max-w-7xl mx-auto">
            <div className="grid md:grid-cols-4 gap-8 text-center">
              <div>
                <div className="text-4xl font-bold text-primary-600 mb-2">10K+</div>
                <div className="text-gray-700 font-medium">Active Investors</div>
              </div>
              <div>
                <div className="text-4xl font-bold text-accent-600 mb-2">$50M+</div>
                <div className="text-gray-700 font-medium">Assets Under Management</div>
              </div>
              <div>
                <div className="text-4xl font-bold text-primary-600 mb-2">15%</div>
                <div className="text-gray-700 font-medium">Average Annual Returns</div>
              </div>
              <div>
                <div className="text-4xl font-bold text-accent-600 mb-2">24/7</div>
                <div className="text-gray-700 font-medium">Support Available</div>
              </div>
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-20 px-4 sm:px-6 lg:px-8 gradient-green">
          <div className="max-w-4xl mx-auto text-center">
            <h2 className="text-4xl font-bold text-white mb-4">Ready to Start Your Journey?</h2>
            <p className="text-xl text-green-50 mb-8">
                  Join thousands of investors who trust Taeab for their financial growth.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                href="/register"
                className="inline-block bg-white text-primary-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition shadow-lg"
              >
                Get Started Today
              </Link>
              <Link
                href="/contact"
                className="inline-block bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary-600 transition"
              >
                Contact Us
              </Link>
            </div>
          </div>
        </section>
      </div>
    </Layout>
  );
}

