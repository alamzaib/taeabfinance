"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { billingAPI, paymentMethodAPI } from "@/lib/api";
import Navigation from "@/components/Navigation";
import * as XLSX from "xlsx";

interface PaymentMethod {
  id: number;
  type: string;
  card_type: string;
  last_four: string;
  exp_month: string;
  exp_year: string;
  holder_name: string;
  is_primary: boolean;
  display: string;
}

interface Payment {
  id: number;
  date: string;
  description: string;
  amount: number;
  currency: string;
  status: string;
  package_name: string | null;
  transaction_id: string | null;
  paid_at: string | null;
}

interface BillingData {
  current_plan: string;
  amount: number;
  currency: string;
  next_billing_date: string | null;
  total_payments: number;
  this_month_payments: number;
  last_month_payments: number;
  percentage_change: number;
  payments_by_status: Record<string, { total: number; count: number }>;
  recent_payments: Payment[];
}

interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export default function BillingPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [billing, setBilling] = useState<BillingData | null>(null);
  const [paymentMethods, setPaymentMethods] = useState<PaymentMethod[]>([]);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [pagination, setPagination] = useState<PaginationData | null>(null);
  const [loading, setLoading] = useState(true);
  const [tableLoading, setTableLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  
  // Filters and pagination - initialize from URL params if available
  const [currentPage, setCurrentPage] = useState(() => {
    const page = searchParams?.get('page');
    return page ? parseInt(page) : 1;
  });
  const [perPage, setPerPage] = useState(() => {
    const perPageParam = searchParams?.get('per_page');
    return perPageParam ? parseInt(perPageParam) : 20;
  });
  const [sortBy, setSortBy] = useState(() => searchParams?.get('sort_by') || "created_at");
  const [sortOrder, setSortOrder] = useState<"asc" | "desc">(() => {
    const order = searchParams?.get('sort_order');
    return (order === 'asc' || order === 'desc') ? order : "desc";
  });
  const [statusFilter, setStatusFilter] = useState(() => searchParams?.get('status') || "");
  
  // Modal states
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [editingMethod, setEditingMethod] = useState<PaymentMethod | null>(null);
  
  // Form states
  const [formData, setFormData] = useState({
    card_number: "",
    exp_month: "",
    exp_year: "",
    holder_name: "",
    cvv: "",
    is_primary: false,
  });
  const [formLoading, setFormLoading] = useState(false);

  useEffect(() => {
    fetchBilling();
  }, []);

  useEffect(() => {
    fetchBillingHistory();
  }, [currentPage, perPage, sortBy, sortOrder, statusFilter]);

  const fetchBilling = async () => {
    try {
      setLoading(true);
      const response = await billingAPI.getBilling();
      if (response.success) {
        setBilling(response.data);
        setPaymentMethods(response.data.payment_methods || []);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to load billing information");
      if (err.response?.status === 401) {
        router.push("/login");
      }
    } finally {
      setLoading(false);
    }
  };

  const fetchBillingHistory = async () => {
    try {
      setTableLoading(true);
      const response = await billingAPI.getHistory({
        page: currentPage,
        per_page: perPage,
        sort_by: sortBy,
        sort_order: sortOrder,
        status: statusFilter || undefined,
      });
      if (response.success) {
        setPayments(response.data.payments);
        setPagination(response.data.pagination);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to load billing history");
    } finally {
      setTableLoading(false);
    }
  };

  const handleSort = (column: string) => {
    if (sortBy === column) {
      setSortOrder(sortOrder === "asc" ? "desc" : "asc");
    } else {
      setSortBy(column);
      setSortOrder("desc");
    }
    setCurrentPage(1);
  };

  const handleExportToExcel = async () => {
    try {
      setTableLoading(true);
      const response = await billingAPI.export({
        status: statusFilter || undefined,
        sort_by: sortBy,
        sort_order: sortOrder,
      });
      
      if (response.success && response.data.payments) {
        const ws = XLSX.utils.json_to_sheet(response.data.payments);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Billing History");
        
        const filename = `billing_history_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, filename);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to export billing history");
    } finally {
      setTableLoading(false);
    }
  };

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      completed: "bg-green-100 text-green-800",
      pending: "bg-yellow-100 text-yellow-800",
      failed: "bg-red-100 text-red-800",
      cancelled: "bg-gray-100 text-gray-800",
    };
    return colors[status.toLowerCase()] || "bg-gray-100 text-gray-800";
  };

  const SortIcon = ({ field }: { field: string }) => {
    if (sortBy !== field) {
      return (
        <svg className="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
      );
    }
    return sortOrder === "asc" ? (
      <svg className="w-4 h-4 ml-1 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
      </svg>
    ) : (
      <svg className="w-4 h-4 ml-1 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
      </svg>
    );
  };


  const handleAddPaymentMethod = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormLoading(true);
    setError("");
    setSuccess("");

    try {
      const response = await paymentMethodAPI.create({
        type: "card",
        card_number: formData.card_number.replace(/\s/g, ""),
        exp_month: formData.exp_month.padStart(2, "0"),
        exp_year: formData.exp_year,
        holder_name: formData.holder_name,
        cvv: formData.cvv,
        is_primary: formData.is_primary,
      });

      if (response.success) {
        setSuccess("Payment method added successfully!");
        setShowAddModal(false);
        resetForm();
        fetchBilling();
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to add payment method");
    } finally {
      setFormLoading(false);
    }
  };

  const handleUpdatePaymentMethod = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingMethod) return;

    setFormLoading(true);
    setError("");
    setSuccess("");

    try {
      const response = await paymentMethodAPI.update(editingMethod.id, {
        exp_month: formData.exp_month.padStart(2, "0"),
        exp_year: formData.exp_year,
        holder_name: formData.holder_name,
      });

      if (response.success) {
        setSuccess("Payment method updated successfully!");
        setShowEditModal(false);
        setEditingMethod(null);
        resetForm();
        fetchBilling();
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to update payment method");
    } finally {
      setFormLoading(false);
    }
  };

  const handleSetPrimary = async (id: number) => {
    try {
      const response = await paymentMethodAPI.setPrimary(id);
      if (response.success) {
        setSuccess("Primary payment method updated!");
        fetchBilling();
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to set primary payment method");
    }
  };

  const handleDeletePaymentMethod = async (id: number) => {
    if (!confirm("Are you sure you want to delete this payment method?")) {
      return;
    }

    try {
      const response = await paymentMethodAPI.delete(id);
      if (response.success) {
        setSuccess("Payment method deleted successfully!");
        fetchBilling();
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Failed to delete payment method");
    }
  };

  const openEditModal = (method: PaymentMethod) => {
    setEditingMethod(method);
    setFormData({
      card_number: "",
      exp_month: method.exp_month || "",
      exp_year: method.exp_year || "",
      holder_name: method.holder_name || "",
      cvv: "",
      is_primary: method.is_primary,
    });
    setShowEditModal(true);
  };

  const resetForm = () => {
    setFormData({
      card_number: "",
      exp_month: "",
      exp_year: "",
      holder_name: "",
      cvv: "",
      is_primary: false,
    });
  };

  const formatCardNumber = (value: string) => {
    const v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
    const matches = v.match(/\d{4,16}/g);
    const match = (matches && matches[0]) || "";
    const parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }
    if (parts.length) {
      return parts.join(" ");
    } else {
      return v;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Navigation />

      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Billing & Subscription</h1>
          <p className="mt-2 text-gray-600">Manage your investment plan and payment methods</p>
        </div>

        {error && (
          <div className="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
        )}

        {success && (
          <div className="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {success}
          </div>
        )}

        {loading ? (
          <div className="text-center py-12">
            <p className="text-gray-600">Loading billing information...</p>
          </div>
        ) : (
          <>
            {/* Current Plan */}
            <div className="bg-white rounded-lg shadow-lg mb-8 border-l-4 border-primary-500">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-xl font-semibold text-gray-900">Current Investment Plan</h2>
              </div>
              <div className="p-6">
                <div className="flex items-center justify-between mb-4">
                  <div>
                    <h3 className="text-2xl font-bold text-gray-900">
                      {billing?.current_plan || "No active plan"}
                    </h3>
                    <p className="text-gray-600 mt-1">
                      ${billing?.amount?.toFixed(2) || "0.00"} per month
                    </p>
                  </div>
                  <Link
                    href="/packages"
                    className="gradient-green text-white px-4 py-2 rounded-lg hover:opacity-90 transition shadow-md font-semibold"
                  >
                    Change Plan
                  </Link>
                </div>
                {billing?.next_billing_date && (
                  <div className="mt-4 pt-4 border-t border-gray-200">
                    <p className="text-sm text-gray-600">
                      Next billing date:{" "}
                      <span className="font-medium text-primary-600">
                        {new Date(billing.next_billing_date).toLocaleDateString("en-US", {
                          year: "numeric",
                          month: "long",
                          day: "numeric",
                        })}
                      </span>
                    </p>
                  </div>
                )}
              </div>
            </div>

            {/* Payment Methods */}
            <div className="bg-white rounded-lg shadow-lg mb-8">
              <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 className="text-xl font-semibold text-gray-900">Payment Methods</h2>
                <button
                  onClick={() => {
                    resetForm();
                    setShowAddModal(true);
                  }}
                  className="gradient-green text-white px-4 py-2 rounded-lg hover:opacity-90 transition shadow-md font-semibold text-sm"
                >
                  + Add Payment Method
                </button>
              </div>
              <div className="p-6">
                {paymentMethods.length === 0 ? (
                  <div className="text-center py-8 text-gray-500">
                    <p>No payment methods added yet.</p>
                    <button
                      onClick={() => {
                        resetForm();
                        setShowAddModal(true);
                      }}
                      className="mt-4 text-primary-600 hover:text-primary-700 font-medium"
                    >
                      Add your first payment method
                    </button>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {paymentMethods.map((method) => (
                      <div
                        key={method.id}
                        className={`border rounded-lg p-4 flex items-center justify-between ${
                          method.is_primary ? "border-primary-500 bg-primary-50" : "border-gray-200"
                        }`}
                      >
                        <div className="flex items-center flex-1">
                          <div className="w-12 h-8 bg-gray-200 rounded flex items-center justify-center mr-4">
                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                            </svg>
                          </div>
                          <div className="flex-1">
                            <div className="flex items-center gap-2">
                              <p className="font-medium text-gray-900">
                                {method.card_type ? method.card_type.toUpperCase() : "Card"} •••• {method.last_four}
                              </p>
                              {method.is_primary && (
                                <span className="px-2 py-1 text-xs font-semibold rounded-full bg-primary-600 text-white">
                                  Primary
                                </span>
                              )}
                            </div>
                            <p className="text-sm text-gray-600">
                              {method.holder_name || "Cardholder"} • Expires {method.exp_month}/{method.exp_year?.slice(-2)}
                            </p>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          {!method.is_primary && (
                            <button
                              onClick={() => handleSetPrimary(method.id)}
                              className="text-primary-600 hover:text-primary-700 font-medium text-sm"
                            >
                              Set Primary
                            </button>
                          )}
                          <button
                            onClick={() => openEditModal(method)}
                            className="text-primary-600 hover:text-primary-700 font-medium text-sm"
                          >
                            Edit
                          </button>
                          <button
                            onClick={() => handleDeletePaymentMethod(method.id)}
                            disabled={method.is_primary}
                            className={`font-medium text-sm ${
                              method.is_primary
                                ? "text-gray-400 cursor-not-allowed"
                                : "text-red-600 hover:text-red-700"
                            }`}
                            title={method.is_primary ? "Cannot delete primary payment method" : "Delete"}
                          >
                            Delete
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Billing History Table */}
            <div className="bg-white rounded-lg shadow-lg">
              <div className="px-6 py-4 border-b border-gray-200">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                  <div className="flex items-center flex-wrap gap-3">
                    <h2 className="text-xl font-semibold text-gray-900">Billing History</h2>
                    {/* Active Filters */}
                    {(statusFilter || (sortBy !== "created_at") || (sortOrder !== "desc" && sortBy === "created_at")) && (
                      <div className="flex items-center gap-2 flex-wrap">
                        {statusFilter && (
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                            Status: {statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1)}
                            <button
                              onClick={() => {
                                setStatusFilter("");
                                setCurrentPage(1);
                              }}
                              className="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary-200 transition"
                              aria-label="Remove status filter"
                            >
                              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </span>
                        )}
                        {sortBy !== "created_at" && (
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent-100 text-accent-800">
                            Sort: {sortBy === "amount" ? "Amount" : sortBy === "status" ? "Status" : "Paid At"} ({sortOrder === "asc" ? "Asc" : "Desc"})
                            <button
                              onClick={() => {
                                setSortBy("created_at");
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
                        {sortOrder === "asc" && sortBy === "created_at" && (
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
                    {/* Status Filter */}
                    <select
                      value={statusFilter}
                      onChange={(e) => {
                        setStatusFilter(e.target.value);
                        setCurrentPage(1);
                      }}
                      className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    >
                      <option value="">All Status</option>
                      <option value="completed">Completed</option>
                      <option value="pending">Pending</option>
                      <option value="failed">Failed</option>
                      <option value="cancelled">Cancelled</option>
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
                      <option value={10}>10 per page</option>
                      <option value={20}>20 per page</option>
                      <option value={50}>50 per page</option>
                      <option value={100}>100 per page</option>
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
                  <div className="p-6 text-center text-gray-500">Loading billing history...</div>
                ) : payments.length > 0 ? (
                  <>
                    <table className="min-w-full divide-y divide-gray-200">
                      <thead className="bg-gray-50">
                        <tr>
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("created_at")}
                          >
                            <div className="flex items-center">
                              Date
                              <SortIcon field="created_at" />
                            </div>
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
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
                          <th
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition"
                            onClick={() => handleSort("status")}
                          >
                            <div className="flex items-center">
                              Status
                              <SortIcon field="status" />
                            </div>
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Transaction ID
                          </th>
                        </tr>
                      </thead>
                      <tbody className="bg-white divide-y divide-gray-200">
                        {payments.map((payment) => (
                          <tr key={payment.id} className="hover:bg-gray-50 transition-colors">
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {new Date(payment.date).toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "short",
                                day: "numeric",
                              })}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {payment.package_name || payment.description}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                              ${payment.amount.toFixed(2)} {payment.currency}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap">
                              <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(payment.status)}`}>
                                {payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                              </span>
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                              {payment.transaction_id || "N/A"}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>

                    {/* Pagination Controls */}
                    {pagination && pagination.total > 0 && (
                      <div className="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                        <div className="flex-1 flex justify-between sm:hidden">
                          <button
                            onClick={() => setCurrentPage(currentPage - 1)}
                            disabled={currentPage === 1}
                            className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            Previous
                          </button>
                          <button
                            onClick={() => setCurrentPage(currentPage + 1)}
                            disabled={currentPage === pagination.last_page}
                            className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            Next
                          </button>
                        </div>
                        <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                          <div>
                            <p className="text-sm text-gray-700">
                              Showing <span className="font-medium">{pagination.from}</span> to{" "}
                              <span className="font-medium">{pagination.to}</span> of{" "}
                              <span className="font-medium">{pagination.total}</span> results
                            </p>
                          </div>
                          <div className="flex items-center space-x-4">
                            <nav
                              className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                              aria-label="Pagination"
                            >
                              <button
                                onClick={() => setCurrentPage(currentPage - 1)}
                                disabled={currentPage === 1}
                                className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                              >
                                <span className="sr-only">Previous</span>
                                <svg
                                  className="h-5 w-5"
                                  xmlns="http://www.w3.org/2000/svg"
                                  viewBox="0 0 20 20"
                                  fill="currentColor"
                                  aria-hidden="true"
                                >
                                  <path
                                    fillRule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clipRule="evenodd"
                                  />
                                </svg>
                              </button>
                              {[...Array(Math.min(pagination.last_page, 10))].map((_, i) => {
                                let pageNum;
                                if (pagination.last_page <= 10) {
                                  pageNum = i + 1;
                                } else if (currentPage <= 5) {
                                  pageNum = i + 1;
                                } else if (currentPage >= pagination.last_page - 4) {
                                  pageNum = pagination.last_page - 9 + i;
                                } else {
                                  pageNum = currentPage - 5 + i;
                                }
                                return (
                                  <button
                                    key={i}
                                    onClick={() => setCurrentPage(pageNum)}
                                    aria-current={currentPage === pageNum ? "page" : undefined}
                                    className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                      currentPage === pageNum
                                        ? "z-10 bg-primary-50 border-primary-500 text-primary-600"
                                        : "bg-white border-gray-300 text-gray-700 hover:bg-gray-50"
                                    }`}
                                  >
                                    {pageNum}
                                  </button>
                                );
                              })}
                              <button
                                onClick={() => setCurrentPage(currentPage + 1)}
                                disabled={currentPage === pagination.last_page}
                                className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                              >
                                <span className="sr-only">Next</span>
                                <svg
                                  className="h-5 w-5"
                                  xmlns="http://www.w3.org/2000/svg"
                                  viewBox="0 0 20 20"
                                  fill="currentColor"
                                  aria-hidden="true"
                                >
                                  <path
                                    fillRule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clipRule="evenodd"
                                  />
                                </svg>
                              </button>
                            </nav>
                          </div>
                        </div>
                      </div>
                    )}
                  </>
                ) : (
                  <div className="p-6 text-center text-gray-500">
                    No billing history available.
                  </div>
                )}
              </div>
            </div>
          </>
        )}
      </div>

      {/* Add Payment Method Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-xl font-bold text-gray-900">Add Payment Method</h3>
              <button
                onClick={() => {
                  setShowAddModal(false);
                  resetForm();
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                ✕
              </button>
            </div>
            <form onSubmit={handleAddPaymentMethod} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Card Number
                </label>
                <input
                  type="text"
                  value={formData.card_number}
                  onChange={(e) =>
                    setFormData({ ...formData, card_number: formatCardNumber(e.target.value) })
                  }
                  placeholder="1234 5678 9012 3456"
                  maxLength={19}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Expiry Month
                  </label>
                  <input
                    type="text"
                    value={formData.exp_month}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        exp_month: e.target.value.replace(/\D/g, "").slice(0, 2),
                      })
                    }
                    placeholder="MM"
                    maxLength={2}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Expiry Year
                  </label>
                  <input
                    type="text"
                    value={formData.exp_year}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        exp_year: e.target.value.replace(/\D/g, "").slice(0, 4),
                      })
                    }
                    placeholder="YYYY"
                    maxLength={4}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Cardholder Name
                </label>
                <input
                  type="text"
                  value={formData.holder_name}
                  onChange={(e) => setFormData({ ...formData, holder_name: e.target.value })}
                  placeholder="John Doe"
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                <input
                  type="text"
                  value={formData.cvv}
                  onChange={(e) =>
                    setFormData({
                      ...formData,
                      cvv: e.target.value.replace(/\D/g, "").slice(0, 3),
                    })
                  }
                  placeholder="123"
                  maxLength={3}
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="is_primary_add"
                  checked={formData.is_primary}
                  onChange={(e) => setFormData({ ...formData, is_primary: e.target.checked })}
                  className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label htmlFor="is_primary_add" className="ml-2 block text-sm text-gray-900">
                  Set as primary payment method
                </label>
              </div>
              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowAddModal(false);
                    resetForm();
                  }}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={formLoading}
                  className="flex-1 px-4 py-2 gradient-green text-white rounded-md hover:opacity-90 disabled:opacity-50"
                >
                  {formLoading ? "Adding..." : "Add Payment Method"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Edit Payment Method Modal */}
      {showEditModal && editingMethod && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-xl font-bold text-gray-900">Edit Payment Method</h3>
              <button
                onClick={() => {
                  setShowEditModal(false);
                  setEditingMethod(null);
                  resetForm();
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                ✕
              </button>
            </div>
            <form onSubmit={handleUpdatePaymentMethod} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Card Number
                </label>
                <input
                  type="text"
                  value={`${editingMethod.card_type?.toUpperCase() || "Card"} •••• ${editingMethod.last_four}`}
                  disabled
                  className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                />
                <p className="text-xs text-gray-500 mt-1">Card number cannot be changed</p>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Expiry Month
                  </label>
                  <input
                    type="text"
                    value={formData.exp_month}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        exp_month: e.target.value.replace(/\D/g, "").slice(0, 2),
                      })
                    }
                    placeholder="MM"
                    maxLength={2}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Expiry Year
                  </label>
                  <input
                    type="text"
                    value={formData.exp_year}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        exp_year: e.target.value.replace(/\D/g, "").slice(0, 4),
                      })
                    }
                    placeholder="YYYY"
                    maxLength={4}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Cardholder Name
                </label>
                <input
                  type="text"
                  value={formData.holder_name}
                  onChange={(e) => setFormData({ ...formData, holder_name: e.target.value })}
                  placeholder="John Doe"
                  required
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowEditModal(false);
                    setEditingMethod(null);
                    resetForm();
                  }}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={formLoading}
                  className="flex-1 px-4 py-2 gradient-green text-white rounded-md hover:opacity-90 disabled:opacity-50"
                >
                  {formLoading ? "Updating..." : "Update Payment Method"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
