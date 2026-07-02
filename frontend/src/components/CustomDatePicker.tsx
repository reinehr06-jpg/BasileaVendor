"use client";

import React, { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";
import { Calendar, ChevronLeft, ChevronRight } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Formulários / CustomDatePicker
// ============================================================
// PROPÓSITO:
//   Seletor de data customizado com calendário em dropdown.
//   Aceita string YYYY-MM-DD como valor e exibe no formato
//   DD/MM/AAAA.
//
// INPUTS / PROPS:
//   value?: string — data selecionada (YYYY-MM-DD)
//   onChange?: (date: string) => void
//   placeholder?: string — padrão "DD/MM/AAAA"
//   disabled?: boolean — desabilita o campo
//
// BOTÕES DE AÇÃO / EVENTOS:
//   Click no trigger → abre/fecha dropdown
//   handlePrevMonth / handleNextMonth → navega entre meses
//   Click em um dia → handleSelectDate(day) → onChange + fecha
//   Clique fora → fecha dropdown
//
// COMPORTAMENTOS:
//   - Calendário com grid 7xN (dias da semana em português)
//   - Dia selecionado: fundo roxo (#6D28D9) com texto branco
//   - Dia atual: fundo roxo claro (#F4EEFF) com texto roxo
//   - Desabilitado: fundo cinza, cursor not-allowed, opacidade 80
//
// #pag71 — CustomDatePicker
// ============================================================
export interface CustomDatePickerProps {
  value?: string;
  onChange?: (date: string) => void;
  placeholder?: string;
  disabled?: boolean;
  className?: string;
}

export default function CustomDatePicker({
  value,
  onChange,
  placeholder = "DD/MM/AAAA",
  disabled = false,
  className = "",
}: CustomDatePickerProps) {
  const { t } = useTranslation();
  const [isOpen, setIsOpen] = useState(false);
  const [currentMonth, setCurrentMonth] = useState(new Date());
  const [dropdownStyle, setDropdownStyle] = useState<React.CSSProperties>({});
  const dropdownRef = useRef<HTMLDivElement>(null);
  const portalRef = useRef<HTMLDivElement>(null);

  // Parse string value (YYYY-MM-DD) to Date object for selection
  const selectedDate = value ? new Date(value + "T00:00:00") : null;

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      const target = event.target as Node;
      if (
        dropdownRef.current && !dropdownRef.current.contains(target) &&
        (!portalRef.current || !portalRef.current.contains(target))
      ) {
        setIsOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Fecha o dropdown ao scrollar para não descolar do input
  useEffect(() => {
    const handleScroll = (e: Event) => {
      if (portalRef.current && portalRef.current.contains(e.target as Node)) return;
      if (isOpen) setIsOpen(false);
    };
    window.addEventListener("scroll", handleScroll, true);
    return () => window.removeEventListener("scroll", handleScroll, true);
  }, [isOpen]);

  useEffect(() => {
    if (isOpen && dropdownRef.current) {
      const rect = dropdownRef.current.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      const spaceAbove = rect.top;
      const dropdownMaxHeight = 300; 
      
      const shouldOpenUpwards = spaceBelow < dropdownMaxHeight && spaceAbove > spaceBelow;

      setDropdownStyle({
        position: "fixed",
        top: shouldOpenUpwards ? undefined : rect.bottom + 4,
        bottom: shouldOpenUpwards ? window.innerHeight - rect.top + 4 : undefined,
        left: rect.left,
      });
    }
  }, [isOpen]);

  const toggleDropdown = () => {
    if (!disabled) setIsOpen(!isOpen);
  };

  const getDaysInMonth = (year: number, month: number) => {
    return new Date(year, month + 1, 0).getDate();
  };

  const getFirstDayOfMonth = (year: number, month: number) => {
    return new Date(year, month, 1).getDay();
  };

  const handlePrevMonth = (e: React.MouseEvent) => {
    e.stopPropagation();
    setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1));
  };

  const handleNextMonth = (e: React.MouseEvent) => {
    e.stopPropagation();
    setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1));
  };

  const handleSelectDate = (day: number) => {
    const year = currentMonth.getFullYear();
    const month = String(currentMonth.getMonth() + 1).padStart(2, "0");
    const dayStr = String(day).padStart(2, "0");
    onChange?.(`${year}-${month}-${dayStr}`);
    setIsOpen(false);
  };

  const formatDisplayDate = (dateStr: string) => {
    if (!dateStr) return "";
    const [year, month, day] = dateStr.split("-");
    if (!year || !month || !day) return dateStr;
    return `${day}/${month}/${year}`;
  };

  const renderCalendar = () => {
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    const daysInMonth = getDaysInMonth(year, month);
    const firstDay = getFirstDayOfMonth(year, month);

    const days = [];
    const weekDays = ["D", "S", "T", "Q", "Q", "S", "S"];

    // Week day headers
    const headers = weekDays.map((wd, i) => (
      <div key={`header-${i}`} className="text-center text-[12px] font-[600] text-[#9CA3AF] mb-2">
        {wd}
      </div>
    ));

    // Empty cells for alignment
    for (let i = 0; i < firstDay; i++) {
      days.push(<div key={`empty-${i}`} className="w-[30px] h-[30px]"></div>);
    }

    // Days
    for (let d = 1; d <= daysInMonth; d++) {
      const isSelected = selectedDate?.getDate() === d && selectedDate?.getMonth() === month && selectedDate?.getFullYear() === year;
      const isToday = new Date().getDate() === d && new Date().getMonth() === month && new Date().getFullYear() === year;

      days.push(
        <div
          key={`day-${d}`}
          onClick={(e) => {
            e.stopPropagation();
            handleSelectDate(d);
          }}
          className={`w-[30px] h-[30px] flex items-center justify-center rounded-full text-[13px] cursor-pointer transition-colors
            ${isSelected ? "bg-[#6D28D9] text-white font-[600] shadow-sm" : 
              isToday ? "bg-[#F4EEFF] text-[#6D28D9] font-[600]" : 
              "text-[#374151] hover:bg-[#F3F4F6]"}
          `}
        >
          {d}
        </div>
      );
    }

    const monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

    return (
      <div className="p-3 w-[260px]">
        {/* Header (Month / Year) */}
        <div className="flex items-center justify-between mb-3">
          <button onClick={handlePrevMonth} className="p-1 hover:bg-[#F3F4F6] rounded-full text-[#6B7280]">
            <ChevronLeft className="w-4 h-4" />
          </button>
          <span className="text-[14px] font-[600] text-[#1A1A2E]">
            {monthNames[month]} {year}
          </span>
          <button onClick={handleNextMonth} className="p-1 hover:bg-[#F3F4F6] rounded-full text-[#6B7280]">
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>
        
        {/* Grid */}
        <div className="grid grid-cols-7 gap-1">
          {headers}
          {days}
        </div>
      </div>
    );
  };

  return (
    <div className={`relative w-full ${className}`} ref={dropdownRef}>
      {/* MAPA DO TESOURO: Gatilho — exibe data formatada ou placeholder */}
      <div
        onClick={toggleDropdown}
        className={`h-[38px] px-[12px] flex items-center justify-between bg-white border rounded-[8px] transition-all cursor-pointer select-none
          ${disabled ? "bg-[#F3F4F6] border-[#E5E7EB] cursor-not-allowed opacity-80" : "hover:border-[#D1D5DB]"}
          ${isOpen ? "border-[#6D28D9] ring-1 ring-[#6D28D9]" : "border-[#E5E7EB]"}
        `}
      >
        <div className="flex-1 overflow-hidden pr-2 text-[13px]">
          {value ? (
            <span className="text-[#111827]">{formatDisplayDate(value)}</span>
          ) : (
            <span className="text-[#9CA3AF]">{placeholder}</span>
          )}
        </div>
        <Calendar
          className={`w-[16px] h-[16px] shrink-0 transition-colors duration-300 ${isOpen ? "text-[#6D28D9]" : "text-[#9CA3AF]"}`}
          strokeWidth={2.4}
        />
      </div>

      {/* MAPA DO TESOURO: Dropdown do calendário com Portal */}
      {isOpen && typeof document !== "undefined" && createPortal(
        <div 
          ref={portalRef}
          style={dropdownStyle}
          className="z-[9999] bg-white border border-[#E5E7EB] rounded-[10px] shadow-[0_10px_30px_rgba(0,0,0,0.06)] overflow-hidden animate-in fade-in zoom-in-95 duration-100"
        >
          {renderCalendar()}
        </div>,
        document.body
      )}
    </div>
  );
}
