"use client";

import { useState, useEffect } from "react";

/**
 * Hook que detecta se o navegador está online ou offline.
 * Retorna `true` quando o usuário está sem internet.
 * 
 * Uso:
 * ```tsx
 * const isOffline = useOffline();
 * if (isOffline) return <BannerOffline />;
 * ```
 */
export function useOffline(): boolean {
  const [isOffline, setIsOffline] = useState(false);

  useEffect(() => {
    // Checa o estado inicial
    setIsOffline(!navigator.onLine);

    const handleOnline = () => setIsOffline(false);
    const handleOffline = () => setIsOffline(true);

    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  return isOffline;
}
