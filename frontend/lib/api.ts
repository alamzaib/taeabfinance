import axios from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'https://developer.taeab.com';

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add request interceptor to include auth token
api.interceptors.request.use(
  (config) => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
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
};

export const billingAPI = {
  getBilling: async () => {
    const response = await api.get('/api/v1/billing');
    return response.data;
  },
};

export default api;

