"use client";

import React, { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";
import { ChevronDown, Check, X, Search } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Formulários / CustomSelect
// ============================================================
// PROPÓSITO:
//   Select customizado com suporte a busca, seleção múltipla,
//   e dropdown estilizado. Substitui <select> nativo em todo
//   o sistema.
//
// INPUTS / PROPS:
//   options: SelectOption[] — { value, label }
//   value?: string | string[] — valor(es) selecionado(s)
//   onChange?: (value: any) => void
//   placeholder?: string — padrão "Selecione"
//   disabled?: boolean
//   multiple?: boolean — modo multisseleção
//   searchable?: boolean — exibe campo de busca
//   className?: string — classes para o container
//   triggerClassName?: string — classes adicionais para o gatilho
//
// BOTÕES DE AÇÃO / EVENTOS:
//   Click no trigger → toggleDropdown
//   Click em opção → handleSelect(optionValue)
//   Click X em tag (multiple) → removeValue(val, e)
//   Clique fora → fecha dropdown
//
// COMPORTAMENTOS:
//   - Modo single: seleciona e fecha o dropdown
//   - Modo multiple: acumula valores, exibe tags removíveis
//   - searchable: filtra options por label (case-insensitive)
//   - Foca no input de busca ao abrir (setTimeout 50ms)
//   - Limpa searchTerm ao fechar
//   - Desabilitado: fundo cinza, cursor not-allowed
//
// #pag72 — CustomSelect
// ============================================================
export interface SelectOption {
  value: string;
  label: string;
}

interface CustomSelectProps {
  options: SelectOption[];
  value?: string | string[]; // string for single, string[] for multiple
  onChange?: (value: any) => void;
  placeholder?: string;
  disabled?: boolean;
  multiple?: boolean;
  searchable?: boolean;
  className?: string;
  triggerClassName?: string; // classes adicionais para o div gatilho (ex: altura compacta)
  placement?: "top" | "bottom"; // define se o dropdown abre para baixo ou para cima
  compact?: boolean; // modo compacto para paginação
}

export default function CustomSelect({
  options = [],
  value,
  onChange,
  placeholder = "Selecione",
  disabled = false,
  multiple = false,
  searchable = false,
  className = "",
  triggerClassName = "",
  placement = "bottom",
  compact = false,
}: CustomSelectProps) {
  const { t } = useTranslation();
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [dropdownStyle, setDropdownStyle] = useState<React.CSSProperties>({});
  const dropdownRef = useRef<HTMLDivElement>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);
  const portalRef = useRef<HTMLDivElement>(null);

  // Fecha o dropdown se clicar fora
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
      // Ignora scroll dentro do próprio dropdown
      if (portalRef.current && portalRef.current.contains(e.target as Node)) return;
      if (isOpen) setIsOpen(false);
    };
    window.addEventListener("scroll", handleScroll, true);
    return () => window.removeEventListener("scroll", handleScroll, true);
  }, [isOpen]);

  // Foca no input de busca ao abrir e ajusta a posição
  useEffect(() => {
    if (isOpen && searchable && searchInputRef.current) {
      setTimeout(() => searchInputRef.current?.focus(), 50);
    }
    if (isOpen && dropdownRef.current) {
      const rect = dropdownRef.current.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      const spaceAbove = rect.top;
      const dropdownMaxHeight = 220; // altura máxima estimada do dropdown
      
      // Abre para cima se não houver espaço embaixo E houver mais espaço em cima, OU se a prop placement for explicitamente 'top'
      const shouldOpenUpwards = placement === "top" || (spaceBelow < dropdownMaxHeight && spaceAbove > spaceBelow);

      setDropdownStyle({
        position: "fixed",
        top: shouldOpenUpwards ? undefined : rect.bottom + 4,
        bottom: shouldOpenUpwards ? window.innerHeight - rect.top + 4 : undefined,
        left: rect.left,
        width: rect.width,
      });
    }
    if (!isOpen) {
      setSearchTerm("");
    }
  }, [isOpen, searchable, placement]);

  const toggleDropdown = () => {
    if (!disabled) setIsOpen(!isOpen);
  };

  const handleSelect = (optionValue: string) => {
    if (multiple) {
      const currentValue = Array.isArray(value) ? value : [];
      if (currentValue.includes(optionValue)) {
        onChange?.(currentValue.filter((v) => v !== optionValue));
      } else {
        onChange?.([...currentValue, optionValue]);
      }
    } else {
      onChange?.(optionValue);
      setIsOpen(false);
    }
  };

  const removeValue = (optionValueToRemove: string, e: React.MouseEvent) => {
    e.stopPropagation();
    if (multiple && Array.isArray(value)) {
      onChange?.(value.filter((v) => v !== optionValueToRemove));
    }
  };

  const filteredOptions = options.filter((opt) =>
    opt.label.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Renderização do valor selecionado
  const renderValue = () => {
    if (multiple) {
      const selectedOptions = Array.isArray(value) ? value : [];
      if (selectedOptions.length === 0) {
        return <span className="text-[#9CA3AF] truncate">{placeholder}</span>;
      }
      return (
        <div className="flex flex-wrap gap-1.5 overflow-hidden">
          {selectedOptions.map((val) => {
            const opt = options.find((o) => o.value === val);
            if (!opt) return null;
            return (
              <span
                key={val}
                className="inline-flex items-center gap-1 px-1.5 py-0.5 bg-[#F3F4F6] text-[#374151] text-[12px] font-medium rounded-[4px] border border-[#D1D5DB] max-w-[120px]"
              >
                <span className="truncate">{opt.label}</span>
                <X
                  className="w-3 h-3 hover:text-[#111827] cursor-pointer shrink-0"
                  onClick={(e) => removeValue(val, e)}
                  strokeWidth={2.5}
                />
              </span>
            );
          })}
        </div>
      );
    } else {
      if (!value) return <span className="text-[#9CA3AF] truncate">{placeholder}</span>;
      const selectedOption = options.find((o) => o.value === value);
      return <span className="text-[#1A1A2E] truncate">{selectedOption?.label || value}</span>;
    }
  };

  const isSelected = (val: string) => {
    if (multiple && Array.isArray(value)) return value.includes(val);
    return value === val;
  };

  return (
    <div className={`relative ${className || "w-full"}`} ref={dropdownRef}>
      {/* MAPA DO TESOURO: Gatilho — exibe valor(es) selecionado(s) */}
      <div
        onClick={toggleDropdown}
        className={`min-h-[38px] px-[12px] flex items-center justify-between bg-white border rounded-[8px] transition-all cursor-pointer select-none
          ${disabled ? "bg-[#F3F4F6] border-[#E5E7EB] cursor-not-allowed opacity-80" : "hover:border-[#D1D5DB]"}
          ${isOpen ? "border-[#D1D5DB]" : "border-[#E5E7EB]"}
          ${triggerClassName}
        `}
      >
        <div className="flex-1 overflow-hidden pr-2">{renderValue()}</div>
        <ChevronDown
          className={`w-[16px] h-[16px] text-[#9CA3AF] shrink-0 transition-transform duration-300 ${isOpen ? "rotate-180 text-[#111827]" : ""}`}
          strokeWidth={2.4}
        />
      </div>

      {/* MAPA DO TESOURO: Dropdown com busca e lista de opções via Portal */}
      {isOpen && typeof document !== "undefined" && createPortal(
        <div 
          ref={portalRef}
          style={dropdownStyle}
          className={`z-[9999] bg-white border border-[#E5E7EB] rounded-[10px] shadow-[0_10px_30px_rgba(0,0,0,0.1)] overflow-hidden animate-in fade-in zoom-in-95 duration-100`}
        >
          
          {searchable && (
            <div className="p-2 border-b border-[#F1F1F4] bg-[#F9FAFB]/50">
              <div className="relative flex items-center bg-white border border-[#E5E7EB] rounded-[6px] px-2.5 h-[32px]  transition-colors">
                <Search className="w-3.5 h-3.5 text-[#9CA3AF] mr-2 shrink-0" strokeWidth={2.5} />
                <input
                  ref={searchInputRef}
                  type="text"
                  placeholder={t("Pesquisar...")}
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="bg-transparent border-none outline-none text-[13px] text-[#374151] w-full"
                  onClick={(e) => e.stopPropagation()}
                />
              </div>
            </div>
          )}

          <div className="max-h-[220px] overflow-y-auto p-1 custom-scrollbar">
            {filteredOptions.length === 0 ? (
              <div className="p-3 text-center text-[13px] text-[#6B7280]">
                {t("Nenhum resultado encontrado")}
              </div>
            ) : (
              filteredOptions.map((opt) => {
                const selected = isSelected(opt.value);
                return (
                  <div
                    key={opt.value}
                    onClick={() => handleSelect(opt.value)}
                    className={`flex items-center justify-between px-3 ${compact ? "py-1.5 text-[12px]" : "py-2 text-[14px]"} rounded-[6px] cursor-pointer transition-colors
                      ${selected ? "bg-[#F3F4F6] text-[#111827] font-medium" : "text-[#374151] hover:bg-[#F9FAFB]"}
                    `}
                  >
                    <span className="truncate pr-4">{opt.label}</span>
                    {selected && <Check className="w-4 h-4 shrink-0 text-[#111827]" strokeWidth={2.5} />}
                  </div>
                );
              })
            )}
          </div>
        </div>,
        document.body
      )}
    </div>
  );
}
