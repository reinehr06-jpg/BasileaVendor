"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import { 
  ChevronUp, 
  ChevronDown, 
  ShoppingCart,
  User,
  Building2,
  Package,
  CreditCard,
  Plus,
  Trash2,
  Download,
  Upload
} from "lucide-react";
import Link from "next/link";

export default function NovaCompraPage() {
  const { t } = useTranslation();

  // Estados dos Accordions
  const [openAccordion, setOpenAccordion] = useState<string | null>("info");
  
  // Dados do formulário
  const [status, setStatus] = useState("Rascunho");
  const [classificacao, setClassificacao] = useState("Material de consumo");
  const [dataSolicitacao, setDataSolicitacao] = useState("");
  const [formaPagamento, setFormaPagamento] = useState("");
  const [maxParcelas, setMaxParcelas] = useState("1");
  
  const [itens, setItens] = useState([
    { id: 1, descricao: "", qtd: 1, valorUnit: "", valorTotal: "" }
  ]);

  const handleImportIA = () => {
    setItens([
      { id: 1, descricao: "Cadeira de Escritório Ergonômica", qtd: 2, valorUnit: "850,00", valorTotal: "1.700,00" },
      { id: 2, descricao: "Mesa de Reunião 6 lugares", qtd: 1, valorUnit: "1.200,00", valorTotal: "1.200,00" },
      { id: 3, descricao: "Projetor Epson Full HD", qtd: 1, valorUnit: "3.500,00", valorTotal: "3.500,00" }
    ]);
  };

  const handleAddItem = () => {
    setItens([...itens, { id: Date.now(), descricao: "", qtd: 1, valorUnit: "", valorTotal: "" }]);
  };

  const handleRemoveItem = (id: number) => {
    setItens(itens.filter(item => item.id !== id));
  };

  const statusOptions = [
    { value: "Rascunho", label: "Rascunho" },
    { value: "Em cotação", label: "Em cotação" },
    { value: "Aguardando aprovação", label: "Aguardando aprovação" },
    { value: "Aprovada", label: "Aprovada" },
    { value: "Pedido enviado", label: "Pedido enviado" },
    { value: "Parcialmente recebida", label: "Parcialmente recebida" },
    { value: "Recebida", label: "Recebida" },
    { value: "Cancelada", label: "Cancelada" },
    { value: "Reprovada", label: "Reprovada" }
  ];

  const classOptions = [
    { value: "Produto para estoque", label: "Produto para estoque" },
    { value: "Material de consumo", label: "Material de consumo" },
    { value: "Serviço", label: "Serviço prestado" },
    { value: "Bem patrimonial", label: "Bem patrimonial" },
    { value: "Manutenção e Reparos", label: "Manutenção e Reparos" },
    { value: "Equipamentos e Eletrônicos", label: "Equipamentos e Eletrônicos" },
    { value: "Material de Escritório", label: "Material de Escritório/Expediente" },
    { value: "Alimentação e Cantina", label: "Alimentação e Cantina" },
    { value: "Eventos e Conferências", label: "Eventos e Conferências" },
    { value: "Assinaturas e Softwares", label: "Assinaturas e Softwares" },
    { value: "Material Didático e Literaturas", label: "Material Didático e Literaturas" },
    { value: "Limpeza e Conservação", label: "Limpeza e Conservação" },
    { value: "Instrumentos Musicais e Áudio", label: "Instrumentos Musicais e Áudio" },
    { value: "Vestuário e Uniformes", label: "Vestuário e Uniformes" }
  ];

  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER TELA DE CADASTRO */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href="/gestao-financeira/compras" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
              <ShoppingCart className="w-[20px] h-[20px] text-[#9333EA]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Nova Solicitação de Compra</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Registre requisições de compra de produtos, serviços ou patrimônios.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: DADOS BÁSICOS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "info" ? null : "info")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "info" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-[#F3F4F6] flex items-center justify-center shrink-0 shadow-sm">
                      <User className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      1. Solicitante e Identificação
                    </span>
                  </div>
                  {openAccordion === "info" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "info" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Número do Pedido
                        </label>
                        <input type="text" placeholder="Gerado automaticamente" disabled className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] bg-[#F9FAFB] text-[#9CA3AF] outline-none" />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data da Solicitação <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomDatePicker 
                          value={dataSolicitacao}
                          onChange={setDataSolicitacao}
                          placeholder="Selecionar data"
                          className="h-[38px] w-full"
                        />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Status da Compra
                        </label>
                        <CustomSelect 
                          options={statusOptions}
                          value={status}
                          onChange={setStatus}
                          placeholder="Selecione..."
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Solicitante (Membro/Funcionário) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect 
                          options={[{label: "Lucas Almeida", value: "1"}, {label: "Ana Silva", value: "2"}]}
                          value=""
                          onChange={() => {}}
                          searchable={true}
                          placeholder="Buscar pessoa..."
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Centro de Custo / Departamento <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect 
                          options={[{label: "Ministério de Louvor", value: "1"}, {label: "Administração", value: "2"}]}
                          value=""
                          onChange={() => {}}
                          searchable={true}
                          placeholder="Buscar departamento..."
                          className="h-[38px]"
                        />
                      </div>

                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: FORNECEDOR E ITENS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "itens" ? null : "itens")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "itens" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-[#F3F4F6] flex items-center justify-center shrink-0 shadow-sm">
                      <Package className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Classificação e Itens da Compra
                    </span>
                  </div>
                  {openAccordion === "itens" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "itens" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Fornecedor Sugerido <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect 
                          options={[{label: "Kalunga", value: "1"}, {label: "Supermercado Extra", value: "2"}]}
                          value=""
                          onChange={() => {}}
                          searchable={true}
                          placeholder="Buscar fornecedor..."
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Classificação da Compra <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect 
                          options={classOptions}
                          value={classificacao}
                          onChange={setClassificacao}
                          placeholder="Selecione..."
                          searchable={true}
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[12px]">
                      <div className="flex items-center justify-between">
                        <label className="text-[12px] font-[600] text-[#374151]">Itens da Solicitação</label>
                        <div className="flex items-center gap-2">
                        </div>
                      </div>
                      <div className="border border-[#E5E7EB] rounded-[8px] overflow-hidden">
                        <table className="w-full text-left">
                          <thead className="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                            <tr>
                              <th className="py-2 px-3 text-[11px] font-[700] text-[#6B7280] uppercase">Descrição do Item / Produto</th>
                              <th className="py-2 px-3 text-[11px] font-[700] text-[#6B7280] uppercase w-[100px]">Qtd</th>
                              <th className="py-2 px-3 text-[11px] font-[700] text-[#6B7280] uppercase w-[150px]">Valor Unitário</th>
                              <th className="py-2 px-3 text-[11px] font-[700] text-[#6B7280] uppercase w-[150px]">Valor Total</th>
                              <th className="py-2 px-3 text-[11px] font-[700] text-[#6B7280] uppercase w-[50px]"></th>
                            </tr>
                          </thead>
                          <tbody>
                            {itens.map((item, index) => (
                              <tr key={item.id} className="border-b border-[#E5E7EB] bg-white transition-all animate-in fade-in duration-300">
                                <td className="p-2"><input type="text" value={item.descricao} onChange={(e) => { const newItens = [...itens]; newItens[index].descricao = e.target.value; setItens(newItens); }} placeholder="Ex: Cadeira de escritório" className="w-full h-[32px] px-2 text-[13px] border border-[#E5E7EB] rounded-[6px] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" /></td>
                                <td className="p-2"><input type="number" value={item.qtd} onChange={(e) => { const newItens = [...itens]; newItens[index].qtd = Number(e.target.value); setItens(newItens); }} placeholder="1" className="w-full h-[32px] px-2 text-[13px] border border-[#E5E7EB] rounded-[6px] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" /></td>
                                <td className="p-2">
                                  <div className="relative">
                                    <span className="absolute left-2 top-1/2 -translate-y-1/2 text-[12px] text-[#9CA3AF]">R$</span>
                                    <input type="text" value={item.valorUnit} onChange={(e) => { const newItens = [...itens]; newItens[index].valorUnit = e.target.value; setItens(newItens); }} placeholder="0,00" className="w-full h-[32px] pl-7 pr-2 text-[13px] border border-[#E5E7EB] rounded-[6px] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                                  </div>
                                </td>
                                <td className="p-2">
                                  <div className="relative">
                                    <span className="absolute left-2 top-1/2 -translate-y-1/2 text-[12px] text-[#9CA3AF]">R$</span>
                                    <input type="text" disabled value={item.valorTotal} placeholder="0,00" className="w-full h-[32px] pl-7 pr-2 text-[13px] border border-[#E5E7EB] rounded-[6px] bg-[#F9FAFB] outline-none font-[700] text-[#10B981]" />
                                  </div>
                                </td>
                                <td className="p-2 text-center">
                                  <button type="button" onClick={() => handleRemoveItem(item.id)} className="p-1.5 text-[#9CA3AF] hover:text-[#DC2626] transition-colors rounded-[6px] hover:bg-[#FEF2F2]"><Trash2 className="w-[14px] h-[14px]" /></button>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                        <div className="p-3 bg-[#F9FAFB] flex items-center justify-between">
                          <button type="button" onClick={handleAddItem} className="flex items-center gap-1.5 text-[12px] font-[600] text-[#6D28D9] hover:text-[#5B21B6] transition-colors"><Plus className="w-[14px] h-[14px]" /> Adicionar Linha</button>
                          <div className="flex items-center gap-3">
                            <span className="text-[12px] font-[600] text-[#6B7280]">Total da Compra:</span>
                            <span className="text-[16px] font-[800] text-[#111827]">R$ {itens.length > 1 ? "6.400,00" : "0,00"}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 3: PAGAMENTO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "pgto" ? null : "pgto")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "pgto" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-[#F3F4F6] flex items-center justify-center shrink-0 shadow-sm">
                      <CreditCard className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      3. Condições de Pagamento e Impostos
                    </span>
                  </div>
                  {openAccordion === "pgto" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "pgto" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Impostos (R$)</label>
                        <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] outline-none focus:border-[#6D28D9]" />
                      </div>
                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Frete (R$)</label>
                        <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] outline-none focus:border-[#6D28D9]" />
                      </div>
                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Descontos (R$)</label>
                        <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Forma de Pagamento</label>
                        <CustomSelect 
                          options={[
                            {label: "Boleto", value: "boleto"}, 
                            {label: "PIX", value: "pix"}, 
                            {label: "Dinheiro Físico", value: "dinheiro"},
                            {label: "Transferência (TED/DOC)", value: "transferencia"},
                            {label: "Cheque", value: "cheque"},
                            {label: "Cartão de Débito", value: "debito"},
                            {label: "Cartão de Crédito", value: "credito"}
                          ]}
                          value={formaPagamento}
                          onChange={setFormaPagamento}
                          placeholder="Selecione..."
                          className="h-[38px]"
                        />
                      </div>
                      
                      {formaPagamento === "credito" && (
                        <div className="col-span-6 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">Máximo de Parcelas Permitidas <span className="text-[#EF4444] ml-0.5">*</span></label>
                          <CustomSelect 
                            options={Array.from({ length: 24 }, (_, i) => ({ value: String(i + 1), label: i === 0 ? "1x (À vista)" : `Até ${i + 1}x` }))}
                            value={maxParcelas}
                            onChange={setMaxParcelas}
                            placeholder="Selecione o limite de parcelas..."
                            className="h-[38px]"
                          />
                        </div>
                      )}
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Conta Bancária / Caixa Destino</label>
                        <CustomSelect 
                          options={[{label: "Itaú - CC 1234", value: "1"}, {label: "Caixa Físico Sede", value: "2"}]}
                          value=""
                          onChange={() => {}}
                          placeholder="De onde sairá o dinheiro?"
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-between">
              <span className="text-[12px] text-[#6B7280]">
                As compras aprovadas irão <strong>gerar automaticamente uma Conta a Pagar</strong>.
              </span>
              <div className="flex items-center gap-[12px]">
                <Link href="/gestao-financeira/compras" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
                  CANCELAR
                </Link>
                <button type="button" className="px-[20px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white text-[12px] font-[700] uppercase tracking-wider rounded-[8px] shadow-sm shadow-[#6D28D9]/20">
                  ENVIAR PARA APROVAÇÃO
                </button>
              </div>
            </div>
          </div>
          
          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[24px] pb-[24px]">
            <p className="text-[12px] font-[500] text-[#9CA3AF]">
              COPYRIGHT © 2026 <span className="font-[700] text-[#6D28D9]">Basiléia</span>, Todos os direitos reservados
            </p>
          </div>
          
        </main>
      </div>
    </div>
  );
}

// Icon helper
function ChevronLeftIcon(props: React.SVGProps<SVGSVGElement>) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="m15 18-6-6 6-6" />
    </svg>
  );
}
