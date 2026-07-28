"use client";

import { useOffline } from "@/hooks/useOffline";
import { motion, AnimatePresence } from "framer-motion";

/**
 * Banner flutuante que aparece quando o navegador detecta perda de conexão.
 * Ele some automaticamente quando a internet voltar.
 */
export default function OfflineBanner() {
  const isOffline = useOffline();

  return (
    <AnimatePresence>
      {isOffline && (
        <motion.div
          initial={{ y: -60, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: -60, opacity: 0 }}
          transition={{ type: "spring", stiffness: 300, damping: 30 }}
          className="fixed top-0 left-0 right-0 z-[9999] bg-amber-500 text-white text-center py-2.5 px-4 font-medium text-sm shadow-lg"
        >
          ⚠️ Você está sem internet. Algumas funcionalidades podem não funcionar corretamente.
        </motion.div>
      )}
    </AnimatePresence>
  );
}
