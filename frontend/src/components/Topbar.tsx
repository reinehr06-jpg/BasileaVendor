/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — COMPONENTE: TOPBAR (Barra Superior)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 USADO EM: Todas as páginas do sistema (importado dentro de cada page.tsx)
 * 📁 ARQUIVO: src/components/Topbar.tsx
 *
 * 🎯 OBJETIVO:
 *    Barra horizontal fixa no topo da área de conteúdo (à direita da sidebar).
 *    Contém:
 *      1. Campo de busca global (⌘K) — busca por transações, contas, relatórios
 *      2. Seletor de sistemas (dropdown) — alterna entre Church OS e Finance OS
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/busca-global?q=termo → Busca unificada em transações, contas e relatórios
 *       📌 REGRA: A busca deve ser debounced (300ms) e retornar no máximo 10 resultados agrupados por tipo
 *    2. GET /api/auth/sistemas-disponiveis → Lista de sistemas que o usuário tem acesso
 *       📌 REGRA: Só exibir sistemas para os quais o usuário tem permissão
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client"; // Obrigatório: componente interativo (usa useState, useRef, useEffect)

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState, useRef, useEffect } from "react";
import { useRouter } from "next/navigation"; // Para navegar entre sistemas
import { Search, Layers, DollarSign, Church } from "lucide-react"; // Ícones
import { useTranslation } from "react-i18next"; // Internacionalização

// ═══════════════════════════════════════════════════════════════════════════════
// 🔝 COMPONENTE: Topbar
// ═══════════════════════════════════════════════════════════════════════════════

