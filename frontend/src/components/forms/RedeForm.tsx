"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { Network, PencilLine, Save, History, Clock, LogOut, AlertCircle, UsersRound } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Cadastros / Redes (Novo)
// ============================================================
// PROPÓSITO:
//   Formulário para cadastro de uma nova rede com vínculo do
//   pastor responsável.
//
// INPUTS / FORMULÁRIOS:
//   - Input de nome da rede (texto)
//   - Select de pastor de rede (buscável)
//
// BOTÕES DE AÇÃO:
//   - Cancelar → redireciona para /cadastros/redes
//   - Salvar rede → submete o formulário
//
// COMPORTAMENTOS:
//   - Campo "Nome da rede" obrigatório
//   - Campo "Pastor de rede" obrigatório
//
// INTEGRAÇÃO BACKEND:
//   - POST /api/redes (salvar nova rede)
//   - GET /api/pastores (carregar lista de pastores)
//
// #pag24 — Redes (Novo)
// ============================================================

export default function RedeForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [pastor, setPastor] = useState("");
  const [nome, setNome] = useState("");
  const [isAtivo, setIsAtivo] = useState(true);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");

  // Mocked data load for Edit Mode
  React.useEffect(() => {
    if (editId) {
      setNome("Rede Esperança");
      setPastor("lucas");
      setIsAtivo(true);
      // Preencher outros estados seria feito aqui com dados reais da API
    }
  }, [editId]);

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      {/* MAPA DO TESOURO: Sidebar navigation */}
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* MAPA DO TESOURO: Topbar with user info */}
        <Topbar />

        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col">

          {/* CABEÇALHO DA PÁGINA */}
          <div className="flex items-center gap-[14px] py-[16px] pb-[16px]">
            {/* MAPA DO TESOURO: Page header icon - Network */}
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <Network className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              {/* MAPA DO TESOURO: Page title */}
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar rede") : t("Nova rede")}</h1>
              {/* MAPA DO TESOURO: Page subtitle description */}
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações da rede.") : t("Cadastre uma nova rede e vincule seu pastor responsável.")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          {/* MAPA DO TESOURO: Main form card */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px]">

            {/* Cabeçalho do Card */}
            <div className="flex flex-col gap-[12px]">
              <div className="flex items-center gap-[8px]">
                <PencilLine className="w-[18px] h-[18px] text-[#4B5563]" strokeWidth={2.2} />
                {/* MAPA DO TESOURO: Section title "Informações da rede" */}
                <h2 className="text-[16px] font-[700] text-[#1A1A2E]">{t("Informações da rede")}</h2>
              </div>
              <div className="h-px bg-[#E5E7EB] w-full"></div>
            </div>

            {/* Formulário */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px] mt-2 mb-[60px]">
              {/* Coluna 1: Nome da rede */}
              <div className="flex flex-col gap-[6px] group">
                {/* MAPA DO TESOURO: Label - Nome da rede (obrigatório) */}
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Nome da rede")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                {/* MAPA DO TESOURO: Input for network name */}
                <input 
                  type="text" 
                  value={nome}
                  onChange={(e) => setNome(e.target.value)}
                  placeholder={t("Digite o nome da rede")}
                  className="h-[42px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none  transition-all hover:border-[#D1D5DB]"
                />
              </div>

              {/* Coluna 2: Pastor de rede */}
              <div className="flex flex-col gap-[6px]">
                {/* MAPA DO TESOURO: Label - Pastor de rede (obrigatório) */}
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Pastor de rede")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                {/* MAPA DO TESOURO: CustomSelect for network pastor (buscável) */}
                <CustomSelect 
                  value={pastor}
                  onChange={setPastor}
                  placeholder={t("Selecione o pastor responsável")}
                  searchable={true}
                  className="h-[42px]"
                  options={[
                    { value: "joao", label: "Pr. João da Silva" },
                    { value: "lucas", label: "Pr. Lucas Ferreira" },
                    { value: "marcos", label: "Pr. Marcos Vinícius" }
                  ]}
                />
                {/* MAPA DO TESOURO: Helper text for pastor select */}
                <p className="text-[12px] text-[#6B7280] mt-1 ml-1">{t("Selecione o responsável pela rede")}</p>
              </div>
            </div>

            {/* SEPARADOR E BOTÕES */}
            <div className="border-t border-[#F1F1F4] pt-[20px] flex items-center justify-end gap-[12px]">
              {/* MAPA DO TESOURO: Cancel link - redirects to /cadastros/redes */}
              <Link href="/cadastros/redes" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                {t("Cancelar")}
              </Link>
              {/* MAPA DO TESOURO: Save button - submits form via POST /api/redes */}
              <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(109,40,217,0.22)] transition-all">
                Salvar rede
              </button>
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO E INATIVAÇÃO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              
              {/* Bloco de Status */}
              <div className="flex flex-col gap-[8px]">
                <span className="text-[13px] font-[600] text-[#4B5563]">{t("Status da Rede")}</span>
                <button 
                  type="button"
                  onClick={() => setIsAtivo(!isAtivo)}
                  className="flex items-center gap-[12px] w-fit focus:outline-none group"
                >
                  <div className={`relative w-[44px] h-[24px] rounded-full transition-colors duration-300 ${isAtivo ? 'bg-[#10B981]' : 'bg-[#E5E7EB]'}`}>
                    <div className={`absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform duration-300 ${isAtivo ? 'translate-x-[20px]' : 'translate-x-0'}`} />
                  </div>
                  <span className={`text-[14px] font-[600] transition-colors ${isAtivo ? 'text-[#10B981]' : 'text-[#6B7280]'}`}>
                    {isAtivo ? "Rede Ativa" : "Rede Inativa"}
                  </span>
                </button>
              </div>

              {/* Campos Condicionais de Inativação */}
              {!isAtivo && (
                <div className="flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300 p-[16px] bg-[#FFF1F2] border border-[#FECDD3] rounded-[8px]">
                  <div className="flex items-center gap-2 text-[#BE123C] mb-2">
                    <AlertCircle className="w-[16px] h-[16px]" strokeWidth={2.4} />
                    <span className="text-[13px] font-[700] uppercase tracking-wider">Motivo da Desativação</span>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <CustomSelect 
                        value={motivoInativacao}
                        onChange={setMotivoInativacao}
                        options={[
                          {value: "encerramento", label: "Encerramento das Atividades"},
                          {value: "fusao", label: "Fusão com outra Rede"},
                          {value: "outro", label: "Outro Motivo"}
                        ]}
                      />
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Descrição do Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <input 
                        type="text" 
                        value={descricaoInativacao} 
                        onChange={(e) => setDescricaoInativacao(e.target.value)}
                        placeholder="Descreva o motivo" 
                        className="h-[42px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none transition-all" 
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="h-px bg-[#E5E7EB] w-full my-2"></div>

              {/* Título Histórico */}
              <div className="flex items-center gap-[8px]">
                <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico da Rede")}</h2>
              </div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo Ativa</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">5 anos</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <UsersRound className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Membros Atuais</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">120 membros</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">01/01/2021</p>
                </div>
              </div>

              {/* Tabela de Ações / Timeline */}
              <div className="mt-2 border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <div className="bg-[#FCFCFD] px-[20px] py-[12px] border-b border-[#E5E7EB] grid grid-cols-[1fr_2fr_1fr] gap-[16px]">
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data / Hora</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ação Realizada</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">Responsável</span>
                </div>
                <div className="flex flex-col">
                  {/* Item 1 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 14:32</span>
                    <span className="text-[13px] text-[#1A1A2E]">Alteração do pastor da rede</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Admin Principal</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">01/01/2021 18:15</span>
                    <span className="text-[13px] text-[#1A1A2E]">Criação inicial da rede</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Pr. Leandro</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* RODAPÉ COPYRIGHT */}
          {/* MAPA DO TESOURO: Footer copyright */}
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
