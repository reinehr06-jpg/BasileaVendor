import { api } from "@/lib/api";

export const MonitorService = {
  logs: async () => {
    return await api.get('/settings/monitor');
  }
};
