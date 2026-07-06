"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { Users, User, ChevronDown, ChevronUp, Trash2, Calendar, Clock, Save, History, LogOut, AlertCircle } from "lucide-react";
import { useTranslation } from "react-i18next";

// ============================================================
// MAPA DO TESOURO — Células / Cadastro (Novo)
// ============================================================
// PROPÓSITO:
//   Formulário para cadastro de uma nova célula, incluindo
//   informações básicas, endereço, membros, visitantes e liderança.
//
// INPUTS / FORMULÁRIOS:
//   - Descrição (text)
//   - Data de início (text + calendário)
//   - Horário de encontro (text + relógio)
//   - Frequência (CustomSelect)
//   - Perfil (CustomSelect + link "Adicionar perfil!")
//   - Rede (CustomSelect)
//   - País (CustomSelect), CEP, Rua, Número, Complemento, Bairro,
//     Cidade (text), Estado (CustomSelect)
//   - Selecionar membro (CustomSelect searchable)
//   - Selecionar visitante (CustomSelect searchable)
//   - Selecionar líder (CustomSelect searchable) + Função (CustomSelect)
//
// BOTÕES DE AÇÃO:
//   - Toggle sections (Info / Membros / Visitantes / Liderança)
//   - "Adicionar membro" — adiciona membro à lista
//   - "Adicionar visitante" — adiciona visitante à lista
//   - "Registrar novo visitante" — link para cadastro de visitante
//   - "Adicionar novo líder" — adiciona líder com função
//   - Lixeira (Trash2) — remove item das listas
//   - "Cancelar" — volta para listagem (Link)
//   - "Salvar célula" — submete o formulário
//
// COMPORTAMENTOS:
//   - Accordion sections com toggle de visibilidade
//   - Listas dinâmicas com add/remove
//
// INTEGRAÇÃO BACKEND:
//   - A implementar
//
// #pag30 — Células / Cadastro (Novo) TREE.md
// ============================================================

