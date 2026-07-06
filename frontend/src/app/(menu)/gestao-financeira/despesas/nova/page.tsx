/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: NOVA DESPESA (Cadastro de Contas a Pagar)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /despesas/nova
 * 📁 ARQUIVO: src/app/despesas/nova/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Formulário completo para registrar uma nova despesa no sistema (contas a pagar,
 *    impostos, aluguéis, compras, etc.).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END (POST /api/despesas):
 *    O payload enviado deve conter todas as informações divididas nos accordions:
 *      1. Valor, Datas e Classificação (obrigatório)
 *      2. Fornecedor e Detalhes (opcional)
 *      3. Parcelamento / Recorrência (se for parcelado/fixo)
 *      4. Anexos e Observações (opcional)
 *
 * 📌 REGRAS DE NEGÓCIO DA TELA:
 *    - Se "Status" for "Pago", a "Data Efetiva de Pagamento" passa a ser obrigatória.
 *    - Se o usuário marcar "Lançamento Parcelado / Recorrente", os campos de recorrência 
 *      devem aparecer e a API deve gerar N registros vinculados.
 *    - O fornecedor pode ser selecionado (já cadastrado) ou pode-se clicar em "Novo" para
 *      criar um fornecedor rapidamente sem sair da tela.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client"; // Componente com muito estado (formulário)

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";       // Menu lateral
import Topbar from "@/components/Topbar";         // Barra superior
import CustomSelect from "@/components/CustomSelect";         // Componente padronizado de Select (UI)
import CustomDatePicker from "@/components/CustomDatePicker"; // Componente padronizado de Calendário (UI)
import { useTranslation } from "react-i18next";
import { 
  ChevronUp, ChevronDown, Info, DollarSign, Tags, User, FileText, UploadCloud, AlertCircle, Layers
} from "lucide-react";
import Link from "next/link"; // Usado para o botão "Voltar" (seta esquerda)

