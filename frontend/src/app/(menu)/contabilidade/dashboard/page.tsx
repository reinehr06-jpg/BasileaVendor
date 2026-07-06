/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: CONTABILIDADE (Conciliação Contábil De-Para)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /contabilidade
 * 📁 ARQUIVO: src/app/contabilidade/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Permitir ao tesoureiro/contador comparar os lançamentos importados de
 *    um extrato bancário (CSV/XML) com os registros já cadastrados no sistema.
 *    O usuário pode:
 *      - Importar arquivo CSV ou XML do banco
 *      - Ver quais registros já foram conciliados automaticamente
 *      - Identificar divergências (valores ou categorias diferentes)
 *      - Vincular registros que o sistema não conseguiu vincular sozinho
 *      - Salvar os ajustes feitos na conciliação
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/contabilidade/registros?periodo=YYYY-MM-DD,YYYY-MM-DD&status=todos&page=1&limit=8
 *       → Retorna a lista paginada de registros importados com status de conciliação
 *    2. GET /api/contabilidade/resumo → Retorna os KPIs (totais, conciliados, divergências, etc.)
 *    3. POST /api/contabilidade/importar → Recebe o arquivo CSV/XML e processa a importação
 *    4. PUT /api/contabilidade/vincular → Envia os vínculos confirmados pelo usuário
 *    5. PUT /api/contabilidade/salvar-ajustes → Salva todos os ajustes feitos em lote
 *    6. GET /api/contabilidade/buscar?q=termo → Busca por histórico, documento ou valor
 *
 * 📌 REGRAS DE NEGÓCIO:
 *    - Um registro pode ter 3 status: "Conciliado" (verde), "Divergência" (amarelo), "Sem vínculo" (vermelho)
 *    - "Conciliado" = o sistema encontrou correspondência exata com um lançamento interno
 *    - "Divergência" = encontrou correspondência, mas valor ou categoria diferem
 *    - "Sem vínculo" = nenhum lançamento interno corresponde a esse registro importado
 *    - O botão "Salvar ajustes" envia TODOS os vínculos/ajustes ao back-end de uma vez
 *    - A busca filtra localmente (front-end) por histórico, documento ou valor
 *    - As abas filtram por status (Todos, Conciliados, Divergências, Sem vínculo)
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client"; // Obrigatório: componente interativo com estados e filtros

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";   // Menu lateral (navegação principal)
import Topbar from "@/components/Topbar";     // Barra superior (busca global)
import { 
  Scale,          // Ícone de balança (título da tela — representa contabilidade)
  UploadCloud,    // Ícone de upload (botão de importar arquivo)
  CheckCircle2,   // Ícone de check (KPI de conciliados)
  AlertTriangle,  // Ícone de alerta (KPI de divergências)
  Link2Off,       // Ícone de link quebrado (KPI de sem vínculo)
  DollarSign,     // Ícone de cifrão (KPI de total financeiro)
  Search,         // Ícone de lupa (busca na tabela)
  Filter,         // Ícone de filtro (botão de filtros avançados)
  Calendar,       // Ícone de calendário (seletor de período)
  MoreVertical,   // Ícone de 3 pontos (menu de ações por registro)
  Eye,            // Ícone de olho (visualizar detalhes do registro)
  Clock,          // Ícone de relógio (data da última importação)
  ChevronLeft,    // Seta esquerda (paginação - página anterior)
  ChevronRight,   // Seta direita (paginação - próxima página)
  Save            // Ícone de salvar (botão "Salvar ajustes")
} from "lucide-react";

// ═══════════════════════════════════════════════════════════════════════════════
// 📊 COMPONENTE PRINCIPAL: ContabilidadePage
// ═══════════════════════════════════════════════════════════════════════════════

