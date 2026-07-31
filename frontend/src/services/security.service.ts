import { api } from "@/lib/api";

export const SecurityService = {
  getUsers: async () => {
    try {
      const res = await api.get<any>('/settings/security/users');
      return { success: true, data: res.users };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao carregar usuários' };
    }
  },

  getDevices: async () => {
    try {
      const res = await api.get<any>('/settings/security/devices');
      return { success: true, data: res.devices };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao carregar dispositivos' };
    }
  },

  generateDevice: async (deviceName: string, userId?: number) => {
    try {
      const payload: any = { device_name: deviceName };
      if (userId) payload.user_id = userId;
      
      const res = await api.post<any>('/settings/security/devices/generate', payload);
      return { success: true, data: res };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao gerar dispositivo' };
    }
  },

  confirmDevice: async (deviceName: string, secret: string, code: string, userId?: number) => {
    try {
      const payload: any = { device_name: deviceName, secret, code };
      if (userId) payload.user_id = userId;

      const res = await api.post<any>('/settings/security/devices/confirm', payload);
      return { success: true, message: res.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao vincular dispositivo' };
    }
  },

  removeDevice: async (deviceName: string, userId?: number) => {
    try {
      const payload: any = { device_name: deviceName };
      if (userId) payload.user_id = userId;

      const res = await api.delete<any>('/settings/security/devices', { data: payload });
      return { success: true, message: res.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao remover dispositivo' };
    }
  }
};
