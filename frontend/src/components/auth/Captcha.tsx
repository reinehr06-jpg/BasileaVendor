import React, { useState } from "react";
import { ShieldCheck, Loader2 } from "lucide-react";

export function Captcha({ onVerify }: { onVerify: (token: string | null) => void }) {
  const [status, setStatus] = useState<"idle" | "loading" | "success">("idle");

  const handleClick = () => {
    if (status === "success") return;
    setStatus("loading");
    // Simulando delay de verificação de rede/análise de bot (800ms)
    setTimeout(() => {
      setStatus("success");
      onVerify("mock-captcha-token-12345");
    }, 800);
  };

  return (
    <div className="w-full flex items-center justify-center" style={{ margin: '4px 0 12px 0' }}>
      <button
        type="button"
        onClick={handleClick}
        disabled={status === "loading" || status === "success"}
        style={{
          width: '100%',
          height: '44px',
          padding: '0 14px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          border: status === 'success' ? '1px solid #BBF7D0' : '1px solid #E5E7EB',
          borderRadius: '8px',
          backgroundColor: status === 'success' ? '#F0FDF4' : '#FAFAFA',
          cursor: status === 'success' ? 'default' : 'pointer',
          transition: 'all 0.2s',
          boxShadow: status === 'success' ? '0 0 8px rgba(34,197,94,0.1)' : 'none',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
          {status === "idle" && (
            <div style={{ width: '18px', height: '18px', borderRadius: '3px', border: '1.5px solid #D1D5DB', backgroundColor: 'white' }} />
          )}
          {status === "loading" && (
            <Loader2 style={{ width: '18px', height: '18px', color: '#9CA3AF' }} className="animate-spin" />
          )}
          {status === "success" && (
            <div style={{ width: '18px', height: '18px', borderRadius: '50%', backgroundColor: '#22C55E', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <ShieldCheck style={{ width: '12px', height: '12px', color: 'white' }} strokeWidth={3} />
            </div>
          )}
          <span style={{ fontSize: '12px', fontWeight: 500, color: status === 'success' ? '#16A34A' : '#6B7280' }}>
            {status === "success" ? "Verificado" : "Verificar se sou humano"}
          </span>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
          <span style={{ fontSize: '9px', color: '#9CA3AF', fontWeight: 600, letterSpacing: '0.05em', textTransform: 'uppercase' }}>Segurança</span>
          <span style={{ fontSize: '8px', color: '#C4C4C4' }}>Mock Turnstile</span>
        </div>
      </button>
    </div>
  );
}
