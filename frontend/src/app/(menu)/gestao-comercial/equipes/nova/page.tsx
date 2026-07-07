"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Network,
  Users,
  Target,
  ChevronDown,
  ChevronUp,
  Save
} from "lucide-react";

import { EquipesService } from "@/services/equipes.service";
import { toast } from "sonner";
import { useRouter } from "next/navigation";
import { VendedoresService, Vendedor } from "@/services/vendedores.service";

type SectionType = "dados-equipe" | "metas" | null;

export default function NovaEquipePage() {
  const { t } = useTranslation();
  const router = useRouter();
  const [openSection, setOpenSection] = useState<SectionType>("dados-equipe");

  const [saving, setSaving] = useState(false);
  const [gestores, setGestores] = useState<Vendedor[]>([]);

  const [formData, setFormData] = useState({
    nome: "",
    status: "ativa",
    gestor_id: "",
    meta_mensal: ""
  });

  React.useEffect(() => {
    // Busca apenas vendedores que são gestores para preencher o select
    VendedoresService.listar().then(vendedores => {
      setGestores(vendedores.filter(v => v.is_gestor || v.gestor === 'Gestor' || v.perfil === 'Gestor' || v.gestor === 'Sim' || true)); 
      // Observação: v.is_gestor precisa ser adicionado ao response do VendedorController index, 
      // mas vamos listar todos temporariamente para não travar a UI.
    });
  }, []);

  const handleSave = async () => {
    setSaving(true);
    const toastId = toast.loading("Salvando...");
    
    const payload = {
      ...formData,
      gestor_id: formData.gestor_id ? Number(formData.gestor_id) : undefined,
      meta_mensal: formData.meta_mensal ? Number(formData.meta_mensal.replace(/\D/g, '')) / 100 : 0
    };

    try {
      const res = await EquipesService.criar(payload as any);
      if (res.success) {
        toast.success("Equipe criada com sucesso!", { id: toastId });
        router.push("/gestao-comercial/equipes");
      } else {
        toast.error("Erro ao salvar", { id: toastId });
      }
    } catch (e) {
      toast.error("Erro de comunicação", { id: toastId });
    } finally {
      setSaving(false);
    }
  };

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange, icon, iconLeft }: any) => (
    <div className="flex flex-col gap-[6px]">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
      </label>
      <div className="relative">
        {iconLeft && (
          <div className="absolute inset-y-0 left-[12px] flex items-center pointer-events-none text-[#6B7280] font-[600]">
            {iconLeft}
          </div>
        )}
        <input 
          type={type} 
          placeholder={placeholder}
          defaultValue={value}
          onChange={onChange ? (e) => onChange(e.target.value) : undefined}
          className={`w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] ${icon ? 'pr-[36px]' : ''} ${iconLeft ? 'pl-[36px]' : ''}`}
        />
        {icon && (
          <div className="absolute inset-y-0 right-[12px] flex items-center pointer-events-none text-[#6B7280] font-[600]">
            {icon}
          </div>
        )}
      </div>
    </div>
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PRINT 1) */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Network className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Nova Equipe")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Cadastre uma nova equipe de vendas e defina seus objetivos.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS (DROPDOWNS) */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: DADOS DA EQUIPE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-equipe")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Users className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados da Equipe")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Informações de identificação da equipe comercial.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-equipe" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-equipe" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Nome da Equipe")} 
                        placeholder="Ex: Vendas Corporativas" 
                        required
                        value={formData.nome}
                        onChange={(v: string) => setFormData(f => ({ ...f, nome: v }))}
                      />
                      
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Status")}
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Ativa", value: "ativa" },
                            { label: "Inativa", value: "inativa" }
                          ]}
                          value={formData.status}
                          onChange={(v) => setFormData(f => ({ ...f, status: v }))}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        {t("Gestor Responsável")} <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <CustomSelect
                        options={[
                          { label: "Sem gestor", value: "" },
                          ...gestores.map(g => ({ label: g.nome, value: g.id.toString() }))
                        ]}
                        value={formData.gestor_id}
                        onChange={(v) => setFormData(f => ({ ...f, gestor_id: v }))}
                        placeholder="Selecione o gestor desta equipe"
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                      />
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: METAS DA EQUIPE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("metas")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Target className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Objetivos e Metas")}
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Defina a meta financeira mensal ou anual desta equipe.")}</p>
                    </div>
                  </div>
                  {openSection === "metas" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "metas" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      <div className="flex flex-col gap-[6px]">
                        <InputField 
                          type="number"
                          label={t("Meta de Vendas")} 
                          placeholder="0,00" 
                          value={formData.meta_mensal}
                          onChange={(v: string) => setFormData(f => ({ ...f, meta_mensal: v }))}
                        />
                        <p className="text-[12px] text-[#9CA3AF] mt-1">
                          {t("Valor esperado de produção do time.")}
                        </p>
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
            {t("Preencha as informações obrigatórias (")} <span className="text-[#EF4444] font-[700]">*</span> {t(") antes de salvar.")}
          </p>
          <div className="flex items-center gap-[12px] ml-auto">
            <Link 
              href="/gestao-comercial/equipes"
              className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center"
            >
              {t("Cancelar")}
            </Link>
            <button 
              disabled={saving}
              onClick={handleSave}
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px] disabled:opacity-70"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {saving ? t("Salvando...") : t("Criar Equipe")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
