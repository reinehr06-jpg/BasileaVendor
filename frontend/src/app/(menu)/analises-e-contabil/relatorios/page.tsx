/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: RELATÓRIOS
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /relatorios
 * 📁 ARQUIVO: src/app/relatorios/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Centralizar a geração, visualização e exportação de todos os relatórios do sistema.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/relatorios?tipo=financeiro → Listar relatórios gerados/disponíveis
 *    2. POST /api/relatorios/gerar → Solicitar a geração de um novo relatório
 *    3. GET /api/relatorios/{id}/download → Baixar o arquivo do relatório
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { 
  FileText, Plus, Info, Download, Eye, FileSpreadsheet, ChevronLeft, ChevronRight, User, Search, Filter, X, Printer
} from "lucide-react";

export default function RelatoriosPage() {
  const [activeTab, setActiveTab] = useState("financeiro");
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const [selectedReport, setSelectedReport] = useState<any>(null);

  // Mock data para a visualização
  const mockReportData = [
    { id: 1, desc: "Conta de Luz", valor: "R$ 450,00", venc: "15/05/2024", pagto: "12/05/2024", cat: "Energia" },
    { id: 2, desc: "Internet", valor: "R$ 120,00", venc: "20/05/2024", pagto: "20/05/2024", cat: "Telecom" },
    { id: 3, desc: "Aluguel", valor: "R$ 3.500,00", venc: "05/05/2024", pagto: "04/05/2024", cat: "Imóveis" },
  ];

  const handleOpenView = (reportName: string) => {
    setSelectedReport(reportName);
    setIsViewModalOpen(true);
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                <FileText className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Relatórios</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Revise os relatórios gerados anteriormente e faça o download novamente.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/analises-e-contabil/relatorios/novo" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Novo relatório
              </Link>
            </div>
          </div>

          {/* ALERT BANNER */}
          <div className="bg-[#FFF7ED] border border-[#FFEDD5] rounded-[12px] p-4 flex items-start gap-3 shrink-0">
            <Info className="w-[18px] h-[18px] text-[#EA580C] shrink-0 mt-0.5" strokeWidth={2.5} />
            <p className="text-[13px] font-[600] text-[#9A3412]">
              Os relatórios antigos ficam disponíveis por 30 dias. Depois disso eles são excluídos permanentemente e será necessário gerá-los novamente.
            </p>
          </div>

          {/* TABS DE MÓDULOS */}
          <div className="flex items-center gap-6 border-b border-[#E5E7EB] shrink-0 mt-2">
            <button 
              onClick={() => setActiveTab("financeiro")}
              className={`pb-3 text-[14px] font-[700] border-b-2 transition-colors ${activeTab === "financeiro" ? "border-[#6D28D9] text-[#6D28D9]" : "border-transparent text-[#6B7280] hover:text-[#374151]"}`}
            >
              Financeiro (DRE, Contas, Caixa)
            </button>
            <button 
              onClick={() => setActiveTab("compras")}
              className={`pb-3 text-[14px] font-[700] border-b-2 transition-colors ${activeTab === "compras" ? "border-[#6D28D9] text-[#6D28D9]" : "border-transparent text-[#6B7280] hover:text-[#374151]"}`}
            >
              Compras e Estoque
            </button>
            <button 
              onClick={() => setActiveTab("patrimonio")}
              className={`pb-3 text-[14px] font-[700] border-b-2 transition-colors ${activeTab === "patrimonio" ? "border-[#6D28D9] text-[#6D28D9]" : "border-transparent text-[#6B7280] hover:text-[#374151]"}`}
            >
              Bens e Patrimônio
            </button>
            <button 
              onClick={() => setActiveTab("exportar")}
              className={`pb-3 text-[14px] font-[700] border-b-2 transition-colors ${activeTab === "exportar" ? "border-[#6D28D9] text-[#6D28D9]" : "border-transparent text-[#6B7280] hover:text-[#374151]"}`}
            >
              Exportar Lançamentos (Contábil)
            </button>
          </div>

          {/* EXPORTAR SECTION */}
          {activeTab === "exportar" ? (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-y-auto custom-scrollbar p-[32px]">
              <div className="max-w-[800px] mx-auto w-full animate-in fade-in zoom-in-95 duration-300">
                <div className="flex items-center gap-[16px] mb-[32px]">
                  <div className="w-[48px] h-[48px] rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <FileSpreadsheet className="w-[24px] h-[24px] text-[#10B981]" strokeWidth={2} />
                  </div>
                  <div>
                    <h2 className="text-[20px] font-[800] text-[#111827]">Gerar Lote de Integração Contábil</h2>
                    <p className="text-[13px] text-[#6B7280]">Envie os dados formatados perfeitamente para o seu contador.</p>
                  </div>
                </div>
                
                <div className="bg-[#F9FAFB] border border-[#E5E7EB] rounded-[16px] p-[24px] flex flex-col gap-[24px]">
                  <div className="grid grid-cols-2 gap-[24px]">
                    <div className="col-span-1 flex flex-col gap-[8px]">
                      <label className="text-[12px] font-[700] text-[#374151] uppercase tracking-wider">Mês Referência</label>
                      <CustomSelect 
                        options={[{label: "Maio / 2024", value: "05/2024"}, {label: "Abril / 2024", value: "04/2024"}]}
                        value="05/2024"
                        onChange={() => {}}
                        placeholder="Selecione..."
                        className="h-[46px]"
                      />
                    </div>
                    
                    <div className="col-span-1 flex flex-col gap-[8px]">
                      <label className="text-[12px] font-[700] text-[#374151] uppercase tracking-wider">Layout Contábil</label>
                      <CustomSelect 
                        options={[{label: "Domínio Sistemas (.txt)", value: "dominio"}, {label: "Alterdata (.csv)", value: "alterdata"}, {label: "Planilha Padrão (.xlsx)", value: "xlsx"}]}
                        value="dominio"
                        onChange={() => {}}
                        placeholder="Selecione..."
                        className="h-[46px]"
                      />
                    </div>
                  </div>

                  <div className="border-t border-[#E5E7EB] pt-[24px]">
                    <label className="text-[12px] font-[700] text-[#374151] uppercase tracking-wider mb-[16px] block">Módulos para Exportação</label>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
                      
                      <label className="flex items-start gap-[12px] p-[16px] bg-white border border-[#E5E7EB] rounded-[12px] cursor-pointer hover:border-[#6D28D9] transition-colors group">
                        <input type="checkbox" defaultChecked className="w-[18px] h-[18px] mt-0.5 text-[#6D28D9] rounded-[4px] border-[#D1D5DB] focus:ring-[#6D28D9]" />
                        <div className="flex flex-col">
                          <span className="text-[14px] font-[700] text-[#111827] group-hover:text-[#6D28D9] transition-colors">Contas a Pagar</span>
                          <span className="text-[12px] text-[#6B7280]">Todas as despesas liquidadas no mês.</span>
                        </div>
                      </label>

                      <label className="flex items-start gap-[12px] p-[16px] bg-white border border-[#E5E7EB] rounded-[12px] cursor-pointer hover:border-[#6D28D9] transition-colors group">
                        <input type="checkbox" defaultChecked className="w-[18px] h-[18px] mt-0.5 text-[#6D28D9] rounded-[4px] border-[#D1D5DB] focus:ring-[#6D28D9]" />
                        <div className="flex flex-col">
                          <span className="text-[14px] font-[700] text-[#111827] group-hover:text-[#6D28D9] transition-colors">Contas a Receber</span>
                          <span className="text-[12px] text-[#6B7280]">Todas as receitas e dízimos liquidados.</span>
                        </div>
                      </label>

                      <label className="flex items-start gap-[12px] p-[16px] bg-white border border-[#E5E7EB] rounded-[12px] cursor-pointer hover:border-[#6D28D9] transition-colors group">
                        <input type="checkbox" defaultChecked className="w-[18px] h-[18px] mt-0.5 text-[#6D28D9] rounded-[4px] border-[#D1D5DB] focus:ring-[#6D28D9]" />
                        <div className="flex flex-col">
                          <span className="text-[14px] font-[700] text-[#111827] group-hover:text-[#6D28D9] transition-colors">Movimento Bancário</span>
                          <span className="text-[12px] text-[#6B7280]">Extrato conciliado de todas as contas.</span>
                        </div>
                      </label>
                      
                      <label className="flex items-start gap-[12px] p-[16px] bg-white border border-[#E5E7EB] rounded-[12px] cursor-pointer hover:border-[#6D28D9] transition-colors group">
                        <input type="checkbox" className="w-[18px] h-[18px] mt-0.5 text-[#6D28D9] rounded-[4px] border-[#D1D5DB] focus:ring-[#6D28D9]" />
                        <div className="flex flex-col">
                          <span className="text-[14px] font-[700] text-[#111827] group-hover:text-[#6D28D9] transition-colors">Notas Fiscais de Serviço</span>
                          <span className="text-[12px] text-[#6B7280]">XMLs anexados aos lançamentos.</span>
                        </div>
                      </label>

                    </div>
                  </div>
                </div>

                <div className="mt-[32px] flex items-center justify-between">
                  <div className="flex items-center gap-[8px] text-[13px] font-[600] text-[#F59E0B] bg-[#FEF3C7] px-[12px] py-[8px] rounded-[8px]">
                    <Info className="w-[16px] h-[16px]" />
                    Verifique se o De-Para de contas está 100% preenchido.
                  </div>
                  <button className="bg-gradient-to-r from-[#6D28D9] to-[#5B21B6] hover:from-[#6D28D9] hover:to-[#5B21B6] transition-all text-white px-[24px] py-[12px] rounded-[12px] text-[14px] font-[800] flex items-center gap-[10px] shadow-[0_4px_12px_rgba(124,58,237,0.3)] hover:-translate-y-[1px]">
                    <Download className="w-[18px] h-[18px]" strokeWidth={2.5} />
                    GERAR ARQUIVO
                  </button>
                </div>
              </div>
            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            
            <div className="p-5 border-b border-[#F1F1F4] flex items-center justify-between gap-3">
              <div className="flex items-center gap-3">
                <h2 className="text-[16px] font-[800] text-[#1A1A2E]">Últimos relatórios</h2>
                <span className="bg-[#F3E8FF] text-[#6D28D9] text-[12px] font-[700] px-2.5 py-0.5 rounded-full">7</span>
              </div>
              
              {/* FILTROS E BUSCA (Refletindo Print 5) */}
              <div className="flex items-center gap-3">
                <div className="relative">
                  <input 
                    type="text" 
                    placeholder="Buscar relatório..." 
                    className="w-[280px] h-[38px] border border-[#E5E7EB] rounded-[8px] pl-10 pr-3 text-[13px] outline-none focus:border-[#6D28D9]"
                  />
                  <Search className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                </div>
                <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[38px]">
                  <Filter className="w-[16px] h-[16px] text-[#9CA3AF]" />
                  Filtros Avançados
                </button>
              </div>
            </div>

            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-white shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Nome do relatório</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Módulo</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Gerado por</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Data e hora ↓</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Formato</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Status</th>
                    <th className="py-4 px-5 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  
                  {/* Row 1 */}
                  <tr className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-3">
                        <div className="w-[36px] h-[36px] rounded-full bg-[#F3E8FF] flex items-center justify-center shrink-0">
                          <User className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2} />
                        </div>
                        <div className="flex flex-col">
                          <span className="text-[13px] font-[700] text-[#111827]">Relatório de Despesas Operacionais</span>
                          <span className="text-[12px] text-[#6B7280]">Lista completa de despesas pagas e a pagar.</span>
                        </div>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <span className="inline-flex items-center px-2.5 py-1 rounded-[6px] text-[11px] font-[700] bg-[#FEE2E2] text-[#DC2626]">Despesas</span>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-2">
                        <User className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        <span className="text-[13px] font-[500] text-[#4B5563]">Pr. João Silva</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <span className="text-[13px] text-[#4B5563]">24/05/2024 10:25</span>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-1.5">
                        <FileSpreadsheet className="w-[14px] h-[14px] text-[#16A34A]" />
                        <span className="text-[12px] font-[700] text-[#16A34A]">Excel</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-1.5">
                        <div className="w-[6px] h-[6px] rounded-full bg-[#10B981]"></div>
                        <span className="text-[12px] font-[700] text-[#10B981]">Concluído</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center justify-center gap-2">
                        <button className="w-[32px] h-[32px] rounded-[6px] border border-[#E5E7EB] flex items-center justify-center text-[#6B7280] hover:text-[#6D28D9] hover:border-[#6D28D9] transition-colors bg-white shadow-sm" title="Baixar Arquivo">
                          <Download className="w-[14px] h-[14px]" strokeWidth={2} />
                        </button>
                        <button onClick={() => handleOpenView("Relatório de Despesas Operacionais")} className="w-[32px] h-[32px] rounded-[6px] border border-[#E5E7EB] flex items-center justify-center text-[#6B7280] hover:text-[#6D28D9] hover:border-[#6D28D9] transition-colors bg-white shadow-sm" title="Visualizar Web">
                          <Eye className="w-[14px] h-[14px]" strokeWidth={2} />
                        </button>
                      </div>
                    </td>
                  </tr>

                  {/* Row 2 */}
                  <tr className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-3">
                        <div className="w-[36px] h-[36px] rounded-full bg-[#FFEDD5] flex items-center justify-center shrink-0">
                          <User className="w-[16px] h-[16px] text-[#EA580C]" strokeWidth={2} />
                        </div>
                        <div className="flex flex-col">
                          <span className="text-[13px] font-[700] text-[#111827]">Receitas de Dízimos e Ofertas</span>
                          <span className="text-[12px] text-[#6B7280]">Resumo de entradas por conta bancária.</span>
                        </div>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <span className="inline-flex items-center px-2.5 py-1 rounded-[6px] text-[11px] font-[700] bg-[#FFEDD5] text-[#EA580C]">Receitas</span>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-2">
                        <User className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        <span className="text-[13px] font-[500] text-[#4B5563]">Ana Souza</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <span className="text-[13px] text-[#4B5563]">24/05/2024 09:30</span>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-1.5">
                        <FileSpreadsheet className="w-[14px] h-[14px] text-[#16A34A]" />
                        <span className="text-[12px] font-[700] text-[#16A34A]">Excel</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center gap-1.5">
                        <div className="w-[6px] h-[6px] rounded-full bg-[#10B981]"></div>
                        <span className="text-[12px] font-[700] text-[#10B981]">Concluído</span>
                      </div>
                    </td>
                    <td className="py-4 px-5">
                      <div className="flex items-center justify-center gap-2">
                        <button className="w-[32px] h-[32px] rounded-[6px] border border-[#E5E7EB] flex items-center justify-center text-[#6B7280] hover:text-[#6D28D9] hover:border-[#6D28D9] transition-colors bg-white shadow-sm" title="Baixar Arquivo">
                          <Download className="w-[14px] h-[14px]" strokeWidth={2} />
                        </button>
                        <button onClick={() => handleOpenView("Receitas de Dízimos e Ofertas")} className="w-[32px] h-[32px] rounded-[6px] border border-[#E5E7EB] flex items-center justify-center text-[#6B7280] hover:text-[#6D28D9] hover:border-[#6D28D9] transition-colors bg-white shadow-sm" title="Visualizar Web">
                          <Eye className="w-[14px] h-[14px]" strokeWidth={2} />
                        </button>
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>

            {/* TABLE FOOTER / PAGINATION */}
            <div className="border-t border-[#F1F1F4] p-4 flex items-center justify-between shrink-0 bg-white">
              
              <div className="flex items-center gap-2">
                <span className="text-[12px] font-[600] text-[#6B7280]">Linhas por página</span>
                <select className="bg-white border border-[#E5E7EB] rounded-[6px] px-2 py-1.5 text-[12px] font-[600] text-[#374151] outline-none">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
              </div>

              <div className="text-[12px] font-[600] text-[#111827]">
                1-2 de 2
              </div>

              <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                  <span className="text-[12px] font-[600] text-[#6B7280]">Página</span>
                  <select className="bg-white border border-[#E5E7EB] rounded-[6px] px-2 py-1.5 text-[12px] font-[600] text-[#374151] outline-none">
                    <option>1</option>
                  </select>
                </div>
                
                <div className="flex items-center gap-1">
                  <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                    <ChevronLeft className="w-[14px] h-[14px]" />
                  </button>
                  <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] bg-[#F3E8FF] text-[#6D28D9] font-[700] text-[12px] transition-colors">
                    1
                  </button>
                  <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                    <ChevronRight className="w-[14px] h-[14px]" />
                  </button>
                </div>
              </div>

              </div>
            </div>
          )}
          
        </main>
      </div>

      {/* MODAL DE VISUALIZAÇÃO DE RELATÓRIO (Refletindo Print 2) */}
      {isViewModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-8 animate-in fade-in duration-200">
          <div className="bg-white rounded-[16px] w-full max-w-[1200px] h-[90vh] flex flex-col shadow-2xl animate-in zoom-in-95 duration-200 overflow-hidden">
            
            {/* Modal Header */}
            <div className="px-8 py-5 border-b border-[#E5E7EB] flex items-center justify-between shrink-0 bg-white">
              <h2 className="text-[18px] font-[800] text-[#1A1A2E]">
                Visualizar Relatório: <span className="font-[600] text-[#4B5563]">{selectedReport}</span>
              </h2>
              
              <div className="flex items-center gap-3">
                <button className="flex items-center gap-2 px-4 py-2 bg-[#6D28D9] text-white rounded-[8px] text-[13px] font-[700] hover:bg-[#5B21B6] shadow-sm transition-colors">
                  <FileSpreadsheet className="w-[16px] h-[16px]" />
                  Download XLSX
                </button>
                <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] text-[#4B5563] rounded-[8px] text-[13px] font-[600] hover:bg-[#F9FAFB] transition-colors">
                  <Printer className="w-[16px] h-[16px]" />
                  Imprimir
                </button>
                <div className="w-[1px] h-[32px] bg-[#E5E7EB] mx-1"></div>
                <button 
                  onClick={() => setIsViewModalOpen(false)}
                  className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] text-[#4B5563] rounded-[8px] text-[13px] font-[600] hover:bg-[#F9FAFB] transition-colors"
                >
                  <X className="w-[16px] h-[16px]" />
                  Fechar
                </button>
              </div>
            </div>

            {/* Modal Body / Table */}
            <div className="flex-1 overflow-auto custom-scrollbar bg-white">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-white shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-4 px-8 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#E5E7EB]">Descrição</th>
                    <th className="py-4 px-8 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#E5E7EB]">Categoria</th>
                    <th className="py-4 px-8 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#E5E7EB]">Vencimento</th>
                    <th className="py-4 px-8 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#E5E7EB]">Pagamento</th>
                    <th className="py-4 px-8 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right border-b border-[#E5E7EB]">Valor</th>
                  </tr>
                </thead>
                <tbody>
                  {mockReportData.map((row, i) => (
                    <tr key={i} className="border-b border-[#F1F1F4] hover:bg-[#F9FAFB] transition-colors">
                      <td className="py-4 px-8 text-[13px] font-[600] text-[#111827]">{row.desc}</td>
                      <td className="py-4 px-8 text-[13px] text-[#4B5563]">{row.cat}</td>
                      <td className="py-4 px-8 text-[13px] text-[#4B5563]">{row.venc}</td>
                      <td className="py-4 px-8 text-[13px] text-[#4B5563]">{row.pagto}</td>
                      <td className="py-4 px-8 text-[13px] font-[700] text-[#10B981] text-right">{row.valor}</td>
                    </tr>
                  ))}
                </tbody>
                <tfoot className="bg-[#F9FAFB]">
                  <tr>
                    <td colSpan={4} className="py-5 px-8 text-[13px] font-[700] text-[#4B5563] text-right uppercase tracking-wider border-t border-[#E5E7EB]">Total</td>
                    <td className="py-5 px-8 text-[16px] font-[800] text-[#1A1A2E] text-right border-t border-[#E5E7EB]">R$ 4.070,00</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      )}
      
    </div>
  );
}
