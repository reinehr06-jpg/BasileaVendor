"use client";

import React, { useState, useEffect } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { 
  ChevronUp, 
  ChevronDown, 
  Database,
  Columns,
  Filter,
  FileSpreadsheet,
  ArrowUpCircle,
  ArrowDownCircle,
  ArrowRightLeft,
  Wallet,
  Building2,
  Tags,
  Search,
  CheckSquare,
  Square,
  GripVertical,
  X,
  Plus,
  LayoutGrid,
  FileText,
  FileCode2,
  ChevronLeft,
  Trash2
} from "lucide-react";
import Link from "next/link";

export default function NovoRelatorioPage() {
  const [openStep, setOpenStep] = useState<number>(1);
  const [fonteSelecionada, setFonteSelecionada] = useState<string>("despesas");
  
  // Período
  const [periodo, setPeriodo] = useState<string>("este_mes");
  const [dataInicial, setDataInicial] = useState("");
  const [dataFinal, setDataFinal] = useState("");
  
  // Colunas
  const [colunasSelecionadas, setColunasSelecionadas] = useState<any[]>([]);
  const [buscaColuna, setBuscaColuna] = useState("");
  const [categoriaColunaFilter, setCategoriaColunaFilter] = useState("Todos os campos");
  const [draggedItemIndex, setDraggedItemIndex] = useState<number | null>(null);

  // Filtros
  const [filtros, setFiltros] = useState<any[]>([]);
  
  // Formato
  const [formato, setFormato] = useState<string>("excel");

  // ==============================
  // DICIONÁRIO DE DADOS (Mock)
  // ==============================
  const dicionarioColunas: any = {
    despesas: [
      { id: "data_vencimento", label: "Data de Vencimento", cat: "Geral", color: "text-[#4B5563]" },
      { id: "data_pagamento", label: "Data de Pagamento", cat: "Geral", color: "text-[#4B5563]" },
      { id: "descricao", label: "Descrição da Despesa", cat: "Dados principais", color: "text-[#6D28D9]" },
      { id: "valor", label: "Valor Original", cat: "Valores", color: "text-[#10B981]" },
      { id: "valor_pago", label: "Valor Pago", cat: "Valores", color: "text-[#10B981]" },
      { id: "categoria", label: "Categoria", cat: "Classificação", color: "text-[#F59E0B]" },
      { id: "fornecedor", label: "Fornecedor", cat: "Entidades", color: "text-[#3B82F6]" },
      { id: "conta", label: "Conta Bancária", cat: "Geral", color: "text-[#4B5563]" },
      { id: "status", label: "Status do Pagamento", cat: "Classificação", color: "text-[#F59E0B]" },
    ],
    receitas: [
      { id: "data_recebimento", label: "Data de Recebimento", cat: "Geral", color: "text-[#4B5563]" },
      { id: "descricao", label: "Descrição da Receita", cat: "Dados principais", color: "text-[#6D28D9]" },
      { id: "valor", label: "Valor Recebido", cat: "Valores", color: "text-[#10B981]" },
      { id: "categoria", label: "Categoria", cat: "Classificação", color: "text-[#F59E0B]" },
      { id: "pagador", label: "Pagador/Membro", cat: "Entidades", color: "text-[#3B82F6]" },
      { id: "conta", label: "Conta Bancária", cat: "Geral", color: "text-[#4B5563]" },
      { id: "status", label: "Status", cat: "Classificação", color: "text-[#F59E0B]" },
    ],
    // Fallback genérico para as outras fontes
    default: [
      { id: "id", label: "ID do Registro", cat: "Geral", color: "text-[#4B5563]" },
      { id: "data", label: "Data de Criação", cat: "Geral", color: "text-[#4B5563]" },
      { id: "nome", label: "Nome/Título", cat: "Dados principais", color: "text-[#6D28D9]" },
      { id: "status", label: "Status", cat: "Classificação", color: "text-[#F59E0B]" },
    ],
    contabilidade: [
      { id: "categoria_interna", label: "Categoria Interna", cat: "Origem", color: "text-[#4B5563]" },
      { id: "status_mapeamento", label: "Status do Mapeamento", cat: "Status", color: "text-[#F59E0B]" },
      { id: "conta_contabil", label: "Conta Contábil (Escritório)", cat: "Destino", color: "text-[#10B981]" },
      { id: "codigo_contabil", label: "Código da Conta", cat: "Destino", color: "text-[#10B981]" },
    ]
  };

  const colunasDisponiveis = dicionarioColunas[fonteSelecionada] || dicionarioColunas["default"];

  // ==============================
  // EFEITOS
  // ==============================
  // Limpar colunas selecionadas se a fonte mudar
  useEffect(() => {
    setColunasSelecionadas([]);
    setFiltros([]);
  }, [fonteSelecionada]);

  // ==============================
  // HANDLERS COLUNAS
  // ==============================
  const handleToggleColumn = (coluna: any) => {
    const exists = colunasSelecionadas.find(c => c.id === coluna.id);
    if (exists) {
      setColunasSelecionadas(colunasSelecionadas.filter(c => c.id !== coluna.id));
    } else {
      setColunasSelecionadas([...colunasSelecionadas, coluna]);
    }
  };

  const handleSelectAll = () => {
    setColunasSelecionadas([...colunasDisponiveis]);
  };

  const handleClearColumns = () => {
    setColunasSelecionadas([]);
  };

  // Drag & Drop
  const handleDragStart = (e: React.DragEvent, index: number) => {
    setDraggedItemIndex(index);
    // Para Firefox permitir o drag
    e.dataTransfer.setData("text/plain", index.toString());
    e.dataTransfer.effectAllowed = "move";
  };

  const handleDragOver = (e: React.DragEvent, index: number) => {
    e.preventDefault(); // Necessário para permitir o drop
    if (draggedItemIndex === null || draggedItemIndex === index) return;
    
    const items = [...colunasSelecionadas];
    const draggedItem = items[draggedItemIndex];
    
    // Remove o item da posição original e insere na nova
    items.splice(draggedItemIndex, 1);
    items.splice(index, 0, draggedItem);
    
    setDraggedItemIndex(index);
    setColunasSelecionadas(items);
  };

  const handleDragEnd = () => {
    setDraggedItemIndex(null);
  };

  // ==============================
  // HANDLERS FILTROS
  // ==============================
  const handleAddFiltro = () => {
    setFiltros([...filtros, { id: Date.now(), logico: "E", campo: colunasDisponiveis[0]?.id || "", operador: "igual", valor: "" }]);
    if (openStep !== 3) setOpenStep(3);
  };

  const handleRemoveFiltro = (id: number) => {
    setFiltros(filtros.filter(f => f.id !== id));
  };

  const handleUpdateFiltro = (id: number, key: string, value: string) => {
    setFiltros(filtros.map(f => f.id === id ? { ...f, [key]: value } : f));
  };

  // ==============================
  // ACTION FINAL
  // ==============================
  const handleGerarRelatorio = () => {
    const payload = {
      fonte: fonteSelecionada,
      periodo: periodo,
      datas: periodo === "personalizado" ? { inicio: dataInicial, fim: dataFinal } : null,
      colunas: colunasSelecionadas.map(c => c.id),
      filtros: filtros,
      formato: formato
    };

    console.log("=== PAYLOAD DO RELATÓRIO ===", payload);
    alert("Payload do Relatório gerado com sucesso! Verifique o Console (F12). \n\nFonte: " + fonteSelecionada + "\nColunas: " + colunasSelecionadas.length + "\nFiltros: " + filtros.length);
  };

  // ==============================
  // RENDER HELPERS
  // ==============================
  const colunasFiltradas = colunasDisponiveis.filter((c: any) => {
    const matchBusca = c.label.toLowerCase().includes(buscaColuna.toLowerCase());
    const matchCat = categoriaColunaFilter === "Todos os campos" || c.cat === categoriaColunaFilter;
    return matchBusca && matchCat;
  });

  const categoriasUnicas = ["Todos os campos", ...Array.from(new Set(colunasDisponiveis.map((c: any) => c.cat))) as string[]];

  const operadoresOptions = [
    { value: "igual", label: "É igual a" },
    { value: "contem", label: "Contém" },
    { value: "maior", label: "Maior que" },
    { value: "menor", label: "Menor que" },
  ];

  const colunasOptions = colunasDisponiveis.map((c: any) => ({ value: c.id, label: c.label }));

  const periodosData = [
    { id: "ultimos_7", label: "Últimos 7 dias" },
    { id: "ultimos_30", label: "Últimos 30 dias" },
    { id: "este_mes", label: "Este mês" },
    { id: "mes_passado", label: "Mês passado" },
    { id: "este_ano", label: "Este ano" },
    { id: "ano_passado", label: "Ano passado" },
    { id: "personalizado", label: "Personalizado" },
  ];

  const fontesDadosInfo = [
    { id: "receitas", label: "Receitas", icon: ArrowUpCircle, color: "text-[#10B981]", bg: "bg-[#ECFDF5]" },
    { id: "despesas", label: "Despesas", icon: ArrowDownCircle, color: "text-[#EF4444]", bg: "bg-[#FEF2F2]" },
    { id: "transferencias", label: "Transferências", icon: ArrowRightLeft, color: "text-[#3B82F6]", bg: "bg-[#EFF6FF]" },
    { id: "contas", label: "Contas Bancárias", icon: Wallet, color: "text-[#F59E0B]", bg: "bg-[#FEF3C7]" },
    { id: "fornecedores", label: "Fornecedores", icon: Building2, color: "text-[#4F46E5]", bg: "bg-[#EEF2FF]" },
    { id: "categorias", label: "Categorias", icon: Tags, color: "text-[#8B5CF6]", bg: "bg-[#F5F3FF]" },
    { id: "contabilidade", label: "Plano de Contas (Contábil)", icon: Database, color: "text-[#EC4899]", bg: "bg-[#FDF2F8]" },
  ];

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="flex-1 flex flex-col overflow-hidden relative">
          
          <div className="shrink-0 px-6 pt-6 pb-2">
             <div className="flex items-center gap-[14px]">
              <Link href="/contabilidade/relatorios" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
                <ChevronLeft className="w-[18px] h-[18px]" />
              </Link>
              <div className="flex flex-col">
                <p className="text-[14px] text-[#6B7280]">Utilize o motor de extração para cruzar e exportar qualquer dado do sistema financeiro.</p>
              </div>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto custom-scrollbar p-6">
            <div className="max-w-[1200px] flex flex-col gap-4 mx-auto pb-24">
              
              {/* PASSO 1: Fonte de Dados */}
              <div className={`bg-white border ${openStep === 1 ? 'border-[#6D28D9] shadow-[0_0_0_2px_rgba(124,58,237,0.1)]' : 'border-[#E5E7EB] shadow-sm'} rounded-[12px] overflow-hidden transition-all duration-300`}>
                <button 
                  onClick={() => setOpenStep(1)}
                  className="w-full flex items-center justify-between p-5 bg-white text-left"
                >
                  <div className="flex items-start gap-4">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                      <Database className="w-[20px] h-[20px] text-[#6D28D9]" />
                    </div>
                    <div>
                      <h3 className="text-[16px] font-[800] text-[#1A1A2E]">Passo 1: Fonte de Dados</h3>
                      <p className="text-[13px] text-[#6B7280] mt-0.5">De onde as informações serão extraídas?</p>
                    </div>
                  </div>
                  {openStep === 1 ? <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" /> : <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />}
                </button>

                <div className={`transition-all duration-300 ease-in-out ${openStep === 1 ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0 overflow-hidden'}`}>
                  <div className="p-6 pt-2 flex flex-col gap-8">
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                      {fontesDadosInfo.map((fonte) => (
                        <button
                          key={fonte.id}
                          onClick={() => setFonteSelecionada(fonte.id)}
                          className={`flex items-center gap-3 p-4 rounded-[12px] border ${fonteSelecionada === fonte.id ? 'border-[#6D28D9] bg-[#F9F5FF]' : 'border-[#E5E7EB] bg-white hover:border-[#D1D5DB]'} transition-colors text-left`}
                        >
                          <div className={`w-[40px] h-[40px] rounded-full ${fonte.bg} flex items-center justify-center shrink-0`}>
                            <fonte.icon className={`w-[20px] h-[20px] ${fonte.color}`} strokeWidth={2} />
                          </div>
                          <span className="text-[14px] font-[700] text-[#111827]">{fonte.label}</span>
                        </button>
                      ))}
                    </div>

                    <div className="flex flex-col gap-3">
                      <h4 className="text-[14px] font-[700] text-[#111827]">Período de Referência</h4>
                      <div className="flex flex-wrap gap-2">
                        {periodosData.map((p) => (
                          <button
                            key={p.id}
                            onClick={() => setPeriodo(p.id)}
                            className={`px-4 py-2 rounded-[8px] border text-[13px] font-[600] transition-colors ${periodo === p.id ? 'border-[#6D28D9] bg-white text-[#6D28D9] shadow-sm' : 'border-[#E5E7EB] bg-white text-[#6B7280] hover:bg-[#F9FAFB]'}`}
                          >
                            {p.label}
                          </button>
                        ))}
                      </div>

                      {periodo === "personalizado" && (
                        <div className="flex items-center gap-4 mt-2 p-4 bg-[#F9FAFB] rounded-[10px] border border-[#E5E7EB] animate-in fade-in slide-in-from-top-2 duration-300">
                          <div className="flex flex-col gap-[6px] flex-1 max-w-[200px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Data Inicial</label>
                            <input 
                              type="date" 
                              value={dataInicial}
                              onChange={(e) => setDataInicial(e.target.value)}
                              className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" 
                            />
                          </div>
                          <span className="text-[#9CA3AF] mt-[24px]">até</span>
                          <div className="flex flex-col gap-[6px] flex-1 max-w-[200px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Data Final</label>
                            <input 
                              type="date" 
                              value={dataFinal}
                              onChange={(e) => setDataFinal(e.target.value)}
                              className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" 
                            />
                          </div>
                        </div>
                      )}
                    </div>

                    <div className="flex justify-end pt-4 border-t border-[#F1F1F4]">
                      <button onClick={() => setOpenStep(2)} className="bg-[#A78BFA] hover:bg-[#8B5CF6] transition-colors text-white px-6 py-2.5 rounded-[8px] text-[13px] font-[700] shadow-sm">
                        Continuar para Colunas
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* PASSO 2: Colunas */}
              <div className={`bg-white border ${openStep === 2 ? 'border-[#6D28D9] shadow-[0_0_0_2px_rgba(124,58,237,0.1)]' : 'border-[#E5E7EB] shadow-sm'} rounded-[12px] overflow-hidden transition-all duration-300`}>
                <button 
                  onClick={() => setOpenStep(2)}
                  className="w-full flex items-center justify-between p-5 bg-white text-left"
                >
                  <div className="flex items-start gap-4">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                      <Columns className="w-[20px] h-[20px] text-[#6D28D9]" />
                    </div>
                    <div>
                      <h3 className="text-[16px] font-[800] text-[#1A1A2E]">Passo 2: Colunas para exportar</h3>
                      <p className="text-[13px] text-[#6B7280] mt-0.5">Selecione e ordene os campos que deseja incluir no seu relatório.</p>
                    </div>
                  </div>
                  {openStep === 2 ? <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" /> : <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />}
                </button>

                <div className={`transition-all duration-300 ease-in-out ${openStep === 2 ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0 overflow-hidden'}`}>
                  <div className="p-6 pt-2 flex flex-col gap-6">
                    
                    <div className="flex items-center gap-4">
                      <div className="relative flex-1 max-w-[300px]">
                        <input 
                          type="text" 
                          placeholder="Buscar campo..." 
                          value={buscaColuna}
                          onChange={(e) => setBuscaColuna(e.target.value)}
                          className="w-full h-[40px] border border-[#E5E7EB] rounded-[8px] pl-10 pr-3 text-[13px] outline-none focus:border-[#6D28D9]" 
                        />
                        <Search className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                      </div>
                      <button onClick={handleSelectAll} className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB]">
                        <CheckSquare className="w-[16px] h-[16px] text-[#6D28D9]" />
                        Selecionar todos
                      </button>
                      <button onClick={handleClearColumns} className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB]">
                        <Square className="w-[16px] h-[16px] text-[#9CA3AF]" />
                        Desmarcar todos
                      </button>
                    </div>

                    <div className="grid grid-cols-12 gap-6 h-[400px]">
                      
                      {/* Categoria Filters */}
                      <div className="col-span-3 flex flex-col gap-2">
                        <h4 className="text-[14px] font-[800] text-[#1A1A2E] mb-2">Categorias</h4>
                        {categoriasUnicas.map((cat, idx) => (
                          <button 
                            key={idx}
                            onClick={() => setCategoriaColunaFilter(cat)}
                            className={`flex items-center justify-between p-3 rounded-[8px] font-[600] text-[13px] transition-colors ${categoriaColunaFilter === cat ? 'bg-[#F9F5FF] text-[#6D28D9] font-[700]' : 'hover:bg-[#F9FAFB] text-[#4B5563]'}`}
                          >
                            {cat} 
                          </button>
                        ))}
                      </div>

                      {/* Campos Disponíveis */}
                      <div className="col-span-5 flex flex-col gap-3 border-l border-r border-[#F1F1F4] px-6 overflow-y-auto custom-scrollbar">
                        <h4 className="text-[14px] font-[800] text-[#6D28D9] mb-2">Campos disponíveis ({colunasFiltradas.length})</h4>
                        
                        {colunasFiltradas.map((col: any) => {
                          const isSelected = colunasSelecionadas.some(c => c.id === col.id);
                          return (
                            <div 
                              key={col.id} 
                              onClick={() => handleToggleColumn(col)}
                              className={`flex items-center justify-between p-3 rounded-[8px] border cursor-pointer transition-colors ${isSelected ? 'border-[#6D28D9] bg-[#F9F5FF]' : 'border-[#E5E7EB] bg-white hover:border-[#D1D5DB]'}`}
                            >
                              <div className="flex items-center gap-3">
                                <div className={`w-[20px] h-[20px] rounded-[4px] border flex items-center justify-center transition-colors ${isSelected ? 'bg-[#6D28D9] border-[#6D28D9]' : 'border-[#D1D5DB] bg-white'}`}>
                                  {isSelected && <CheckSquare className="w-[14px] h-[14px] text-white" />}
                                </div>
                                <div className="flex flex-col">
                                  <span className={`text-[13px] font-[700] ${isSelected ? 'text-[#6D28D9]' : 'text-[#111827]'}`}>{col.label}</span>
                                </div>
                              </div>
                              <span className={`text-[10px] font-[700] px-2 py-1 rounded-full bg-opacity-10 ${col.color.replace('text-', 'bg-')} ${col.color}`}>
                                {col.cat}
                              </span>
                            </div>
                          );
                        })}
                      </div>

                      {/* Colunas Selecionadas (Dropzone) */}
                      <div className="col-span-4 flex flex-col gap-3 pl-2 overflow-y-auto custom-scrollbar">
                        <div className="flex items-center justify-between mb-2">
                          <h4 className="text-[14px] font-[800] text-[#6D28D9]">Colunas selecionadas ({colunasSelecionadas.length})</h4>
                          <button onClick={handleClearColumns} className="text-[11px] font-[700] text-[#EF4444] flex items-center gap-1 hover:bg-[#FEF2F2] px-2 py-1 rounded-[4px]">
                            Limpar
                          </button>
                        </div>
                        <p className="text-[11px] text-[#6B7280] flex items-center gap-1 mb-2">
                          <GripVertical className="w-[12px] h-[12px]" /> Arraste para reordenar
                        </p>

                        {colunasSelecionadas.map((col, index) => (
                          <div 
                            key={col.id} 
                            draggable
                            onDragStart={(e) => handleDragStart(e, index)}
                            onDragOver={(e) => handleDragOver(e, index)}
                            onDragEnd={handleDragEnd}
                            className={`flex items-center gap-3 p-2 rounded-[8px] border bg-white group cursor-move transition-transform ${draggedItemIndex === index ? 'border-[#6D28D9] shadow-lg opacity-80 scale-[1.02] z-10' : 'border-[#E5E7EB] hover:border-[#D1D5DB]'}`}
                          >
                            <GripVertical className="w-[14px] h-[14px] text-[#D1D5DB] group-hover:text-[#9CA3AF]" />
                            <div className="w-[20px] h-[20px] rounded-full bg-[#F3E8FF] text-[#6D28D9] flex items-center justify-center text-[10px] font-[800] shrink-0">
                              {index + 1}
                            </div>
                            <span className="text-[12px] font-[700] text-[#111827] flex-1 truncate">{col.label}</span>
                            <button onClick={() => handleToggleColumn(col)} className="text-[#9CA3AF] hover:text-[#EF4444] p-1">
                              <X className="w-[14px] h-[14px]" />
                            </button>
                          </div>
                        ))}

                        {colunasSelecionadas.length === 0 && (
                          <div className="flex items-center justify-center h-full border border-dashed border-[#D1D5DB] rounded-[8px] p-6 text-center">
                            <span className="text-[13px] text-[#9CA3AF] font-[600]">Nenhuma coluna selecionada. Clique nos campos ao lado.</span>
                          </div>
                        )}

                        {colunasSelecionadas.length > 0 && (
                          <div className="mt-auto p-3 rounded-[8px] bg-[#F9F5FF] text-center border border-[#E9D5FF]">
                            <span className="text-[13px] font-[700] text-[#6D28D9]">Total de colunas selecionadas: {colunasSelecionadas.length}</span>
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="flex justify-end pt-4 border-t border-[#F1F1F4]">
                      <button onClick={() => setOpenStep(3)} className="bg-[#A78BFA] hover:bg-[#8B5CF6] transition-colors text-white px-6 py-2.5 rounded-[8px] text-[13px] font-[700] shadow-sm">
                        Continuar para Filtros
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* PASSO 3: Filtros */}
              <div className={`bg-white border ${openStep === 3 ? 'border-[#6D28D9] shadow-[0_0_0_2px_rgba(124,58,237,0.1)]' : 'border-[#E5E7EB] shadow-sm'} rounded-[12px] overflow-hidden transition-all duration-300`}>
                <button 
                  onClick={() => setOpenStep(3)}
                  className="w-full flex items-center justify-between p-5 bg-white text-left"
                >
                  <div className="flex items-start gap-4">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                      <Filter className="w-[20px] h-[20px] text-[#6D28D9]" />
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <h3 className="text-[16px] font-[800] text-[#1A1A2E]">Passo 3: Filtros (Opcional)</h3>
                      </div>
                      <p className="text-[13px] text-[#6B7280] mt-0.5">Adicione regras para exportar apenas os dados que você precisa.</p>
                    </div>
                  </div>
                  {openStep === 3 && (
                     <div className="mr-4">
                       <button onClick={(e) => { e.stopPropagation(); handleAddFiltro(); }} className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[6px] text-[12px] font-[600] hover:bg-[#F9FAFB] bg-white z-10 relative">
                         <Plus className="w-[14px] h-[14px]" /> Adicionar Regra
                       </button>
                     </div>
                  )}
                  {openStep === 3 ? <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" /> : <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />}
                </button>

                <div className={`transition-all duration-300 ease-in-out ${openStep === 3 ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0 overflow-hidden'}`}>
                  <div className="p-6 pt-2 flex flex-col gap-6">
                    
                    {filtros.length === 0 ? (
                      <div className="bg-[#F9FAFB] border border-[#E5E7EB] border-dashed rounded-[12px] py-16 flex flex-col items-center justify-center text-center">
                        <Filter className="w-[32px] h-[32px] text-[#D1D5DB] mb-3" />
                        <h4 className="text-[15px] font-[800] text-[#1A1A2E] mb-1">Sem filtros aplicados</h4>
                        <p className="text-[13px] text-[#6B7280] max-w-[400px] mb-4">
                          Se você não adicionar nenhuma regra, o sistema irá exportar todos os dados cadastrados neste módulo.
                        </p>
                        <button onClick={handleAddFiltro} className="px-4 py-2 border border-[#E5E7EB] bg-white rounded-[8px] text-[#6D28D9] text-[13px] font-[700] hover:bg-[#F9FAFB] shadow-sm flex items-center gap-2">
                          <Plus className="w-[14px] h-[14px]" /> Adicionar minha primeira regra
                        </button>
                      </div>
                    ) : (
                      <div className="flex flex-col gap-3">
                        {filtros.map((filtro, index) => (
                          <div key={filtro.id} className="flex items-center gap-3 p-3 bg-white border border-[#E5E7EB] rounded-[8px] shadow-sm animate-in fade-in slide-in-from-top-2">
                            
                            <div className="w-[80px]">
                              <CustomSelect 
                                value={filtro.logico}
                                onChange={(val) => handleUpdateFiltro(filtro.id, 'logico', val)}
                                placeholder="E/OU"
                                searchable={false}
                                options={[
                                  { value: "E", label: "E" },
                                  { value: "OU", label: "OU" }
                                ]}
                                className="h-[38px]"
                              />
                            </div>
                            
                            <div className="w-[250px]">
                              <CustomSelect 
                                value={filtro.campo}
                                onChange={(val) => handleUpdateFiltro(filtro.id, 'campo', val)}
                                placeholder="Campo"
                                searchable={false}
                                options={colunasOptions}
                                className="h-[38px]"
                              />
                            </div>

                            <div className="w-[180px]">
                              <CustomSelect 
                                value={filtro.operador}
                                onChange={(val) => handleUpdateFiltro(filtro.id, 'operador', val)}
                                placeholder="Condição"
                                searchable={false}
                                options={operadoresOptions}
                                className="h-[38px]"
                              />
                            </div>

                            <div className="flex-1">
                              <input 
                                type="text" 
                                value={filtro.valor}
                                onChange={(e) => handleUpdateFiltro(filtro.id, 'valor', e.target.value)}
                                placeholder="Digite o valor..." 
                                className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9] transition-all bg-white"
                              />
                            </div>

                            <button onClick={() => handleRemoveFiltro(filtro.id)} className="w-[38px] h-[38px] flex items-center justify-center rounded-[8px] border border-[#E5E7EB] text-[#9CA3AF] hover:text-[#EF4444] hover:bg-[#FEF2F2] hover:border-[#FCA5A5] transition-colors shrink-0">
                              <Trash2 className="w-[16px] h-[16px]" />
                            </button>
                          </div>
                        ))}
                      </div>
                    )}

                    <div className="flex justify-end pt-4 border-t border-[#F1F1F4]">
                      <button onClick={() => setOpenStep(4)} className="bg-[#A78BFA] hover:bg-[#8B5CF6] transition-colors text-white px-6 py-2.5 rounded-[8px] text-[13px] font-[700] shadow-sm">
                        Continuar para Formato
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* PASSO 4: Formato */}
              <div className={`bg-white border ${openStep === 4 ? 'border-[#6D28D9] shadow-[0_0_0_2px_rgba(124,58,237,0.1)]' : 'border-[#E5E7EB] shadow-sm'} rounded-[12px] overflow-hidden transition-all duration-300`}>
                <button 
                  onClick={() => setOpenStep(4)}
                  className="w-full flex items-center justify-between p-5 bg-white text-left"
                >
                  <div className="flex items-start gap-4">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                      <FileSpreadsheet className="w-[20px] h-[20px] text-[#6D28D9]" />
                    </div>
                    <div>
                      <h3 className="text-[16px] font-[800] text-[#1A1A2E]">Passo 4: Formato de Exportação</h3>
                      <p className="text-[13px] text-[#6B7280] mt-0.5">Como você deseja receber este relatório?</p>
                    </div>
                  </div>
                  {openStep === 4 ? <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" /> : <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />}
                </button>

                <div className={`transition-all duration-300 ease-in-out ${openStep === 4 ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0 overflow-hidden'}`}>
                  <div className="p-6 pt-2 flex flex-col gap-6">
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <button onClick={() => setFormato("web")} className={`flex flex-col items-center justify-center p-8 rounded-[12px] border transition-all ${formato === "web" ? 'border-[#6D28D9] bg-[#F9F5FF]' : 'border-[#E5E7EB] bg-white hover:border-[#D1D5DB]'}`}>
                        <LayoutGrid className={`w-[32px] h-[32px] mb-4 ${formato === "web" ? 'text-[#6D28D9]' : 'text-[#6D28D9]'}`} />
                        <span className="text-[14px] font-[800] text-[#1A1A2E] mb-1">Visualizador Web</span>
                        <span className="text-[12px] text-[#6B7280]">Ver o relatório na tela</span>
                      </button>
                      <button onClick={() => setFormato("pdf")} className={`flex flex-col items-center justify-center p-8 rounded-[12px] border transition-all ${formato === "pdf" ? 'border-[#6D28D9] bg-[#F9F5FF]' : 'border-[#E5E7EB] bg-white hover:border-[#D1D5DB]'}`}>
                        <FileText className={`w-[32px] h-[32px] mb-4 ${formato === "pdf" ? 'text-[#6D28D9]' : 'text-[#EF4444]'}`} />
                        <span className="text-[14px] font-[800] text-[#1A1A2E] mb-1">Documento PDF</span>
                        <span className="text-[12px] text-[#6B7280]">Melhor para impressão e leitura</span>
                      </button>
                      <button onClick={() => setFormato("excel")} className={`flex flex-col items-center justify-center p-8 rounded-[12px] border transition-all ${formato === "excel" ? 'border-[#6D28D9] bg-[#F9F5FF]' : 'border-[#E5E7EB] bg-white hover:border-[#D1D5DB]'}`}>
                        <FileSpreadsheet className={`w-[32px] h-[32px] mb-4 ${formato === "excel" ? 'text-[#6D28D9]' : 'text-[#10B981]'}`} />
                        <span className="text-[14px] font-[800] text-[#1A1A2E] mb-1">Planilha Excel (.xlsx)</span>
                        <span className={`text-[12px] ${formato === "excel" ? 'text-[#6D28D9]' : 'text-[#6B7280]'}`}>Melhor para análise de dados</span>
                      </button>
                    </div>

                  </div>
                </div>
              </div>

            </div>
          </div>
          
          {/* BARRA FIXA DE AÇÃO FINAL */}
          <div className="h-[80px] shrink-0 bg-white border-t border-[#E5E7EB] px-6 flex items-center justify-end shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
            <button onClick={handleGerarRelatorio} className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-8 py-3 rounded-[8px] text-[14px] font-[800] uppercase tracking-wider shadow-sm flex items-center gap-2">
              Gerar Relatório
            </button>
          </div>

        </main>
      </div>
    </div>
  );
}
