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
  CloudUpload,
  Info,
  Paperclip,
  Building2,
  FileText,
  Briefcase,
  MapPin,
  Users,
  Settings,
  Plus,
  Trash2
} from "lucide-react";
import Link from "next/link";

export default function EditarFornecedorPage({ params: paramsPromise }: { params: Promise<{ id: string }> }) {
  const { t } = useTranslation();
  const params = React.use(paramsPromise);

  // Estados dos Accordions (Exclusividade Mútua)
  const [openAccordion, setOpenAccordion] = useState<string | null>("basico");

  // --- ESTADOS: INFORMAÇÕES BÁSICAS ---
  const [tipoPessoa, setTipoPessoa] = useState("PJ");
  const [nomeFantasia, setNomeFantasia] = useState("Imobiliária Souza Ltda");
  const [razaoSocial, setRazaoSocial] = useState("Imobiliária Souza e Carvalho Ltda");
  const [cpfCnpj, setCpfCnpj] = useState("12.345.678/0001-90");
  const [inscEstadual, setInscEstadual] = useState("");
  const [inscMunicipal, setInscMunicipal] = useState("");
  const [tipoFornecedor, setTipoFornecedor] = useState("servico");
  const [categoria, setCategoria] = useState("");
  const [observacao, setObservacao] = useState("");

  // --- ESTADOS: DADOS FISCAIS E FINANCEIROS ---
  const [regimeTributario, setRegimeTributario] = useState("simples");
  const [banco, setBanco] = useState("033 - Santander");
  const [agencia, setAgencia] = useState("1234");
  const [conta, setConta] = useState("56789-0");
  const [tipoConta, setTipoConta] = useState("corrente");
  const [chavePix, setChavePix] = useState("12345678000190");

  // --- ESTADOS: CONDIÇÕES COMERCIAIS ---
  const [prazoPagamento, setPrazoPagamento] = useState("15");
  const [formaPagamento, setFormaPagamento] = useState("pix");
  const [limiteCredito, setLimiteCredito] = useState("15.000,00");
  const [descontoPadrao, setDescontoPadrao] = useState("0,0%");

  // --- ESTADOS: ENDEREÇO ---
  const [cep, setCep] = useState("04567-890");
  const [rua, setRua] = useState("Rua Fictícia");
  const [numero, setNumero] = useState("123");
  const [complemento, setComplemento] = useState("Sala 4");
  const [bairro, setBairro] = useState("Centro");
  const [cidade, setCidade] = useState("São Paulo");
  const [uf, setUf] = useState("SP");

  // --- ESTADOS: CONTATOS MÚLTIPLOS ---
  const [contatos, setContatos] = useState([
    { id: 1, nome: "João", email: "joao@imobilsouza.com.br", telefone: "(11) 98765-4321", tipo: "Comercial" }
  ]);

  const addContato = () => {
    setContatos([...contatos, { id: Date.now(), nome: "", email: "", telefone: "", tipo: "Comercial" }]);
  };
  const removeContato = (id: number) => {
    setContatos(contatos.filter(c => c.id !== id));
  };
  const updateContato = (id: number, field: string, value: string) => {
    setContatos(contatos.map(c => c.id === id ? { ...c, [field]: value } : c));
  };

  // --- ESTADOS: OPERACIONAL E CONFIGURAÇÕES ---
  const [status, setStatus] = useState("Ativo");
  const [responsavel, setResponsavel] = useState("1");
  const [dataInicio, setDataInicio] = useState("");
  const [site, setSite] = useState("www.imobilsouza.com.br");
  const [tags, setTags] = useState("");

  // --- OPTIONS MOCK ---
  const tipoPessoaOptions = [
    { value: "PJ", label: "Pessoa Jurídica" },
    { value: "PF", label: "Pessoa Física" }
  ];
  const tipoFornecedorOptions = [
    { value: "produtos_geral", label: "Fornecedor de Produtos em Geral" },
    { value: "servicos_terceirizados", label: "Prestador de Serviços Terceirizados" },
    { value: "concessionaria", label: "Concessionária (Água, Luz, Internet)" },
    { value: "profissional_autonomo", label: "Profissional Autônomo" },
    { value: "locador", label: "Locador de Imóveis/Equipamentos" },
    { value: "assessoria", label: "Assessoria / Consultoria" },
    { value: "orgao_publico", label: "Órgão Público / Impostos" },
    { value: "ambos", label: "Misto (Produtos e Serviços)" }
  ];
  const regimeOptions = [
    { value: "simples", label: "Simples Nacional" },
    { value: "lucro_presumido", label: "Lucro Presumido" },
    { value: "lucro_real", label: "Lucro Real" },
    { value: "mei", label: "MEI" }
  ];
  const tipoContaOptions = [
    { value: "corrente", label: "Conta Corrente" },
    { value: "poupanca", label: "Conta Poupança" }
  ];
  const prazoPagamentoOptions = [
    { value: "vista", label: "À vista" },
    { value: "15", label: "15 dias" },
    { value: "30", label: "30 dias" },
    { value: "60", label: "60 dias" }
  ];
  const formaPagamentoOptions = [
    { value: "pix", label: "PIX" },
    { value: "boleto", label: "Boleto" },
    { value: "transferencia", label: "Transferência" }
  ];
  const tipoContatoOptions = [
    { value: "Financeiro", label: "Financeiro / Cobrança" },
    { value: "Comercial", label: "Comercial / Vendas" },
    { value: "Suporte", label: "Suporte Técnico" },
    { value: "Administrativo", label: "Administrativo" },
    { value: "Gerencia", label: "Diretor / Gerente" },
    { value: "Atendimento", label: "Atendimento ao Cliente" },
    { value: "Juridico", label: "Jurídico" },
    { value: "Logistica", label: "Operacional / Logística" },
    { value: "Representante", label: "Representante" },
    { value: "Socio", label: "Proprietário / Sócio" },
    { value: "Outros", label: "Outros" }
  ];
  const statusOptions = [
    { value: "Ativo", label: "Ativo" },
    { value: "Inativo", label: "Inativo" },
    { value: "Bloqueado", label: "Bloqueado" }
  ];
  const ufOptions = [
    { value: "PR", label: "PR" },
    { value: "SP", label: "SP" },
    { value: "SC", label: "SC" }
  ];

  // Máscara Dinâmica CPF/CNPJ
  const handleCpfCnpjChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let v = e.target.value.replace(/\D/g, "");
    if (tipoPessoa === "PF") {
      if (v.length > 11) v = v.slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    } else {
      if (v.length > 14) v = v.slice(0, 14);
      v = v.replace(/^(\d{2})(\d)/, "$1.$2");
      v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
      v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
      v = v.replace(/(\d{4})(\d)/, "$1-$2");
    }
    setCpfCnpj(v);
  };

  // Acionador do Accordion
  const toggle = (section: string) => {
    setOpenAccordion(openAccordion === section ? null : section);
  };

  // Helper de Label com Estética Church OS
  const Label = ({ text, required }: { text: string, required?: boolean }) => (
    <label className="text-[12px] font-[600] text-[#374151]">
      {t(text)} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
    </label>
  );

  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href={`/fornecedores/${params.id}`} className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#FEF3C7] flex items-center justify-center shrink-0">
              <Building2 className="w-[20px] h-[20px] text-[#F59E0B]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Editar Fornecedor</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Altere as informações cadastrais do fornecedor.</p>
            </div>
          </div>

          {/* FORMULÁRIO GIGANTE */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* 1. INFORMAÇÕES BÁSICAS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("basico")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "basico" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Info className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">1. Informações do Fornecedor</span>
                  </div>
                  {openAccordion === "basico" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "basico" ? "max-h-[1500px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Tipo de Pessoa" required />
                        <CustomSelect value={tipoPessoa} onChange={setTipoPessoa} searchable={false} options={tipoPessoaOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text={tipoPessoa === "PF" ? "CPF" : "CNPJ"} required />
                        <input type="text" value={cpfCnpj} onChange={handleCpfCnpjChange} placeholder={tipoPessoa === "PF" ? "000.000.000-00" : "00.000.000/0000-00"} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px] col-span-2">
                        <Label text={tipoPessoa === "PF" ? "Nome Completo" : "Razão Social"} required />
                        <input type="text" value={razaoSocial} onChange={(e) => setRazaoSocial(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>

                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px] col-span-2">
                        <Label text="Nome Fantasia / Apelido" required />
                        <input type="text" value={nomeFantasia} onChange={(e) => setNomeFantasia(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Inscrição Estadual" />
                        <input type="text" value={inscEstadual} onChange={(e) => setInscEstadual(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Inscrição Municipal" />
                        <input type="text" value={inscMunicipal} onChange={(e) => setInscMunicipal(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Tipo de Fornecedor" required />
                        <CustomSelect value={tipoFornecedor} onChange={setTipoFornecedor} searchable={true} options={tipoFornecedorOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Categoria Padrão" />
                        <CustomSelect value={categoria} onChange={setCategoria} searchable={true} options={[{value:"1", label:"Manutenção"}, {value:"2", label:"Limpeza"}]} className="h-[38px]" />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[8px]">
                      <Label text="Observações Gerais" />
                      <textarea rows={3} value={observacao} onChange={(e) => setObservacao(e.target.value)} className="w-full border border-[#E5E7EB] rounded-[8px] p-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white resize-none" />
                    </div>

                  </div>
                </div>
              </div>

              {/* 2. DADOS FISCAIS E FINANCEIROS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("fiscal")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "fiscal" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <FileText className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">2. Dados Fiscais e Financeiros</span>
                  </div>
                  {openAccordion === "fiscal" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "fiscal" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Regime Tributário" />
                        <CustomSelect value={regimeTributario} onChange={setRegimeTributario} searchable={false} options={regimeOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Chave PIX" />
                        <input type="text" value={chavePix} onChange={(e) => setChavePix(e.target.value)} placeholder="E-mail, CPF, CNPJ ou Celular" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px] col-span-2">
                        <Label text="Banco" />
                        <input type="text" value={banco} onChange={(e) => setBanco(e.target.value)} placeholder="Ex: 033 - Santander" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>
                    <div className="grid grid-cols-3 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Agência" />
                        <input type="text" value={agencia} onChange={(e) => setAgencia(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Conta" />
                        <input type="text" value={conta} onChange={(e) => setConta(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Tipo de Conta" />
                        <CustomSelect value={tipoConta} onChange={setTipoConta} searchable={false} options={tipoContaOptions} className="h-[38px]" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* 3. CONDIÇÕES COMERCIAIS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("comercial")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "comercial" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Briefcase className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">3. Condições Comerciais</span>
                  </div>
                  {openAccordion === "comercial" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "comercial" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Prazo de Pagamento Padrão" />
                        <CustomSelect value={prazoPagamento} onChange={setPrazoPagamento} searchable={false} options={prazoPagamentoOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Forma de Pgto Preferida" />
                        <CustomSelect value={formaPagamento} onChange={setFormaPagamento} searchable={false} options={formaPagamentoOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Limite de Crédito (R$)" />
                        <input type="text" value={limiteCredito} onChange={(e) => setLimiteCredito(e.target.value)} placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="% Desconto Padrão" />
                        <input type="text" value={descontoPadrao} onChange={(e) => setDescontoPadrao(e.target.value)} placeholder="0,0%" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* 4. ENDEREÇO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("endereco")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "endereco" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <MapPin className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">4. Endereço</span>
                  </div>
                  {openAccordion === "endereco" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "endereco" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="CEP" />
                        <input type="text" value={cep} onChange={(e) => setCep(e.target.value)} placeholder="00000-000" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px] col-span-2">
                        <Label text="Rua / Logradouro" />
                        <input type="text" value={rua} onChange={(e) => setRua(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Número" />
                        <input type="text" value={numero} onChange={(e) => setNumero(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Complemento" />
                        <input type="text" value={complemento} onChange={(e) => setComplemento(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Bairro" />
                        <input type="text" value={bairro} onChange={(e) => setBairro(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Cidade" />
                        <input type="text" value={cidade} onChange={(e) => setCidade(e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="UF" />
                        <CustomSelect value={uf} onChange={setUf} searchable={false} options={ufOptions} className="h-[38px]" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* 5. CONTATOS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("contato")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "contato" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Users className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">5. Contatos ({contatos.length})</span>
                  </div>
                  {openAccordion === "contato" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "contato" ? "max-h-[1500px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    {contatos.map((contato, index) => (
                      <div key={contato.id} className="p-[16px] border border-[#E5E7EB] rounded-[12px] bg-[#F9FAFB] relative">
                        {contatos.length > 1 && (
                          <button onClick={() => removeContato(contato.id)} className="absolute top-[16px] right-[16px] text-[#9CA3AF] hover:text-[#EF4444] transition-colors">
                            <Trash2 className="w-[16px] h-[16px]" />
                          </button>
                        )}
                        <div className="mb-[16px] pb-[16px] border-b border-[#E5E7EB]">
                          <span className="text-[13px] font-[700] text-[#374151]">Contato {index + 1}</span>
                        </div>
                        <div className="grid grid-cols-4 gap-[24px]">
                          <div className="flex flex-col gap-[8px]">
                            <Label text="Tipo de Contato" />
                            <CustomSelect value={contato.tipo} onChange={(v) => updateContato(contato.id, 'tipo', v)} searchable={true} options={tipoContatoOptions} className="h-[38px] bg-white" />
                          </div>
                          <div className="flex flex-col gap-[8px]">
                            <Label text="Nome do Contato" />
                            <input type="text" value={contato.nome} onChange={(e) => updateContato(contato.id, 'nome', e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                          </div>
                          <div className="flex flex-col gap-[8px]">
                            <Label text="Email" />
                            <input type="email" value={contato.email} onChange={(e) => updateContato(contato.id, 'email', e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                          </div>
                          <div className="flex flex-col gap-[8px]">
                            <Label text="Telefone/WhatsApp" />
                            <input type="text" value={contato.telefone} onChange={(e) => updateContato(contato.id, 'telefone', e.target.value)} className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                          </div>
                        </div>
                      </div>
                    ))}

                    <div>
                      <button type="button" onClick={addContato} className="flex items-center gap-2 text-[13px] font-[600] text-[#6D28D9] hover:text-[#5B21B6] transition-colors px-2 py-1">
                        <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                        ADICIONAR OUTRO CONTATO
                      </button>
                    </div>

                  </div>
                </div>
              </div>

              {/* 6. OPERACIONAL E CONFIGURAÇÕES */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("operacional")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "operacional" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Settings className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">6. Operacional e Configurações</span>
                  </div>
                  {openAccordion === "operacional" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "operacional" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    <div className="grid grid-cols-4 gap-[24px]">
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Status do Cadastro" required />
                        <CustomSelect value={status} onChange={setStatus} searchable={false} options={statusOptions} className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Responsável Interno" />
                        <CustomSelect value={responsavel} onChange={setResponsavel} searchable={true} options={[{value:"1", label:"Vinicius Reinehr"}]} placeholder="Selecione o colaborador..." className="h-[38px]" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Data Início do Relacionamento" />
                        <CustomDatePicker value={dataInicio} onChange={setDataInicio} placeholder="DD/MM/AAAA" />
                      </div>
                      <div className="flex flex-col gap-[8px]">
                        <Label text="Site do Fornecedor" />
                        <input type="text" value={site} onChange={(e) => setSite(e.target.value)} placeholder="www.exemplo.com.br" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                      </div>
                    </div>
                    <div className="flex flex-col gap-[8px]">
                      <Label text="Tags / Etiquetas" />
                      <input type="text" value={tags} onChange={(e) => setTags(e.target.value)} placeholder="Ex: Prioridade, Contrato Anual, Parceiro Premium (separados por vírgula)" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white" />
                    </div>
                  </div>
                </div>
              </div>

              {/* 7. ANEXOS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button type="button" onClick={() => toggle("anexo")} className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "anexo" ? "border-b border-[#E5E7EB]" : ""}`}>
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Paperclip className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">7. Documentos e Anexos</span>
                  </div>
                  {openAccordion === "anexo" ? <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" />}
                </button>
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "anexo" ? "max-h-[500px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px]">
                    <p className="text-[12px] text-[#6B7280] mb-4">Anexe aqui o contrato, certidões negativas (FGTS, Receita Federal), e propostas comerciais.</p>
                    <div className="border border-dashed border-[#D1D5DB] rounded-[10px] p-[32px] flex flex-col items-center justify-center text-center hover:bg-[#F9FAFB] transition-colors cursor-pointer bg-white">
                      <div className="w-[40px] h-[40px] rounded-full bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-center mb-[12px]">
                        <CloudUpload className="w-[20px] h-[20px] text-[#6B7280]" strokeWidth={2} />
                      </div>
                      <span className="text-[13px] font-[600] text-[#374151] mb-[4px]">Clique para fazer o upload</span>
                      <span className="text-[12px] text-[#6B7280]">ou arraste e solte os arquivos aqui</span>
                      <span className="text-[11px] font-[500] text-[#9CA3AF] mt-[12px] uppercase tracking-wider">
                        JPG, PNG ou PDF — Máx. 5 MB
                      </span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-end gap-[12px]">
              <Link href="/pessoas-e-empresas/fornecedores" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
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
