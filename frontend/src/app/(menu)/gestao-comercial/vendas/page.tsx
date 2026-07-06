"use client";
import { VendasService } from "@/services/vendas.service";


import React, { useState, useEffect } from "react";
import { toast } from "sonner";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import CustomSelect from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import {
  ShoppingCart,
  Plus,
  RefreshCcw,
  Search,
  Trash2,
} from "lucide-react";

// MOCK DATA PARA VENDAS


export default function VendasPage() {
  const { t } = useTranslation();
  const [buscaCliente, setBuscaCliente] = useState("");
  const [buscaVendedor, setBuscaVendedor] = useState("");
  const [buscaStatus, setBuscaStatus] = useState("");
  const [buscaData, setBuscaData] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [vendas, setVendas] = useState<any[]>([]);

  useEffect(() => {
    VendasService.listar().then(setVendas);
  }, []);

  const filteredVendas = vendas.filter(v => {
    const matchCliente = v.cliente.toLowerCase().includes(buscaCliente.toLowerCase());
    const matchVendedor = v.vendedor.toLowerCase().includes(buscaVendedor.toLowerCase());
    const matchStatus = buscaStatus ? v.status === buscaStatus : true;
    // Simple date string matching for mock purposes
    const matchData = buscaData ? v.data.includes(buscaData.split("-").reverse().join("/")) : true; 
    
    return matchCliente && matchVendedor && matchStatus && matchData;
  });
  const paginatedVendas = filteredVendas.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  const handleExcluir = (id: number) => {
    if (window.confirm(t("Tem certeza que deseja excluir este registro de venda?"))) {
      setVendas(prev => prev.filter(v => v.id !== id));
    }
  };

  const handleReenviarCobranca = (id: number) => {
    toast.success(t("Cobrança recriada e reenviada com sucesso!"));
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <ShoppingCart className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Todas as Vendas")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Acompanhe e gerencie as vendas da sua operação.")}</p>
              </div>
            </div>

            {/* Toolbar: Botão e Buscas */}
            <div className="p-[24px] flex flex-col xl:flex-row items-start xl:items-center justify-between gap-[16px]">
              <div className="flex items-center gap-[12px]">
                {/* Contador Visual de Vendas Filtradas */}
                <span className="text-[13px] font-[500] text-[#6B7280] hidden sm:inline-block">
                  {filteredVendas.length} {filteredVendas.length === 1 ? t("venda encontrada") : t("vendas encontradas")}
                </span>
              </div>
              
              <div className="flex flex-wrap items-center gap-[10px] w-full xl:w-auto">
                {/* Filtro de Status */}
                <CustomSelect
                  options={[
                    { label: t("Todos os Status"), value: "" },
                    { label: t("Pago"), value: "Pago" },
                    { label: t("Parcelado"), value: "Parcelado" },
                    { label: t("Churn"), value: "Churn" }
                  ]}
                  value={buscaStatus}
                  onChange={setBuscaStatus}
                  placeholder={t("Status")}
                  triggerClassName="h-[36px] min-w-[150px] bg-white text-[12px]"
                />

                {/* Filtro de Data */}
                <div className="h-[36px]">
                  <CustomDatePicker
                    value={buscaData}
                    onChange={setBuscaData}
                    placeholder={t("Filtrar por data")}
                    className="w-[140px] h-[36px] text-[12px] bg-white"
                  />
                </div>

                <div className="relative flex items-center w-full sm:w-auto h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaCliente}
                    onChange={(e) => setBuscaCliente(e.target.value)}
                    placeholder={t("Buscar por Cliente")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full sm:w-[150px]"
                  />
                </div>
                <div className="relative flex items-center w-full sm:w-auto h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaVendedor}
                    onChange={(e) => setBuscaVendedor(e.target.value)}
                    placeholder={t("Buscar por Vendedor")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full sm:w-[150px]"
                  />
                </div>
              </div>
            </div>

            {/* Tabela - Scroll horizontal caso a tela seja menor */}
            <div className="flex-1 flex flex-col overflow-x-auto">
              
              <div className="min-w-[1000px] flex-1 flex flex-col">
                {/* Cabeçalho */}
                <div className="grid grid-cols-[110px_1.8fr_1.3fr_1.4fr_100px_100px_90px_120px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD]">
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Cliente")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Vendedor")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Plano adquirido")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Valor")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Pagamento")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280]">{t("Data")}</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Ações")}</span>
                </div>

                {/* Linhas */}
                {paginatedVendas.map((venda) => (
                  <div key={venda.id} className="grid grid-cols-[110px_1.8fr_1.3fr_1.4fr_100px_100px_90px_120px] items-center px-[24px] py-[12px] min-h-[56px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0">
                    
                    {/* STATUS (Condicional para Parcelado) */}
                    <div className="flex flex-col items-start pr-4">
                      <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                        venda.status === "Pago" ? "bg-[#D1FAE5] text-[#059669]" :
                        venda.status === "Churn" ? "bg-[#FEE2E2] text-[#DC2626]" :
                        venda.status === "Parcelado" ? "bg-[#DBEAFE] text-[#2563EB]" :
                        "bg-[#F3F4F6] text-[#6B7280]"
                      }`}>
                        {venda.status === "Parcelado" ? t("Parcelado") :
                         venda.status === "Pago" ? t("Pago") :
                         venda.status === "Churn" ? t("Churn") : t(venda.status)}
                      </span>
                      {venda.status === "Parcelado" && venda.parcelas && (
                        <span className="text-[10px] font-[600] text-[#6B7280] mt-1 ml-1">
                          {venda.parcelas}
                        </span>
                      )}
                    </div>

                    {/* CLIENTE (Nome grande + CPF/CNPJ pequeno) */}
                    <div className="flex flex-col pr-4">
                      <Link href={`/gestao-comercial/clientes/${venda.id}`} className="text-[13px] font-[600] text-[#6D28D9] truncate cursor-pointer hover:underline mb-0.5">
                        {venda.cliente}
                      </Link>
                      <span className="text-[11px] font-[500] text-[#9CA3AF] truncate">
                        {venda.cpf}
                      </span>
                    </div>

                    {/* VENDEDOR */}
                    <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{venda.vendedor}</span>
                    
                    {/* PLANO */}
                    <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{venda.plano}</span>
                    
                    {/* VALOR */}
                    <span className="text-[12px] font-[600] text-[#111827] truncate pr-4">{venda.valor}</span>
                    
                    {/* PAGAMENTO */}
                    <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{venda.pagamento}</span>
                    
                    {/* DATA */}
                    <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{venda.data}</span>

                    {/* AÇÕES (Recriar, Editar, Excluir) */}
                    <div className="flex items-center justify-center gap-[6px]">
                      {/* Recriar Cobrança */}
                      <button 
                        onClick={() => handleReenviarCobranca(venda.id)}
                        title={t("Criar cobrança novamente")}
                        className="w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white flex items-center justify-center hover:bg-[#F3F4F6] transition-colors"
                      >
                        <RefreshCcw className="w-[14px] h-[14px] text-[#6B7280]" strokeWidth={2.2} />
                      </button>
                      
                      {/* Excluir */}
                      <button className="w-[30px] h-[30px] rounded-[8px] bg-[#EF4444]/[0.08] flex items-center justify-center hover:bg-[#EF4444]/[0.16] transition-colors border border-transparent" onClick={() => handleExcluir(venda.id)}>
                        <Trash2 className="w-[14px] h-[14px] text-[#EF4444]" strokeWidth={2.2} />
                      </button>
                    </div>

                  </div>
                ))}
              </div>
            </div>

            <div className="p-[12px_24px] border-t border-[#E5E7EB]">
              <Pagination
                currentPage={currentPage}
                onPageChange={handlePageChange}
                pageSize={pageSize}
                onPageSizeChange={handlePageSizeChange}
                total={filteredVendas.length}
              />
            </div>
          </div>

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[22px] pb-[12px]">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
