export class SysLogger {
  static async log(level: 'error' | 'info' | 'warning', message: string, payload?: any) {
    try {
      const sysadminKey = process.env.NEXT_PUBLIC_SYSADMIN_KEY || process.env.SYSADMIN_KEY;
      
      if (!sysadminKey) {
        return; // Do nothing if key is not set
      }

      const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

      await fetch(`${apiUrl}/sysadmin/logs/ingest`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Sysadmin-Key': sysadminKey,
        },
        body: JSON.stringify({
          source: 'frontend',
          level,
          message,
          payload,
        }),
      });
    } catch (e) {
      // Silently fail to avoid infinite loops of errors
      console.error('Failed to ingest log:', e);
    }
  }

  static error(message: string, payload?: any) {
    this.log('error', message, payload);
  }

  static info(message: string, payload?: any) {
    this.log('info', message, payload);
  }

  static warning(message: string, payload?: any) {
    this.log('warning', message, payload);
  }
}
