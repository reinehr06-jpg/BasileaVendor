"use client";

import React, { useState, useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { api } from "@/lib/api";
import { AuthSplitLayout } from "@/components/auth/AuthSplitLayout";
import { toast } from "sonner";
import { useAuth } from "@/context/AuthContext";

export default function TwoFactorSetupPage() {
    const [qrCode, setQrCode] = useState('');
    const [secret, setSecret] = useState('');
    const [code, setCode] = useState('');
    const [userId, setUserId] = useState('');
    const [loading, setLoading] = useState(false);
    
    const router = useRouter();
    const { login } = useAuth();
    
    const searchParams = useSearchParams();
    
    useEffect(() => {
        const stored = searchParams.get('user_id');
        if (stored) {
            setUserId(stored);
            api.post('/auth/2fa/setup', { user_id: stored })
                .then(res => {
                    setQrCode(res.qr_code_html);
                    setSecret(res.secret);
                })
                .catch(err => {
                    toast.error("Erro ao iniciar configuração", { description: err.message });
                    router.push('/auth/login');
                });
        } else {
            router.push('/auth/login');
        }
    }, [router, searchParams]);
    
    const handleConfirm = async () => {
        setLoading(true);
        try {
            const res = await api.post('/auth/2fa/confirm', {
                user_id: userId,
                code: code
            });
            
            // Re-utiliza a estrutura de login (só para setar cookie etc)
            // Em vez de chamar a API de novo, setamos manualmente os cookies como o AuthContext faria.
            // Para ser limpo, vamos apenas simular que o AuthContext lidou com isso:
            document.cookie = `auth_token=${res.token}; path=/; max-age=86400; SameSite=Lax`;
            
            localStorage.removeItem('2fa_setup_user_id');
            toast.success("2FA Configurado!", { description: "Seu acesso está protegido." });
            window.location.href = '/dashboard'; // hard redirect to reload state
        } catch (err: any) {
            toast.error("Erro", { description: err.message || "Código inválido." });
        } finally {
            setLoading(false);
        }
    };
    
    return (
        <AuthSplitLayout>
            <div className="fade-in" style={{ width: '100%', maxWidth: '400px', margin: '0 auto' }}>
                <div className="card-header" style={{ marginBottom: '20px', textAlign: 'center' }}>
                    <h1 style={{ fontSize: '24px', fontWeight: 'bold' }}>Configurar 2FA</h1>
                    <p style={{ color: '#6b7280', marginTop: '10px' }}>
                        Escaneie o QR Code com seu app autenticador (Google Authenticator, Authy, etc.)
                    </p>
                </div>
                
                {qrCode ? (
                    <div style={{ display: 'flex', justifyContent: 'center', marginBottom: '24px' }}>
                        <div dangerouslySetInnerHTML={{ __html: qrCode }} />
                    </div>
                ) : (
                    <div style={{ textAlign: 'center', margin: '20px 0' }}>Carregando...</div>
                )}
                
                {secret && (
                    <div style={{ marginBottom: '24px', padding: '16px', backgroundColor: '#f3f4f6', borderRadius: '8px' }}>
                        <p style={{ fontSize: '14px', color: '#4b5563', marginBottom: '8px' }}>
                            Ou insira manualmente no app:
                        </p>
                        <code style={{ fontSize: '14px', fontFamily: 'monospace', letterSpacing: '1px' }}>{secret}</code>
                    </div>
                )}
                
                <div className="field">
                    <label style={{ display: 'block', fontSize: '14px', fontWeight: '500', marginBottom: '8px' }}>
                        Código de verificação
                    </label>
                    <div className="input-wrap">
                        <input
                            type="text"
                            value={code}
                            onChange={(e) => setCode(e.target.value)}
                            placeholder="000000"
                            maxLength={6}
                        />
                    </div>
                </div>
                
                <button
                    type="button"
                    onClick={handleConfirm}
                    disabled={code.length !== 6 || loading}
                    className="btn"
                    style={{ marginTop: '20px' }}
                >
                    {loading ? 'Confirmando...' : 'Confirmar e Continuar'}
                </button>
                
                <div style={{ marginTop: '20px', textAlign: 'center' }}>
                    <a onClick={() => router.push('/auth/login')} style={{ cursor: 'pointer', color: '#2563eb', fontSize: '14px' }}>
                        Voltar ao login
                    </a>
                </div>
            </div>
        </AuthSplitLayout>
    );
}