export default function Topbar() {
  const { t } = useTranslation(); // Hook de tradução
  const router = useRouter();     // Hook de navegação Next.js

  /*
   * 📦 Estado: Controla se o dropdown de "Meus Sistemas" está aberto ou fechado
   */
  const [isSystemsOpen, setIsSystemsOpen] = useState(false);

  /*
   * 🔗 Ref: Referência ao container do dropdown para detectar cliques fora dele
   */
  const systemsRef = useRef<HTMLDivElement>(null);

  /*
   * 🖱️ Effect: Fecha o dropdown quando o usuário clica fora dele
   *    Isso melhora a UX — o dropdown some automaticamente ao clicar em qualquer outro lugar.
   */
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (systemsRef.current && !systemsRef.current.contains(event.target as Node)) {
        setIsSystemsOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    /* ────── CONTAINER DA TOPBAR ──────
     * Fixo no topo (sticky), altura de 56px, fundo branco com borda inferior cinza
     */
    <header className="h-[56px] bg-white border-b border-[#E5E7EB] px-[32px] flex items-center justify-between shrink-0 sticky top-0 z-50 w-full">

      {/*
       * ══════════════════════════════════════════════════════════════
       * 🔍 BUSCA GLOBAL (lado esquerdo da topbar)
       * ══════════════════════════════════════════════════════════════
       * Campo de texto para buscar transações, contas e relatórios.
       * O atalho ⌘K (Mac) ou Ctrl+K (Windows) pode ser implementado para focar neste campo.
       *
       * 🔗 BACK-END: GET /api/busca-global?q={termo}
       *    Resposta esperada: {
       *      resultados: [
       *        { tipo: "transacao", id: 123, titulo: "Dízimo João", valor: 500 },
       *        { tipo: "conta", id: 5, titulo: "Banco do Brasil - C/C" },
       *        { tipo: "relatorio", id: "dre-2024", titulo: "DRE Junho 2024" }
       *      ]
       *    }
       * 📌 REGRA: Implementar debounce de 300ms no onChange do input
       * 📌 REGRA: Mostrar resultados em um dropdown abaixo do campo de busca
       */}
      <div className="relative flex items-center w-[340px] h-10 bg-[#F9FAFB] border border-[#E5E7EB] rounded-[10px] px-3 transition-all">
        {/* Ícone de lupa (decorativo, à esquerda do input) */}
        <Search className="text-[#9CA3AF] w-4 h-4 mr-2 shrink-0" strokeWidth={2.4} />
        {/* Campo de busca — 🔗 BACK-END: conectar onChange com a API de busca global */}
        <input 
          type="text" 
          placeholder={t("Buscar transações, contas, relatórios...")} 
          className="bg-transparent border-none outline-none text-[13px] text-[#374151] placeholder-[#9CA3AF] w-full"
        />
        {/* Indicador de atalho de teclado (⌘K) */}
        <span className="text-[#9CA3AF] text-[12px] font-medium shrink-0 ml-2">{t("⌘K")}</span>
      </div>

      {/*
       * ══════════════════════════════════════════════════════════════
       * ⚙️ AÇÕES (lado direito da topbar)
       * ══════════════════════════════════════════════════════════════
       */}
      <div className="flex items-center gap-5">
        
        {/*
         * 📱 SELETOR DE SISTEMAS (Dropdown)
         * Permite ao usuário alternar entre os módulos do ecossistema Basileia:
         *   - Basileia Church OS (gestão eclesiástica — membros, cultos, etc.)
         *   - Basileia Finance OS (gestão financeira — este sistema atual)
         *
         * 🔗 BACK-END: GET /api/auth/sistemas-disponiveis
         *    Resposta: [
         *      { id: "church", nome: "Basileia Church OS", descricao: "Gestão eclesiástica", url: "/church" },
         *      { id: "finance", nome: "Basileia Finance OS", descricao: "Gestão financeira", url: "/" }
         *    ]
         * 📌 REGRA: Só mostrar sistemas onde o usuário tem permissão de acesso
         * 📌 REGRA: Ao clicar num sistema, redirecionar para a URL dele
         */}
        <div className="relative" ref={systemsRef}>
          {/* Botão que abre/fecha o dropdown (ícone de camadas) */}
          <button 
            onClick={() => setIsSystemsOpen(!isSystemsOpen)}
            className={`transition-colors p-2 rounded-full ${isSystemsOpen ? 'bg-gray-100 text-[#374151]' : 'text-[#6B7280] hover:text-[#374151]'}`}
          >
            <Layers className="w-[22px] h-[22px]" strokeWidth={2.2} />
          </button>

          {/* ────── DROPDOWN DE SISTEMAS (aparece quando isSystemsOpen = true) ────── */}
          {isSystemsOpen && (
            <div className="absolute right-0 mt-2 w-[280px] bg-white border border-[#E5E7EB] rounded-[14px] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden">
              {/* Cabeçalho do dropdown */}
              <div className="px-4 py-3 border-b border-[#F1F1F4]">
                <h3 className="text-[13px] font-[700] text-[#1A1A2E]">{t("Meus Sistemas")}</h3>
              </div>
              <div className="p-2">
                {/*
                 * SISTEMA 1: Basileia Church OS
                 * 🔗 BACK-END: Ao clicar, redirecionar para a URL do Church OS
                 *    📌 REGRA: A URL deve vir da API, não hardcoded
                 */}
                <button className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-[#F9FAFB] rounded-[10px] transition-colors group mt-1">
                  <div className="w-[36px] h-[36px] rounded-[10px] bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100 group-hover:bg-indigo-100 transition-colors">
                    <Church className="w-[18px] h-[18px] text-indigo-600" strokeWidth={2.5} />
                  </div>
                  <div className="flex flex-col items-start">
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{t("Basileia Church OS")}</span>
                    <span className="text-[11px] font-[500] text-[#6B7280]">{t("Gestão eclesiástica")}</span>
                  </div>
                </button>

                {/*
                 * SISTEMA 2: Basileia Finance OS (este sistema — ativo)
                 * 🔗 BACK-END: Ao clicar, redirecionar para / (Dashboard financeiro)
                 */}
                <button 
                  onClick={() => router.push("/")}
                  className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-[#F9FAFB] rounded-[10px] transition-colors group mt-1"
                >
                  <div className="w-[36px] h-[36px] rounded-[10px] bg-green-50 flex items-center justify-center shrink-0 border border-green-100 group-hover:bg-green-100 transition-colors">
                    <DollarSign className="w-[18px] h-[18px] text-green-600" strokeWidth={2.5} />
                  </div>
                  <div className="flex flex-col items-start">
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{t("Basileia Finance OS")}</span>
                    <span className="text-[11px] font-[500] text-[#6B7280]">{t("Gestão financeira")}</span>
                  </div>
                </button>
              </div>
            </div>
          )}
        </div>

      </div>
    </header>
  );
}
