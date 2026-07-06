/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: FUNCIONÁRIOS E PREBENDAS
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /cadastros/funcionarios
 * 📁 ARQUIVO: src/app/(menu)/cadastros/funcionarios/page.tsx
 *
 * 🎯 OBJETIVO DA TELA:
 *    Gestão do quadro de pessoal da igreja. Serve para cadastrar Pastores (que recebem 
 *    Prebenda Ministerial) e Funcionários CLT (Zelador, Secretária). Facilita a 
 *    geração de folha de pagamento no final do mês.
 *
 * ⚙️ REGRAS DE NEGÓCIO E COMPORTAMENTO:
 *    - É possível gerar um recibo automático (PDF) para os salários/prebendas.
 *    - Pode-se criar uma "Despesa Recorrente" automática todo mês baseada neste cadastro.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END (APIs):
 *    1. GET /api/funcionarios → Lista todos os colaboradores ativos.
 *    2. POST /api/funcionarios/folha → Gera em lote os pagamentos do mês atual.
 *    3. GET /api/funcionarios/{id}/recibo → Retorna buffer do PDF gerado.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Users, User, Plus, Search, Filter, Download, FileText, Printer, X, CheckCircle2, AlertCircle
} from "lucide-react";

export default function FuncionariosPage() {
  const [isReceiptModalOpen, setIsReceiptModalOpen] = useState(false);
  const [selectedFuncionario, setSelectedFuncionario] = useState<any>(null);

  const mockFuncionarios = [
    { id: 1, nome: "Carlos Almeida", documento: "123.456.789-00", cargo: "Zelador", salario: "R$ 2.500,00", pgtoData: "05/05/2024", conta: "Itaú - CC 1234", forma: "PIX", status: "Pago", filial: "Igreja Sede" },
    { id: 2, nome: "Mariana Souza", documento: "987.654.321-11", cargo: "Secretária", salario: "R$ 3.200,00", pgtoData: "05/05/2024", conta: "Itaú - CC 1234", forma: "Transferência", status: "Pendente", filial: "Igreja Sede" },
    { id: 3, nome: "Pr. João Silva", documento: "444.555.666-77", cargo: "Pastor Auxiliar", salario: "R$ 4.500,00", pgtoData: "05/05/2024", conta: "Caixa Físico", forma: "Dinheiro", status: "Pago", filial: "Filial Zona Sul" },
  ];

  const handleOpenReceipt = (func: any) => {
    setSelectedFuncionario(func);
    setIsReceiptModalOpen(true);
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#EFF6FF] flex items-center justify-center shrink-0">
                <Users className="w-[20px] h-[20px] text-[#3B82F6]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Funcionários e Pagamentos</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gestão de folha, histórico de pagamentos e geração de recibos.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/cadastros/funcionarios/novo" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Novo Funcionário
              </Link>
            </div>
          </div>

          {/* TABLE SECTION */}
          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            
            <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
              <div className="flex items-center gap-2">
                <div className="relative">
                  <input 
                    type="text" 
                    placeholder="Buscar por nome, cargo ou CPF..." 
                    className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#3B82F6]"
                  />
                  <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                </div>
                <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                  <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" />
                  Filtros
                </button>
              </div>
            </div>

            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Funcionário</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Salário Base</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Competência (Pagto)</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Status</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {mockFuncionarios.map((func) => (
                    <tr key={func.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                      <td className="py-4 px-5">
                        <div className="flex items-center gap-3">
                          <div className="w-[36px] h-[36px] rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0">
                            <User className="w-[16px] h-[16px] text-[#3B82F6]" strokeWidth={2} />
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{func.nome}</span>
                            <span className="text-[11px] text-[#6B7280]">{func.cargo} • {func.filial}</span>
                          </div>
                        </div>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[800] text-[#111827]">{func.salario}</span>
                      </td>
                      <td className="py-4 px-5">
                        <div className="flex flex-col">
                          <span className="text-[13px] text-[#4B5563]">{func.pgtoData}</span>
                          <span className="text-[11px] text-[#9CA3AF]">{func.forma} • {func.conta}</span>
                        </div>
                      </td>
                      <td className="py-4 px-5">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${func.status === 'Pago' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#FEF2F2] text-[#EF4444]'}`}>
                          {func.status === 'Pago' && <CheckCircle2 className="w-[12px] h-[12px] mr-1" />}
                          {func.status === 'Pendente' && <AlertCircle className="w-[12px] h-[12px] mr-1" />}
                          {func.status}
                        </span>
                      </td>
                      <td className="py-4 px-5 text-right">
                        <button 
                          onClick={() => handleOpenReceipt(func)}
                          className={`flex items-center gap-2 px-3 py-1.5 border rounded-[6px] text-[12px] font-[700] transition-colors ml-auto ${
                            func.status === 'Pago' 
                            ? 'bg-[#F9FAFB] border-[#E5E7EB] text-[#4B5563] hover:bg-white hover:border-[#3B82F6] hover:text-[#3B82F6]' 
                            : 'bg-white border-[#E5E7EB] text-[#9CA3AF] cursor-not-allowed opacity-50'
                          }`}
                          disabled={func.status !== 'Pago'}
                        >
                          <FileText className="w-[14px] h-[14px]" /> Gerar Recibo
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          
        </main>
      </div>

      {/* MODAL DO RECIBO (PDF) */}
      {isReceiptModalOpen && selectedFuncionario && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-8 animate-in fade-in duration-200">
          <div className="bg-white rounded-[16px] w-full max-w-[800px] flex flex-col shadow-2xl animate-in zoom-in-95 duration-200 overflow-hidden">
            
            <div className="px-6 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
              <h2 className="text-[18px] font-[800] text-[#1A1A2E]">
                Recibo de Pagamento
              </h2>
              
              <div className="flex items-center gap-3">
                <button className="flex items-center gap-2 px-4 py-2 bg-[#3B82F6] text-white rounded-[8px] text-[13px] font-[700] hover:bg-[#2563EB] shadow-sm transition-colors">
                  <Download className="w-[16px] h-[16px]" />
                  Baixar PDF
                </button>
                <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] bg-white text-[#4B5563] rounded-[8px] text-[13px] font-[600] hover:bg-[#F3F4F6] transition-colors">
                  <Printer className="w-[16px] h-[16px]" />
                  Imprimir
                </button>
                <div className="w-[1px] h-[32px] bg-[#E5E7EB] mx-1"></div>
                <button 
                  onClick={() => setIsReceiptModalOpen(false)}
                  className="w-[36px] h-[36px] flex items-center justify-center rounded-full hover:bg-[#E5E7EB] text-[#6B7280] transition-colors"
                >
                  <X className="w-[20px] h-[20px]" />
                </button>
              </div>
            </div>

            {/* A4 PAPER SIMULATION */}
            <div className="p-8 bg-[#F3F4F6] flex justify-center overflow-y-auto custom-scrollbar" style={{ maxHeight: '70vh' }}>
              <div className="w-[600px] bg-white shadow-md p-10 border border-[#E5E7EB] shrink-0" style={{ aspectRatio: '1/1.414' }}>
                
                <div className="text-center border-b-2 border-[#111827] pb-6 mb-6">
                  <h1 className="text-[24px] font-[800] text-[#111827] uppercase tracking-widest">RECIBO DE PAGAMENTO</h1>
                  <p className="text-[14px] text-[#4B5563] mt-1 font-[600]">Nº 2024/005 - VALOR: <span className="font-[800] text-[#111827]">{selectedFuncionario.salario}</span></p>
                </div>

                <div className="text-[14px] leading-loose text-[#374151] text-justify mb-10">
                  Recebi(emos) de <span className="font-[800]">Basileia Financeiro - {selectedFuncionario.filial}</span>, 
                  a importância de <span className="font-[800]">{selectedFuncionario.salario}</span>, 
                  referente ao pagamento de salário / serviços prestados de competência do mês atual, 
                  conforme dados abaixo discriminados.
                </div>

                <div className="grid grid-cols-2 gap-y-4 gap-x-8 mb-16 border border-[#E5E7EB] p-6 rounded-[8px]">
                  <div className="col-span-2">
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase">Nome do Beneficiário</span>
                    <p className="text-[15px] font-[700] text-[#111827]">{selectedFuncionario.nome}</p>
                  </div>
                  <div>
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase">Documento (CPF)</span>
                    <p className="text-[14px] font-[600] text-[#374151]">{selectedFuncionario.documento}</p>
                  </div>
                  <div>
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase">Cargo / Função</span>
                    <p className="text-[14px] font-[600] text-[#374151]">{selectedFuncionario.cargo}</p>
                  </div>
                  <div>
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase">Forma de Pagamento</span>
                    <p className="text-[14px] font-[600] text-[#374151]">{selectedFuncionario.forma}</p>
                  </div>
                  <div>
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase">Data do Pagamento</span>
                    <p className="text-[14px] font-[600] text-[#374151]">{selectedFuncionario.pgtoData}</p>
                  </div>
                </div>

                <div className="flex flex-col items-center mt-20">
                  <div className="w-[300px] border-b border-[#111827] mb-2"></div>
                  <span className="text-[13px] font-[700] text-[#111827]">{selectedFuncionario.nome}</span>
                  <span className="text-[11px] text-[#6B7280] mt-1">Assinatura do Recebedor</span>
                </div>
                
                <div className="flex flex-col items-center mt-12">
                  <span className="text-[11px] text-[#9CA3AF]">Documento gerado eletronicamente por Basileia Financeiro.</span>
                </div>

              </div>
            </div>

          </div>
        </div>
      )}
      
    </div>
  );
}
