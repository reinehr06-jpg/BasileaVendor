/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: COMPRAS (Pedidos e Cotações)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /compras
 * 📁 ARQUIVO: src/app/compras/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Gerenciar o fluxo de compras da instituição (solicitação → cotação → aprovação → compra → recebimento).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/compras?page=1&limit=10&status=todos → Lista paginada de pedidos de compra
 *    2. POST /api/compras → Criar novo pedido de compra (abre modal ou redireciona para form)
 *    3. PUT /api/compras/{id}/status → Alterar status de uma compra (ex: de "Aguardando aprovação" para "Aprovada")
 *
 * 📌 REGRAS DE NEGÓCIO:
 *    - O fluxo ideal de status: Rascunho → Em cotação → Aguardando aprovação → Aprovada → Pedido enviado → Parcialmente recebida → Recebida
 *    - Se um pedido for reprovado, o status vai para "Reprovada" e o processo encerra.
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
  ShoppingCart, Plus, Search, MoreVertical, Filter, FileText
} from "lucide-react";

export default function ComprasPage() {
  const [fornecedorFilter, setFornecedorFilter] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

  const mockCompras = [
    { id: 1, numero: "COMP-2024-001", data: "20/05/2024", solicitante: "Lucas Almeida", fornecedor: "Kalunga", valor: "R$ 450,00", status: "Aguardando aprovação" },
    { id: 2, numero: "COMP-2024-002", data: "21/05/2024", solicitante: "Pr. Marcos", fornecedor: "Loja de Instrumentos", valor: "R$ 2.500,00", status: "Aprovada" },
    { id: 3, numero: "COMP-2024-003", data: "22/05/2024", solicitante: "Ana Silva", fornecedor: "Supermercado Extra", valor: "R$ 320,00", status: "Recebida" },
    { id: 4, numero: "COMP-2024-004", data: "23/05/2024", solicitante: "Carlos Souza", fornecedor: "Construtora X", valor: "R$ 15.000,00", status: "Em cotação" },
  ];

  const getStatusColor = (status: string) => {
    switch(status) {
      case 'Rascunho': return 'bg-[#F3F4F6] text-[#6B7280]';
      case 'Em cotação': return 'bg-[#E0F2FE] text-[#0284C7]';
      case 'Aguardando aprovação': return 'bg-[#FEF3C7] text-[#D97706]';
      case 'Aprovada': return 'bg-[#ECFDF5] text-[#10B981]';
      case 'Pedido enviado': return 'bg-[#E0E7FF] text-[#4F46E5]';
      case 'Parcialmente recebida': return 'bg-[#ECFCCB] text-[#65A30D]';
      case 'Recebida': return 'bg-[#10B981] text-white';
      case 'Cancelada': return 'bg-[#FEE2E2] text-[#DC2626]';
      case 'Reprovada': return 'bg-[#7F1D1D] text-white';
      default: return 'bg-[#F3F4F6] text-[#6B7280]';
    }
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1400px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                <ShoppingCart className="w-[20px] h-[20px] text-[#9333EA]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Pedidos de Compra</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerencie solicitações, cotações e ordens de compra.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/compras/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Compra
              </Link>
            </div>
          </div>

          {/* FILTERS ROW */}
          <div className="flex items-center gap-3 shrink-0 bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-3 mb-4">
            <div className="flex items-center gap-2 px-2 border-r border-[#E5E7EB] shrink-0">
              <Filter className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.5} />
              <h2 className="text-[13px] font-[700] text-[#1A1A2E] pr-2">Filtros</h2>
            </div>
            
            <div className="flex-1 flex items-center gap-3">
              <div className="relative w-full max-w-[300px]">
                <input 
                  type="text" 
                  placeholder="Buscar nº pedido, solicitante..." 
                  className="w-full h-[36px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[13px] text-[#111827] placeholder-[#9CA3AF] outline-none hover:border-[#D1D5DB] focus:bg-white focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all"
                />
                <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" strokeWidth={2.5} />
              </div>

              <div className="w-[200px]">
                <CustomSelect 
                  options={[
                    {label: "Todos os Fornecedores", value: ""},
                    {label: "Kalunga", value: "kalunga"},
                    {label: "Supermercado Extra", value: "extra"}
                  ]}
                  value={fornecedorFilter}
                  onChange={setFornecedorFilter}
                  placeholder="Fornecedor"
                  className="h-[36px]"
                />
              </div>

              <div className="w-[200px]">
                <CustomSelect 
                  options={[
                    {label: "Todos os Status", value: ""},
                    {label: "Em cotação", value: "cotacao"},
                    {label: "Aguardando aprovação", value: "aprovacao"},
                    {label: "Aprovada", value: "aprovada"},
                    {label: "Recebida", value: "recebida"}
                  ]}
                  value={statusFilter}
                  onChange={setStatusFilter}
                  placeholder="Status"
                  className="h-[36px]"
                />
              </div>
            </div>

            <button className="h-[36px] px-4 rounded-[8px] text-[12px] font-[600] text-[#6B7280] hover:text-[#1A1A2E] hover:bg-[#F3F4F6] transition-colors shrink-0 border border-transparent hover:border-[#E5E7EB]">
              Limpar
            </button>
          </div>

          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">

            {/* TABELA */}
            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Nº Pedido / Data</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Solicitante</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Fornecedor</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Valor Total</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {mockCompras.map((item) => (
                    <tr key={item.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                      <td className="py-4 px-5">
                        <div className="flex items-center gap-3">
                          <div className="w-[36px] h-[36px] rounded-[8px] bg-[#F3F4F6] flex items-center justify-center shrink-0">
                            <FileText className="w-[16px] h-[16px] text-[#6B7280]" />
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{item.numero}</span>
                            <span className="text-[11px] text-[#6B7280]">{item.data}</span>
                          </div>
                        </div>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[600] text-[#374151]">{item.solicitante}</span>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[600] text-[#374151]">{item.fornecedor}</span>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[14px] font-[800] text-[#111827]">{item.valor}</span>
                      </td>
                      <td className="py-4 px-5 text-center">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${getStatusColor(item.status)}`}>
                          {item.status}
                        </span>
                      </td>
                      <td className="py-4 px-5 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <Link href="/compras/1" className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#6D28D9] hover:bg-[#F3E8FF] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                          </Link>
                          <button className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#E5E7EB] transition-colors">
                            <MoreVertical className="w-[16px] h-[16px]" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          
        </main>
      </div>
    </div>
  );
}
