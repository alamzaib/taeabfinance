"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { packageAPI } from "@/lib/api";
import { useAuth } from "@/lib/useAuth";
import Navigation from "@/components/Navigation";

interface Package {
  id: number;
  name: string;
  price: number;
  currency: string;
  period: string;
  features: string[];
  popular: boolean;
}

export default function PackagesPage() {
  const router = useRouter();
  const { isAuthenticated, user, loading: authLoading } = useAuth();
  const [packages, setPackages] = useState<Package[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [requestingPackage, setRequestingPackage] = useState<number | null>(null);
  const [requestSuccess, setRequestSuccess] = useState<string | null>(null);

  useEffect(() => {
    const fetchPackages = async () => {
      try {
        const response = await packageAPI.getPackages();
        console.log('Packages API Response:', response); // Debug log
        if (response.success && response.data && response.data.packages) {
          setPackages(response.data.packages);
        } else {
          setError("No packages available");
        }
      } catch (err: any) {
        console.error('Packages API Error:', err); // Debug log
        setError(err.response?.data?.message || err.message || "Failed to load investment plans");
      } finally {
        setLoading(false);
      }
    };

    fetchPackages();
  }, []);

  const handlePackageRequest = async (packageId: number) => {
    if (!isAuthenticated) {
      router.push("/login");
      return;
    }

    setRequestingPackage(packageId);
    setRequestSuccess(null);
    setError("");

    try {
      const response = await packageAPI.requestPackage(packageId);
      if (response.success) {
        setRequestSuccess(`Package request submitted successfully! Your request is pending approval.`);
        // Optionally redirect to dashboard after a delay
        setTimeout(() => {
          router.push("/dashboard");
        }, 2000);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || err.message || "Failed to submit package request");
    } finally {
      setRequestingPackage(null);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">Choose Your Investment Plan</h1>
          <p className="text-xl text-gray-600">
            Select the perfect plan to start investing, saving, and earning
          </p>
        </div>

        {requestSuccess && (
          <div className="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {requestSuccess}
          </div>
        )}
        
        {error && !requestSuccess && (
          <div className="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        {loading ? (
          <div className="text-center py-12">
            <p className="text-gray-600">Loading investment plans...</p>
          </div>
        ) : packages.length === 0 && !error ? (
          <div className="text-center py-12">
            <p className="text-gray-600">No packages available at the moment.</p>
          </div>
        ) : (
          <div className="grid md:grid-cols-3 gap-8">
            {packages.map((pkg) => (
              <div
                key={pkg.id}
                className={`bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 ease-in-out cursor-pointer group ${
                  pkg.popular 
                    ? "ring-4 ring-primary-400 transform scale-105 border-2 border-primary-500 hover:scale-110 hover:shadow-2xl hover:ring-primary-500" 
                    : "border border-gray-200 hover:scale-105 hover:shadow-2xl hover:border-primary-300 hover:-translate-y-2"
                }`}
              >
                {pkg.popular && (
                  <div className="gradient-green text-white text-center py-2 text-sm font-semibold">
                    Most Popular Plan
                  </div>
                )}
                <div className="p-8">
                  <h3 className="text-2xl font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors duration-300">
                    {pkg.name}
                  </h3>
                  <div className="mb-6">
                    <span className="text-4xl font-bold text-primary-600 group-hover:text-primary-700 transition-colors duration-300">
                      ${pkg.price.toFixed(2)}
                    </span>
                    <span className="text-gray-600 ml-2">per {pkg.period}</span>
                  </div>
                  <ul className="space-y-4 mb-8">
                    {pkg.features.map((feature, featureIndex) => (
                      <li key={featureIndex} className="flex items-start group-hover:translate-x-1 transition-transform duration-300">
                        <svg
                          className="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0 group-hover:text-primary-600 group-hover:scale-110 transition-all duration-300"
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
                        <span className="text-gray-700 group-hover:text-gray-900 transition-colors duration-300">{feature}</span>
                      </li>
                    ))}
                  </ul>
                  {isAuthenticated ? (
                    <button
                      onClick={() => handlePackageRequest(pkg.id)}
                      disabled={requestingPackage === pkg.id}
                      className={`block w-full text-center py-3 px-4 rounded-lg font-semibold transition-all duration-300 transform ${
                        pkg.popular
                          ? "gradient-green text-white hover:opacity-90 shadow-md hover:shadow-lg hover:scale-105"
                          : "bg-gray-100 text-gray-900 hover:bg-primary-50 hover:text-primary-700 hover:shadow-md hover:scale-105"
                      } disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100`}
                    >
                      {requestingPackage === pkg.id ? "Submitting..." : "Request Approval"}
                    </button>
                  ) : (
                    <Link
                      href="/login"
                      className={`block w-full text-center py-3 px-4 rounded-lg font-semibold transition-all duration-300 transform ${
                        pkg.popular
                          ? "gradient-green text-white hover:opacity-90 shadow-md hover:shadow-lg hover:scale-105"
                          : "bg-gray-100 text-gray-900 hover:bg-primary-50 hover:text-primary-700 hover:shadow-md hover:scale-105"
                      }`}
                    >
                      Login to Select
                    </Link>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}

        <div className="mt-12 text-center">
          <p className="text-gray-600 mb-4">All plans include a 14-day free trial</p>
          <p className="text-sm text-gray-500">
            Need help choosing? <a href="#" className="text-primary-600 hover:text-primary-700">Contact us</a>
          </p>
        </div>
      </div>
    </div>
  );
}
