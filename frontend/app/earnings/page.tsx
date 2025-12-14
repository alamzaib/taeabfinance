"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { earningsAPI } from "@/lib/api";
import Layout from "@/components/Layout";
import * as XLSX from "xlsx";

interface Earning {
  id: number;
  type: string;
  description: string;
  amount: number;
  currency: string;
  earned_date: string;
  package_name: string | null;
}

interface EarningsData {
  total_earnings: number;
  this_month_earnings: number;
  last_month_earnings: number;
  percentage_change: number;
  earnings_by_type: Record<string, number>;
  monthly_earnings: Array<{ month: string; total: number }>;
  recent_earnings: Earning[];
}

interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export default function EarningsPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [earningsData, setEarningsData] = useState<EarningsData | null>(null);
  const [earnings, setEarnings] = useState<Earning[]>([]);
  const [pagination, setPagination] = useState<PaginationData | null>(null);
  const [loading, setLoading] = useState(true);
  const [tableLoading, setTableLoading] = useState(false);
  const [error, setError] = useState("");
  
  // Filters and pagination - initialize from URL params if available
  const [currentPage, setCurrentPage] = useState(() => {
    const page = searchParams?.get('page');
    return page ? parseInt(page) : 1;
  });
  const [perPage, setPerPage] = useState(() => {
    const perPageParam = searchParams?.get('per_page');
    return perPageParam ? parseInt(perPageParam) : 20;
  });
  const [sortBy, setSortBy] = useState(() => searchParams?.get('sort_by') || "earned_date");
  const [sortOrder, setSortOrder] = useState<"asc" | "desc">(() => {
    const order = searchParams?.get('sort_order');
    return (order === 'asc' || order === 'desc') ? order : "desc";
  });
  const [typeFilter, setTypeFilter] = useState(() => searchParams?.get('type') || "");

  useEffect(() => {
    fetchEarningsSummary();
    fetchEarningsHistory();
  }, [currentPage, perPage, sortBy, sortOrder, typeFilter]);

  const fetchEarningsSummary = async () => {
    try {
      const response = await earningsAPI.getEarnings();
      if (response.success) {
        setEarningsData(response.data);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to load earnings");
      if (err.response?.status === 401) {
        router.push("/login");
      }
    } finally {
      setLoading(false);
    }
  };

  const fetchEarningsHistory = async () => {
    try {
      setTableLoading(true);
      const response = await earningsAPI.getHistory({
        page: currentPage,
        per_page: perPage,
        sort_by: sortBy,
        sort_order: sortOrder,
        type: typeFilter || undefined,
      });
      if (response.success) {
        setEarnings(response.data.earnings);
        setPagination(response.data.pagination);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to load earnings history");
    } finally {
      setTableLoading(false);
    }
  };

  const handleExportToExcel = async () => {
    try {
      setTableLoading(true);
      const response = await earningsAPI.export({
        type: typeFilter || undefined,
      });
      
      if (response.success && response.data.earnings) {
        // Create workbook and worksheet
        const ws = XLSX.utils.json_to_sheet(response.data.earnings);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Earnings");
        
        // Generate filename with current date
        const filename = `earnings_${new Date().toISOString().split('T')[0]}.xlsx`;
        
        // Write file
        XLSX.writeFile(wb, filename);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to export earnings");
    } finally {
      setTableLoading(false);
    }
  };

  const handleSort = (field: string) => {
    if (sortBy === field) {
      setSortOrder(sortOrder === "asc" ? "desc" : "asc");
    } else {
      setSortBy(field);
      setSortOrder("desc");
    }
    setCurrentPage(1);
  };

  const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
      dividend: "Dividend",
      interest: "Interest",
      profit: "Profit",
      bonus: "Bonus",
      referral: "Referral",
    };
    return labels[type] || type.charAt(0).toUpperCase() + type.slice(1);
  };

  const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
      dividend: "bg-blue-100 text-blue-800",
      interest: "bg-green-100 text-green-800",
      profit: "bg-purple-100 text-purple-800",
      bonus: "bg-yellow-100 text-yellow-800",
      referral: "bg-pink-100 text-pink-800",
    };
    return colors[type] || "bg-gray-100 text-gray-800";
  };

  const SortIcon = ({ field }: { field: string }) => {
    if (sortBy !== field) {
      return (
        <svg className="w-4 h-4 text-gray-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
      );
    }
    return sortOrder === "asc" ? (
      <svg className="w-4 h-4 text-primary-600 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
      </svg>
    ) : (
      <svg className="w-4 h-4 text-primary-600 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
      </svg>
    );
  };

  return (
    <Layout>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Earnings History</h1>
          <p className="mt-2 text-gray-600">Track your earnings, dividends, and passive income</p>
        </div>

        {error && (
          <div className="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
        )}

        {loading ? (
          <div className="text-center py-12">
            <p className="text-gray-600">Loading earnings data...</p>
          </div>
        ) : earningsData ? (
          <>
            {/* Earnings Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <div className="bg-white rounded-lg shadow-lg p-6 border-l-4 border-primary-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">Total Earnings</p>
                    <p className="text-3xl font-bold text-gray-900 mt-2">
                      ${earningsData.total_earnings.toFixed(2)}
                    </p>
                  </div>
                  <div className="w-12 h-12 gradient-green rounded-lg flex items-center justify-center">
                    <svg
                      className="w-6 h-6 text-white"
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
                </div>
              </div>

              <div className="bg-white rounded-lg shadow-lg p-6 border-l-4 border-accent-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">This Month</p>
                    <p className="text-3xl font-bold text-gray-900 mt-2">
                      ${earningsData.this_month_earnings.toFixed(2)}
                    </p>
                  </div>
                  <div className="w-12 h-12 bg-gradient-to-br from-accent-500 to-accent-600 rounded-lg flex items-center justify-center">
                    <svg
                      className="w-6 h-6 text-white"
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
                </div>
                <p
                  className={`text-sm mt-4 font-semibold ${
                    earningsData.percentage_change >= 0 ? "text-primary-600" : "text-red-600"
                  }`}
                >
                  {earningsData.percentage_change >= 0 ? "+" : ""}
                  {earningsData.percentage_change.toFixed(1)}% from last month
                </p>
              </div>

              <div className="bg-white rounded-lg shadow-lg p-6 border-l-4 border-primary-400">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">Last Month</p>
                    <p className="text-3xl font-bold text-gray-900 mt-2">
                      ${earningsData.last_month_earnings.toFixed(2)}
                    </p>
                  </div>
                  <div className="w-12 h-12 gradient-green rounded-lg flex items-center justify-center">
                    <svg
                      className="w-6 h-6 text-white"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            {/* Earnings by Type */}
            {Object.keys(earningsData.earnings_by_type).length > 0 && (
              <div className="bg-white rounded-lg shadow-lg mb-8">
                <div className="px-6 py-4 border-b border-gray-200">
                  <h2 className="text-xl font-semibold text-gray-900">Earnings by Type</h2>
                </div>
                <div className="p-6">
                  <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                    {Object.entries(earningsData.earnings_by_type).map(([type, amount]) => (
                      <div key={type} className="text-center p-4 bg-gray-50 rounded-lg">
                        <p className="text-sm font-medium text-gray-600 mb-1">{getTypeLabel(type)}</p>
                        <p className="text-2xl font-bold text-primary-600">${amount.toFixed(2)}</p>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Earnings History Table */}
            <div className="bg-white rounded-lg shadow-lg">
              <div className="px-6 py-4 border-b border-gray-200">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                  <div className="flex items-center flex-wrap gap-3">
                    <h2 className="text-xl font-semibold text-gray-900">Earnings History</h2>
                    {/* Active Filters */}
                    {(typeFilter || (sortBy !== "earned_date") || (sortOrder !== "desc" && sortBy === "earned_date")) && (
                      <div className="flex items-center gap-2 flex-wrap">
                        {typeFilter && (
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                            Type: {getTypeLabel(typeFilter)}
                            <button
                              onClick={() => {
                                setTypeFilter("");
                                setCurrentPage(1);
                              }}
                              className="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary-200 transition"
                              aria-label="Remove type filter"
                            >
                              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </span>
                        )}
                        {sortBy !== "earned_date" && (
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent-100 text-accent-800">
                            Sort: {sortBy === "amount" ? "Amount" : sortBy === "type" ? "Type" : "Description"} ({sortOrder === "asc" ? "Asc" : "Desc"})
                            <button
                              onClick={() => {
                                setSortBy("earned_date");
                                setSortOrder("desc");
                                setCurrentPage(1);
                              }}
                              className="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-accent-200 transition"
                              aria-label="Reset sort"
                            >
                              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </span>
                        )}
                        {sortOrder === "asc" && sortBy === "earned_date" && (
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent-100 text-accent-800">
                            Sort: Oldest First
                            <button
                              onClick={() => {
                                setSortOrder("desc");
                                setCurrentPage(1);
                              }}
                              className="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-accent-200 transition"
                              aria-label="Reset sort order"
                            >
                              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </span>
                        )}
                      </div>
                    )}
                  </div>
                  <div className="flex flex-wrap items-center gap-3">
                    {/* Type Filter */}
                    <select
                      value={typeFilter}
                      onChange={(e) => {
                        setTypeFilter(e.target.value);
                        setCurrentPage(1);
                      }}
                      className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    >
                      <option value="">All Types</option>
                      <option value="dividend">Dividend</option>
                      <option value="interest">Interest</option>
                      <option value="profit">Profit</option>
                      <option value="bonus">Bonus</option>
                      <option value="referral">Referral</option>
                    </select>

                    {/* Records per page */}
                    <select
                      value={perPage}
                      onChange={(e) => {
                        setPerPage(Number(e.target.value));
                        setCurrentPage(1);
                      }}
                      className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    >
                      <option value="10">10 per page</option>
                      <option value="20">20 per page</option>
                      <option value="50">50 per page</option>
                      <option value="100">100 per page</option>
                    </select>

                    {/* Export Button */}
                    <button
                      onClick={handleExportToExcel}
                      disabled={tableLoading}
                      className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      Export Excel
                    </button>
                  </div>
                </div>
              </div>
              <div className="overflow-x-auto">
                {tableLoading ? (
                  <div className="p-6 text-center text-gray-500">Loading earnings...</div>
                ) : earnings.length > 0 ? (
                  <>
                    <table className="min-w-full divide-y divide-gray-200">
                      <thead className="bg-gray-50">
                        <tr>
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("earned_date")}
                          >
                            <div className="flex items-center">
                              Date
                              <SortIcon field="earned_date" />
                            </div>
                          </th>
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("type")}
                          >
                            <div className="flex items-center">
                              Type
                              <SortIcon field="type" />
                            </div>
                          </th>
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("description")}
                          >
                            <div className="flex items-center">
                              Description
                              <SortIcon field="description" />
                            </div>
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Package
                          </th>
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("amount")}
                          >
                            <div className="flex items-center">
                              Amount
                              <SortIcon field="amount" />
                            </div>
                          </th>
                        </tr>
                      </thead>
                      <tbody className="bg-white divide-y divide-gray-200">
                        {earnings.map((earning) => (
                          <tr key={earning.id} className="hover:bg-gray-50 transition-colors">
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {new Date(earning.earned_date).toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "short",
                                day: "numeric",
                              })}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                              <span
                                className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getTypeColor(
                                  earning.type
                                )}`}
                              >
                                {getTypeLabel(earning.type)}
                              </span>
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {earning.description}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                              {earning.package_name || "N/A"}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-600">
                              +${earning.amount.toFixed(2)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>

                    {/* Pagination */}
                    {pagination && pagination.last_page > 1 && (
                      <div className="px-6 py-4 border-t border-gray-200">
                        <div className="flex flex-col sm:flex-row justify-between items-center gap-4">
                          <div className="text-sm text-gray-700">
                            Showing <span className="font-medium">{pagination.from || 0}</span> to{" "}
                            <span className="font-medium">{pagination.to || 0}</span> of{" "}
                            <span className="font-medium">{pagination.total}</span> results
                          </div>
                          <div className="flex items-center gap-2">
                            <button
                              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                              disabled={currentPage === 1}
                              className="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                              Previous
                            </button>
                            <div className="flex items-center gap-1">
                              {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                                let pageNum;
                                if (pagination.last_page <= 5) {
                                  pageNum = i + 1;
                                } else if (currentPage <= 3) {
                                  pageNum = i + 1;
                                } else if (currentPage >= pagination.last_page - 2) {
                                  pageNum = pagination.last_page - 4 + i;
                                } else {
                                  pageNum = currentPage - 2 + i;
                                }
                                return (
                                  <button
                                    key={pageNum}
                                    onClick={() => setCurrentPage(pageNum)}
                                    className={`px-3 py-2 border rounded-md text-sm font-medium transition ${
                                      currentPage === pageNum
                                        ? "border-primary-500 bg-primary-50 text-primary-600"
                                        : "border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
                                    }`}
                                  >
                                    {pageNum}
                                  </button>
                                );
                              })}
                            </div>
                            <button
                              onClick={() => setCurrentPage((p) => Math.min(pagination.last_page, p + 1))}
                              disabled={currentPage === pagination.last_page}
                              className="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                              Next
                            </button>
                          </div>
                        </div>
                      </div>
                    )}
                  </>
                ) : (
                  <div className="p-6 text-center text-gray-500">
                    No earnings history available
                  </div>
                )}
              </div>
            </div>
          </>
        ) : null}
      </div>
    </Layout>
  );
}
