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
  Info,
  DollarSign,
  Tags,
  User,
  FileText,
  UploadCloud,
  AlertCircle
} from "lucide-react";
import Link from "next/link";

export default function NovaReceitaPage() {
  const { t } = useTranslation();

  // Estados dos Accordions
  const [openAccordion, setOpenAccordion] = useState<string | null>("info");
  
  // Estados do Formulário
  const [origemTipo, setOrigemTipo] = useState("");
  const [status, setStatus] = useState("Pago");

  const [dataEmissao, setDataEmissao] = useState("");
  const [dataVencimento, setDataVencimento] = useState("");
  const [dataPrevista, setDataPrevista] = useState("");
  const [dataEfetiva, setDataEfetiva] = useState("");
  const [formaPagamento, setFormaPagamento] = useState("");
  const [maxParcelas, setMaxParcelas] = useState("1");

  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER TELA DE CADASTRO */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href="/financeiro/receitas" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
              <DollarSign className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Nova Receita</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Preencha os dados do recebimento para registrar no caixa.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: VALOR E CLASSIFICAÇÃO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "info" ? null : "info")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "info" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <DollarSign className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      1. Valor e Classificação
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
                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Valor Previsto
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#6B7280] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] transition-all bg-white" />
                        </div>
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Valor Recebido <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#10B981] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] transition-all bg-white" />
                        </div>
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Conta Financeira <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Selecione a conta..."
                          options={[{value: "1", label: "Itaú - CC 1234"}, {value: "2", label: "Caixa Físico"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Categoria Principal <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Ex: Dízimos"
                          searchable={true}
                          options={[{value: "1", label: "Dízimos"}, {value: "2", label: "Ofertas"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data de Emissão <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomDatePicker value={dataEmissao} onChange={setDataEmissao} placeholder="DD/MM/AAAA" />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data de Vencimento <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomDatePicker value={dataVencimento} onChange={setDataVencimento} placeholder="DD/MM/AAAA" />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data Prev. de Recebimento
                        </label>
                        <CustomDatePicker value={dataPrevista} onChange={setDataPrevista} placeholder="DD/MM/AAAA" />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data Efet. de Recebimento <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomDatePicker value={dataEfetiva} onChange={setDataEfetiva} placeholder="DD/MM/AAAA" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Descrição da Receita <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="text" placeholder="Resumo da entrada..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] transition-all bg-white" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Categoria Secundária
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Opcional"
                          searchable={true}
                          options={[{value: "1", label: "Congregação A"}]}
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: RATEIO E DEPARTAMENTOS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "rateio" ? null : "rateio")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "rateio" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Tags className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Rateio e Departamentos
                    </span>
                  </div>
                  {openAccordion === "rateio" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "rateio" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Igreja ou Filial <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Selecione a filial..."
                          options={[{value: "1", label: "Igreja Sede"}, {value: "2", label: "Filial Zona Sul"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Ministério ou Departamento
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Ex: Jovens, Louvor..."
                          searchable={true}
                          options={[{value: "1", label: "Jovens"}, {value: "2", label: "Infantil"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Centro de Custo
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Selecione..."
                          options={[{value: "1", label: "Administrativo"}, {value: "2", label: "Eventos"}]}
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 3: ORIGEM */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "origem" ? null : "origem")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "origem" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <User className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      3. Origem da Receita
                    </span>
                  </div>
                  {openAccordion === "origem" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "origem" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Tipo de Origem <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value={origemTipo}
                          onChange={setOrigemTipo}
                          placeholder="Selecione o tipo..."
                          options={[
                            {value: "membro", label: "Membro"},
                            {value: "fornecedor", label: "Fornecedor / Empresa"},
                            {value: "filial", label: "Igreja / Filial"},
                            {value: "ministerio", label: "Ministério"},
                            {value: "outra", label: "Outra origem"}
                          ]}
                          className="h-[38px]"
                        />
                      </div>

                      {origemTipo !== "outra" && origemTipo !== "" && (
                        <div className="col-span-6 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">
                            Selecione o {origemTipo} <span className="text-[#EF4444] ml-0.5">*</span>
                          </label>
                          <CustomSelect
                            value=""
                            onChange={() => {}}
                            placeholder={`Buscar ${origemTipo}...`}
                            searchable={true}
                            options={[]}
                            className="h-[38px]"
                          />
                        </div>
                      )}

                      {origemTipo === "outra" && (
                        <div className="col-span-12 grid grid-cols-12 gap-[24px] mt-[8px] p-[20px] bg-[#F9FAFB] rounded-[12px] border border-[#E5E7EB]">
                          <div className="col-span-12 flex items-center gap-[8px] mb-[4px]">
                            <AlertCircle className="w-[16px] h-[16px] text-[#6B7280]" />
                            <h3 className="text-[13px] font-[700] text-[#374151]">Preenchimento Manual da Origem</h3>
                          </div>
                          
                          <div className="col-span-6 flex flex-col gap-[8px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Nome da Pessoa ou Empresa <span className="text-[#EF4444] ml-0.5">*</span></label>
                            <input type="text" placeholder="Ex: João da Silva..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                          </div>

                          <div className="col-span-6 flex flex-col gap-[8px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Documento (CPF/CNPJ)</label>
                            <input type="text" placeholder="000.000.000-00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                          </div>

                          <div className="col-span-6 flex flex-col gap-[8px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Telefone / Contato</label>
                            <input type="text" placeholder="(00) 00000-0000" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                          </div>

                          <div className="col-span-6 flex flex-col gap-[8px]">
                            <label className="text-[12px] font-[600] text-[#374151]">Observação sobre a origem</label>
                            <input type="text" placeholder="Algum detalhe adicional..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                          </div>
                        </div>
                      )}
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 4: PAGAMENTO E ANEXOS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "pgto" ? null : "pgto")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "pgto" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <FileText className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      4. Pagamento e Anexos
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
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Forma de Pagamento <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value={formaPagamento}
                          onChange={setFormaPagamento}
                          placeholder="Selecione..."
                          options={[
                            {label: "Boleto", value: "boleto"}, 
                            {label: "PIX", value: "pix"}, 
                            {label: "Dinheiro Físico", value: "dinheiro"},
                            {label: "Transferência (TED/DOC)", value: "transferencia"},
                            {label: "Cheque", value: "cheque"},
                            {label: "Cartão de Débito", value: "debito"},
                            {label: "Cartão de Crédito", value: "credito"}
                          ]}
                          className="h-[38px]"
                        />
                      </div>

                      {formaPagamento === "credito" && (
                        <div className="col-span-4 flex flex-col gap-[8px]">
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

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Status da Receita <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value={status}
                          onChange={setStatus}
                          placeholder="Selecione..."
                          options={[
                            {value: "Rascunho", label: "Rascunho"},
                            {value: "Aguardando recebimento", label: "Aguardando recebimento"},
                            {value: "Parcialmente recebido", label: "Parcialmente recebido"},
                            {value: "Recebido", label: "Recebido"},
                            {value: "Vencido", label: "Vencido"},
                            {value: "Cancelado", label: "Cancelado"},
                            {value: "Estornado", label: "Estornado"}
                          ]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Nº do Recibo ou Documento
                        </label>
                        <input type="text" placeholder="Opcional" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Descrição Detalhada / Observações Internas
                        </label>
                        <textarea 
                          placeholder="Digite detalhes importantes sobre este recebimento..." 
                          className="w-full h-[80px] border border-[#E5E7EB] rounded-[8px] p-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9] resize-none"
                        ></textarea>
                      </div>

                      {/* FILE UPLOAD */}
                      <div className="col-span-12 flex flex-col gap-[8px] mt-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Anexos (Comprovantes, Notas, Documentos)
                        </label>
                        <div className="w-full h-[120px] border-2 border-dashed border-[#D1D5DB] rounded-[12px] bg-[#F9FAFB] flex flex-col items-center justify-center cursor-pointer hover:bg-[#F3F4F6] transition-colors group">
                          <UploadCloud className="w-[28px] h-[28px] text-[#9CA3AF] group-hover:text-[#10B981] transition-colors mb-2" />
                          <span className="text-[13px] font-[600] text-[#4B5563]">Clique ou arraste arquivos para anexar</span>
                          <span className="text-[11px] text-[#9CA3AF] mt-1">PDF, JPG, PNG (Max 5MB)</span>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-end gap-[12px]">
              <Link href="/financeiro/receitas" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
                CANCELAR
              </Link>
              <button type="button" className="px-[20px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white text-[12px] font-[700] uppercase tracking-wider rounded-[8px] shadow-sm shadow-[#6D28D9]/20">
                SALVAR E CONCLUIR
              </button>
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