export default function ContabilidadePage() {
  /*
   * 📦 Estado: Aba ativa do filtro de status
   *    Valores possíveis: "todos" | "conciliados" | "divergencias" | "sem-vinculo"
   *    Controla qual filtro de status está selecionado na toolbar da tabela.
   */
  const [activeTab, setActiveTab] = useState("todos");

  /*
   * 📦 Estado: Texto digitado no campo de busca
   *    Filtra localmente (front-end) os registros da tabela por histórico, documento ou valor.
   *    🔗 BACK-END (futuro): Pode ser substituído por GET /api/contabilidade/buscar?q=termo
   */
  const [searchQuery, setSearchQuery] = useState("");

  /*
   * 📋 DADOS MOCKADOS: Registros de conciliação contábil
   * 🔗 BACK-END: GET /api/contabilidade/registros?periodo=2024-05-01,2024-05-22&status=todos&page=1&limit=8
   *    Cada registro retornado deve conter:
   *    - id: Identificador único do registro importado
   *    - data: Data do lançamento (formato DD/MM/YYYY)
   *    - documento: Código do documento (ex: "REC-1024", "DES-2041")
   *    - historico: Descrição do lançamento contábil
   *    - valor: Valor em R$ formatado
   *    - categoria: Categoria no sistema (ex: "Receitas > Dízimos") ou "—" se sem vínculo
   *    - conta: Conta contábil (ex: "3.1.1.01 - Receitas com Dízimos") ou "—" se sem vínculo
   *    - status: "Conciliado" | "Divergência" | "Sem vínculo"
   */
  const tableData = [
    { id: 1, data: "15/05/2024", documento: "REC-1024", historico: "Dízimos culto domingo", valor: "R$ 12.540,00", categoria: "Receitas > Dízimos", conta: "3.1.1.01 - Receitas com Dízimos", status: "Conciliado" },
    { id: 2, data: "16/05/2024", documento: "DES-2041", historico: "Energia elétrica sede", valor: "R$ 2.340,19", categoria: "Despesas > Energia Elétrica", conta: "4.2.1.05 - Energia, Água e Esgoto", status: "Conciliado" },
    { id: 3, data: "16/05/2024", documento: "DES-2048", historico: "Compra papelaria escritório", valor: "R$ 486,70", categoria: "—", conta: "—", status: "Sem vínculo" },
    { id: 4, data: "17/05/2024", documento: "DES-2052", historico: "Aluguel equipamento evento", valor: "R$ 4.800,00", categoria: "Eventos > Locação", conta: "4.2.1.99 - Outras Despesas", status: "Divergência" },
    { id: 5, data: "17/05/2024", documento: "REC-1031", historico: "Oferta conferência", valor: "R$ 8.950,00", categoria: "Receitas > Ofertas", conta: "3.1.1.03 - Ofertas", status: "Conciliado" },
    { id: 6, data: "18/05/2024", documento: "DES-2056", historico: "Serviço limpeza terceirizada", valor: "R$ 1.250,00", categoria: "Despesas > Serviços Gerais", conta: "4.2.1.07 - Serviços Gerais", status: "Conciliado" },
    { id: 7, data: "19/05/2024", documento: "REC-1035", historico: "Oferta missão urbana", valor: "R$ 3.120,00", categoria: "Receitas > Ofertas", conta: "3.1.1.03 - Ofertas", status: "Conciliado" },
    { id: 8, data: "19/05/2024", documento: "DES-2059", historico: "Internet e telefonia", valor: "R$ 299,90", categoria: "Despesas > Telecom", conta: "4.2.1.06 - Telecomunicações", status: "Conciliado" },
  ];

  /*
   * 🏷️ Função: Gera o badge colorido para o status de cada registro
   *    - Conciliado → verde com bolinha verde
   *    - Divergência → amarelo com bolinha amarela
   *    - Sem vínculo → vermelho com bolinha vermelha
   */
  const getStatusBadge = (status: string) => {
    const map: Record<string, { color: string; bg: string }> = {
      "Conciliado": { color: "#10B981", bg: "#ECFDF5" },   // Verde
      "Divergência": { color: "#F59E0B", bg: "#FFFBEB" },  // Amarelo
      "Sem vínculo": { color: "#EF4444", bg: "#FEF2F2" },  // Vermelho
    };
    const s = map[status] || map["Conciliado"];
    return (
      <span className="inline-flex items-center gap-[6px] px-[10px] py-[4px] rounded-full text-[11px] font-[700]" style={{ color: s.color, backgroundColor: s.bg }}>
        <span className="w-[6px] h-[6px] rounded-full" style={{ backgroundColor: s.color }}></span>
        {status}
      </span>
    );
  };

  /*
   * 🎨 Função: Define a cor do texto da categoria na tabela
   *    - "—" (sem vínculo) → vermelho
   *    - Receitas → verde
   *    - Eventos → amarelo
   *    - Despesas → vermelho
   */
  const getCategoriaColor = (cat: string) => {
    if (cat === "—") return "#EF4444";                // Sem vínculo = vermelho
    if (cat.startsWith("Receitas")) return "#10B981";  // Receitas = verde
    if (cat.startsWith("Eventos")) return "#F59E0B";   // Eventos = amarelo
    return "#EF4444";                                  // Despesas = vermelho
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // 🖼️ RENDERIZAÇÃO DA TELA
  // ═══════════════════════════════════════════════════════════════════════════

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      {/* ────── SIDEBAR: Navegação lateral ────── */}
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        {/* ────── TOPBAR: Busca global ────── */}
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden">
          
          {/*
           * ══════════════════════════════════════════════════════════════
           * 🔵 SEÇÃO 1: HEADER DA PÁGINA
           * ══════════════════════════════════════════════════════════════
           * Contém: Título + Subtítulo + Data da última importação + Botão de importar
           */}
          <div className="flex items-center justify-between shrink-0">
            {/* LADO ESQUERDO: Ícone + Título */}
            <div className="flex items-center gap-4">
              {/* Ícone decorativo: balança (contabilidade) */}
              <div className="w-[44px] h-[44px] rounded-full bg-[#F3E8FF] flex items-center justify-center shrink-0">
                <Scale className="w-[22px] h-[22px] text-[#6D28D9]" strokeWidth={2} />
              </div>
              <div>
                <h1 className="text-[18px] font-[800] text-[#1A1A2E]">Conciliação Contábil (De-Para)</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Compare os lançamentos importados com os registros do sistema e ajuste vínculos.</p>
              </div>
            </div>

            {/* LADO DIREITO: Data de última importação + Botão de importar */}
            <div className="flex items-center gap-3">
              {/* 🔗 BACK-END: GET /api/contabilidade/ultima-importacao → { data: "22/05/2024 14:32" } */}
              <div className="flex items-center gap-2 text-[12px] text-[#6B7280]">
                <Clock className="w-[14px] h-[14px]" />
                <span>Última importação: <strong className="text-[#374151]">22/05/2024 14:32</strong></span>
              </div>
              {/*
               * 📤 BOTÃO: Importar arquivo (CSV/XML)
               * 🔗 BACK-END: POST /api/contabilidade/importar
               *    Body: FormData com o arquivo (multipart/form-data)
               *    📌 REGRA: Aceitar apenas .csv e .xml
               *    📌 REGRA: Após importação, recarregar a tabela automaticamente
               *    📌 REGRA: Se já houver importação do mesmo período, perguntar se quer substituir
               */}
              <button className="flex items-center gap-2 px-5 h-[40px] rounded-[8px] text-[13px] font-[700] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white">
                <UploadCloud className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Importar arquivo (CSV/XML)
              </button>
            </div>
          </div>

          {/*
           * ══════════════════════════════════════════════════════════════
           * 📊 SEÇÃO 2: KPIs (6 cartões de indicadores compactos)
           * ══════════════════════════════════════════════════════════════
           * 🔗 BACK-END: GET /api/contabilidade/resumo
           *    Resposta esperada: {
           *      total_importados: 44,
           *      conciliados: 38,
           *      divergencias: 4,
           *      sem_vinculo: 2,
           *      total_financeiro: 128430.22,
           *      percentual_conciliado: 86
           *    }
           */}
          <div className="grid grid-cols-6 gap-3 shrink-0">
            {[
              /* KPI 1: Total de registros importados do arquivo CSV/XML */
              { label: "Registros importados", value: "44", icon: <Scale className="w-[12px] h-[12px] text-[#3B82F6]" />, bg: "#EFF6FF" },
              /* KPI 2: Registros que o sistema conseguiu vincular automaticamente */
              { label: "Conciliados", value: "38", icon: <CheckCircle2 className="w-[12px] h-[12px] text-[#10B981]" />, bg: "#ECFDF5" },
              /* KPI 3: Registros com valor ou categoria diferente do esperado */
              { label: "Com divergência", value: "4", icon: <AlertTriangle className="w-[12px] h-[12px] text-[#F59E0B]" />, bg: "#FFFBEB" },
              /* KPI 4: Registros que não têm correspondência no sistema */
              { label: "Sem vínculo", value: "2", icon: <Link2Off className="w-[12px] h-[12px] text-[#EF4444]" />, bg: "#FEF2F2" },
              /* KPI 5: Soma de todos os valores importados */
              { label: "Total financeiro", value: "R$ 128.430,22", icon: <DollarSign className="w-[12px] h-[12px] text-[#6D28D9]" />, bg: "#F3E8FF" },
            ].map((kpi, i) => (
              <div key={i} className="bg-white rounded-[10px] border border-[#E5E7EB] px-3 py-[10px] shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex items-center gap-3">
                <div className="w-[24px] h-[24px] rounded-full flex items-center justify-center shrink-0" style={{ backgroundColor: kpi.bg }}>{kpi.icon}</div>
                <div className="flex flex-col">
                  <span className="text-[10px] font-[600] text-[#9CA3AF] leading-none">{kpi.label}</span>
                  <span className="text-[16px] font-[800] text-[#1A1A2E] leading-tight">{kpi.value}</span>
                </div>
              </div>
            ))}
            {/* KPI 6: Barra de progresso da conciliação */}
            {/* 🔗 BACK-END: resumo.percentual_conciliado e resumo.conciliados / resumo.total_importados */}
            <div className="bg-white rounded-[10px] border border-[#E5E7EB] px-3 py-[10px] shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex flex-col justify-center">
              <div className="flex items-baseline justify-between mb-[6px]">
                <span className="text-[14px] font-[800] text-[#10B981]">86%</span>
                <span className="text-[10px] font-[600] text-[#9CA3AF]">conciliado</span>
              </div>
              {/* Barra visual de progresso */}
              <div className="w-full h-[4px] bg-[#E5E7EB] rounded-full overflow-hidden">
                <div className="h-full bg-[#10B981] rounded-full" style={{ width: "86%" }}></div>
              </div>
              <span className="text-[10px] font-[500] text-[#9CA3AF] mt-[4px]">38 de 44 registros</span>
            </div>
          </div>

          {/*
           * ══════════════════════════════════════════════════════════════
           * 📋 SEÇÃO 3: TABELA DE CONCILIAÇÃO (Card principal)
           * ══════════════════════════════════════════════════════════════
           * Contém: Toolbar com abas + busca + filtros | Tabela | Footer com paginação
           */}
          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            
            {/*
             * ────── TOOLBAR: Abas de filtro + Busca + Filtros + Período ──────
             */}
            <div className="flex items-center justify-between border-b border-[#E5E7EB] px-5 shrink-0">
              {/*
               * 🏷️ ABAS DE FILTRO POR STATUS
               * Cada aba filtra a tabela pelo status do registro.
               * O filtro é aplicado no front-end (via .filter() no array).
               * 🔗 BACK-END (alternativa): GET /api/contabilidade/registros?status=conciliado
               */}
              <div className="flex items-center gap-6">
                {[
                  { key: "todos", label: "Todos", count: "44", countColor: "#6D28D9" },           // Todos os registros
                  { key: "conciliados", label: "Conciliados", count: "38", countColor: "#10B981" }, // Só os conciliados
                  { key: "divergencias", label: "Divergências", count: "4", countColor: "#F59E0B" }, // Só divergências
                  { key: "sem-vinculo", label: "Sem vínculo", count: "2", countColor: "#EF4444" },   // Só sem vínculo
                ].map((tab) => (
                  <button
                    key={tab.key}
                    onClick={() => setActiveTab(tab.key)}
                    className={`flex items-center gap-[6px] text-[13px] font-[700] py-[16px] border-b-[2px] transition-colors ${activeTab === tab.key ? "text-[#6D28D9] border-[#6D28D9]" : "text-[#6B7280] border-transparent hover:text-[#374151]"}`}
                  >
                    {tab.label} <span className="font-[800]" style={{ color: tab.countColor }}>{tab.count}</span>
                  </button>
                ))}
              </div>

              {/* ────── FERRAMENTAS: Busca + Filtros + Período ────── */}
              <div className="flex items-center gap-3 py-3">
                {/*
                 * 🔍 CAMPO DE BUSCA
                 * Filtra localmente por: histórico contábil, nº do documento, ou valor.
                 * 🔗 BACK-END (futuro): GET /api/contabilidade/buscar?q=termo
                 */}
                <div className="relative">
                  <input type="text" value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} placeholder="Buscar por histórico, documento, valor..." className="w-[260px] h-[36px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[12px] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] focus:bg-white transition-all" />
                  <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                </div>
                {/*
                 * 🔧 BOTÃO: Filtros avançados
                 * 📌 TODO: Implementar drawer/modal com filtros por: categoria, conta contábil, faixa de valor
                 */}
                <button className="h-[36px] px-4 border border-[#E5E7EB] rounded-[8px] bg-white flex items-center gap-2 text-[12px] font-[600] text-[#374151] hover:bg-[#F9FAFB] transition-colors">
                  <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" /> Filtros
                </button>
                {/*
                 * 📅 BOTÃO: Seletor de período
                 * 🔗 BACK-END: Ao trocar o período, recarregar a tabela com:
                 *    GET /api/contabilidade/registros?periodo=YYYY-MM-DD,YYYY-MM-DD
                 * 📌 TODO: Implementar date range picker (calendário pop-up)
                 */}
                <button className="h-[36px] px-4 border border-[#E5E7EB] rounded-[8px] bg-white flex items-center gap-2 text-[12px] font-[600] text-[#374151] hover:bg-[#F9FAFB] transition-colors">
                  <Calendar className="w-[14px] h-[14px] text-[#9CA3AF]" />
                  <span>Período</span>
                  <span className="text-[#111827] font-[700]">01/05/2024 - 22/05/2024</span>
                </button>
              </div>
            </div>

            {/*
             * ────── TABELA: Lista de registros de conciliação ──────
             * Cada linha representa um lançamento importado do extrato bancário.
             */}
            <div className="flex-1 overflow-y-auto custom-scrollbar">
              <table className="w-full text-left border-collapse">
                {/* Cabeçalho fixo da tabela (sticky) */}
                <thead className="sticky top-0 bg-white shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    {/* Checkbox: selecionar todos os registros para ação em lote */}
                    <th className="py-3 px-5 w-[40px]">
                      <input type="checkbox" className="w-4 h-4 rounded border-[#D1D5DB] text-[#6D28D9] focus:ring-[#6D28D9] cursor-pointer" />
                    </th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Data</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Documento</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Histórico contábil</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Valor</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Categoria no sistema</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Conta contábil</th>
                    <th className="py-3 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Status</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase text-right">Ação</th>
                  </tr>
                </thead>
                <tbody>
                  {/*
                   * 🔄 FILTROS ENCADEADOS:
                   * 1. Primeiro filtra por status (baseado na aba ativa)
                   * 2. Depois filtra por texto de busca (histórico, documento ou valor)
                   */}
                  {tableData
                    /* Filtro 1: Por aba de status */
                    .filter((row) => {
                      if (activeTab === "conciliados") return row.status === "Conciliado";
                      if (activeTab === "divergencias") return row.status === "Divergência";
                      if (activeTab === "sem-vinculo") return row.status === "Sem vínculo";
                      return true; // "todos" — mostra tudo
                    })
                    /* Filtro 2: Por texto de busca */
                    .filter((row) => {
                      if (!searchQuery) return true; // Se não digitou nada, mostra tudo
                      const q = searchQuery.toLowerCase();
                      return row.historico.toLowerCase().includes(q) || row.documento.toLowerCase().includes(q) || row.valor.toLowerCase().includes(q);
                    })
                    .map((row) => (
                    <tr key={row.id} className="border-b border-[#F3F4F6] hover:bg-[#FAFAFA] transition-colors">
                      {/* Checkbox: selecionar registro individual para ação em lote */}
                      <td className="py-[14px] px-5">
                        <input type="checkbox" className="w-4 h-4 rounded border-[#D1D5DB] text-[#6D28D9] focus:ring-[#6D28D9] cursor-pointer" />
                      </td>
                      {/* Data do lançamento */}
                      <td className="py-[14px] px-3 text-[12px] text-[#6B7280] font-[500]">{row.data}</td>
                      {/* Número do documento (ex: REC-1024, DES-2048) */}
                      <td className="py-[14px] px-3 text-[12px] text-[#374151] font-[600]">{row.documento}</td>
                      {/* Descrição do lançamento contábil */}
                      <td className="py-[14px] px-3 text-[12px] text-[#111827] font-[600]">{row.historico}</td>
                      {/* Valor em R$ */}
                      <td className="py-[14px] px-3 text-[12px] text-[#111827] font-[700]">{row.valor}</td>
                      {/* Categoria vinculada no sistema (verde=receita, vermelho=despesa, "—"=sem vínculo) */}
                      <td className="py-[14px] px-3 text-[12px] font-[600]" style={{ color: getCategoriaColor(row.categoria) }}>{row.categoria}</td>
                      {/* Código da conta contábil (plano de contas) */}
                      <td className="py-[14px] px-3 text-[11px] text-[#6B7280] font-[500]">{row.conta}</td>
                      {/* Badge de status: Conciliado (verde) | Divergência (amarelo) | Sem vínculo (vermelho) */}
                      <td className="py-[14px] px-3">{getStatusBadge(row.status)}</td>
                      {/* Botões de ação por registro */}
                      <td className="py-[14px] px-5">
                        <div className="flex items-center justify-end gap-2">
                          {/*
                           * 👁️ BOTÃO: Ver detalhes do registro
                           * 🔗 BACK-END: GET /api/contabilidade/registros/{id}
                           * 📌 TODO: Abrir modal/drawer lateral com detalhes completos do registro
                           */}
                          <button className="w-[30px] h-[30px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#6D28D9] hover:bg-[#F3E8FF] transition-colors">
                            <Eye className="w-[15px] h-[15px]" strokeWidth={2} />
                          </button>
                          {/*
                           * ⋮ BOTÃO: Menu de mais opções
                           * 📌 TODO: Dropdown com: "Vincular manualmente", "Editar categoria", "Ignorar"
                           * 🔗 BACK-END:
                           *    - PUT /api/contabilidade/vincular/{id} → vincular manualmente a um lançamento
                           *    - PUT /api/contabilidade/registros/{id}/ignorar → marcar como ignorado
                           */}
                          <button className="w-[30px] h-[30px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#6D28D9] hover:bg-[#F3E8FF] transition-colors">
                            <MoreVertical className="w-[15px] h-[15px]" strokeWidth={2} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/*
             * ────── FOOTER: Ações em lote + Paginação + Salvar ──────
             */}
            <div className="px-5 py-3 border-t border-[#E5E7EB] bg-white flex items-center justify-between shrink-0">
              {/* LADO ESQUERDO: Seleção em lote */}
              <div className="flex items-center gap-4">
                {/* 📌 TODO: Atualizar dinamicamente com a quantidade de checkboxes marcados */}
                <span className="text-[12px] font-[600] text-[#6B7280]">0 itens selecionados</span>
                {/*
                 * 📦 SELECT: Ações em lote
                 * 📌 TODO: Opções: "Vincular selecionados", "Ignorar selecionados", "Exportar selecionados"
                 * 🔗 BACK-END: PUT /api/contabilidade/lote → { acao: "vincular"|"ignorar", ids: [1,2,3] }
                 */}
                <select className="h-[34px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] px-3 text-[12px] font-[600] text-[#4B5563] outline-none">
                  <option>Ações em lote</option>
                </select>
              </div>

              {/* CENTRO: Paginação */}
              {/* 🔗 BACK-END: Usar page e limit nos parâmetros da API */}
              <div className="flex items-center gap-1">
                <span className="text-[12px] font-[500] text-[#6B7280] mr-3">Exibindo 1 a 8 de 44 registros</span>
                {/* Botão: Página anterior */}
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#F3F4F6]"><ChevronLeft className="w-[14px] h-[14px]" /></button>
                {/* Página ativa (fundo roxo) */}
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] bg-[#6D28D9] text-white font-[700] text-[12px]">1</button>
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#4B5563] font-[600] text-[12px] hover:bg-[#F3F4F6]">2</button>
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#4B5563] font-[600] text-[12px] hover:bg-[#F3F4F6]">3</button>
                <span className="text-[#9CA3AF] mx-1 text-[12px]">...</span>
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#4B5563] font-[600] text-[12px] hover:bg-[#F3F4F6]">6</button>
                {/* Botão: Próxima página */}
                <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#F3F4F6]"><ChevronRight className="w-[14px] h-[14px]" /></button>
                <span className="text-[12px] font-[500] text-[#6B7280] ml-3">8 por página</span>
              </div>

              {/*
               * 💾 BOTÃO: Salvar ajustes
               * 🔗 BACK-END: PUT /api/contabilidade/salvar-ajustes
               *    Body: { ajustes: [ { registro_id: 3, acao: "vincular", categoria_id: 12, conta_id: 45 }, ... ] }
               *    📌 REGRA: Envia TODOS os ajustes feitos pelo usuário de uma vez
               *    📌 REGRA: Após salvar com sucesso, exibir toast de confirmação
               *    📌 REGRA: Se houver erro, exibir mensagem de erro e não limpar os ajustes
               */}
              <button className="px-5 h-[40px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white rounded-[8px] font-[700] text-[13px] flex items-center gap-2">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Salvar ajustes
              </button>
            </div>

          </div>

        </main>
      </div>
    </div>
  );
}
