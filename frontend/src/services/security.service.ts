import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || '/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Interceptor para injetar token
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});

export const SecurityService = {
  getDevices: async () => {
    try {
      const res = await api.get('/settings/security/devices');
      return { success: true, data: res.data.devices };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao carregar dispositivos' };
    }
  },

  generateDevice: async (deviceName: string) => {
    try {
      const res = await api.post('/settings/security/devices/generate', { device_name: deviceName });
      return { success: true, data: res.data };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao gerar dispositivo' };
    }
  },

  confirmDevice: async (deviceName: string, secret: string, code: string) => {
    try {
      const res = await api.post('/settings/security/devices/confirm', { 
        device_name: deviceName,
        secret,
        code
      });
      return { success: true, message: res.data.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao vincular dispositivo' };
    }
  },

  removeDevice: async (deviceName: string) => {
    try {
      const res = await api.delete('/settings/security/devices', { data: { device_name: deviceName } });
      return { success: true, message: res.data.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao remover dispositivo' };
    }
  }
};