export default function NovaDespesaPage() {
  const { t } = useTranslation();

  // Estados dos Accordions (controla qual seção está aberta - 1 por vez ou múltiplas)
  const [openAccordion, setOpenAccordion] = useState<string | null>("info");
  
  // Estados do Formulário
  const [isParcelada, setIsParcelada] = useState(false);
  const [origemTipo, setOrigemTipo] = useState("");
  const [status, setStatus] = useState("Pendente");

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
            <Link href="/gestao-financeira/despesas" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#FEE2E2] flex items-center justify-center shrink-0">
              <DollarSign className="w-[20px] h-[20px] text-[#DC2626]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Nova Despesa</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Registre contas a pagar, custos fixos e saídas do caixa.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: VALOR, DATAS E CLASSIFICAÇÃO */}
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
                      1. Valor, Datas e Classificação
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
                          Valor Total <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#DC2626] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                        </div>
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Conta Financeira <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="De onde vai sair..."
                          options={[{value: "1", label: "Itaú - CC 1234"}, {value: "2", label: "Caixa Físico"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Categoria Financeira <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Ex: Energia..."
                          searchable={true}
                          options={[{value: "1", label: "Energia"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Centro de Custo / Dep. <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Selecione..."
                          searchable={true}
                          options={[{value: "1", label: "Sede"}]}
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
                          Data Prevista Pgt.
                        </label>
                        <CustomDatePicker value={dataPrevista} onChange={setDataPrevista} placeholder="DD/MM/AAAA" />
                      </div>
                      
                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data Efetiva Pgt.
                        </label>
                        <CustomDatePicker value={dataEfetiva} onChange={setDataEfetiva} placeholder="DD/MM/AAAA" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Descrição da Despesa <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="text" placeholder="Resumo do que foi comprado/gasto..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] transition-all bg-white" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Pedido de Compra Relacionado
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Vincular a um pedido aprovado..."
                          searchable={true}
                          options={[{value: "1", label: "PC-2026/001 - Cadeiras"}]}
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: PARCELAMENTO */}
              <div className={`bg-white border ${isParcelada ? 'border-[#3B82F6] ring-1 ring-[#3B82F6]' : 'border-[#E5E7EB]'} rounded-[12px] overflow-hidden transition-all`}>
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "parcelamento" ? null : "parcelamento")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "parcelamento" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Layers className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Parcelamento
                    </span>
                  </div>
                  <div className="flex items-center gap-[16px]">
                    <label className="flex items-center gap-2 cursor-pointer" onClick={(e) => e.stopPropagation()}>
                      <span className="text-[12px] font-[600] text-[#4B5563]">Esta é uma despesa parcelada?</span>
                      <div className={`w-[44px] h-[24px] rounded-full p-1 transition-colors ${isParcelada ? 'bg-[#3B82F6]' : 'bg-[#D1D5DB]'}`} onClick={() => {setIsParcelada(!isParcelada); if(!isParcelada) setOpenAccordion("parcelamento");}}>
                        <div className={`w-[16px] h-[16px] bg-white rounded-full shadow-sm transition-transform ${isParcelada ? 'translate-x-[20px]' : 'translate-x-0'}`}></div>
                      </div>
                    </label>
                    {openAccordion === "parcelamento" ? (
                      <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                    ) : (
                      <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                    )}
                  </div>
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "parcelamento" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  {isParcelada ? (
                    <div className="p-[24px] flex flex-col gap-[24px]">
                      <div className="grid grid-cols-12 gap-[24px]">
                        <div className="col-span-4 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">Qtd. de Parcelas</label>
                          <CustomSelect
                            value=""
                            onChange={() => {}}
                            placeholder="Ex: 12"
                            options={Array.from({ length: 23 }, (_, i) => ({ value: String(i + 2), label: `${i + 2}x` }))}
                            className="h-[38px]"
                          />
                        </div>
                        <div className="col-span-4 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">Periodicidade</label>
                          <CustomSelect
                            value=""
                            onChange={() => {}}
                            placeholder="Mensal"
                            options={[
                              {value: "diario", label: "Diário"},
                              {value: "semanal", label: "Semanal"},
                              {value: "quinzenal", label: "Quinzenal"},
                              {value: "mensal", label: "Mensal"},
                              {value: "bimestral", label: "Bimestral"},
                              {value: "trimestral", label: "Trimestral"},
                              {value: "semestral", label: "Semestral"},
                              {value: "anual", label: "Anual"}
                            ]}
                            className="h-[38px]"
                          />
                        </div>
                        <div className="col-span-4 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">Valor por Parcela (R$)</label>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                        </div>
                        <div className="col-span-12 flex justify-end">
                          <button className="h-[38px] px-[20px] bg-[#F3F4F6] text-[#374151] font-[700] text-[12px] rounded-[8px] hover:bg-[#E5E7EB] transition-colors border border-[#D1D5DB]">
                            GERAR PREVISÃO DAS PARCELAS
                          </button>
                        </div>
                      </div>
                    </div>
                  ) : (
                    <div className="p-[24px] text-[13px] text-[#6B7280]">
                      Ligue a chave de parcelamento acima para configurar múltiplas parcelas.
                    </div>
                  )}
                </div>
              </div>

              {/* SEÇÃO 3: FAVORECIDO / DESTINO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "fornecedor" ? null : "fornecedor")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "fornecedor" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <User className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      3. Favorecido / Destino do Pagamento
                    </span>
                  </div>
                  {openAccordion === "fornecedor" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "fornecedor" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Tipo de Favorecido <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value={origemTipo}
                          onChange={setOrigemTipo}
                          placeholder="Selecione o tipo..."
                          options={[
                            {value: "fornecedor", label: "Fornecedor / Empresa"},
                            {value: "membro", label: "Membro / Pessoa Física"},
                            {value: "filial", label: "Igreja / Filial"},
                            {value: "ministerio", label: "Ministério"},
                            {value: "outra", label: "Outro Destino (Avulso)"}
                          ]}
                          className="h-[38px]"
                        />
                      </div>

                      {origemTipo !== "outra" && origemTipo !== "" && (
                        <div className="col-span-6 flex flex-col gap-[8px]">
                          <label className="text-[12px] font-[600] text-[#374151]">
                            Selecione o {origemTipo === 'fornecedor' ? 'Fornecedor' : origemTipo} <span className="text-[#EF4444] ml-0.5">*</span>
                          </label>
                          <CustomSelect
                            value=""
                            onChange={() => {}}
                            placeholder={`Buscar cadastrado...`}
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
                            <h3 className="text-[13px] font-[700] text-[#374151]">Preenchimento Manual do Favorecido</h3>
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
                            <label className="text-[12px] font-[600] text-[#374151]">Observação sobre o destino</label>
                            <input type="text" placeholder="Algum detalhe adicional..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                          </div>
                        </div>
                      )}
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 4: DETALHES E ANEXOS */}
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
                          Status da Despesa <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value={status}
                          onChange={setStatus}
                          placeholder="Selecione..."
                          options={[
                            {value: "Rascunho", label: "Rascunho"},
                            {value: "Aguardando aprovação", label: "Aguardando aprovação"},
                            {value: "Agendado", label: "Agendado"},
                            {value: "Em aberto", label: "Em aberto"},
                            {value: "Parcialmente pago", label: "Parcialmente pago"},
                            {value: "Pago", label: "Pago"},
                            {value: "Vencido", label: "Vencido"},
                            {value: "Cancelado", label: "Cancelado"},
                            {value: "Contestação", label: "Contestação"}
                          ]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-12 mt-4 flex items-center gap-[8px] mb-[4px] border-t border-[#F1F1F4] pt-[16px]">
                        <FileText className="w-[16px] h-[16px] text-[#6B7280]" />
                        <h3 className="text-[13px] font-[700] text-[#374151]">Dados da Nota Fiscal (Opcional)</h3>
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Número da NF</label>
                        <input type="text" placeholder="Ex: 123456" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Nome da Nota</label>
                        <input type="text" placeholder="Opcional" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Chave da NF-e</label>
                        <input type="text" placeholder="44 posições numéricas" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Descrição Detalhada / Observações Internas
                        </label>
                        <textarea 
                          placeholder="Ex: Compra de materiais para o evento..." 
                          className="w-full h-[80px] border border-[#E5E7EB] rounded-[8px] p-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9] resize-none"
                        ></textarea>
                      </div>

                      {/* FILE UPLOAD */}
                      <div className="col-span-12 flex flex-col gap-[8px] mt-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Anexos (Boleto, Nota Fiscal, Recibo)
                        </label>
                        <div className="w-full h-[120px] border-2 border-dashed border-[#D1D5DB] rounded-[12px] bg-[#F9FAFB] flex flex-col items-center justify-center cursor-pointer hover:bg-[#F3F4F6] transition-colors group">
                          <UploadCloud className="w-[28px] h-[28px] text-[#9CA3AF] group-hover:text-[#DC2626] transition-colors mb-2" />
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
              <Link href="/gestao-financeira/despesas" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
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
