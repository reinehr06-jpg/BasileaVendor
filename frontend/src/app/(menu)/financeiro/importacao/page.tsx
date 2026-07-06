/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: CONCILIAÇÃO BANCÁRIA (IMPORTAÇÃO)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /importacao
 * 📁 ARQUIVO: src/app/importacao/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Permitir que o usuário envie extratos (OFX, CSV) e concilie (dê baixa) com os
 *    lançamentos do sistema ou crie novos.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. POST /api/importacao/upload (multipart/form-data) → Envia o arquivo e retorna parsing
 *    2. POST /api/importacao/conciliar → Salva os vínculos definidos pelo usuário
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { 
  UploadCloud, FileText, CheckCircle2, AlertCircle, ArrowRight, Database, Trash2, X, Link as LinkIcon, Plus
} from "lucide-react";

export default function ImportacaoPage() {
  const [step, setStep] = useState(1); // 1 = Upload, 2 = Conciliação

  const mockOfx = [
    { id: 1, data: "20/05/2024", desc: "PIX RECEBIDO - JOAO SILVA", valor: "500,00", tipo: "receita", acaoDefault: "novo", sugerido: "Dízimos", status: "pendente", duplicado: false },
    { id: 2, data: "21/05/2024", desc: "PAG BOLETO - SABESP", valor: "150,00", tipo: "despesa", acaoDefault: "vincular", sugerido: "Energia/Água", status: "pendente", duplicado: false, match: "Despesa #1042 (R$ 150,00)" },
    { id: 3, data: "21/05/2024", desc: "PAG BOLETO - SABESP", valor: "150,00", tipo: "despesa", acaoDefault: "", sugerido: "", status: "duplicado", duplicado: true },
    { id: 4, data: "22/05/2024", desc: "TARIFA BANCARIA", valor: "19,90", tipo: "despesa", acaoDefault: "novo", sugerido: "Taxas Bancárias", status: "pendente", duplicado: false },
  ];

  const handleSimulateUpload = () => {
    // Simulando o delay do upload
    setTimeout(() => {
      setStep(2);
    }, 800);
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1400px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                <Database className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Conciliação Bancária</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Importe e concilie os dados do seu extrato diretamente no sistema.</p>
              </div>
            </div>
            
            {step === 2 && (
              <div className="flex items-center gap-3">
                <button 
                  onClick={() => setStep(1)}
                  className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm"
                >
                  <X className="w-[14px] h-[14px]" /> Cancelar Lote
                </button>
                <button className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                  <CheckCircle2 className="w-[16px] h-[16px]" strokeWidth={2.5} />
                  Salvar Conciliação
                </button>
              </div>
            )}
          </div>

          <div className="flex flex-col flex-1 overflow-hidden min-h-0 gap-4">
            
            {/* STEP 1: UPLOAD AREA */}
            {step === 1 && (
              <div className="flex-1 flex items-center justify-center p-10 animate-in fade-in zoom-in-95">
                <div className="bg-white rounded-[20px] border border-[#E5E7EB] shadow-sm p-10 w-full max-w-[600px] flex flex-col items-center text-center">
                  
                  <div className="w-[80px] h-[80px] rounded-full bg-[#F3F4F6] flex items-center justify-center mb-6">
                    <UploadCloud className="w-[40px] h-[40px] text-[#9CA3AF]" strokeWidth={2} />
                  </div>
                  
                  <h2 className="text-[20px] font-[800] text-[#111827] mb-2">Envie seu extrato bancário</h2>
                  <p className="text-[14px] text-[#6B7280] mb-8">Arraste e solte o arquivo aqui ou clique para selecionar do seu computador. Aceitamos os formatos OFX, CSV e XLSX.</p>
                  
                  <div 
                    onClick={handleSimulateUpload}
                    className="w-full h-[180px] border-2 border-dashed border-[#D1D5DB] rounded-[16px] bg-[#F9FAFB] flex flex-col items-center justify-center cursor-pointer hover:bg-[#F3F4F6] hover:border-[#10B981] transition-all group"
                  >
                    <FileText className="w-[32px] h-[32px] text-[#9CA3AF] group-hover:text-[#10B981] transition-colors mb-3" />
                    <span className="text-[14px] font-[700] text-[#4B5563] group-hover:text-[#10B981] transition-colors">Clique para importar arquivo</span>
                    <span className="text-[12px] font-[500] text-[#9CA3AF] mt-1">.ofx, .csv ou .xlsx (Max 10MB)</span>
                  </div>

                  <div className="w-full flex items-center gap-3 mt-8 bg-[#EFF6FF] border border-[#BFDBFE] p-4 rounded-[12px]">
                    <AlertCircle className="w-[20px] h-[20px] text-[#3B82F6] shrink-0" />
                    <div className="flex flex-col text-left">
                      <span className="text-[13px] font-[700] text-[#1E40AF]">Recomendação</span>
                      <span className="text-[12px] text-[#2563EB]">Dê preferência para arquivos OFX exportados diretamente pelo seu Internet Banking. Eles são mais precisos.</span>
                    </div>
                  </div>

                </div>
              </div>
            )}

            {/* STEP 2: CONCILIAÇÃO (SPLIT VIEW) */}
            {step === 2 && (
              <div className="flex flex-col flex-1 overflow-hidden animate-in fade-in slide-in-from-bottom-4">
                
                <div className="bg-[#10B981] text-white p-4 rounded-[12px] shadow-sm shrink-0 flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <CheckCircle2 className="w-[20px] h-[20px]" />
                    <div className="flex flex-col">
                      <span className="text-[14px] font-[800]">Leitura Concluída (Itaú - CC 1234)</span>
                      <span className="text-[12px] opacity-90">4 registros encontrados. Lançamentos duplicados foram detectados automaticamente.</span>
                    </div>
                  </div>
                  <div className="bg-white/20 px-3 py-1 rounded-[6px] text-[13px] font-[700]">
                    20/05/2024 até 22/05/2024
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden mt-4">
                  <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                    <table className="w-full text-left border-collapse min-w-[900px]">
                      <thead className="sticky top-0 z-10 shadow-[0_1px_0_#F1F1F4]">
                        <tr>
                          <th colSpan={3} className="py-2 px-5 text-[10px] font-[800] text-[#4B5563] uppercase tracking-wider border-b border-r border-[#E5E7EB] bg-[#F9FAFB] text-center">
                            Extrato do Banco
                          </th>
                          <th colSpan={2} className="py-2 px-5 text-[10px] font-[800] text-[#6D28D9] uppercase tracking-wider border-b border-[#E5E7EB] bg-[#F4EEFF] text-center">
                            Sistema Basiléia (Conciliação)
                          </th>
                        </tr>
                        <tr>
                          <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] bg-[#F9FAFB]">Data</th>
                          <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] bg-[#F9FAFB]">Descrição Original</th>
                          <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] bg-[#F9FAFB] border-r border-[#E5E7EB]">Valor</th>
                          <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] bg-white w-[350px]">Ação / Destino</th>
                          <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] bg-white text-right">Opções</th>
                        </tr>
                      </thead>
                      <tbody>
                        {mockOfx.map((item) => (
                          <tr key={item.id} className={`hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4] ${item.duplicado ? 'opacity-70 bg-[#FEF2F2] hover:bg-[#FEF2F2]' : ''}`}>
                            <td className="py-4 px-5">
                              <span className="text-[13px] font-[600] text-[#4B5563]">{item.data}</span>
                            </td>
                            <td className="py-4 px-5">
                              <span className="text-[13px] font-[700] text-[#111827] bg-white border border-[#E5E7EB] px-2 py-1 rounded-[6px] font-mono shadow-sm">{item.desc}</span>
                            </td>
                            <td className="py-4 px-5 border-r border-[#E5E7EB] bg-[#F9FAFB]/30">
                              <span className={`text-[14px] font-[800] ${item.tipo === 'receita' ? 'text-[#10B981]' : 'text-[#DC2626]'}`}>
                                {item.tipo === 'receita' ? '+ R$' : '- R$'} {item.valor}
                              </span>
                            </td>
                            <td className="py-4 px-5">
                              {item.duplicado ? (
                                <div className="flex flex-col gap-1">
                                  <div className="flex items-center gap-2 p-2 bg-[#FEF2F2] rounded-[8px] border border-[#FCA5A5]">
                                    <AlertCircle className="w-[16px] h-[16px] text-[#DC2626]" />
                                    <span className="text-[12px] font-[700] text-[#DC2626]">Transação já importada anteriormente.</span>
                                  </div>
                                </div>
                              ) : (
                                <div className="flex flex-col gap-2">
                                  {item.acaoDefault === 'novo' && (
                                    <>
                                      <div className="flex items-center gap-2 text-[12px] font-[700] text-[#059669]">
                                        <Plus className="w-[14px] h-[14px]" />
                                        Criar novo lançamento
                                      </div>
                                      <CustomSelect 
                                        options={[{label: item.sugerido, value: "1"}]}
                                        value="1"
                                        placeholder="Categoria sugerida..."
                                        className="h-[34px]"
                                      />
                                    </>
                                  )}
                                  {item.acaoDefault === 'vincular' && (
                                    <>
                                      <div className="flex items-center gap-2 text-[12px] font-[700] text-[#3B82F6]">
                                        <LinkIcon className="w-[14px] h-[14px]" />
                                        Vincular a lançamento existente
                                      </div>
                                      <CustomSelect 
                                        options={[{label: item.match!, value: "1"}]}
                                        value="1"
                                        placeholder="Selecione o lançamento..."
                                        className="h-[34px]"
                                      />
                                    </>
                                  )}
                                </div>
                              )}
                            </td>
                            <td className="py-4 px-5 text-right">
                              <button className={`flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-[6px] text-[12px] font-[700] transition-colors ml-auto shadow-sm ${
                                item.duplicado 
                                ? 'bg-white border-[#E5E7EB] text-[#9CA3AF] cursor-not-allowed' 
                                : 'bg-white border-[#E5E7EB] text-[#DC2626] hover:bg-[#FEF2F2] hover:border-[#FCA5A5]'
                              }`}>
                                <Trash2 className="w-[14px] h-[14px]" /> {item.duplicado ? 'Ignorada' : 'Ignorar'}
                              </button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
                
                {/* Legenda de atalhos ou sumário (Opcional) */}
                <div className="mt-4 flex items-center justify-between text-[12px] text-[#6B7280]">
                  <p>Mostrando 4 registros. 1 duplicado ignorado.</p>
                  <p>As conciliações vinculadas darão baixa automática nos lançamentos pendentes.</p>
                </div>

              </div>
            )}

          </div>
          
        </main>
      </div>
    </div>
  );
}

