/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: DEMONSTRATIVO DE RESULTADOS (DRE)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /analises-e-contabil/dre
 * 📁 ARQUIVO: src/app/(menu)/analises-e-contabil/dre/page.tsx
 *
 * 🎯 OBJETIVO DA TELA:
 *    Exibir o principal relatório gerencial da igreja. Ele mostra se a operação
 *    "deu lucro" (Superávit) ou "deu prejuízo" (Déficit) no período selecionado.
 *    Ele consolida as Receitas e subtrai as Despesas e Deduções de forma estruturada.
 *
 * ⚙️ ARQUITETURA DE ESTADOS (React useState):
 *    - `expandedRows`: Um array de strings que guarda quais linhas do DRE estão "abertas"
 *      para mostrar as subcategorias. Por padrão, começa com 'receitas' e 'despesas' abertas.
 *
 * 🔄 FLUXO DE DADOS (Integrações com Back-end):
 *    - GET /api/dre?mes=2024-05&filial_id=todas
 *      O Front-end envia o Mês e a Filial. O Back-end processa os livros caixas e 
 *      devolve um JSON já no formato de "árvore" (Receitas > Dízimos, Ofertas, etc.).
 *
 * 💡 REGRAS DE NEGÓCIO E CÁLCULOS:
 *    - (1) Receitas Totais Brutas: Tudo o que entrou.
 *    - (2) Deduções: Taxas ou estornos que abatem a receita bruta diretamente.
 *    - (3) Receita Líquida = (1) - (2)
 *    - (4) Custos e Despesas: Todo o gasto operacional do período.
 *    - (5) Resultado Operacional (Superávit/Déficit) = (3) - (4)
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
// 🗺️ MAPA DO TESOURO: Importamos os ícones do lucide-react para compor a interface.
// O CustomSelect é o nosso componente padrão de dropdown estilizado.
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { 
  BarChart4, Download, Filter, TrendingUp, TrendingDown, Printer, ChevronDown, ChevronRight
} from "lucide-react";