export default function CelulaForm({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const [openSections, setOpenSections] = useState({
    info: true,
    membros: false,
    visitantes: false,
    lideranca: false
  });

  const toggleSection = (section: keyof typeof openSections) => {
    setOpenSections(prev => ({ ...prev, [section]: !prev[section] }));
  };

  const [nome, setNome] = useState("");
  const [isAtivo, setIsAtivo] = useState(true);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");
  const [frequencia, setFrequencia] = useState("");
  const [perfil, setPerfil] = useState("");
  const [rede, setRede] = useState("");
  const [pais, setPais] = useState("");
  const [estado, setEstado] = useState("");
  const [status, setStatus] = useState("ativo");
  const [motivo, setMotivo] = useState("");

  // Arrays de relacionamento
  const [membros, setMembros] = useState<{id: number, nome: string}[]>([
    { id: 1, nome: "Ana Paula Mendes" },
    { id: 2, nome: "Bruno Oliveira" },
    { id: 3, nome: "Carlos Eduardo Silva" },
    { id: 4, nome: "Débora Santos" },
  ]);
  const [selectedMembro, setSelectedMembro] = useState("");

  const [visitantes, setVisitantes] = useState<{id: number, nome: string}[]>([
    { id: 1, nome: "Fernanda Lima" },
    { id: 2, nome: "Gabriel Rocha" },
    { id: 3, nome: "Juliana Martins" },
  ]);
  const [selectedVisitante, setSelectedVisitante] = useState("");

  const [lideres, setLideres] = useState<{id: number, nome: string, funcao: string}[]>([
    { id: 1, nome: "Ricardo Nascimento", funcao: "Líder geral" },
    { id: 2, nome: "Mariana Costa", funcao: "Gestor" },
    { id: 3, nome: "Paulo Henrique", funcao: "Líder auxiliar" },
    { id: 4, nome: "Renata Almeida", funcao: "Anfitrião" },
  ]);
  const [selectedLiderMembro, setSelectedLiderMembro] = useState("");
  const [selectedLiderFuncao, setSelectedLiderFuncao] = useState("");

  // Mocked data load for Edit Mode
  React.useEffect(() => {
    if (editId) {
      setNome("Célula Centro");
      setFrequencia("semanal");
      // Preencher outros estados seria feito aqui com dados reais da API
    }
  }, [editId]);

  // Funções de ação simuladas
  const addMembro = () => {
    if(selectedMembro) {
      setMembros([...membros, { id: Date.now(), nome: "Membro " + selectedMembro }]);
      setSelectedMembro("");
    }
  };

  const addVisitante = () => {
    if(selectedVisitante) {
      setVisitantes([...visitantes, { id: Date.now(), nome: "Visitante " + selectedVisitante }]);
      setSelectedVisitante("");
    }
  };

  const addLider = () => {
    if(selectedLiderMembro && selectedLiderFuncao) {
      setLideres([...lideres, { id: Date.now(), nome: "Membro " + selectedLiderMembro, funcao: selectedLiderFuncao }]);
      setSelectedLiderMembro("");
      setSelectedLiderFuncao("");
    }
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        <Topbar />

        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col max-w-[1200px] w-full mx-auto">

          {/* CABEÇALHO DA PÁGINA */}
          {/* MAPA DO TESOURO: Cabeçalho da página — ícone, título e subtítulo */}
          <div className="flex items-center gap-[14px] py-[16px] pb-[16px]">
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <Users className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar célula") : t("Nova célula")}</h1>
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações da célula.") : t("Cadastre uma nova célula e organize participantes e liderança.")}</p>
            </div>
          </div>

          <div className="flex flex-col gap-[16px]">

            {/* DROPDOWN 1: INFORMAÇÕES DA CÉLULA */}
            {/* MAPA DO TESOURO: Card accordion — Informações da célula */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden transition-all duration-300">
              {/* MAPA DO TESOURO: Botão toggle — expande/recolhe seção Informações */}
              <button 
                onClick={() => toggleSection('info')}
                className="w-full flex items-center justify-between p-[16px_20px] bg-white hover:bg-[#FAFAFC] transition-colors"
              >
                <div className="flex items-center gap-[10px]">
                  <div className="w-[28px] h-[28px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                    <User className="w-[14px] h-[14px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Informações da célula")} <span className="text-[#EF4444]">*</span></h2>
                </div>
                {openSections.info ? (
                  <ChevronUp className="w-[18px] h-[18px] text-[#6B7280]" />
                ) : (
                  <ChevronDown className="w-[18px] h-[18px] text-[#6B7280]" />
                )}
              </button>

              {openSections.info && (
                <div className="p-[20px] pt-0 border-t border-[#F1F1F4] flex flex-col gap-[20px] animate-in fade-in slide-in-from-top-2 duration-300">
                  {/* Linha 1 */}
                  <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px] mt-4">
                    <div className="flex flex-col gap-[6px] group md:col-span-1">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Nome da Célula")} <span className="text-[#EF4444]">*</span></label>
                      {/* MAPA DO TESOURO: Input — Nome da célula */}
                      <input 
                        type="text" 
                        value={nome}
                        onChange={(e) => setNome(e.target.value)}
                        placeholder={t("Digite o nome da célula")} 
                        className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" 
                      />
                    </div>
                    <div className="flex flex-col gap-[6px] group">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Data de inicio")} <span className="text-[#EF4444]">*</span></label>
                      <div className="relative">
                        {/* MAPA DO TESOURO: Input — Data de início */}
                        <input type="text" placeholder={t("dd/mm/aaaa")} className="h-[40px] w-full px-[12px] pr-[36px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                        <Calendar className="absolute right-[12px] top-[12px] w-[16px] h-[16px] text-[#9CA3AF] pointer-events-none" strokeWidth={2} />
                      </div>
                    </div>
                    <div className="flex flex-col gap-[6px] group">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Horário de encontro")} <span className="text-[#EF4444]">*</span></label>
                      <div className="relative">
                        {/* MAPA DO TESOURO: Input — Horário de encontro */}
                        <input type="text" placeholder={t("hh:mm")} className="h-[40px] w-full px-[12px] pr-[36px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                        <Clock className="absolute right-[12px] top-[12px] w-[16px] h-[16px] text-[#9CA3AF] pointer-events-none" strokeWidth={2} />
                      </div>
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Frequência")} <span className="text-[#EF4444]">*</span></label>
                      {/* MAPA DO TESOURO: Select — Frequência */}
                      <CustomSelect value={frequencia} onChange={setFrequencia} placeholder={t("Selecione")} options={[{value: "semanal", label: "Semanal"}]} />
                    </div>
                  </div>

                  {/* Linha 2 (Perfil, Rede, Status, Motivo) */}
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[16px]">
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Perfil")} <span className="text-[#EF4444]">*</span></label>
                      {/* MAPA DO TESOURO: Select — Perfil da célula */}
                      <CustomSelect value={perfil} onChange={setPerfil} placeholder={t("Selecione o perfil")} options={[]} />
                      {/* MAPA DO TESOURO: Link — Adicionar novo perfil */}
                      <button className="text-[12px] text-[#6D28D9] font-[500] hover:underline self-start mt-1">{t("Adicionar perfil!")}</button>
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Rede")}</label>
                      {/* MAPA DO TESOURO: Select — Rede */}
                      <CustomSelect value={rede} onChange={setRede} placeholder={t("Selecione a rede")} options={[]} />
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[12px] font-[600] text-[#4B5563]">{t("Status")} <span className="text-[#EF4444]">*</span></label>
                      <CustomSelect 
                        value={status} 
                        onChange={setStatus} 
                        placeholder={t("Status")} 
                        options={[
                          { value: "ativo", label: "Ativa" },
                          { value: "standby", label: "Em Standby" },
                          { value: "inativo", label: "Inativa" }
                        ]} 
                      />
                    </div>
                    {(status === 'standby' || status === 'inativo') && (
                      <div className="flex flex-col gap-[6px] animate-in fade-in duration-300">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Motivo")} <span className="text-[#EF4444]">*</span></label>
                        <input 
                          type="text" 
                          value={motivo}
                          onChange={(e) => setMotivo(e.target.value)}
                          placeholder={t("Motivo da inatividade")} 
                          className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none transition-all" 
                        />
                      </div>
                    )}
                  </div>

                  {/* SEÇÃO ENDEREÇO */}
                  {/* MAPA DO TESOURO: Divisor — Endereço Completo */}
                  <div className="relative mt-2 mb-2">
                    <div className="absolute inset-0 flex items-center" aria-hidden="true">
                      <div className="w-full border-t border-[#E5E7EB]"></div>
                    </div>
                    <div className="relative flex justify-center">
                      <span className="bg-white px-3 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Endereço Completo")}</span>
                    </div>
                  </div>

                  <div className="flex flex-col gap-[16px]">
                    <div className="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr] gap-[16px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("País")}</label>
                        {/* MAPA DO TESOURO: Select — País */}
                        <CustomSelect value={pais} onChange={setPais} placeholder={t("Selecione")} options={[{value:"BR", label: "Brasil"}]} />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">CEP</label>
                        {/* MAPA DO TESOURO: Input — CEP */}
                        <input type="text" placeholder="00000-000" className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Rua")}</label>
                        {/* MAPA DO TESOURO: Input — Rua */}
                        <input type="text" placeholder={t("Ex: Av. Paulista")} className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-[1fr_1fr_1.5fr_1fr_1fr] gap-[16px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Número")}</label>
                        {/* MAPA DO TESOURO: Input — Número */}
                        <input type="text" placeholder="Ex: 1000" className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Complemento")}</label>
                        {/* MAPA DO TESOURO: Input — Complemento */}
                        <input type="text" placeholder={t("Ex: Apto 42")} className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Bairro")}</label>
                        {/* MAPA DO TESOURO: Input — Bairro */}
                        <input type="text" placeholder={t("Ex: Bela Vista")} className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Cidade")}</label>
                        {/* MAPA DO TESOURO: Input — Cidade */}
                        <input type="text" className="h-[40px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none  transition-all" />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[12px] font-[600] text-[#4B5563]">{t("Estado")}</label>
                        {/* MAPA DO TESOURO: Select — Estado */}
                        <CustomSelect value={estado} onChange={setEstado} placeholder={t("Selecione")} options={[]} />
                      </div>
                    </div>
                  </div>

                </div>
              )}
            </div>

            {/* DROPDOWN 2: MEMBROS */}
            {/* MAPA DO TESOURO: Card accordion — Membros da célula */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden transition-all duration-300">
              {/* MAPA DO TESOURO: Botão toggle — expande/recolhe seção Membros */}
              <button onClick={() => toggleSection('membros')} className="w-full flex items-center justify-between p-[16px_20px] bg-white hover:bg-[#FAFAFC] transition-colors">
                <div className="flex items-center gap-[10px]">
                  <div className="w-[28px] h-[28px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                    <Users className="w-[14px] h-[14px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Membros")}</h2>
                </div>
                {openSections.membros ? <ChevronUp className="w-[18px] h-[18px] text-[#6B7280]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#6B7280]" />}
              </button>

              {openSections.membros && (
                <div className="p-[20px] pt-0 border-t border-[#F1F1F4] flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300">
                  <div className="flex items-center gap-[16px] mt-4">
                    <div className="flex-1">
                      {/* MAPA DO TESOURO: Select — Selecionar membro para adicionar */}
                      <CustomSelect value={selectedMembro} onChange={setSelectedMembro} placeholder={t("Selecionar membro")} searchable={true} options={[{value: "1", label: "Membro Teste 1"}]} />
                    </div>
                    {/* MAPA DO TESOURO: Botão — Adicionar membro à lista */}
                    <button onClick={addMembro} className="px-[16px] h-[40px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[13px] font-[600] rounded-[8px] transition-all shadow-sm">
                      {t("Adicionar membro")}
                    </button>
                  </div>
                  
                  {/* Lista de Membros Adicionados */}
                  {/* MAPA DO TESOURO: Lista — Membros adicionados com botão de remover */}
                  {membros.length > 0 && (
                    <div className="border border-[#F1F1F4] rounded-[8px] overflow-hidden mt-2">
                      <div className="bg-[#FCFCFD] px-[16px] py-[10px] border-b border-[#F1F1F4]">
                        <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Nome do membro")}</span>
                      </div>
                      {membros.map(m => (
                        <div key={m.id} className="flex items-center justify-between px-[16px] py-[10px] border-b border-[#F1F1F4] last:border-0 bg-white">
                          <span className="text-[13px] font-[500] text-[#4B5563]">{m.nome}</span>
                          {/* MAPA DO TESOURO: Botão — Remover membro da lista */}
                          <button onClick={() => setMembros(membros.filter(x => x.id !== m.id))} className="text-[#EF4444] hover:bg-[#EF4444]/10 p-1.5 rounded-[6px] transition-colors">
                            <Trash2 className="w-[14px] h-[14px]" strokeWidth={2.4} />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* DROPDOWN 3: VISITANTES */}
            {/* MAPA DO TESOURO: Card accordion — Visitantes da célula */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden transition-all duration-300">
              {/* MAPA DO TESOURO: Botão toggle — expande/recolhe seção Visitantes */}
              <button onClick={() => toggleSection('visitantes')} className="w-full flex items-center justify-between p-[16px_20px] bg-white hover:bg-[#FAFAFC] transition-colors">
                <div className="flex items-center gap-[10px]">
                  <div className="w-[28px] h-[28px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                    <Users className="w-[14px] h-[14px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Visitantes")}</h2>
                </div>
                {openSections.visitantes ? <ChevronUp className="w-[18px] h-[18px] text-[#6B7280]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#6B7280]" />}
              </button>

              {openSections.visitantes && (
                <div className="p-[20px] pt-0 border-t border-[#F1F1F4] flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300">
                  <div className="flex items-center gap-[16px] mt-4">
                    <div className="flex-1 max-w-[500px]">
                      {/* MAPA DO TESOURO: Select — Selecionar visitante para adicionar */}
                      <CustomSelect value={selectedVisitante} onChange={setSelectedVisitante} placeholder={t("Selecionar visitante")} searchable={true} options={[{value: "1", label: "Visitante Teste 1"}]} />
                    </div>
                    {/* MAPA DO TESOURO: Botão — Adicionar visitante à lista */}
                    <button onClick={addVisitante} className="px-[16px] h-[40px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[13px] font-[600] rounded-[8px] transition-all shadow-sm">
                      {t("Adicionar visitante")}
                    </button>
                    {/* MAPA DO TESOURO: Link — Registrar novo visitante (fora da célula) */}
                    <Link href="/cadastros/visitantes/novo" className="px-[16px] h-[40px] border border-[#6D28D9] text-[#6D28D9] hover:bg-[#F4EEFF] text-[13px] font-[600] rounded-[8px] transition-all flex items-center justify-center">
                      {t("Registrar novo visitante")}
                    </Link>
                  </div>
                  
                  {/* Lista de Visitantes */}
                  {/* MAPA DO TESOURO: Lista — Visitantes adicionados com botão de remover */}
                  {visitantes.length > 0 && (
                    <div className="border border-[#F1F1F4] rounded-[8px] overflow-hidden mt-2">
                      <div className="bg-[#FCFCFD] px-[16px] py-[10px] border-b border-[#F1F1F4]">
                        <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Nome do visitante")}</span>
                      </div>
                      {visitantes.map(v => (
                        <div key={v.id} className="flex items-center justify-between px-[16px] py-[10px] border-b border-[#F1F1F4] last:border-0 bg-white">
                          <span className="text-[13px] font-[500] text-[#4B5563]">{v.nome}</span>
                          {/* MAPA DO TESOURO: Botão — Remover visitante da lista */}
                          <button onClick={() => setVisitantes(visitantes.filter(x => x.id !== v.id))} className="text-[#EF4444] hover:bg-[#EF4444]/10 p-1.5 rounded-[6px] transition-colors">
                            <Trash2 className="w-[14px] h-[14px]" strokeWidth={2.4} />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* DROPDOWN 4: LIDERANÇA */}
            {/* MAPA DO TESOURO: Card accordion — Liderança da célula */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden transition-all duration-300">
              {/* MAPA DO TESOURO: Botão toggle — expande/recolhe seção Liderança */}
              <button onClick={() => toggleSection('lideranca')} className="w-full flex items-center justify-between p-[16px_20px] bg-white hover:bg-[#FAFAFC] transition-colors">
                <div className="flex items-center gap-[10px]">
                  <div className="w-[28px] h-[28px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                    <Users className="w-[14px] h-[14px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Liderança")}</h2>
                </div>
                {openSections.lideranca ? <ChevronUp className="w-[18px] h-[18px] text-[#6B7280]" /> : <ChevronDown className="w-[18px] h-[18px] text-[#6B7280]" />}
              </button>

              {openSections.lideranca && (
                <div className="p-[20px] pt-0 border-t border-[#F1F1F4] flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300">
                  <div className="flex flex-col md:flex-row items-center gap-[16px] mt-4">
                    <div className="flex-1 w-full">
                      {/* MAPA DO TESOURO: Select — Selecionar membro para líder */}
                      <CustomSelect value={selectedLiderMembro} onChange={setSelectedLiderMembro} placeholder={t("Selecionar membro")} searchable={true} options={[{value: "1", label: "Membro Teste 1"}]} />
                    </div>
                    <div className="flex-1 w-full">
                      {/* MAPA DO TESOURO: Select — Função do líder */}
                      <CustomSelect value={selectedLiderFuncao} onChange={setSelectedLiderFuncao} placeholder={t("Função específica")} options={[
                        { value: "Gestor", label: "Gestor" },
                        { value: "Lider geral", label: "Lider geral" },
                        { value: "Lider auxiliar", label: "Lider auxiliar" },
                        { value: "Anfitrião", label: "Anfitrião" },
                      ]} />
                    </div>
                    {/* MAPA DO TESOURO: Botão — Adicionar novo líder à lista */}
                    <button onClick={addLider} className="w-full md:w-auto px-[16px] h-[40px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[13px] font-[600] rounded-[8px] transition-all shadow-sm shrink-0">
                      {t("Adicionar novo líder")}
                    </button>
                  </div>
                  
                  {/* Lista de Líderes */}
                  {/* MAPA DO TESOURO: Lista — Líderes adicionados com botão de remover */}
                  {lideres.length > 0 && (
                    <div className="border border-[#F1F1F4] rounded-[8px] overflow-hidden mt-2">
                      <div className="grid grid-cols-2 bg-[#FCFCFD] px-[16px] py-[10px] border-b border-[#F1F1F4]">
                        <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Nome")}</span>
                        <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Função")}</span>
                      </div>
                      {lideres.map(l => (
                        <div key={l.id} className="grid grid-cols-2 items-center px-[16px] py-[10px] border-b border-[#F1F1F4] last:border-0 bg-white relative">
                          <span className="text-[13px] font-[500] text-[#4B5563] truncate pr-4">{l.nome}</span>
                          <span className="text-[13px] font-[500] text-[#4B5563]">{l.funcao}</span>
                          <button onClick={() => setLideres(lideres.filter(x => x.id !== l.id))} className="absolute right-[16px] text-[#EF4444] hover:bg-[#EF4444]/10 p-1.5 rounded-[6px] transition-colors bg-white">
                            <Trash2 className="w-[14px] h-[14px]" strokeWidth={2.4} />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO E INATIVAÇÃO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              
              {/* Bloco de Status */}
              <div className="flex flex-col gap-[8px]">
                <span className="text-[13px] font-[600] text-[#4B5563]">{t("Status da Célula")}</span>
                <button 
                  type="button"
                  onClick={() => setIsAtivo(!isAtivo)}
                  className="flex items-center gap-[12px] w-fit focus:outline-none group"
                >
                  <div className={`relative w-[44px] h-[24px] rounded-full transition-colors duration-300 ${isAtivo ? 'bg-[#10B981]' : 'bg-[#E5E7EB]'}`}>
                    <div className={`absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform duration-300 ${isAtivo ? 'translate-x-[20px]' : 'translate-x-0'}`} />
                  </div>
                  <span className={`text-[14px] font-[600] transition-colors ${isAtivo ? 'text-[#10B981]' : 'text-[#6B7280]'}`}>
                    {isAtivo ? "Célula Ativa" : "Célula Inativa"}
                  </span>
                </button>
              </div>

              {/* Campos Condicionais de Inativação */}
              {!isAtivo && (
                <div className="flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300 p-[16px] bg-[#FFF1F2] border border-[#FECDD3] rounded-[8px]">
                  <div className="flex items-center gap-2 text-[#BE123C] mb-2">
                    <AlertCircle className="w-[16px] h-[16px]" strokeWidth={2.4} />
                    <span className="text-[13px] font-[700] uppercase tracking-wider">Motivo da Desativação</span>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <CustomSelect 
                        value={motivoInativacao}
                        onChange={setMotivoInativacao}
                        options={[
                          {value: "multiplicacao", label: "Multiplicação / Fechamento"},
                          {value: "fusao", label: "Fusão com outra Célula"},
                          {value: "outro", label: "Outro Motivo"}
                        ]}
                      />
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Descrição do Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <input 
                        type="text" 
                        value={descricaoInativacao} 
                        onChange={(e) => setDescricaoInativacao(e.target.value)}
                        placeholder="Descreva o motivo" 
                        className="h-[42px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none transition-all" 
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="h-px bg-[#E5E7EB] w-full my-2"></div>

              {/* Título Histórico */}
              <div className="flex items-center gap-[8px]">
                <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico da Célula")}</h2>
              </div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo Ativa</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">2 anos e 5 meses</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Users className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Membros Atuais</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">15 membros</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">20/01/2024</p>
                </div>
              </div>

              {/* Tabela de Ações / Timeline */}
              <div className="mt-2 border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <div className="bg-[#FCFCFD] px-[20px] py-[12px] border-b border-[#E5E7EB] grid grid-cols-[1fr_2fr_1fr] gap-[16px]">
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data / Hora</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ação Realizada</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">Responsável</span>
                </div>
                <div className="flex flex-col">
                  {/* Item 1 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 14:32</span>
                    <span className="text-[13px] text-[#1A1A2E]">Inclusão de novo líder: <strong>Marcos Silva</strong></span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Admin Principal</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">20/01/2024 18:15</span>
                    <span className="text-[13px] text-[#1A1A2E]">Criação inicial da célula</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Pr. Leandro</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* RODAPÉ DO FORMULÁRIO */}
          {/* MAPA DO TESOURO: Rodapé — copyright e botões de ação */}
          <div className="mt-[24px] bg-white rounded-[12px] border border-[#E5E7EB] p-[20px] flex items-center justify-between">
            <p className="text-[13px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Basiléia")}</span>{t(", Todos os direitos reservados")}
            </p>
            <div className="flex items-center gap-[12px]">
              {/* MAPA DO TESOURO: Link — Cancelar e voltar à listagem */}
              <Link href="/celulas/cadastro" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                {t("Cancelar")}
              </Link>
              {/* MAPA DO TESOURO: Botão — Salvar célula (submit do formulário) */}
              <button className="px-[24px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(109,40,217,0.22)] transition-all flex items-center gap-[6px]">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.4} />
                Salvar célula
              </button>
            </div>
          </div>

        </main>
      </div>
    </div>
  );
}
