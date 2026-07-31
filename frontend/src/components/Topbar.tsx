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
 *      2. Seletor de sistemas (dropdown) — alterna entre Church OS e Vendor OS
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
import { useRouter, usePathname } from "next/navigation"; // Para navegar entre sistemas e ler rota atual
import { Search, Layers, DollarSign, Church, HelpCircle, BookOpen, PlayCircle } from "lucide-react"; // Ícones
import { useTranslation } from "react-i18next"; // Internacionalização
import { useTour } from "@/contexts/TourContext";

// ═══════════════════════════════════════════════════════════════════════════════
// 🔝 COMPONENTE: Topbar
// ═══════════════════════════════════════════════════════════════════════════════

export default function Topbar() {
  const { t } = useTranslation();
  const router = useRouter();
  const pathname = usePathname();
  const { startTour } = useTour();

  /*
   * 📦 Estado: Controla se o dropdown de "Meus Sistemas" está aberto ou fechado
   */
  const [isSystemsOpen, setIsSystemsOpen] = useState(false);
  const [isHelpOpen, setIsHelpOpen] = useState(false);

  /*
   * 🔗 Ref: Referência ao container do dropdown para detectar cliques fora dele
   */
  const systemsRef = useRef<HTMLDivElement>(null);
  const helpRef = useRef<HTMLDivElement>(null);

  /*
   * 🖱️ Effect: Fecha o dropdown quando o usuário clica fora dele
   *    Isso melhora a UX — o dropdown some automaticamente ao clicar em qualquer outro lugar.
   */
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (systemsRef.current && !systemsRef.current.contains(event.target as Node)) {
        setIsSystemsOpen(false);
      }
      if (helpRef.current && !helpRef.current.contains(event.target as Node)) {
        setIsHelpOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const getHelpContext = () => {
    if (!pathname) return { title: "Ajuda Contextual", desc: "Utilize o menu lateral para navegar pelas opções." };
    if (pathname === '/' || pathname === '/dashboard' || pathname === '/gestor' || pathname === '/vendedor') {
      return { title: "Dashboard", desc: "Acompanhe seus principais indicadores financeiros, faturamento e métricas de vendas atuais." };
    }
    if (pathname.includes('/vendedores') || pathname.includes('/equipes')) {
      return { title: "Gestão de Equipe", desc: "Gerencie seus vendedores, consulte métricas de desempenho individuais e comissões." };
    }
    if (pathname.includes('/clientes')) {
      return { title: "Carteira de Clientes", desc: "Acompanhe todo o histórico, faturas pendentes e o perfil financeiro dos seus clientes." };
    }
    if (pathname.includes('/links-pagamento')) {
      return { title: "Links de Pagamento", desc: "Crie links rápidos para receber via PIX ou Cartão sem precisar de loja virtual." };
    }
    if (pathname.includes('/configuracoes')) {
      return { title: "Configurações", desc: "Ajuste integrações com Asaas, dados da empresa, permissões e termos de uso do seu sistema." };
    }
    return { title: "Ajuda Contextual", desc: "Dicas e informações sobre a tela que você está visualizando aparecerão aqui." };
  };
  const helpContext = getHelpContext();

  /*
   * 🔍 ESTADOS DA BUSCA GLOBAL
   */
  const [searchTerm, setSearchTerm] = useState("");
  const [searchResults, setSearchResults] = useState<{titulo: string, url: string, tipo: string}[]>([]);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const searchRef = useRef<HTMLDivElement>(null);

  // Páginas mapeadas para a busca
  const paginas = [
    { titulo: "Dashboard", url: "/", tipo: "Página" },
    { titulo: "Transações", url: "/financeiro/transacoes", tipo: "Financeiro" },
    { titulo: "Vendedores", url: "/financeiro/vendedores", tipo: "Equipe" },
    { titulo: "Novo Vendedor", url: "/financeiro/vendedores/novo", tipo: "Ação" },
    { titulo: "Clientes", url: "/financeiro/clientes", tipo: "CRM" },
    { titulo: "Metas", url: "/financeiro/metas", tipo: "Desempenho" },
    { titulo: "Nova Meta", url: "/financeiro/metas/nova", tipo: "Ação" },
    { titulo: "Links de Pagamento", url: "/financeiro/links-pagamento", tipo: "Financeiro" },
    { titulo: "Novo Link de Pagamento", url: "/financeiro/links-pagamento/novo", tipo: "Ação" },
    { titulo: "Termos de Uso", url: "/configuracoes/termos", tipo: "Configurações" },
    { titulo: "Monitor de Vendas", url: "/configuracoes/monitor", tipo: "Configurações" },
    { titulo: "Integrações", url: "/configuracoes/integracoes", tipo: "Configurações" }
  ];

  // Normalizador de string (remove acentos e deixa minúsculo)
  const normalize = (str: string) => {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
  };

  useEffect(() => {
    if (searchTerm.trim().length > 0) {
      const term = normalize(searchTerm);
      const results = paginas.filter(p => normalize(p.titulo).includes(term) || normalize(p.tipo).includes(term));
      setSearchResults(results.slice(0, 5));
      setIsSearchOpen(true);
    } else {
      setSearchResults([]);
      setIsSearchOpen(false);
    }
  }, [searchTerm]);

  useEffect(() => {
    function handleClickOutsideSearch(event: MouseEvent) {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setIsSearchOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutsideSearch);
    return () => document.removeEventListener("mousedown", handleClickOutsideSearch);
  }, []);

  return (
    /* ────── CONTAINER DA TOPBAR ──────
     * Fixo no topo (sticky), altura de 56px, fundo branco com borda inferior cinza
     */
    <header className="h-[56px] bg-white border-b border-[#E5E7EB] px-[32px] flex items-center justify-between shrink-0 sticky top-0 z-50 w-full">

      {/*
       * 🔍 BUSCA GLOBAL
       */}
      <div className="relative" ref={searchRef}>
        <div className="relative flex items-center w-[340px] h-10 bg-[#F9FAFB] border border-[#E5E7EB] rounded-[10px] px-3 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
          <Search className="text-[#9CA3AF] w-4 h-4 mr-2 shrink-0" strokeWidth={2.4} />
          <input 
            type="text" 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            onFocus={() => { if(searchTerm.trim().length > 0) setIsSearchOpen(true); }}
            placeholder={t("Buscar páginas e recursos...")} 
            className="bg-transparent border-none outline-none text-[13px] text-[#374151] placeholder-[#9CA3AF] w-full"
          />
          <span className="text-[#9CA3AF] text-[12px] font-medium shrink-0 ml-2">{t("⌘K")}</span>
        </div>

        {/* Dropdown de Busca */}
        {isSearchOpen && (
          <div className="absolute left-0 mt-2 w-full bg-white border border-[#E5E7EB] rounded-[14px] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden z-50">
            {searchResults.length > 0 ? (
              <div className="p-2">
                <div className="px-2 py-1 mb-1">
                  <span className="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">Páginas Sugeridas</span>
                </div>
                {searchResults.map((res, idx) => (
                  <button 
                    key={idx}
                    onClick={() => {
                      router.push(res.url);
                      setIsSearchOpen(false);
                      setSearchTerm("");
                    }}
                    className="w-full flex items-center justify-between px-3 py-2.5 hover:bg-[#F9FAFB] rounded-[10px] transition-colors group text-left"
                  >
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{res.titulo}</span>
                    <span className="text-[11px] font-[500] text-[#6B7280] bg-[#F3F4F6] px-2 py-0.5 rounded-full">{res.tipo}</span>
                  </button>
                ))}
              </div>
            ) : (
              <div className="p-4 text-center text-[13px] text-[#6B7280]">
                Nenhum resultado encontrado para "{searchTerm}"
              </div>
            )}
          </div>
        )}
      </div>

      {/*
       * ══════════════════════════════════════════════════════════════
       * ⚙️ AÇÕES (lado direito da topbar)
       * ══════════════════════════════════════════════════════════════
       */}
      <div className="flex items-center gap-5">
        
        {/*
         * ❓ BOTÃO DE AJUDA CONTEXTUAL
         */}
        <div className="relative" ref={helpRef}>
          <button 
            onClick={() => setIsHelpOpen(!isHelpOpen)}
            title={t("Ajuda Interativa")}
            className={`transition-colors p-2 rounded-full ${isHelpOpen ? 'bg-gray-100 text-[#374151]' : 'text-[#6B7280] hover:text-[#374151] hover:bg-gray-100'}`}
          >
            <HelpCircle className="w-[22px] h-[22px]" strokeWidth={2.2} />
          </button>

          {/* ────── DROPDOWN DE AJUDA ────── */}
          {isHelpOpen && (
            <div className="absolute right-0 mt-2 w-[300px] bg-white border border-[#E5E7EB] rounded-[14px] shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden">
              <div className="px-4 py-3 border-b border-[#F1F1F4] flex items-center gap-2">
                <BookOpen className="w-[16px] h-[16px] text-indigo-600" />
                <h3 className="text-[13px] font-[700] text-[#1A1A2E]">{t("Dica Rápida")}</h3>
              </div>
              <div className="p-4 flex flex-col gap-2">
                <span className="text-[14px] font-[700] text-[#1A1A2E] leading-tight">
                  {helpContext.title}
                </span>
                <p className="text-[12px] text-[#6B7280] leading-relaxed mb-1">
                  {helpContext.desc}
                </p>
                <div className="h-px w-full bg-[#F3F4F6] my-1"></div>
                <button 
                  onClick={() => {
                    setIsHelpOpen(false);
                    startTour();
                  }}
                  className="w-full flex items-center justify-center gap-2 mt-1 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-[8px] transition-colors text-[12px] font-[600]"
                >
                  <PlayCircle className="w-[14px] h-[14px]" />
                  Iniciar Tour Interativo
                </button>
              </div>
            </div>
          )}
        </div>

        {/*
         * 📱 SELETOR DE SISTEMAS (Dropdown)
         * Permite ao usuário alternar entre os módulos do ecossistema Basileia:
         *   - Basileia Church OS (gestão eclesiástica — membros, cultos, etc.)
         *   - Basileia Vendor OS (gestão financeira — este sistema atual)
         *
         * 🔗 BACK-END: GET /api/auth/sistemas-disponiveis
         *    Resposta: [
         *      { id: "church", nome: "Basileia Church OS", descricao: "Gestão eclesiástica", url: "/church" },
         *      { id: "finance", nome: "Basileia Vendor OS", descricao: "Gestão financeira", url: "/" }
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
                 * SISTEMA 2: Basileia Vendor OS (este sistema — ativo)
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
                    <span className="text-[13px] font-[600] text-[#1A1A2E]">{t("Basileia Vendor OS")}</span>
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
