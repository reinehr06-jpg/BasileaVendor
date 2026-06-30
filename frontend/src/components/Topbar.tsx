"use client";

import React, { useState, useRef, useEffect } from "react";
import { usePathname, useRouter } from "next/navigation";
import { Search, Bell, ChevronDown, Layers, Activity, DollarSign, CheckCircle2, MessageCircle, Church } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Shell / Topbar
// ============================================================
// PROPÓSITO:
//   Barra superior fixa (sticky) com busca global, seletor de
//   sistemas e indicador de conexões.
//
// INPUTS / PROPS:
//   Nenhum — componentes internos com estado local.
//
// BOTÕES DE AÇÃO / EVENTOS:
//   Systems dropdown → router.push("/"), router.push("/help/chat")
//   Connections dropdown → exibe saúde das conexões
//   Clique fora → fecha dropdowns (handleClickOutside)
//
// COMPORTAMENTOS:
//   - Busca global com placeholder e atalho ⌘K
//   - Systems dropdown: alterna entre Church, Finance, Help
//   - Connections dropdown: indicador de saúde verde + botão "Revisar"
//   - Dropdowns mutuamente exclusivos (apenas 1 aberto por vez)
//   - Sticky no topo com z-50
//
// #pag69 — Topbar
// ============================================================
export default function Topbar() {
  const { t } = useTranslation();
  const router = useRouter();
  const [isSystemsOpen, setIsSystemsOpen] = useState(false);
  const [isConnectionsOpen, setIsConnectionsOpen] = useState(false);

  const systemsRef = useRef<HTMLDivElement>(null);
  const connectionsRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (systemsRef.current && !systemsRef.current.contains(event.target as Node)) {
        setIsSystemsOpen(false);
      }
      if (connectionsRef.current && !connectionsRef.current.contains(event.target as Node)) {
        setIsConnectionsOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <header className="h-[56px] bg-white border-b border-[#E5E7EB] px-[32px] flex items-center justify-between shrink-0 sticky top-0 z-50 w-full">
      {/* MAPA DO TESOURO: Busca global com atalho ⌘K */}
      <div className="relative flex items-center w-[340px] h-10 bg-[#F9FAFB] border border-[#E5E7EB] rounded-[10px] px-3  transition-all">
        <Search className="text-[#9CA3AF] w-4 h-4 mr-2 shrink-0" strokeWidth={2.4} />
        <input 
          type="text" 
          placeholder={t("Buscar membros, células, eventos...")} 
          className="bg-transparent border-none outline-none text-[13px] text-[#374151] placeholder-[#9CA3AF] w-full"
        />
        <span className="text-[#9CA3AF] text-[12px] font-medium shrink-0 ml-2">{t("⌘K")}</span>
      </div>

      {/* RIGHT - Ações */}
      <div className="flex items-center gap-5">
        
        {/* MAPA DO TESOURO: Dropdown de seleção de sistemas (Church, Finance, Help) */}
        <div className="relative" ref={systemsRef}>
          <button 
            onClick={() => { setIsSystemsOpen(!isSystemsOpen); setIsConnectionsOpen(false); }}
            className={`transition-colors p-2 rounded-full ${isSystemsOpen ? 'bg-gray-100 text-[#374151]' : 'text-[#6B7280] hover:text-[#374151]'}`}
          >
            <Layers className="w-[22px] h-[22px]" strokeWidth={2.2} />
          </button>

          {isSystemsOpen && (
            <div className="absolute right-0 mt-2 w-[280px] bg-white border border-[#E5E7EB] rounded-[14px] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden">
              <div className="px-4 py-3 border-b border-[#F1F1F4]">
                <h3 className="text-[13px] font-[700] text-[#1A1A2E]">{t("Meus Sistemas")}</h3>
              </div>
              <div className="p-2">
                <button 
                  onClick={() => router.push("/")}
                  className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-[#F9FAFB] rounded-[10px] transition-colors group"
                >
                  <div className="w-[36px] h-[36px] rounded-[10px] bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100 group-hover:bg-indigo-100 transition-colors">
                    <Church className="w-[18px] h-[18px] text-indigo-600" strokeWidth={2.5} />
                  </div>
                  <div className="flex flex-col items-start">
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{t("Basileia Church")}</span>
                    <span className="text-[11px] font-[500] text-[#6B7280]">{t("Gestão eclesiástica")}</span>
                  </div>
                </button>
                <button className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-[#F9FAFB] rounded-[10px] transition-colors group mt-1">
                  <div className="w-[36px] h-[36px] rounded-[10px] bg-green-50 flex items-center justify-center shrink-0 border border-green-100 group-hover:bg-green-100 transition-colors">
                    <DollarSign className="w-[18px] h-[18px] text-green-600" strokeWidth={2.5} />
                  </div>
                  <div className="flex flex-col items-start">
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{t("Basileia Finance")}</span>
                    <span className="text-[11px] font-[500] text-[#6B7280]">{t("Gestão financeira completa")}</span>
                  </div>
                </button>
              </div>
            </div>
          )}
        </div>

        {/* MAPA DO TESOURO: Dropdown de saúde das conexões */}
        <div className="relative" ref={connectionsRef}>
          <button 
            onClick={() => { setIsConnectionsOpen(!isConnectionsOpen); setIsSystemsOpen(false); }}
            className={`transition-colors p-2 rounded-full relative ${isConnectionsOpen ? 'bg-gray-100 text-[#374151]' : 'text-[#6B7280] hover:text-[#374151]'}`}
          >
            <Activity className="w-[22px] h-[22px]" strokeWidth={2.2} />
            {/* Indicador de saúde */}
            <span className="absolute top-[4px] right-[4px] w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
          </button>

          {isConnectionsOpen && (
            <div className="absolute right-0 mt-2 w-[320px] bg-white border border-[#E5E7EB] rounded-[14px] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden">
              <div className="p-5 flex flex-col items-center text-center">
                <div className="w-[48px] h-[48px] rounded-full bg-green-50 flex items-center justify-center mb-3">
                  <CheckCircle2 className="w-[24px] h-[24px] text-green-500" strokeWidth={2.2} />
                </div>
                <h3 className="text-[14px] font-[700] text-[#1A1A2E] mb-1.5">{t("Conexões saudáveis")}</h3>
                <p className="text-[12px] font-[500] text-[#6B7280] mb-5">
                  {t("Todas as conexões estão funcionando perfeitamente! Nenhuma instabilidade detectada.")}
                </p>
                <button className="w-full bg-[#F9FAFB] hover:bg-gray-100 border border-[#E5E7EB] text-[#374151] text-[13px] font-[600] py-2 rounded-[10px] transition-colors">
                  {t("Revisar conexões")}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
