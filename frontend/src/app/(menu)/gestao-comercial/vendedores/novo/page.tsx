"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Users,
  User,
  BadgeCheck,
  Coins,
  ChevronDown,
  ChevronUp,
  Save,
  X,
  Lock
} from "lucide-react";

import { VendedoresService } from "@/services/vendedores.service";
import { EquipesService, Equipe } from "@/services/equipes.service";
import { toast } from "sonner";
import { useRouter } from "next/navigation";

type SectionType = "dados-pessoais" | "funcao" | "comissoes" | null;

export default function NovoVendedorPage() {
  const { t } = useTranslation();
  const router = useRouter();
  const [openSection, setOpenSection] = useState<SectionType>("dados-pessoais");

  const [saving, setSaving] = useState(false);
  const [equipes, setEquipes] = useState<Equipe[]>([]);

  const [formData, setFormData] = useState({
    nome: "",
    email: "",
    telefone: "",
    senha: "",
    is_gestor: false,
    status: "Ativo",
    equipe_id: "",
    percentual_comissao: "0",
    comissao_inicial: "0",
    comissao_recorrencia: "0",
    comissao_gestor_primeira: "0",
    comissao_gestor_recorrencia: "0"
  });

  React.useEffect(() => {
    EquipesService.listar().then(setEquipes);
  }, []);

  const handleSave = async () => {
    setSaving(true);
    const toastId = toast.loading("Salvando...");
    
    const payload = {
      ...formData,
      is_gestor: formData.is_gestor,
      equipe_id: formData.equipe_id ? Number(formData.equipe_id) : undefined,
      percentual_comissao: formData.percentual_comissao ? Number(formData.percentual_comissao) : undefined,
      comissao_inicial: formData.comissao_inicial ? Number(formData.comissao_inicial) : undefined,
      comissao_recorrencia: formData.comissao_recorrencia ? Number(formData.comissao_recorrencia) : undefined,
      comissao_gestor_primeira: formData.comissao_gestor_primeira ? Number(formData.comissao_gestor_primeira) : undefined,
      comissao_gestor_recorrencia: formData.comissao_gestor_recorrencia ? Number(formData.comissao_gestor_recorrencia) : undefined,
    };

    try {
      const res = await VendedoresService.criar(payload as any);
      if (res.success) {
        toast.success("Vendedor criado com sucesso!", { id: toastId });
        router.push("/gestao-comercial/vendedores");
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

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PRINT 1) */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Users className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Novo Vendedor")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Cadastre um novo vendedor e configure suas comissões.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS (DROPDOWNS) */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: DADOS PESSOAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-pessoais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <User className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados Pessoais")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Informações de acesso e contato.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-pessoais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-pessoais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Nome completo")} 
                        placeholder="Ex: João da Silva"
                        required 
                        value={formData.nome}
                        onChange={(v: string) => setFormData(f => ({ ...f, nome: v }))}
                      />
                      <InputField 
                        type="email"
                        label={t("E-mail (Acesso)")} 
                        placeholder="vendedor@email.com"
                        required 
                        value={formData.email}
                        onChange={(v: string) => setFormData(f => ({ ...f, email: v }))}
                      />
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("WhatsApp / Telefone")}
                        </label>
                        <div className="flex items-center h-[40px]">
                          <div className="h-full px-[12px] bg-[#F9FAFB] border border-[#E5E7EB] border-r-0 rounded-l-[8px] flex items-center justify-center text-[#6B7280] text-[14px]">
                            +55
                          </div>
                          <input 
                            type="text" 
                            placeholder="(00) 00000-0000"
                            value={formData.telefone}
                            onChange={(e) => setFormData(f => ({ ...f, telefone: e.target.value }))}
                            className="w-full h-full bg-white border border-[#E5E7EB] rounded-r-[8px] border-l-0 px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          />
                        </div>
                      </div>
                      
                      {/* Senha Provisória Simples */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Senha Provisória")}
                        </label>
                        <div className="h-[40px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] px-[12px] flex items-center gap-[8px] text-[14px] text-[#4B5563] cursor-not-allowed">
                          <Lock className="w-[14px] h-[14px] text-[#9CA3AF]" />
                          <span className="font-[600]">Basileia123</span>
                          <span className="text-[12px] text-[#9CA3AF] ml-auto">{t("(Troca obrigatória)")}</span>
                        </div>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: FUNÇÃO E EQUIPE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("funcao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <BadgeCheck className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Função e Equipe")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Defina o nível de acesso e o líder direto.")}</p>
                    </div>
                  </div>
                  {openSection === "funcao" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "funcao" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Perfil")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Vendedor", value: "false" },
                            { label: "Gestor", value: "true" }
                          ]}
                          value={formData.is_gestor.toString()}
                          onChange={(v) => setFormData(f => ({ ...f, is_gestor: v === "true" }))}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                      
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Status")}
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Ativo", value: "Ativo" },
                            { label: "Inativo", value: "Inativo" }
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
                        {t("Equipe")}
                      </label>
                      <CustomSelect
                        options={[
                          { label: "Sem Equipe", value: "" },
                          ...equipes.map(eq => ({ label: eq.nome, value: eq.id.toString() }))
                        ]}
                        value={formData.equipe_id}
                        onChange={(v) => setFormData(f => ({ ...f, equipe_id: v }))}
                        placeholder="Selecione uma equipe"
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                      />
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 3: COMISSÕES DO VENDEDOR */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("comissoes")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Coins className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Comissões")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Percentuais aplicados às vendas deste membro.")}</p>
                    </div>
                  </div>
                  {openSection === "comissoes" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "comissoes" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Comissão Inicial (Venda)")}
                        type="number"
                        placeholder="Ex: 10"
                        value={formData.comissao_inicial}
                        onChange={(v: string) => setFormData(f => ({ ...f, comissao_inicial: v }))}
                        icon="%"
                      />
                      <InputField 
                        label={t("Comissão Recorrência")}
                        type="number"
                        placeholder="Ex: 5"
                        value={formData.comissao_recorrencia}
                        onChange={(v: string) => setFormData(f => ({ ...f, comissao_recorrencia: v }))}
                        icon="%"
                      />
                    </div>

                    {formData.is_gestor && (
                      <>
                        <div className="w-full h-[1px] bg-[#F3F4F6] my-[4px]"></div>
                        <h3 className="text-[14px] font-[600] text-[#111827] mb-[4px]">{t("Comissões de Gestor (Equipe)")}</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                          <InputField 
                            label={t("Comissão Gestor (1ª Venda)")}
                            type="number"
                            placeholder="Ex: 2"
                            value={formData.comissao_gestor_primeira}
                            onChange={(v: string) => setFormData(f => ({ ...f, comissao_gestor_primeira: v }))}
                            icon="%"
                          />
                          <InputField 
                            label={t("Comissão Gestor (Recorrência)")}
                            type="number"
                            placeholder="Ex: 1"
                            value={formData.comissao_gestor_recorrencia}
                            onChange={(v: string) => setFormData(f => ({ ...f, comissao_gestor_recorrencia: v }))}
                            icon="%"
                          />
                        </div>
                      </>
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
              href="/gestao-comercial/vendedores"
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
              {saving ? t("Salvando...") : t("Salvar")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
