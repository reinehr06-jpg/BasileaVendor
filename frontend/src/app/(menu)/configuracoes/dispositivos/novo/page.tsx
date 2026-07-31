"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Smartphone,
  Info,
  QrCode,
  ChevronDown,
  ChevronUp,
  Save,
  ArrowLeft
} from "lucide-react";

import { toast } from "sonner";
import { useRouter } from "next/navigation";

type SectionType = "dados-dispositivo" | "qrcode" | null;

const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange, icon }: any) => (
  <div className="flex flex-col gap-[6px]">
    <label className="text-[13px] font-[600] text-[#4B5563]">
      {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
    </label>
    <div className="relative">
      <input 
        type={type} 
        placeholder={placeholder}
        defaultValue={value}
        onChange={onChange ? (e) => onChange(e.target.value) : undefined}
        className={`w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] ${icon ? 'pr-[36px]' : ''}`}
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

  const [formData, setFormData] = useState({
    nome: "",
    tipoConta: "Gestor", // Gestor ou Vendedor
  });

  const handleSave = async () => {
    setSaving(true);
    const toastId = toast.loading("Registrando Dispositivo...");
    
    // Simulate backend call
    setTimeout(() => {
      toast.success("Dispositivo registrado e QRCode gerado!", { id: toastId });
      setSaving(false);
      setOpenSection("qrcode");
    }, 1500);
  };

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1000px] mx-auto">
            
            {/* VOLTAR */}
            <Link 
              href="/configuracoes/dispositivos"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              {t("Voltar para Dispositivos")}
            </Link>

            {/* CABEÇALHO DA PÁGINA */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Smartphone className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Novo Dispositivo de Acesso")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Cadastre um novo celular e gere o QR Code para autenticação em 2 fatores.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS (DROPDOWNS) */}
            <div className="flex flex-col gap-[16px]">

              {/* CARD 1: DADOS DO DISPOSITIVO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                <button 
                  onClick={() => toggleSection("dados-dispositivo")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Info className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados do Dispositivo")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Nome do aparelho e vínculo de conta.")}</p>
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
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Nome do Dispositivo")} 
                        placeholder="Ex: iPhone do João"
                        required 
                        value={formData.nome}
                        onChange={(v: string) => setFormData(f => ({ ...f, nome: v }))}
                      />
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Tipo de Conta")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Gestor", value: "Gestor" },
                            { label: "Vendedor", value: "Vendedor" }
                          ]}
                          value={formData.tipoConta}
                          onChange={(v) => setFormData(f => ({ ...f, tipoConta: v }))}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* CARD 2: QR CODE 2FA */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm mb-[30px]">
                <button 
                  onClick={() => toggleSection("qrcode")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <QrCode className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Autenticação de 2 Fatores")}
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Escaneie para ativar a página de administração.")}</p>
                    </div>
                  </div>
                  {openSection === "qrcode" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "qrcode" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="flex flex-col items-center justify-center p-[40px] bg-[#F9FAFB] rounded-[12px] border border-[#E5E7EB]">
                      <div className="w-[200px] h-[200px] bg-white border-4 border-[#7C3AED] rounded-[16px] shadow-lg flex items-center justify-center relative p-2">
                        {/* Fake QR Code image for demonstration */}
                        <img 
                          src={`https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=VendorOS-2FA-${formData.tipoConta}-${formData.nome.replace(/\s+/g, '') || 'NovoApp'}`} 
                          alt="QR Code" 
                          className="w-full h-full object-contain"
                        />
                      </div>
                      <h3 className="text-[16px] font-[700] text-[#111827] mt-[24px]">{t("Escaneie o QR Code")}</h3>
                      <p className="text-[14px] text-[#6B7280] text-center max-w-[400px] mt-[8px]">
                        {t("Abra o aplicativo no outro celular e escaneie este código para autorizar o acesso à página de administração.")}
                      </p>
                      
                      <div className="mt-[24px] px-[16px] py-[8px] bg-[#EEF2FF] border border-[#C7D2FE] rounded-[8px] text-[13px] text-[#4F46E5] font-[600]">
                        Conta configurada para: <span className="uppercase font-[800]">{formData.tipoConta}</span>
                      </div>
                    </div>

                  </div>
                )}
              </div>

            </div>
          </div>
        </main>

        {/* BARRA INFERIOR FLUTUANTE (FIXA) PARA SALVAR */}
        <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] px-[32px] flex items-center justify-between z-40">
          <p className="text-[13px] text-[#6B7280] hidden md:block">
            {t("Preencha as informações obrigatórias (")} <span className="text-[#EF4444] font-[700]">*</span> {t(") antes de gerar o código.")}
          </p>
          <div className="flex items-center gap-[12px] ml-auto">
            <button 
              disabled={saving}
              onClick={handleSave}
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px] disabled:opacity-70"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {saving ? t("Gerando...") : t("Gerar QRCode")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
