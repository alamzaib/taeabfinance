import Link from "next/link";
import Logo from "@/components/Logo";

export default function PaymentCancelPage() {
  return (
    <div className="min-h-screen gradient-green-light flex items-center justify-center px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full">
        <div className="bg-white rounded-lg shadow-xl p-8 text-center border border-red-100">
          <div className="mb-6">
            <Logo />
          </div>
          <div className="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg
              className="w-10 h-10 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
          <p className="text-gray-600 mb-8">
            Your payment was cancelled. No charges have been made to your account. 
            You can try again anytime to start your investment journey.
          </p>
          <div className="space-y-3">
            <Link
              href="/packages"
              className="block w-full gradient-green text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition shadow-md"
            >
              View Investment Plans
            </Link>
            <Link
              href="/dashboard"
              className="block w-full bg-gray-100 text-gray-900 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition"
            >
              Go to Dashboard
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
