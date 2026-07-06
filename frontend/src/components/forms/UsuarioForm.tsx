"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  UsersRound,
  Save,
  Eye,
  EyeOff,
  History,
  Clock,
  LogOut,
  AlertCircle
} from "lucide-react";

export default function UsuarioForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [nome, setNome] = useState("");
  const [email, setEmail] = useState("");
  const [telefone, setTelefone] = useState("");
  const [nivelAcesso, setNivelAcesso] = useState("");
  const [isAtivo, setIsAtivo] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");

  // Mocked data load for Edit Mode
  React.useEffect(() => {
    if (editId) {
      setNome("João da Silva");
      setEmail("joao@exemplo.com");
      setTelefone("(11) 98765-4321");
      setNivelAcesso("operario");
      setIsAtivo(true);
    }
  }, [editId]);

  const InputField = ({ label, type = "text", placeholder = "", disabled = false, options = [], required = false, value, onChange }: any) => (
    <div className="flex flex-col gap-[4px] group relative">
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
        />
      ) : (
        <div className="relative">
          <input 
            type={type === "password" && showPassword ? "text" : type} 
            placeholder={placeholder}
            disabled={disabled}
            value={value}
            onChange={onChange ? (e) => onChange(e.target.value) : undefined}
            className={`w-full h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none  transition-all hover:border-[#D1D5DB] ${disabled ? 'bg-[#F3F4F6] text-[#9CA3AF] cursor-not-allowed' : ''} ${type === "password" ? "pr-[40px]" : ""}`}
          />
          {type === "password" && (
            <button 
              type="button" 
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-[12px] top-1/2 -translate-y-1/2 text-[#9CA3AF] hover:text-[#6B7280] transition-colors"
            >
              {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          )}
        </div>
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
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <UsersRound className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar usuário") : t("Novo usuário")}</h1>
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações cadastrais e visualize o histórico do usuário.") : t("Cadastre um novo usuário para acesso ao sistema.")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL (Sem Accordion) */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[24px]">

            <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
              
              <InputField label={t("Nome completo")} required={true} placeholder={t("Ex: João da Silva")} value={nome} onChange={setNome} />
              <InputField label={t("E-mail (Login)")} type="email" required={true} placeholder={t("joao@exemplo.com")} value={email} onChange={setEmail} />
              
              <InputField label={t("Telefone / WhatsApp")} placeholder="(00) 00000-0000" value={telefone} onChange={setTelefone} />
              <InputField label={t("Senha de acesso")} type="password" required={!editId} placeholder={editId ? t("Deixe em branco para não alterar") : t("Mínimo de 8 caracteres")} />

              <InputField label={t("Nível de Acesso")} required={true} type="select" value={nivelAcesso} onChange={setNivelAcesso} options={[
                {value:"administrador", label:"Administrador"}, 
                {value:"operario", label:"Operário"},
                {value:"financeiro", label:"Financeiro"},
                {value:"cuidado", label:"Cuidado"}
              ]} />

              {/* Flag Ativo/Inativo */}
              <div className="flex flex-col gap-[8px] justify-center pt-[6px]">
                <span className="text-[13px] font-[600] text-[#4B5563]">{t("Status do Usuário")}</span>
                <button 
                  type="button"
                  onClick={() => setIsAtivo(!isAtivo)}
                  className="flex items-center gap-[12px] w-fit focus:outline-none group"
                >
                  <div className={`relative w-[44px] h-[24px] rounded-full transition-colors duration-300 ${isAtivo ? 'bg-[#10B981]' : 'bg-[#E5E7EB]'}`}>
                    <div className={`absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform duration-300 ${isAtivo ? 'translate-x-[20px]' : 'translate-x-0'}`} />
                  </div>
                  <span className={`text-[14px] font-[600] transition-colors ${isAtivo ? 'text-[#10B981]' : 'text-[#6B7280]'}`}>
                    {isAtivo ? "Usuário Ativo" : "Usuário Inativo"}
                  </span>
                </button>
              </div>
            </div>

            {/* Campos Condicionais de Inativação */}
            {!isAtivo && (
              <div className="flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300 p-[16px] bg-[#FFF1F2] border border-[#FECDD3] rounded-[8px] mt-2">
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
                    required={true}
                    options={[
                      {value: "desligamento", label: "Desligamento"},
                      {value: "afastamento", label: "Afastamento"},
                      {value: "outro", label: "Outro Motivo"}
                    ]} 
                  />
                  <InputField 
                    label={t("Descrição do Motivo")} 
                    value={descricaoInativacao} 
                    onChange={setDescricaoInativacao} 
                    required={true} 
                    placeholder="Descreva o motivo da desativação"
                  />
                </div>
              </div>
            )}

            {/* AVISO E RODAPÉ DO CARD COM BOTÕES */}
            <div className="mt-[16px] pt-[20px] border-t border-[#F1F1F4] flex items-center justify-between gap-[12px]">
              <p className="text-[12px] text-[#6B7280] ml-[4px]">
                <span className="text-[#EF4444] font-bold">*</span> Campos obrigatórios para salvar o usuário.
              </p>
              <div className="flex items-center gap-[12px]">
                <Link href="/usuarios" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                  {t("Cancelar")}
                </Link>
                <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-gradient-to-r from-[#6D28D9] to-[#6D28D9] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(109,40,217,0.22)] hover:opacity-95 transition-all">
                  <Save className="w-[16px] h-[16px]" strokeWidth={2.2} />
                  Salvar usuário
                </button>
              </div>
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              <div className="flex items-center gap-[8px]">
                <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico do Usuário")}</h2>
              </div>
              <div className="h-px bg-[#E5E7EB] w-full"></div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo Total Online</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">124h 30m</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <LogOut className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Último Acesso</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">Hoje às 08:42</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">15/03/2026</p>
                </div>
              </div>

              {/* Tabela de Ações / Timeline */}
              <div className="mt-4 border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <div className="bg-[#FCFCFD] px-[20px] py-[12px] border-b border-[#E5E7EB] grid grid-cols-[1fr_2fr_1fr] gap-[16px]">
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data / Hora</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ação Realizada</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">IP de Acesso</span>
                </div>
                <div className="flex flex-col">
                  {/* Item 1 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 14:32</span>
                    <span className="text-[13px] text-[#1A1A2E]">Edição de cadastro do membro <strong>Maria Souza</strong></span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">192.168.1.15</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 08:42</span>
                    <span className="text-[13px] text-[#1A1A2E]">Login no sistema</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">192.168.1.15</span>
                  </div>
                  {/* Item 3 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">10/06/2026 18:15</span>
                    <span className="text-[13px] text-[#1A1A2E]">Inativação da célula <strong>Centro</strong></span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">177.32.14.88</span>
                  </div>
                  {/* Item 4 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">10/06/2026 16:00</span>
                    <span className="text-[13px] text-[#1A1A2E]">Criação de novo ministério <strong>Louvor</strong></span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">177.32.14.88</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[16px] pb-[12px]">
            <p className="text-[13px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Basiléia")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
