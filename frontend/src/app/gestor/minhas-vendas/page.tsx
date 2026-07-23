"use client";
import { VendasService } from "@/services/vendas.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
import Pagination from "@/components/Pagination";
import { useTranslation } from "react-i18next";
import {
  Search,
  Filter,
  Plus,
  MoreVertical,
  CheckCircle2,
  XCircle,
  Clock,
  ArrowUpRight,
  Pencil,
  Trash2
} from "lucide-react";




export default function GestorMinhasVendasPage() {
  const { t } = useTranslation();
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [vendas, setVendas] = useState<any[]>([]);
  const [busca, setBusca] = useState("");

  useEffect(() => {
    VendasService.listar({ page: currentPage, search: busca }).then((res) => {
      setVendas(res.data);
      if (res.meta) {
        setTotalPages(res.meta.last_page || 1);
        setTotalItems(res.meta.total || res.data.length);
      }
    });
  }, [currentPage, busca]);

  return (
    <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
      <div className="w-full flex flex-col gap-[24px]">

        {/* Page Header (Padrão Admin) */}
        <div className="flex items-start justify-between gap-[16px]">
          <div className="flex flex-col">
            <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Minhas Vendas")}</h1>
            <p className="text-[14px] text-[#6B7280]">{t("Gerencie suas vendas e acompanhe os status de pagamento.")}</p>
          </div>
          <Link href="/vendedor/nova-venda" className="flex items-center gap-[6px] px-[16px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide shrink-0">
            <Plus className="w-[16px] h-[16px]" strokeWidth={2.4} />
            {t("NOVA VENDA")}
          </Link>
        </div>

        {/* Filters / Search Bar (Padrão Admin) */}
        <div className="bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] flex flex-col sm:flex-row items-center justify-between gap-[16px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
          <div className="relative w-full sm:max-w-[320px]">
            <Search className="absolute left-[12px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-[#9CA3AF]" />
            <input 
              type="text" 
              value={busca}
              onChange={(e) => {
                setBusca(e.target.value);
                setCurrentPage(1);
              }}
              placeholder={t("Buscar vendas...")}
              className="w-full h-[40px] pl-[38px] pr-[12px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#111827] placeholder-[#9CA3AF] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all"
            />
          </div>
          <button className="h-[40px] px-[16px] bg-white border border-[#E5E7EB] text-[#374151] rounded-[8px] flex items-center gap-[8px] text-[14px] font-[600] hover:bg-[#F9FAFB] transition-colors shrink-0 whitespace-nowrap">
            <Filter className="w-[16px] h-[16px] text-[#6B7280]" />
            {t("Filtros")}
          </button>
        </div>

        {/* Data Grid Wrapper (Padrão Admin) */}
        <div className="bg-white border border-[#E5E7EB] rounded-[12px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
          
          <div className="p-[16px_24px_0_24px] flex items-center justify-between pb-[16px]">
            <h2 className="text-[16px] font-[700] text-[#111827]">{t("Lista de Vendas")}</h2>
            <span className="text-[13px] text-[#6B7280]">{totalItems} {t("vendas encontradas")}</span>
          </div>

          <div className="w-full h-px bg-[#E5E7EB]"></div>

          <div className="overflow-x-auto">
            {/* CSS Grid (Padrão Equipes) */}
            <div className="min-w-[1000px] flex flex-col text-[14px]">
              
              {/* Header */}
              <div className="grid grid-cols-[minmax(200px,2fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(160px,1.5fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px] gap-4 p-[16px_24px] bg-[#F9FAFB] border-b border-[#E5E7EB] text-[12px] font-[600] text-[#6B7280] uppercase tracking-wider">
                <div className="flex items-center gap-2">{t("Cliente")}</div>
                <div className="flex items-center gap-2">{t("Plano")}</div>
                <div className="flex items-center gap-2">{t("Valor")}</div>
                <div className="flex items-center gap-2">{t("Tipo")}</div>
                <div className="flex items-center gap-2">{t("Status")}</div>
                <div className="flex items-center gap-2">{t("Pagamento")}</div>
                <div className="flex items-center gap-2">{t("Data")}</div>
                <div className="flex items-center justify-end gap-2">{t("Ações")}</div>
              </div>

              {/* Rows */}
              {vendas.map((venda, i) => {
                let badgeBg = "bg-[#F3F4F6]";
                let statusColor = "text-[#6B7280]";
                if (venda.status === "concluida") {
                  badgeBg = "bg-[#DCFCE7]"; statusColor = "text-[#15803D]";
                } else if (venda.status === "pendente") {
                  badgeBg = "bg-[#FEF9C3]"; statusColor = "text-[#A16207]";
                } else if (venda.status === "cancelada") {
                  badgeBg = "bg-[#FEE2E2]"; statusColor = "text-[#DC2626]";
                }

                return (
                <div key={venda.id} className={`grid grid-cols-[minmax(200px,2fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(120px,1fr)_minmax(160px,1.5fr)_minmax(120px,1fr)_minmax(120px,1fr)_80px] gap-4 p-[16px_24px] items-center hover:bg-[#F9FAFB] transition-colors ${i !== vendas.length - 1 ? 'border-b border-[#E5E7EB]' : ''}`}>
                  
                  {/* Cliente */}
                  <div className="flex items-center gap-[12px] truncate">
                    <span className="font-[600] text-[#6D28D9] truncate hover:underline cursor-pointer">{venda.cliente?.nome || 'N/A'}</span>
                  </div>

                  {/* Plano */}
                  <div className="flex items-center truncate">
                    <span className="text-[#374151] truncate">{venda.plano || '-'}</span>
                  </div>

                  {/* Valor */}
                  <div className="flex items-center truncate">
                    <span className="font-[600] text-[#111827] truncate">
                      {new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(venda.valor))}
                    </span>
                  </div>

                  {/* Tipo */}
                  <div className="flex items-center truncate">
                    <span className="text-[#6B7280] text-[13px] truncate">{venda.forma_pagamento || '-'}</span>
                  </div>

                  {/* Status */}
                  <div className="flex flex-col items-start gap-[4px] truncate">
                    <span className={`px-[8px] py-[2px] ${badgeBg} ${statusColor} rounded-full text-[11px] font-[700] uppercase tracking-wider leading-tight whitespace-nowrap`}>
                      {venda.status === "concluida" ? t("Concluída") :
                       venda.status === "pendente" ? t("Pendente") :
                       venda.status === "cancelada" ? t("Cancelada") : t(venda.status)}
                    </span>
                  </div>

                  {/* Pagamento */}
                  <div className="flex items-center truncate">
                    <span className="text-[#6B7280] text-[13px] truncate">{venda.modo_cobranca || '-'}</span>
                  </div>

                  {/* Data */}
                  <div className="flex items-center truncate">
                    <span className="text-[#6B7280] text-[13px] truncate">
                      {venda.created_at ? new Date(venda.created_at).toLocaleDateString('pt-BR') : '-'}
                    </span>
                  </div>

                  {/* Ações */}
                  <div className="flex items-center justify-end gap-[8px]">
                    <button className="w-[32px] h-[32px] flex items-center justify-center rounded-[8px] hover:bg-[#E5E7EB] text-[#6B7280] transition-colors">
                      <Pencil className="w-[16px] h-[16px]" />
                    </button>
                    <button className="w-[32px] h-[32px] flex items-center justify-center rounded-[8px] hover:bg-[#FEE2E2] text-[#EF4444] transition-colors">
                      <Trash2 className="w-[16px] h-[16px]" />
                    </button>
                  </div>
                  
                </div>
              )})}
            </div>
          </div>

          <div className="w-full h-px bg-[#E5E7EB]"></div>

          <Pagination
            total={vendas.length}
            currentPage={currentPage}
            pageSize={10}
            onPageChange={setCurrentPage}
            onPageSizeChange={() => {}}
          />
        </div>
      </div>
    </main>
  );
}
