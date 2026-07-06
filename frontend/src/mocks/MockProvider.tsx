"use client";
// ============================================================
// MAPA DO TESOURO — Mock Provider (MSW)
// ============================================================
// PROPÓSITO:
//   Inicializa o servidor Fake no navegador quando em ambiente Dev.
//
// #arq09
// ============================================================

import { useEffect, useState } from "react";

export function MockProvider({ children }: { children: React.ReactNode }) {
  const [mockingEnabled, setMockingEnabled] = useState(false);

  useEffect(() => {
    async function enableMocking() {
      if (process.env.NODE_ENV !== "development" || process.env.NEXT_PUBLIC_USE_MOCK !== "true") {
        setMockingEnabled(true);
        return;
      }

      const { worker } = await import("./browser");
      await worker.start({ onUnhandledRequest: "bypass" });
      setMockingEnabled(true);
    }

    enableMocking();
  }, []);

  if (!mockingEnabled) {
    return null; // Or a loading skeleton
  }

  return <>{children}</>;
}
