import Link from "next/link";
import Logo from "@/components/Logo";

export default function PaymentSuccessPage() {
  return (
    <div className="min-h-screen gradient-green-light flex items-center justify-center px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full">
        <div className="bg-white rounded-lg shadow-xl p-8 text-center border border-primary-100">
          <div className="mb-6">
            <Logo />
          </div>
          <div className="w-20 h-20 gradient-green rounded-full flex items-center justify-center mx-auto mb-6">
            <svg
              className="w-10 h-10 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
          <p className="text-gray-600 mb-8">
            Your investment plan has been activated successfully. Start investing, saving, and earning today!
          </p>
          <div className="bg-primary-50 rounded-lg p-4 mb-6 border border-primary-200">
            <div className="text-sm text-gray-600 mb-1">Transaction ID</div>
            <div className="font-mono text-sm font-semibold text-primary-700">
              TXN-2024-0123456789
            </div>
          </div>
          <div className="space-y-3">
            <Link
              href="/dashboard"
              className="block w-full gradient-green text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition shadow-md"
            >
              Go to Dashboard
            </Link>
            <Link
              href="/billing"
              className="block w-full bg-gray-100 text-gray-900 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition"
            >
              View Billing
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
