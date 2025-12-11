import axios from 'axios';

// API Base URL - can be overridden with NEXT_PUBLIC_API_URL environment variable
// Using Laravel's default artisan serve port
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

// Log API URL for debugging (only in development)
if (typeof window !== 'undefined' && process.env.NODE_ENV === 'development') {
  console.log('API Base URL:', API_BASE_URL);
}

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add request interceptor to include auth token and log requests
api.interceptors.request.use(
  (config) => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    // Log request for debugging
    console.log('API Request:', {
      method: config.method,
      url: config.url,
      baseURL: config.baseURL,
      fullURL: `${config.baseURL}${config.url}`,
    });
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Log network errors for debugging
    if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
      console.error('Network Error - Check:', {
        url: error.config?.url,
        baseURL: error.config?.baseURL,
        fullURL: `${error.config?.baseURL}${error.config?.url}`,
        message: 'Make sure the backend server is running and CORS is configured correctly'
      });
    }
    
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      if (typeof window !== 'undefined') {
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// API endpoints
export const authAPI = {
  login: async (email: string, password: string) => {
    const response = await api.post('/api/v1/login', { email, password });
    return response.data;
  },
  
  register: async (name: string, email: string, password: string, password_confirmation: string) => {
    const response = await api.post('/api/v1/register', {
      name,
      email,
      password,
      password_confirmation,
    });
    return response.data;
  },
  
  getUser: async () => {
    const response = await api.get('/api/v1/user');
    return response.data;
  },
  
  logout: async () => {
    const response = await api.post('/api/v1/logout');
    return response.data;
  },
};

export const packageAPI = {
  getPackages: async () => {
    const response = await api.get('/api/v1/packages');
    return response.data;
  },
  
  requestPackage: async (packageId: number) => {
    const response = await api.post('/api/v1/packages/request', { package_id: packageId });
    return response.data;
  },
};

export const billingAPI = {
  getBilling: async () => {
    const response = await api.get('/api/v1/billing');
    return response.data;
  },
  
  getHistory: async (params?: {
    per_page?: number;
    status?: string;
    start_date?: string;
    end_date?: string;
    sort_by?: string;
    sort_order?: string;
    page?: number;
  }) => {
    const response = await api.get('/api/v1/billing/history', { params });
    return response.data;
  },

  export: async (params?: {
    status?: string;
    start_date?: string;
    end_date?: string;
    sort_by?: string;
    sort_order?: string;
  }) => {
    const response = await api.get('/api/v1/billing/export', { params });
    return response.data;
  },
};

export const paymentMethodAPI = {
  getAll: async () => {
    const response = await api.get('/api/v1/payment-methods');
    return response.data;
  },
  
  create: async (data: {
    type: string;
    card_number: string;
    exp_month: string;
    exp_year: string;
    holder_name: string;
    cvv: string;
    is_primary?: boolean;
  }) => {
    const response = await api.post('/api/v1/payment-methods', data);
    return response.data;
  },
  
  update: async (id: number, data: {
    exp_month?: string;
    exp_year?: string;
    holder_name?: string;
  }) => {
    const response = await api.put(`/api/v1/payment-methods/${id}`, data);
    return response.data;
  },
  
  setPrimary: async (id: number) => {
    const response = await api.post(`/api/v1/payment-methods/${id}/set-primary`);
    return response.data;
  },
  
  delete: async (id: number) => {
    const response = await api.delete(`/api/v1/payment-methods/${id}`);
    return response.data;
  },
};

export const earningsAPI = {
  getEarnings: async () => {
    const response = await api.get('/api/v1/earnings');
    return response.data;
  },
  
  getHistory: async (params?: {
    per_page?: number;
    type?: string;
    start_date?: string;
    end_date?: string;
    sort_by?: string;
    sort_order?: string;
    page?: number;
  }) => {
    const response = await api.get('/api/v1/earnings/history', { params });
    return response.data;
  },
  
  export: async (params?: {
    type?: string;
    start_date?: string;
    end_date?: string;
  }) => {
    const response = await api.get('/api/v1/earnings/export', { params });
    return response.data;
      },
    };

    export const contactAPI = {
      submit: async (data: {
        subject: string;
        message: string;
        name?: string;
        email?: string;
        phone?: string;
      }) => {
        const response = await api.post('/api/v1/contact', data);
        return response.data;
      },
    };

    export default api;

