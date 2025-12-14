import Link from "next/link";
import Layout from "@/components/Layout";
import Logo from "@/components/Logo";

export default function HomePage() {
  return (
    <Layout showNavigation={true}>
      <div className="min-h-screen bg-white">

      {/* Hero Section */}
      <section className="pt-32 pb-20 px-4 sm:px-6 lg:px-8 gradient-green-light">
        <div className="max-w-7xl mx-auto">
          <div className="text-center">
            <h1 className="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
              <span className="text-primary-600">Invest</span>,{" "}
              <span className="text-accent-600">Save</span>,{" "}
              <span className="text-primary-500">Earn</span>
            </h1>
            <p className="text-xl text-gray-700 mb-8 max-w-3xl mx-auto">
              Grow your wealth with smart investments, secure savings, and passive income opportunities. 
              Start your financial journey today with our trusted platform.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                href="/register"
                className="gradient-green text-white px-8 py-4 rounded-lg text-lg font-semibold hover:opacity-90 transition shadow-lg"
              >
                Start Investing Now
              </Link>
              <Link
                href="/packages"
                className="bg-white text-primary-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition border-2 border-primary-600 shadow-md"
              >
                View Investment Plans
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-gray-900 mb-4">Why Choose Taeab Finance?</h2>
            <p className="text-xl text-gray-600">Your trusted partner for financial growth</p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            <div className="p-8 rounded-xl border-2 border-primary-100 hover:border-primary-300 hover:shadow-xl transition bg-gradient-to-br from-primary-50 to-white">
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
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                  />
                </svg>
              </div>
              <h3 className="text-2xl font-bold text-gray-900 mb-3">Smart Investing</h3>
              <p className="text-gray-600 leading-relaxed">
                Diversified investment portfolios with expert guidance. Maximize returns while minimizing risks 
                with our AI-powered investment strategies.
              </p>
            </div>
            <div className="p-8 rounded-xl border-2 border-accent-100 hover:border-accent-300 hover:shadow-xl transition bg-gradient-to-br from-accent-50 to-white">
              <div className="w-16 h-16 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl flex items-center justify-center mb-6">
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
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <h3 className="text-2xl font-bold text-gray-900 mb-3">Secure Savings</h3>
              <p className="text-gray-600 leading-relaxed">
                High-yield savings accounts with competitive interest rates. Build your emergency fund 
                and achieve your financial goals faster with secure, guaranteed returns.
              </p>
            </div>
            <div className="p-8 rounded-xl border-2 border-primary-100 hover:border-primary-300 hover:shadow-xl transition bg-gradient-to-br from-primary-50 to-white">
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
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <h3 className="text-2xl font-bold text-gray-900 mb-3">Passive Earnings</h3>
              <p className="text-gray-600 leading-relaxed">
                Earn passive income through dividends, interest, and automated investment strategies. 
                Let your money work for you while you focus on what matters most.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 gradient-green-light">
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
          <h2 className="text-4xl font-bold text-white mb-4">Ready to Start Your Financial Journey?</h2>
          <p className="text-xl text-green-50 mb-8">
            Join thousands of investors who are already growing their wealth with Taeab Finance.
          </p>
          <Link
            href="/register"
            className="inline-block bg-white text-primary-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition shadow-lg"
          >
            Create Free Account
          </Link>
        </div>
      </section>

      {/* Footer */}
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
                  <Link href="/dashboard" className="hover:text-primary-400 transition">
                    Portfolio
                  </Link>
                </li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4 text-white">Company</h4>
              <ul className="space-y-2 text-gray-400">
                <li>
                  <a href="#" className="hover:text-primary-400 transition">
                    About Us
                  </a>
                </li>
                <li>
                  <a href="#" className="hover:text-primary-400 transition">
                    Contact
                  </a>
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
            <p>&copy; 2024 Taeab Finance. All rights reserved.</p>
          </div>
        </div>
      </footer>
      </div>
    </Layout>
  );
}