export default function DREPage() {
  // 🗺️ MAPA DO TESOURO: Estado que controla o Accordion da Tabela.
  // Se a string 'receitas' estiver no array, as linhas filhas (1.1, 1.2) aparecerão.
  const [expandedRow, setExpandedRow] = useState<string | null>('receitas');

  // 🗺️ MAPA DO TESOURO: Função disparada ao clicar na linha principal.
  // Ela verifica se o ID da linha já está aberto; se sim, remove (fecha); se não, adiciona (abre).
  const toggleRow = (rowId: string) => {
    if (expandedRow === rowId) {
      setExpandedRow(null);
    } else {
      setExpandedRow(rowId);
    }
  };

  return (
    // 🗺️ MAPA DO TESOURO: Container principal (Layout Base).
    // Evita o scroll no body inteiro (`overflow-hidden`) para que apenas a tabela role.
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-hidden relative">
          
          {/* 🗺️ MAPA DO TESOURO: HEADER DA PÁGINA */}
          {/* Contém o título e os botões de ação global (Imprimir, Exportar) */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#FEF3C7] flex items-center justify-center shrink-0">
                <BarChart4 className="w-[20px] h-[20px] text-[#D97706]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Demonstrativo de Resultados (DRE)</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Visão analítica de receitas e despesas estruturadas por competência.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Printer className="w-[14px] h-[14px]" /> Imprimir
              </button>
              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar PDF
              </button>
            </div>
          </div>

          <div className="flex flex-col flex-1 overflow-hidden min-h-0 gap-4">
            
            {/* 🗺️ MAPA DO TESOURO: BARRA DE FILTROS */}
            {/* Os parâmetros escolhidos aqui (Mês e Filial) devem ser enviados para a API para recalcular o DRE. */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm p-4 flex items-end gap-4 shrink-0">
              <div className="flex flex-col gap-1 w-[200px]">
                <label className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Mês / Ano</label>
                <input type="month" className="w-full h-[40px] border border-[#E5E7EB] rounded-[8px] px-3 text-[13px] font-[600] text-[#111827] outline-none focus:border-[#D97706]" defaultValue="2024-05" />
              </div>

              <div className="flex flex-col gap-1 flex-1 max-w-[300px]">
                <label className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Igreja / Filial</label>
                <CustomSelect
                  value="todas"
                  onChange={() => {}}
                  placeholder="Todas as Filiais (Consolidado)"
                  options={[{value: "todas", label: "Todas as Filiais (Consolidado)"}]}
                  className="h-[40px]"
                />
              </div>

              <button className="h-[40px] px-6 bg-[#111827] text-white rounded-[8px] text-[13px] font-[700] shadow-sm hover:bg-[#374151] transition-colors">
                Gerar Demonstrativo
              </button>
            </div>

            {/* 🗺️ MAPA DO TESOURO: ESTRUTURA DA TABELA DO DRE */}
            {/* A tabela usa custom-scrollbar para garantir rolagem interna suave quando houver muitas linhas. */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden">
              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#E5E7EB] z-10">
                    <tr>
                      <th className="py-3 px-6 text-[12px] font-[800] text-[#374151] uppercase tracking-wider border-b border-[#E5E7EB]">Descrição / Categoria</th>
                      <th className="py-3 px-6 text-[12px] font-[800] text-[#374151] uppercase tracking-wider border-b border-[#E5E7EB] text-right w-[200px]">Realizado</th>
                      <th className="py-3 px-6 text-[12px] font-[800] text-[#374151] uppercase tracking-wider border-b border-[#E5E7EB] text-right w-[150px]">Análise Vert. (%)</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                    {/* 🗺️ MAPA DO TESOURO: BLOCO 1 - RECEITAS TOTAIS */}
                    {/* onClick dispara a função toggleRow('receitas'), expandindo/recolhendo as categorias filhas. */}
                    <tr 
                      className="bg-[#F0FDF4] border-b border-[#E5E7EB] cursor-pointer hover:bg-[#DCFCE7] transition-colors"
                      onClick={() => toggleRow('receitas')}
                    >
                      <td className="py-4 px-6 flex items-center gap-2">
                        {expandedRow === 'receitas' ? <ChevronDown className="w-[16px] h-[16px] text-[#166534]" strokeWidth={3} /> : <ChevronRight className="w-[16px] h-[16px] text-[#166534]" strokeWidth={3} />}
                        <span className="text-[15px] font-[800] text-[#166534] uppercase tracking-wider">1. Receitas Totais Brutas</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[15px] font-[800] text-[#166534]">R$ 45.000,00</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[13px] font-[700] text-[#15803D]">100,0%</span>
                      </td>
                    </tr>

                    {/* 🗺️ MAPA DO TESOURO: SUB-ITENS DE RECEITAS (Renderização Condicional) */}
                    {/* Só aparecem na tela se 'receitas' estiver selecionada. */}
                    {expandedRow === 'receitas' && (
                      <>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">1.1 Dízimos e Contribuições</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 35.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">77,7%</td>
                        </tr>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">1.2 Ofertas e Alçadas</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 8.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">17,7%</td>
                        </tr>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">1.3 Receitas Diversas / Cantina</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 2.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">4,4%</td>
                        </tr>
                      </>
                    )}

                    {/* 🗺️ MAPA DO TESOURO: BLOCO 2 - DEDUÇÕES DA RECEITA */}
                    <tr className="bg-[#FFFBEB] border-b border-[#F1F1F4]">
                      <td className="py-3 px-6 flex items-center gap-2">
                        <ChevronRight className="w-[16px] h-[16px] text-[#B45309] opacity-50" strokeWidth={3} />
                        <span className="text-[14px] font-[800] text-[#B45309] uppercase tracking-wider">2. (-) Deduções da Receita</span>
                      </td>
                      <td className="py-3 px-6 text-right">
                        <span className="text-[14px] font-[800] text-[#B45309]">R$ 0,00</span>
                      </td>
                      <td className="py-3 px-6 text-right">
                        <span className="text-[13px] font-[700] text-[#D97706]">0,0%</span>
                      </td>
                    </tr>

                    {/* 🗺️ MAPA DO TESOURO: BLOCO 3 - RECEITA LÍQUIDA (Resultado Matemático: Bloco 1 - Bloco 2) */}
                    <tr className="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                      <td className="py-4 px-6 flex items-center gap-2">
                        <div className="w-[16px]"></div>
                        <span className="text-[15px] font-[800] text-[#111827] uppercase tracking-wider">3. Receita Líquida</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[15px] font-[800] text-[#111827]">R$ 45.000,00</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[13px] font-[700] text-[#4B5563]">100,0%</span>
                      </td>
                    </tr>

                    {/* 🗺️ MAPA DO TESOURO: BLOCO 4 - DESPESAS TOTAIS */}
                    <tr 
                      className="bg-[#FEF2F2] border-b border-[#E5E7EB] cursor-pointer hover:bg-[#FEE2E2] transition-colors"
                      onClick={() => toggleRow('despesas')}
                    >
                      <td className="py-4 px-6 flex items-center gap-2">
                        {expandedRow === 'despesas' ? <ChevronDown className="w-[16px] h-[16px] text-[#991B1B]" strokeWidth={3} /> : <ChevronRight className="w-[16px] h-[16px] text-[#991B1B]" strokeWidth={3} />}
                        <span className="text-[15px] font-[800] text-[#991B1B] uppercase tracking-wider">4. (-) Custos e Despesas Operacionais</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[15px] font-[800] text-[#991B1B]">R$ 21.000,00</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[13px] font-[700] text-[#B91C1C]">46,6%</span>
                      </td>
                    </tr>

                    {/* 🗺️ MAPA DO TESOURO: SUB-ITENS DE DESPESAS */}
                    {expandedRow === 'despesas' && (
                      <>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">4.1 Despesas com Pessoal / Pastoral</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 12.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">26,6%</td>
                        </tr>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">4.2 Custos com Templo e Imóveis</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 5.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">11,1%</td>
                        </tr>
                        <tr className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB]">
                          <td className="py-3 px-6 pl-14 text-[13px] font-[600] text-[#4B5563]">4.3 Despesas Administrativas</td>
                          <td className="py-3 px-6 text-right text-[14px] font-[700] text-[#374151]">R$ 4.000,00</td>
                          <td className="py-3 px-6 text-right text-[12px] font-[600] text-[#9CA3AF]">8,8%</td>
                        </tr>
                      </>
                    )}

                    {/* 🗺️ MAPA DO TESOURO: BLOCO 5 - RESULTADO OPERACIONAL ANTES DOS IMPOSTOS */}
                    <tr className="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                      <td className="py-4 px-6 flex items-center gap-2">
                        <div className="w-[16px]"></div>
                        <span className="text-[14px] font-[800] text-[#111827] uppercase tracking-wider">5. Resultado Operacional (Superávit)</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[14px] font-[800] text-[#111827]">R$ 24.000,00</span>
                      </td>
                      <td className="py-4 px-6 text-right">
                        <span className="text-[13px] font-[700] text-[#4B5563]">53,3%</span>
                      </td>
                    </tr>

                    {/* 🗺️ MAPA DO TESOURO: BLOCO FINAL - O SALDO LÍQUIDO */}
                    {/* Estilizado em Dark Mode (fundo preto) para máximo destaque, indicando lucro ou prejuízo. */}
                    <tr className="bg-[#111827] border-b border-[#1F2937]">
                      <td className="py-6 px-6 flex items-center gap-3">
                        <div className="w-[24px] h-[24px] bg-[#10B981] rounded-full flex items-center justify-center">
                          <TrendingUp className="w-[14px] h-[14px] text-[#111827]" strokeWidth={3} />
                        </div>
                        <span className="text-[18px] font-[800] text-white uppercase tracking-widest">Resultado do Exercício</span>
                      </td>
                      <td className="py-6 px-6 text-right">
                        <span className="text-[20px] font-[800] text-[#10B981]">R$ 24.000,00</span>
                      </td>
                      <td className="py-6 px-6 text-right">
                        <span className="text-[14px] font-[800] text-[#9CA3AF]">53,3%</span>
                      </td>
                    </tr>

                  </tbody>
                </table>
              </div>
            </div>

          </div>
          
        </main>
      </div>
    </div>
  );
}
