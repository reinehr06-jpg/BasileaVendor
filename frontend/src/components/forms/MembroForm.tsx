"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect, { SelectOption } from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import {
  UsersRound,
  UserRound,
  ClipboardList,
  BadgeCheck,
  Home,
  ChevronDown,
  ChevronUp,
  Save,
  History,
  Clock,
  LogOut,
  AlertCircle
} from "lucide-react";

type SectionType = "obrigatorias" | "opcionais" | "perfil" | "grupos" | null;

// ============================================================
// MAPA DO TESOURO — Cadastros / Membros (Novo)
// ============================================================
// PROPÓSITO:
//   Formulário de cadastro completo de novo membro com seções
//   em accordion (obrigatórias, opcionais, perfil, grupos).
//
// INPUTS / FORMULÁRIOS:
//   [accordion] Dados Obrigatórios → nome, CPF, data nasc., tel., sexo, estado civil etc.
//   [accordion] Dados Opcionais → naturalidade, escolaridade, profissão, sangue etc.
//   [accordion] Perfil do Membro → cargo, ministérios, redes, líder, data batismo etc.
//   [accordion] Grupos e Conexões → células, cursos, eventos
//
// BOTÕES DE AÇÃO:
//   [Link] Cancelar → volta para listagem de membros
//   [button] Salvar alterações → POST /api/membros
//
// COMPORTAMENTOS:
//   - Accordion: apenas uma seção aberta por vez
//   - Seções com validação de campos obrigatórios (*)
//   - Condicionais: exibe campos extras conforme seleção
//
// #pag17 — Membros (Novo)
// ============================================================
export default function MembroForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("obrigatorias");

  // Estados Condicionais
  const [ministerios, setMinisterios] = useState<string[]>([]);
  const [isForeign, setIsForeign] = useState("nao");
  const [isMarried, setIsMarried] = useState("nao");
  const [hasChildren, setHasChildren] = useState("nao");
  const [isBaptized, setIsBaptized] = useState("nao");
  const [childrenCount, setChildrenCount] = useState<number>(0);
  const [memberStatus, setMemberStatus] = useState("Ativo");

  // Estados Perfil & Redes
  const [perfil, setPerfil] = useState("");
  const [motivoPerfil, setMotivoPerfil] = useState("");
  const [rede, setRede] = useState("");
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");

  // Mocked data load for Edit Mode
  React.useEffect(() => {
    if (editId) {
      // Mocked fetch for editing member
      setMemberStatus("Ativo");
      setMinisterios(["louvor"]);
      setIsMarried("sim");
      // Preencher outros estados seria feito aqui com dados reais da API
    }
  }, [editId]);

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const InputField = ({ label, type = "text", placeholder = "", disabled = false, options = [], required = false, value, onChange, multiple = false, searchable = false }: any) => (
    <div className="flex flex-col gap-[4px] group">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label}
        {required && <span className="text-[#EF4444] ml-1">*</span>}
      </label>
      {type === "select" ? (
        <CustomSelect 
          options={options}
          value={value}
          onChange={onChange}
          placeholder={placeholder || "Selecione"}
          disabled={disabled}
          multiple={multiple}
          searchable={searchable}
        />
      ) : type === "date" ? (
        <CustomDatePicker
          value={value}
          onChange={onChange}
          placeholder={placeholder || "DD/MM/AAAA"}
          disabled={disabled}
        />
      ) : type === "textarea" ? (
        <textarea 
          placeholder={placeholder}
          disabled={disabled}
          value={value}
          onChange={onChange ? (e) => onChange(e.target.value) : undefined}
          className={`min-h-[80px] p-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] [outline:0] focus-visible:[outline:0] transition-all hover:border-[#D1D5DB] resize-y ${disabled ? 'bg-[#F3F4F6] text-[#9CA3AF] cursor-not-allowed' : ''}`}
        />
      ) : (
        <input 
          type={type} 
          placeholder={placeholder}
          disabled={disabled}
          value={value}
          onChange={onChange ? (e) => onChange(e.target.value) : undefined}
          min={type === "number" ? 0 : undefined}
          className={`h-[38px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] [outline:0] focus-visible:[outline:0] transition-all hover:border-[#D1D5DB] ${disabled ? 'bg-[#F3F4F6] text-[#9CA3AF] cursor-not-allowed' : ''}`}
        />
      )}
    </div>
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        <Topbar />

        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col">

          {/* CABEÇALHO DA PÁGINA */}
          <div className="flex items-center gap-[14px] py-[16px] pb-[16px]">
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F3F4F6] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <UsersRound className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar membro") : t("Novo membro")}</h1>
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações cadastrais do membro.") : t("Cadastre um novo membro da sua igreja.")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[16px] flex flex-col gap-[12px]">

            {/* SEÇÃO 1: INFORMAÇÕES BÁSICAS */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "obrigatorias" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("obrigatorias")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "obrigatorias" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <UserRound className={`w-[18px] h-[18px] transition-colors ${openSection === "obrigatorias" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">
                      {t("Informações Básicas")} <span className="text-[#EF4444] ml-0.5">*</span>
                    </h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Dados essenciais para o cadastro.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "obrigatorias" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "obrigatorias" ? "max-h-[2000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-2 lg:grid-cols-3 gap-x-[16px] gap-y-[14px]">
                    <div className="col-span-2"><InputField label={t("Nome completo")} placeholder={t("Ex: João da Silva")} /></div>
                    <InputField label={t("Data de nascimento")} type="date" value="" />
                    <InputField label={t("Telefone / WhatsApp")} placeholder="(00) 00000-0000" />
                    <InputField label={t("Sexo")} type="select" options={[{value:"M", label:"Masculino"}, {value:"F", label:"Feminino"}]} />
                    <InputField label={t("Documento (CPF/RG)")} placeholder="000.000.000-00" />
                    {/* MAPA DO TESOURO: Status define se o membro está ativo ou inativo na igreja. Se "Inativo", o back DEVE bloquear notificações automaticamente. */}
                    <div className="flex flex-col gap-[4px]">
                      <InputField
                        label={t("Membro Ativo")}
                        type="select"
                        value={memberStatus}
                        onChange={setMemberStatus}
                        options={[{value:"Ativo", label:t("Ativo")}, {value:"Inativo", label:t("Inativo")}]}
                      />
                      {memberStatus === "Inativo" && (
                        <div className="flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300 p-[16px] bg-[#FFF1F2] border border-[#FECDD3] rounded-[8px] mt-2 col-span-1 lg:col-span-3">
                          <div className="flex items-center gap-2 text-[#BE123C] mb-2">
                            <AlertCircle className="w-[16px] h-[16px]" strokeWidth={2.4} />
                            <span className="text-[13px] font-[700] uppercase tracking-wider">Motivo da Desativação</span>
                          </div>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                            <InputField 
                              label={t("Motivo")} 
                              type="select" 
                              value={motivoInativacao}
                              onChange={setMotivoInativacao}
                              options={[
                                {value: "mudanca", label: "Mudou de Igreja"},
                                {value: "afastamento", label: "Afastamento"},
                                {value: "disciplina", label: "Disciplina"},
                                {value: "outro", label: "Outro Motivo"}
                              ]}
                            />
                            <InputField 
                              label={t("Descrição do Motivo")} 
                              value={descricaoInativacao} 
                              onChange={setDescricaoInativacao}
                              placeholder="Descreva o motivo" 
                            />
                          </div>
                        </div>
                      )}
                    </div>
                    <div className="col-span-2 lg:col-span-3">
                      <InputField 
                        label={t("Ministérios")} 
                        type="select" 
                        multiple={true}
                        searchable={true}
                        value={ministerios}
                        onChange={setMinisterios}
                        placeholder={t("Selecione um ou mais ministérios...")}
                        options={[
                          {value:"louvor", label:"Louvor"}, 
                          {value:"infantil", label:"Infantil"},
                          {value:"jovens", label:"Jovens"},
                          {value:"midia", label:"Mídia & Comunicação"}
                        ]} 
                      />
                    </div>
                  </div>

                  <div className="flex items-center gap-[8px] mt-[24px] mb-[12px]">
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                    <h3 className="text-[12px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Endereço Completo")}</h3>
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                  </div>

                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    <InputField label={t("País")} type="select" searchable options={[{value:"BR", label:"Brasil"}, {value:"US", label:"Estados Unidos"}]} />
                    <InputField label="CEP" placeholder="00000-000" />
                    <div className="col-span-2"><InputField label={t("Rua")} placeholder={t("Ex: Av. Paulista")} /></div>
                    <InputField label={t("Número")} placeholder="Ex: 1000" />
                    <InputField label={t("Complemento")} placeholder={t("Ex: Apto 42")} />
                    <InputField label={t("Bairro")} placeholder={t("Ex: Bela Vista")} />
                    <div className="grid grid-cols-2 gap-[12px]">
                      <InputField label={t("Cidade")} />
                      <InputField label={t("Estado")} type="select" searchable options={[{value:"SP", label:"São Paulo"}, {value:"RJ", label:"Rio de Janeiro"}]} />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* SEÇÃO 2: INFORMAÇÕES OPCIONAIS */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "opcionais" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("opcionais")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "opcionais" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <ClipboardList className={`w-[18px] h-[18px] transition-colors ${openSection === "opcionais" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Informações Opcionais")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Dados complementares para o cadastro.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "opcionais" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "opcionais" ? "max-h-[2000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  
                  {/* Bloco Demográfico */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px] mb-[20px]">
                    <InputField 
                      label={t("É estrangeiro?")} 
                      type="select" 
                      value={isForeign} 
                      onChange={setIsForeign} 
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {isForeign === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Nacionalidade")} type="select" searchable options={[{value:"us", label:"Americano"}, {value:"ar", label:"Argentino"}]} />
                      </div>
                    )}
                    <InputField 
                      label={t("Escolaridade")} 
                      type="select" 
                      options={[
                        {value:"fundamental_incompleto", label:"Fundamental Incompleto"},
                        {value:"fundamental_completo", label:"Fundamental Completo"},
                        {value:"medio_incompleto", label:"Médio Incompleto"},
                        {value:"medio_completo", label:"Médio Completo"},
                        {value:"superior_incompleto", label:"Superior Incompleto"},
                        {value:"superior_completo", label:"Superior Completo"},
                        {value:"pos", label:"Pós-graduação"},
                        {value:"mestrado", label:"Mestrado"},
                        {value:"doutorado", label:"Doutorado"}
                      ]} 
                    />
                    <InputField label={t("Profissão")} placeholder={t("Ex: Engenheiro")} />
                  </div>

                  <div className="h-px bg-[#E5E7EB] w-full mb-[20px]"></div>

                  {/* Bloco Espiritual */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px] mb-[20px]">
                    <InputField 
                      label={t("Batizado?")} 
                      type="select" 
                      value={isBaptized}
                      onChange={setIsBaptized}
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {isBaptized === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Data do batismo")} type="date" value="" />
                      </div>
                    )}
                    <div className="col-span-2">
                      <InputField label={t("Data que aceitou Jesus")} type="date" value="" />
                    </div>
                  </div>

                  <div className="h-px bg-[#E5E7EB] w-full mb-[20px]"></div>

                  {/* Bloco Familiar */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    <InputField 
                      label={t("Casado?")} 
                      type="select" 
                      value={isMarried} 
                      onChange={setIsMarried} 
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {isMarried === "sim" && (
                      <>
                        <div className="col-span-2 animate-in fade-in slide-in-from-top-1">
                          <InputField label={t("Selecionar cônjuge")} type="select" searchable placeholder={t("Buscar membro...")} options={[{value:"1", label:"Maria da Silva"}, {value:"2", label:"Pedro Santos"}]} />
                        </div>
                        <div className="animate-in fade-in slide-in-from-top-1">
                          <InputField label={t("Data de casamento")} type="date" value="" />
                        </div>
                      </>
                    )}
                    
                    <InputField 
                      label={t("Tem filhos?")} 
                      type="select" 
                      value={hasChildren} 
                      onChange={setHasChildren} 
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {hasChildren === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Quantidade de filhos")} type="number" value={childrenCount} onChange={(val: string) => setChildrenCount(parseInt(val) || 0)} placeholder="0" />
                      </div>
                    )}
                    
                    {/* Renderiza selects dinâmicos para filhos */}
                    {hasChildren === "sim" && childrenCount > 0 && (
                      <div className="col-span-2 lg:col-span-4 grid grid-cols-2 lg:grid-cols-4 gap-[16px] mt-2 animate-in fade-in">
                        {Array.from({ length: Math.min(childrenCount, 10) }).map((_, index) => (
                          <InputField key={index} label={`Filho ${index + 1}`} type="select" searchable placeholder={t("Buscar membro...")} options={[{value:"1", label:"Joãozinho"}, {value:"2", label:"Ana"}]} />
                        ))}
                      </div>
                    )}

                  </div>

                </div>
              </div>
            </div>

            {/* SEÇÃO 3: INFORMAÇÕES DO PERFIL */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "perfil" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("perfil")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "perfil" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <BadgeCheck className={`w-[18px] h-[18px] transition-colors ${openSection === "perfil" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Informações do Perfil")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Detalhes sobre o perfil e contatos do membro.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "perfil" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "perfil" ? "max-h-[1000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-2 lg:grid-cols-3 gap-x-[16px] gap-y-[14px]">
                    <InputField 
                      label={t("Perfil")} 
                      type="select" 
                      value={perfil}
                      onChange={setPerfil}
                      options={[
                        {value:"membro", label:"Membro"}, 
                        {value:"visitante", label:"Visitante"},
                        {value:"pastor", label:"Pastor"}
                      ]} 
                    />
                    <InputField 
                      label={t("Motivo do perfil")} 
                      type="select" 
                      value={motivoPerfil}
                      onChange={setMotivoPerfil}
                      options={[
                        {value:"transferencia", label:"Transferência"}, 
                        {value:"batismo", label:"Batismo"},
                        {value:"reconciliacao", label:"Reconciliação"}
                      ]} 
                    />
                    <InputField label={t("Data")} type="date" value="" />
                    
                    <div className="col-span-2 lg:col-span-3 h-px bg-[#E5E7EB] w-full my-[4px]"></div>

                    <div className="col-span-2 lg:col-span-1">
                      <InputField label={t("Celular / Recado")} placeholder="+55 (00) 00000-0000" />
                    </div>

                    <div className="col-span-2 lg:col-span-3 mt-2">
                      <InputField label={t("Observações do perfil")} type="textarea" placeholder={t("Adicione notas adicionais sobre o membro...")} />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* SEÇÃO 4: REDES */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "grupos" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("grupos")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "grupos" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <Home className={`w-[18px] h-[18px] transition-colors ${openSection === "grupos" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Redes")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Adicione o membro a células e redes.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "grupos" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "grupos" ? "max-h-[1000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-2 gap-x-[16px] gap-y-[14px]">
                    <InputField 
                      label={t("Rede")} 
                      type="select" 
                      value={rede}
                      onChange={setRede}
                      options={[
                        {value:"jovens", label:"Rede de Jovens"}, 
                        {value:"casais", label:"Rede de Casais"},
                        {value:"kids", label:"Basileia Kids"}
                      ]} 
                    />
                    <InputField 
                      label={t("Células")} 
                      disabled={true} 
                      placeholder={t("Vinculado pela página de Células")} 
                      value="Nenhuma célula vinculada"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* AVISO E RODAPÉ DO CARD COM BOTÕES */}
            <div className="mt-[8px] flex items-center justify-between gap-[12px]">
              <p className="text-[12px] text-[#6B7280] ml-[4px]">
                <span className="text-[#EF4444] font-bold">*</span> Campos obrigatórios para salvar o membro.
              </p>
              <div className="flex items-center gap-[12px]">
                <Link href="/cadastros/membros" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                  {t("Cancelar")}
                </Link>
                <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-gradient-to-r from-[#111827] to-[#374151] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(17,24,39,0.22)] hover:opacity-95 transition-all">
                  <Save className="w-[16px] h-[16px]" strokeWidth={2.2} />
                  Salvar membro
                </button>
              </div>
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              <div className="flex items-center gap-[8px]">
                <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico do Membro")}</h2>
              </div>
              <div className="h-px bg-[#E5E7EB] w-full"></div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo como Membro</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">2 anos e 3 meses</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <LogOut className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Batismo</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">12/04/2024</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">01/01/2024</p>
                </div>
              </div>

              {/* Tabela de Ações / Timeline */}
              <div className="mt-4 border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <div className="bg-[#FCFCFD] px-[20px] py-[12px] border-b border-[#E5E7EB] grid grid-cols-[1fr_2fr_1fr] gap-[16px]">
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data / Hora</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ação Realizada</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">Responsável</span>
                </div>
                <div className="flex flex-col">
                  {/* Item 1 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 14:32</span>
                    <span className="text-[13px] text-[#1A1A2E]">Atualização de informações de endereço</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">João da Silva</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">10/06/2026 16:00</span>
                    <span className="text-[13px] text-[#1A1A2E]">Inclusão no ministério <strong>Louvor</strong></span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Pr. Leandro</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[16px] pb-[12px]">
            <p className="text-[13px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#111827]">{t("Basiléia")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
