/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: FORNECEDORES (Listagem)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /fornecedores
 * 📁 ARQUIVO: src/app/fornecedores/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Listagem e gerenciamento de fornecedores (empresas e prestadores de serviço).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/fornecedores?page=1&limit=10&status=todos → Lista de fornecedores
 *    2. POST /api/fornecedores → Criar novo fornecedor (redireciona /fornecedores/novo)
 *    3. GET /api/fornecedores/buscar?q=termo → Busca por nome/CNPJ/CPF
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import Pagination from "@/components/Pagination";
import { FornecedoresService } from "@/services/fornecedores.service";
import { 
  Building2, Plus, Filter, Search
} from "lucide-react";

export default function FornecedoresPage() {
  const [statusFilter, setStatusFilter] = useState("");
  const [buscaGeral, setBuscaGeral] = useState("");
  const [fornecedores, setFornecedores] = useState<any[]>([]);
  
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(15);
  const [totalItems, setTotalItems] = useState(0);

  useEffect(() => {
    const params = {
      page: currentPage,
      search: buscaGeral,
      // Se houver busca por status, tratar se a API suportar
    };
    
    FornecedoresService.listar(params).then((res: any) => {
      setFornecedores(res.data || []);
      setTotalItems(res.total || res.meta?.total || 0);
    });
  }, [currentPage, buscaGeral, pageSize]);

  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  const handleClearFilters = () => {
    setBuscaGeral("");
    setStatusFilter("");
    setCurrentPage(1);
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-full bg-[#FEF3C7] flex items-center justify-center shrink-0">
                <Building2 className="w-[20px] h-[20px] text-[#F59E0B]" strokeWidth={2.5} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Fornecedores</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerenciamento de Fornecedores</p>
              </div>
            </div>
            <Link href="/pessoas-e-empresas/fornecedores/novo" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[12px] font-[600] flex items-center gap-2 shadow-sm">
              <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
              NOVO FORNECEDOR
            </Link>
          </div>

          {/* FILTERS ROW */}
          <div className="flex items-center gap-3 shrink-0 bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-3">
            <div className="flex items-center gap-2 px-2 border-r border-[#E5E7EB] shrink-0">
              <Filter className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.5} />
              <h2 className="text-[13px] font-[700] text-[#1A1A2E] pr-2">Filtros</h2>
            </div>
            
            <div className="flex-1 flex items-center gap-3">
              <div className="relative w-full max-w-[300px]">
                <input 
                  type="text" 
                  value={buscaGeral}
                  onChange={(e) => { setBuscaGeral(e.target.value); setCurrentPage(1); }}
                  placeholder="Buscar por nome..." 
                  className="w-full h-[36px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[13px] text-[#111827] placeholder-[#9CA3AF] outline-none hover:border-[#D1D5DB] focus:bg-white focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" 
                />
                <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" strokeWidth={2.5} />
              </div>

              <div className="w-[160px]">
                <CustomSelect 
                  options={[
                    {label: "Status: Todos", value: ""},
                    {label: "Ativo", value: "Ativo"},
                    {label: "Inativo", value: "Inativo"}
                  ]}
                  value={statusFilter}
                  onChange={setStatusFilter}
                  placeholder="Status"
                  className="h-[36px]"
                />
              </div>
            </div>

            <button onClick={handleClearFilters} className="h-[36px] px-4 rounded-[8px] text-[12px] font-[600] text-[#6B7280] hover:text-[#1A1A2E] hover:bg-[#F3F4F6] transition-colors shrink-0 border border-transparent hover:border-[#E5E7EB]">
              Limpar
            </button>
          </div>

          {/* TABLE SECTION */}
          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            <div className="flex items-center justify-between p-4 border-b border-[#F1F1F4] shrink-0">
              <h2 className="text-[14px] font-[700] text-[#1A1A2E]">Fornecedores Cadastrados</h2>
            </div>
            
            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-white shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">Código</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">Nome</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">CPF/CNPJ</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">E-mail</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">Telefone</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">Status</th>
                    <th className="py-3 px-4 text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider text-center">Ação</th>
                  </tr>
                </thead>
                <tbody>
                  {fornecedores.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="py-8 text-center text-[13px] text-[#6B7280]">
                        Nenhum fornecedor encontrado.
                      </td>
                    </tr>
                  ) : fornecedores.map((f: any) => (
                    <tr key={f.id} className="hover:bg-[#F9FAFB] transition-colors border-b border-[#F1F1F4]">
                      <td className="py-3 px-4 text-[12px] font-[600] text-[#6B7280]">{f.id}</td>
                      <td className="py-3 px-4">
                        <div className="flex flex-col">
                          <Link href={`/pessoas-e-empresas/fornecedores/${f.id}`} className="text-[13px] font-[700] text-[#6D28D9] hover:underline">
                            {f.nome}
                          </Link>
                        </div>
                      </td>
                      <td className="py-3 px-4 text-[12px] text-[#4B5563]">{f.documento || "-"}</td>
                      <td className="py-3 px-4 text-[12px] text-[#4B5563]">{f.email || "-"}</td>
                      <td className="py-3 px-4 text-[12px] text-[#4B5563]">{f.telefone || "-"}</td>
                      <td className="py-3 px-4">
                        <span className={`inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-[700] uppercase tracking-wide ${f.status === 'Ativo' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#FEE2E2] text-[#DC2626]'}`}>
                          {f.status || 'Ativo'}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-center">
                        <Link href={`/pessoas-e-empresas/fornecedores/${f.id}`} className="inline-flex items-center justify-center w-[32px] h-[32px] rounded-[6px] text-[#9CA3AF] hover:bg-[#F3E8FF] hover:text-[#5B21B6] transition-colors">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* TABLE FOOTER / PAGINATION */}
            <div className="border-t border-[#F1F1F4] bg-[#F9FAFB]">
              <Pagination
                currentPage={currentPage}
                onPageChange={handlePageChange}
                pageSize={pageSize}
                onPageSizeChange={handlePageSizeChange}
                total={totalItems}
              />
            </div>

          </div>
          
        </main>
      </div>
    </div>
  );
}
