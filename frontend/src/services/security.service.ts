import { api } from "@/lib/api";

export const SecurityService = {
  getDevices: async () => {
    try {
      const res = await api.get<any>('/settings/security/devices');
      return { success: true, data: res.devices };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao carregar dispositivos' };
    }
  },

  generateDevice: async (deviceName: string) => {
    try {
      const res = await api.post<any>('/settings/security/devices/generate', { device_name: deviceName });
      return { success: true, data: res };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao gerar dispositivo' };
    }
  },

  confirmDevice: async (deviceName: string, secret: string, code: string) => {
    try {
      const res = await api.post<any>('/settings/security/devices/confirm', { 
        device_name: deviceName,
        secret,
        code
      });
      return { success: true, message: res.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao vincular dispositivo' };
    }
  },

  removeDevice: async (deviceName: string) => {
    try {
      const res = await api.delete<any>('/settings/security/devices', { data: { device_name: deviceName } });
      return { success: true, message: res.message };
    } catch (e: any) {
      return { success: false, error: e.response?.data?.message || 'Erro ao remover dispositivo' };
    }
  }
};
