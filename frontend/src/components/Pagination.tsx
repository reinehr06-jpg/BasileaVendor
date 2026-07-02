"use client";

import React from "react";
import CustomSelect from "@/components/CustomSelect";
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Tabelas / Pagination
// ============================================================
// PROPÓSITO:
//   Componente de paginação para tabelas. Exibe controles de
//   página, seletor de linhas por página, e contagem total.
//
// INPUTS / PROPS:
//   total: number — total de registros
//   currentPage: number — página atual (1-indexed)
//   pageSize: number — itens por página
//   onPageChange: (page: number) => void
//   onPageSizeChange: (size: number) => void
//
// BOTÕES DE AÇÃO / EVENTOS:
//   Select pageSize → onPageSizeChange(Number(v))
//   Select page → onPageChange(Number(v))
//   ChevronsLeft → onPageChange(1)
//   ChevronLeft → onPageChange(currentPage - 1)
//   ChevronRight → onPageChange(currentPage + 1)
//   ChevronsRight → onPageChange(totalPages)
//
// COMPORTAMENTOS:
//   - totalPages = Math.max(1, Math.ceil(total / pageSize))
//   - from/to calculados com (currentPage - 1) * pageSize + 1
//   - Botões desabilitados (disabled) quando nos limites:
//     currentPage <= 1 (anterior/primeiro), currentPage >= totalPages
//     (próximo/último)
//   - Página atual exibida em botão roxo destacado
//   - PageSize options: 10, 20, 50
// ============================================================
interface PaginationProps {
  total: number;
  currentPage: number;
  pageSize: number;
  onPageChange: (page: number) => void;
  onPageSizeChange: (size: number) => void;
}

export default function Pagination({
  total,
  currentPage,
  pageSize,
  onPageChange,
  onPageSizeChange,
}: PaginationProps) {
  const { t } = useTranslation();
  const totalPages = Math.max(1, Math.ceil(total / pageSize));
  const from = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
  const to = Math.min(currentPage * pageSize, total);

  const pageSizeOptions = [
    { value: "10", label: "10" },
    { value: "20", label: "20" },
    { value: "50", label: "50" },
  ];

  const pageOptions = Array.from({ length: totalPages }, (_, i) => ({
    value: String(i + 1),
    label: String(i + 1),
  }));

  return (
    <div className="p-[18px_24px_20px_24px] border-t border-[#F1F1F4] bg-white flex items-center justify-between text-[13px] text-[#6B7280]">
      <div className="flex items-center gap-[8px]">{/* MAPA DO TESOURO: Seletor de linhas por página */}
        <span>{t("Linhas por página")}</span>
        <CustomSelect
          options={pageSizeOptions}
          value={String(pageSize)}
          onChange={(v) => onPageSizeChange(Number(v))}
          className="w-[80px]"
          triggerClassName="!min-h-[32px] !h-[32px]"
          compact={true}
        />
      </div>

      <span className="font-[500] text-[#374151]">
        {from}-{to} {t("de")} {total}
      </span>

      <div className="flex items-center gap-[6px]">{/* MAPA DO TESOURO: Navegador de páginas */}
        <span className="mr-1">{t("Página")}</span>
        <CustomSelect
          options={pageOptions}
          value={String(currentPage)}
          onChange={(v) => onPageChange(Number(v))}
          className="w-[80px]"
          triggerClassName="!min-h-[32px] !h-[32px]"
          compact={true}
        />
        <div className="flex items-center gap-[2px]">{/* MAPA DO TESOURO: Botões de navegação (primeiro, anterior, atual, próximo, último) */}
          <button
            onClick={() => onPageChange(1)}
            disabled={currentPage <= 1}
            className="w-[28px] h-[28px] flex items-center justify-center text-[#C4C4C4] disabled:opacity-30 disabled:cursor-not-allowed hover:text-[#6B7280] transition-colors"
          >
            <ChevronsLeft className="w-[15px] h-[15px]" strokeWidth={2.4} />
          </button>
          <button
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage <= 1}
            className="w-[28px] h-[28px] flex items-center justify-center text-[#C4C4C4] disabled:opacity-30 disabled:cursor-not-allowed hover:text-[#6B7280] transition-colors"
          >
            <ChevronLeft className="w-[15px] h-[15px]" strokeWidth={2.4} />
          </button>
          <button className="w-[30px] h-[30px] flex items-center justify-center rounded-[6px] border border-[#6D28D9] text-[#6D28D9] text-[13px] font-[600]">
            {currentPage}
          </button>
          <button
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage >= totalPages}
            className="w-[28px] h-[28px] flex items-center justify-center text-[#C4C4C4] disabled:opacity-30 disabled:cursor-not-allowed hover:text-[#6B7280] transition-colors"
          >
            <ChevronRight className="w-[15px] h-[15px]" strokeWidth={2.4} />
          </button>
          <button
            onClick={() => onPageChange(totalPages)}
            disabled={currentPage >= totalPages}
            className="w-[28px] h-[28px] flex items-center justify-center text-[#C4C4C4] disabled:opacity-30 disabled:cursor-not-allowed hover:text-[#6B7280] transition-colors"
          >
            <ChevronsRight className="w-[15px] h-[15px]" strokeWidth={2.4} />
          </button>
        </div>
      </div>
    </div>
  );
}
