"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { UsersRound, Tag, Save, History, Clock, LogOut, AlertCircle } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Cadastros / Ministérios (Novo)
// ============================================================
// PROPÓSITO:
//   Formulário para cadastro de um novo ministério com vínculo
//   do líder responsável.
//
// INPUTS / FORMULÁRIOS:
//   - Input de nome do ministério (texto)
//   - Select de líder do ministério (buscável)
//
// BOTÕES DE AÇÃO:
//   - Cancelar → redireciona para /cadastros/ministerios
//   - Salvar ministério → submete o formulário
//
// COMPORTAMENTOS:
//   - Campo "Nome do ministério" obrigatório
//   - Campo "Líder do ministério" obrigatório
//
// INTEGRAÇÃO BACKEND:
//   - POST /api/ministerios (salvar novo ministério)
//   - GET /api/membros (carregar lista de líderes)
//
// #pag21 — Ministérios (Novo)
// ============================================================

export default function MinisterioForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [lider, setLider] = useState("");
  const [nome, setNome] = useState("");
  const [isAtivo, setIsAtivo] = useState(true);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");

  // Mocked data load for Edit Mode
  React.useEffect(() => {
    if (editId) {
      setNome("Ministério de Louvor");
      setLider("lucas");
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
            {/* MAPA DO TESOURO: Page header icon - UsersRound */}
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <UsersRound className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              {/* MAPA DO TESOURO: Page title */}
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar ministério") : t("Novo ministério")}</h1>
              {/* MAPA DO TESOURO: Page subtitle description */}
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações do ministério.") : t("Cadastre um novo ministério e vincule seu líder responsável.")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          {/* MAPA DO TESOURO: Main form card */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px]">

            {/* Cabeçalho do Card */}
            <div className="flex flex-col gap-[12px]">
              <div className="flex items-center gap-[8px]">
                <Tag className="w-[18px] h-[18px] text-[#4B5563]" strokeWidth={2.2} />
                {/* MAPA DO TESOURO: Section title "Informações do ministério" */}
                <h2 className="text-[16px] font-[700] text-[#1A1A2E]">{t("Informações do ministério")}</h2>
              </div>
              <div className="h-px bg-[#E5E7EB] w-full"></div>
            </div>

            {/* Formulário */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px] mt-2 mb-[60px]">
              {/* Coluna 1: Nome do ministério */}
              <div className="flex flex-col gap-[6px] group">
                {/* MAPA DO TESOURO: Label - Nome do ministério (obrigatório) */}
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Nome do ministério")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                {/* MAPA DO TESOURO: Input for ministry name */}
                <input 
                  type="text" 
                  value={nome}
                  onChange={(e) => setNome(e.target.value)}
                  placeholder={t("Digite o nome do ministério")}
                  className="h-[42px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] [outline:0] focus-visible:[outline:0] transition-all hover:border-[#D1D5DB]"
                />
              </div>

              {/* Coluna 2: Líder do ministério */}
              <div className="flex flex-col gap-[6px]">
                {/* MAPA DO TESOURO: Label - Líder do ministério (obrigatório) */}
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Líder do ministério")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                {/* MAPA DO TESOURO: CustomSelect for ministry leader (buscável) */}
                <CustomSelect 
                  value={lider}
                  onChange={setLider}
                  placeholder={t("Selecione o líder responsável")}
                  searchable={true}
                  className="h-[42px]"
                  options={[
                    { value: "lucas", label: "Pr. Lucas Ferreira" },
                    { value: "ana", label: "Pra. Ana Souza" },
                    { value: "vinicius", label: "Vinicius Reinehr" }
                  ]}
                />
                {/* MAPA DO TESOURO: Helper text for leader select */}
                <p className="text-[12px] text-[#6B7280] mt-1 ml-1">{t("Selecione o responsável pelo ministério")}</p>
              </div>
            </div>

            {/* SEPARADOR E BOTÕES */}
            <div className="border-t border-[#F1F1F4] pt-[20px] flex items-center justify-end gap-[12px]">
              {/* MAPA DO TESOURO: Cancel link - redirects to /cadastros/ministerios */}
              <Link href="/cadastros/ministerios" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                {t("Cancelar")}
              </Link>
              {/* MAPA DO TESOURO: Save button - submits form via POST /api/ministerios */}
              <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-[#5B21B6] hover:bg-[#4C1D95] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(76,29,149,0.22)] transition-all">
                Salvar ministério
              </button>
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO E INATIVAÇÃO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              
              {/* Bloco de Status */}
              <div className="flex flex-col gap-[8px]">
                <span className="text-[13px] font-[600] text-[#4B5563]">{t("Status do Ministério")}</span>
                <button 
                  type="button"
                  onClick={() => setIsAtivo(!isAtivo)}
                  className="flex items-center gap-[12px] w-fit focus:outline-none group"
                >
                  <div className={`relative w-[44px] h-[24px] rounded-full transition-colors duration-300 ${isAtivo ? 'bg-[#10B981]' : 'bg-[#E5E7EB]'}`}>
                    <div className={`absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform duration-300 ${isAtivo ? 'translate-x-[20px]' : 'translate-x-0'}`} />
                  </div>
                  <span className={`text-[14px] font-[600] transition-colors ${isAtivo ? 'text-[#10B981]' : 'text-[#6B7280]'}`}>
                    {isAtivo ? "Ministério Ativo" : "Ministério Inativo"}
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
                          {value: "encerramentos", label: "Encerramento das Atividades"},
                          {value: "fusao", label: "Fusão com outro Ministério"},
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
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico do Ministério")}</h2>
              </div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo Ativo</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">3 anos</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <UsersRound className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Membros Atuais</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">24 membros</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">10/05/2023</p>
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
                    <span className="text-[13px] text-[#1A1A2E]">Alteração do líder do ministério</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Admin Principal</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">10/05/2023 18:15</span>
                    <span className="text-[13px] text-[#1A1A2E]">Criação inicial do ministério</span>
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
