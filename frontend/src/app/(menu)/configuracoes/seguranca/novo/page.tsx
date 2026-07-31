"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  Smartphone,
  ScanLine,
  ChevronDown,
  ChevronUp,
  Save,
  ShieldCheck
} from "  ArrowLeft,
} from "lucide-react";

import { toast } from "sonner";
import { useRouter } from "next/navigation";
import { SecurityService } from "@/services/security.service";

type SectionType = "dados-dispositivo" | "vincular" | null;

const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange, icon, disabled = false }: any) => (
  <div className="flex flex-col gap-[6px]">
    <label className="text-[13px] font-[600] text-[#4B5563]">
      {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
    </label>
    <div className="relative">
      <input 
        type={type} 
        placeholder={placeholder}
        defaultValue={value}
        disabled={disabled}
        onChange={onChange ? (e) => onChange(e.target.value) : undefined}
        className={`w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] ${icon ? 'pr-[36px]' : ''} ${disabled ? 'opacity-50 cursor-not-allowed bg-[#F9FAFB]' : ''}`}
      />
      {icon && (
        <div className="absolute inset-y-0 right-[12px] flex items-center pointer-events-none text-[#6B7280] font-[600]">
          {icon}
        </div>
      )}
    </div>
  </div>
);

export default function NovoDispositivoPage() {
  const { t } = useTranslation();
  const router = useRouter();
  const [openSection, setOpenSection] = useState<SectionType>("dados-dispositivo");

  const [saving, setSaving] = useState(false);
  const [generating, setGenerating] = useState(false);
  
  const [deviceName, setDeviceName] = useState("");
  const [userId, setUserId] = useState<number | "">("");
  const [users, setUsers] = useState<any[]>([]);
  
  const [qrCodeHtml, setQrCodeHtml] = useState<string | null>(null);
  const [secret, setSecret] = useState<string | null>(null);
  const [code, setCode] = useState("");

  React.useEffect(() => {
    loadUsers();
  }, []);

  const loadUsers = async () => {
    const res = await SecurityService.getUsers();
    if (res.success) {
      setUsers(res.data);
    }
  };

  const handleGenerate = async () => {
    if (!deviceName.trim()) {
      toast.error("Por favor, informe o nome do dispositivo.");
      return;
    }
    
    setGenerating(true);
    const targetUserId = userId !== "" ? Number(userId) : undefined;
    const res = await SecurityService.generateDevice(deviceName, targetUserId);
    setGenerating(false);

    if (res.success) {
      setQrCodeHtml(res.data.qr_code_html);
      setSecret(res.data.secret);
      setOpenSection("vincular");
    } else {
      toast.error(res.error);
    }
  };

  const handleSave = async () => {
    if (!secret || !code) {
      toast.error("Por favor, digite o código de 6 dígitos.");
      return;
    }

    setSaving(true);
    const toastId = toast.loading("Verificando código...");
    
    const targetUserId = userId !== "" ? Number(userId) : undefined;
    const res = await SecurityService.confirmDevice(deviceName, secret, code, targetUserId);
    
    if (res.success) {
      toast.success("Dispositivo vinculado com sucesso!", { id: toastId });
      router.push("/configuracoes/seguranca");
    } else {
      toast.error(res.error, { id: toastId });
      setSaving(false);
    }
  };

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col max-w-[1200px] mx-auto w-full">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <ShieldCheck className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                
              <Link 
                href="/configuracoes/seguranca"
                className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
              >
                <ArrowLeft className="w-[16px] h-[16px]" />
                Voltar
              </Link>
<h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Novo Dispositivo 2FA")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Adicione um novo dispositivo para autenticação de dois fatores.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: DADOS DO DISPOSITIVO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-dispositivo")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Smartphone className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados do Dispositivo")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Dê um nome para identificar o dispositivo facilmente.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-dispositivo" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-dispositivo" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="max-w-md flex flex-col gap-[16px]">
                      {users.length > 1 && (
                        <div className="flex flex-col gap-[6px]">
                          <label className="text-[13px] font-[600] text-[#4B5563]">
                            {t("Usuário Destino")}
                          </label>
                          <select 
                            value={userId}
                            onChange={(e) => setUserId(e.target.value as any)}
                            disabled={!!qrCodeHtml}
                            className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            <option value="">Meu próprio usuário</option>
                            {users.map(u => (
                              <option key={u.id} value={u.id}>{u.name} ({u.email})</option>
                            ))}
                          </select>
                        </div>
                      )}

                      <InputField 
                        label={t("Nome do Dispositivo")} 
                        placeholder="Ex: iPhone do Sócio, Celular Pessoal"
                        required 
                        value={deviceName}
                        onChange={(v: string) => setDeviceName(v)}
                        disabled={!!qrCodeHtml} // Disable if already generated
                      />

                      {!qrCodeHtml && (
                        <button 
                          onClick={handleGenerate}
                          disabled={generating || !deviceName.trim()}
                          className="bg-[#10B981] hover:bg-[#059669] text-white px-[20px] py-[10px] rounded-[8px] text-[14px] font-[600] w-fit flex items-center gap-[8px] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <ScanLine className="w-[16px] h-[16px]" />
                          {generating ? "Gerando..." : "Gerar QR Code"}
                        </button>
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* SEÇÃO 2: VINCULAR AUTENTICADOR */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("vincular")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <ScanLine className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Vincular Autenticador")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Escaneie o QR Code e insira o código gerado.")}</p>
                    </div>
                  </div>
                  {openSection === "vincular" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "vincular" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {!qrCodeHtml ? (
                      <div className="bg-[#FEF9C3] text-[#A16207] p-[16px] rounded-[8px] text-[14px] font-[500] border border-[#FEF08A]">
                        Você precisa informar o nome do dispositivo e gerar o QR Code primeiro na seção acima.
                      </div>
                    ) : (
                      <div className="flex flex-col md:flex-row gap-[32px] items-start">
                        <div className="bg-[#F9FAFB] border border-[#E5E7EB] p-[24px] rounded-[12px] flex flex-col items-center justify-center">
                          {/* QR Code Injetado */}
                          <div dangerouslySetInnerHTML={{ __html: qrCodeHtml }} className="[&>svg]:w-[200px] [&>svg]:h-[200px]" />
                        </div>
                        
                        <div className="flex flex-col gap-[16px] max-w-sm w-full pt-[12px]">
                          <div className="flex flex-col gap-[8px]">
                            <h3 className="text-[16px] font-[700] text-[#111827]">Escaneie com o app</h3>
                            <p className="text-[13px] text-[#6B7280]">
                              Abra o Google Authenticator ou Authy no seu celular e aponte a câmera para o QR Code ao lado.
                            </p>
                          </div>
                          
                          <div className="mt-[12px]">
                            <InputField 
                              label={t("Código de 6 dígitos")}
                              type="text"
                              placeholder="000000"
                              value={code}
                              onChange={(v: string) => setCode(v)}
                            />
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                )}
              </div>

            </div>
          </div>
        </main>

        {/* BARRA INFERIOR FLUTUANTE (FIXA) PARA SALVAR */}
        <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] px-[32px] flex items-center justify-between z-40">
          <p className="text-[13px] text-[#6B7280] hidden md:block">
            {t("Preencha as informações obrigatórias (")} <span className="text-[#EF4444] font-[700]">*</span> {t(") antes de salvar.")}
          </p>
          <div className="flex items-center gap-[12px] ml-auto">
            <Link 
              href="/configuracoes/seguranca"
              className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center"
            >
              {t("Cancelar")}
            </Link>
            <button 
              disabled={saving || !qrCodeHtml || code.length !== 6}
              onClick={handleSave}
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px] disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {saving ? t("Salvando...") : t("Salvar Dispositivo")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
