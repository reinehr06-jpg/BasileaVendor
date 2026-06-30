"use client";

// ============================================================
// MAPA DO TESOURO — Formulário de Filiais
// ============================================================
// PROPÓSITO:
//   Formulário reutilizável para criação e edição de filiais.
//   Utiliza design de accordions (Sanfona) e gerencia 
//   dados básicos, contato, endereço e administrador de acesso inicial.
//
// COMPONENTES:
//   - Acordeon 1: Informações Básicas (Sede/Filial/Congregação, Status, Matriz)
//   - Acordeon 2: Contato & Endereço
//   - Acordeon 3: Acesso & Administração (Vínculo de pessoa e senha de acesso)
//
// DEPENDÊNCIAS:
//   - CustomSelect e CustomDatePicker
// ============================================================

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import {
  Building2,
  MapPin,
  Shield,
  ChevronDown,
  Save,
} from "lucide-react";

type SectionType = "basicas" | "contato" | "acesso" | null;

export default function FilialForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("basicas");

  // Estados Básicos
  const [codigo, setCodigo] = useState("");
  const [tipo, setTipo] = useState("");
  const [matriz, setMatriz] = useState("");
  const [nomeOficial, setNomeOficial] = useState("");
  const [nomeFantasia, setNomeFantasia] = useState("");
  const [cnpj, setCnpj] = useState("");
  const [dataAbertura, setDataAbertura] = useState("");
  const [status, setStatus] = useState("ativa");

  // Estados Contato & Endereço
  const [responsavel, setResponsavel] = useState("");
  const [email, setEmail] = useState("");
  const [telefone, setTelefone] = useState("");
  const [cep, setCep] = useState("");
  const [logradouro, setLogradouro] = useState("");
  const [numero, setNumero] = useState("");
  const [bairro, setBairro] = useState("");
  const [cidade, setCidade] = useState("");
  const [estado, setEstado] = useState("");
  const [pais, setPais] = useState("BR");
  const [fusoHorario, setFusoHorario] = useState("America/Sao_Paulo");
  const [idioma, setIdioma] = useState("pt-BR");

  // Estados Acesso & Admin
  const [administrador, setAdministrador] = useState("");
  const [emailAcesso, setEmailAcesso] = useState("");
  const [senha, setSenha] = useState("");

  React.useEffect(() => {
    if (editId) {
      // Mocked fetch for editing filial
      setCodigo("FL-001");
      setNomeFantasia("Sede Principal");
      setTipo("sede");
      setPais("BR");
      setFusoHorario("America/Sao_Paulo");
      setIdioma("pt-BR");
      setStatus("ativa");
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
              <Building2 className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar filial") : t("Nova filial")}</h1>
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações cadastrais da filial.") : t("Cadastre uma nova filial da igreja.")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[16px] flex flex-col gap-[12px]">

            {/* SEÇÃO 1: INFORMAÇÕES BÁSICAS */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "basicas" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("basicas")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "basicas" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <Building2 className={`w-[18px] h-[18px] transition-colors ${openSection === "basicas" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">
                      {t("Informações Básicas")} <span className="text-[#EF4444] ml-0.5">*</span>
                    </h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Identidade, vínculo e dados principais.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "basicas" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "basicas" ? "max-h-[2000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    <InputField label={t("Código da filial")} placeholder={t("Ex: FL-001")} required={true} value={codigo} onChange={setCodigo} />
                    <InputField 
                      label={t("Tipo de unidade")} 
                      type="select" 
                      required={true}
                      value={tipo}
                      onChange={setTipo}
                      options={[
                        {value: "sede", label: "Sede"},
                        {value: "filial", label: "Filial"},
                        {value: "congregacao", label: "Congregação"},
                        {value: "campus", label: "Campus"}
                      ]}
                    />
                    <div className="col-span-2">
                      <InputField label={t("Igreja matriz vinculada")} type="select" disabled={true} value="1" options={[{value:"1", label:"Sede Principal"}]} />
                    </div>
                    
                    <div className="col-span-2 lg:col-span-4 h-px bg-[#E5E7EB] w-full my-[4px]"></div>

                    <div className="col-span-2"><InputField label={t("Nome oficial da filial")} placeholder={t("Razão social ou nome de registro")} required={true} value={nomeOficial} onChange={setNomeOficial} /></div>
                    <div className="col-span-2"><InputField label={t("Nome fantasia")} placeholder={t("Ex: Basileia Sul")} required={true} value={nomeFantasia} onChange={setNomeFantasia} /></div>
                    
                    {tipo !== "congregacao" && (
                      <InputField label={t("CNPJ da filial")} placeholder="00.000.000/0000-00" required={true} value={cnpj} onChange={setCnpj} />
                    )}
                    <InputField label={t("Data de abertura")} type="date" value={dataAbertura} onChange={setDataAbertura} />
                    
                    <div className="col-span-2">
                      <InputField 
                        label={t("Status inicial")} 
                        type="select" 
                        required={true}
                        value={status}
                        onChange={setStatus}
                        options={[
                          {value: "ativa", label: "Ativa"},
                          {value: "implantacao", label: "Em Implantação"},
                          {value: "inativa", label: "Inativa"}
                        ]}
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* SEÇÃO 2: CONTATO & ENDEREÇO */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "contato" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("contato")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "contato" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <MapPin className={`w-[18px] h-[18px] transition-colors ${openSection === "contato" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Contato & Endereço")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Localização física, timezone e meios de comunicação.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "contato" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "contato" ? "max-h-[2000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px] mb-[20px]">
                    <div className="col-span-2">
                      <InputField label={t("Pastor responsável")} type="select" searchable={true} placeholder={t("Buscar pessoa...")} value={responsavel} onChange={setResponsavel} options={[{value:"1", label:"Pr. João Silva"}]} />
                    </div>
                    <InputField label={t("E-mail principal")} type="email" placeholder="contato@igreja.com" value={email} onChange={setEmail} />
                    <InputField label={t("Telefone / WhatsApp")} placeholder="(00) 00000-0000" value={telefone} onChange={setTelefone} />
                  </div>

                  <div className="flex items-center gap-[8px] mt-[12px] mb-[12px]">
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                    <h3 className="text-[12px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Endereço Físico")}</h3>
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                  </div>

                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    <InputField label={t("País")} type="select" searchable options={[{value:"BR", label:"Brasil"}, {value:"US", label:"Estados Unidos"}]} value={pais} onChange={setPais} />
                    <InputField label="CEP" placeholder="00000-000" value={cep} onChange={setCep} />
                    <div className="col-span-2"><InputField label={t("Logradouro")} placeholder={t("Ex: Av. Principal")} value={logradouro} onChange={setLogradouro} /></div>
                    <InputField label={t("Número")} placeholder="Ex: 100" value={numero} onChange={setNumero} />
                    <InputField label={t("Bairro")} placeholder={t("Ex: Centro")} value={bairro} onChange={setBairro} />
                    <div className="grid grid-cols-2 gap-[12px] col-span-2">
                      <InputField label={t("Cidade")} value={cidade} onChange={setCidade} />
                      <InputField label={t("Estado")} type="select" searchable options={[{value:"SP", label:"São Paulo"}, {value:"SC", label:"Santa Catarina"}]} value={estado} onChange={setEstado} />
                    </div>
                  </div>

                  <div className="flex items-center gap-[8px] mt-[24px] mb-[12px]">
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                    <h3 className="text-[12px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Localização Sistêmica")}</h3>
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                  </div>

                  <div className="grid grid-cols-2 gap-x-[16px] gap-y-[14px]">
                    <InputField 
                      label={t("Fuso horário")} 
                      type="select" 
                      searchable 
                      options={[{value:"America/Sao_Paulo", label:"América/São Paulo (BRT)"}]} 
                      value={fusoHorario} 
                      onChange={setFusoHorario} 
                    />
                    <InputField 
                      label={t("Idioma do sistema")} 
                      type="select" 
                      options={[{value:"pt-BR", label:"Português (Brasil)"}, {value:"en-US", label:"English"}]} 
                      value={idioma} 
                      onChange={setIdioma} 
                    />
                  </div>

                </div>
              </div>
            </div>

            {/* SEÇÃO 3: ACESSO & ADMINISTRAÇÃO */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "acesso" ? "border-[#D1D5DB] shadow-sm overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              <button 
                onClick={() => toggleSection("acesso")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "acesso" ? "bg-gradient-to-br from-[#111827] to-[#374151] shadow-md shadow-gray-900/20" : "bg-[#F3F4F6] group-hover:bg-[#E5E7EB]"}`}>
                    <Shield className={`w-[18px] h-[18px] transition-colors ${openSection === "acesso" ? "text-white" : "text-[#4B5563]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Acesso & Administração")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Acesso inicial para configuração do ambiente da filial.")}</p>
                  </div>
                </div>
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "acesso" ? "rotate-180 text-[#111827]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "acesso" ? "max-h-[1000px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-1 lg:grid-cols-3 gap-x-[16px] gap-y-[14px]">
                    <InputField 
                      label={t("Vincular Pessoa")} 
                      type="select" 
                      searchable 
                      placeholder={t("Buscar membro...")}
                      value={administrador}
                      onChange={setAdministrador}
                      options={[
                        {value:"1", label:"Pr. João Silva"}, 
                      ]} 
                    />
                    <InputField 
                      label={t("E-mail de acesso")} 
                      type="email" 
                      placeholder="usuario@email.com" 
                      value={emailAcesso} 
                      onChange={setEmailAcesso} 
                    />
                    <div className="flex flex-col">
                      <InputField 
                        label={t("Senha de acesso")} 
                        type="password" 
                        placeholder="••••••••" 
                        value={senha} 
                        onChange={setSenha} 
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* AVISO E RODAPÉ DO CARD COM BOTÕES */}
            <div className="mt-[8px] flex items-center justify-between gap-[12px]">
              <p className="text-[12px] text-[#6B7280] ml-[4px]">
                <span className="text-[#EF4444] font-bold">*</span> {t("Campos obrigatórios para salvar a filial.")}
              </p>
              <div className="flex items-center gap-[12px]">
                <Link href="/filiais" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                  {t("Cancelar")}
                </Link>
                <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-gradient-to-r from-[#111827] to-[#374151] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(17,24,39,0.22)] hover:opacity-95 transition-all">
                  <Save className="w-[16px] h-[16px]" strokeWidth={2.2} />
                  {t("Salvar filial")}
                </button>
              </div>
            </div>

          </div>

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
