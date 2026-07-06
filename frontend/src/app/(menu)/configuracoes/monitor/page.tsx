"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { useTranslation } from "react-i18next";
import {
  Activity,
  ArrowLeft,
  Filter,
  RefreshCcw,
  Search,
  CheckCircle2,
  AlertCircle,
  Clock,
  Terminal,
  Code,
  ChevronDown,
  ChevronUp
} from "lucide-react";

// MOCK DATA
const mockLogs = [
  {
    id: "evt_109283019",
    timestamp: "10:45:22 05/07/2026",
    event: "PAYMENT_RECEIVED",
    source: "Asaas",
    status: 200,
    customer: "Tabernáculo Church",
    amount: "R$ 197,00",
    payload: {
      "event": "PAYMENT_RECEIVED",
      "payment": {
        "id": "pay_982374982374",
        "customer": "cus_000005123412",
        "value": 197.00,
        "netValue": 191.09,
        "billingType": "CREDIT_CARD",
        "status": "RECEIVED"
      }
    }
  },
  {
    id: "evt_109283020",
    timestamp: "10:42:15 05/07/2026",
    event: "SUBSCRIPTION_CREATED",
    source: "Asaas",
    status: 200,
    customer: "Igreja Batista Central",
    amount: "R$ 299,00",
    payload: {
      "event": "SUBSCRIPTION_CREATED",
      "subscription": {
        "id": "sub_4985734958",
        "customer": "cus_000005123499",
        "value": 299.00,
        "cycle": "MONTHLY",
        "status": "ACTIVE"
      }
    }
  },
  {
    id: "evt_109283021",
    timestamp: "10:30:05 05/07/2026",
    event: "WEBHOOK_FAILED",
    source: "System",
    status: 500,
    customer: "Desconhecido",
    amount: "-",
    payload: {
      "error": "Timeout connection to Database",
      "code": 504,
      "stack": "Error: Timeout connection... at /src/services/webhook.js:45"
    }
  },
  {
    id: "evt_109283022",
    timestamp: "09:15:44 05/07/2026",
    event: "PAYMENT_REFUNDED",
    source: "Stripe",
    status: 200,
    customer: "Comunidade da Graça",
    amount: "R$ 50,00",
    payload: {
      "event": "charge.refunded",
      "data": {
        "object": {
          "id": "ch_3M4X...",
          "amount_refunded": 5000,
          "status": "succeeded"
        }
      }
    }
  }
];

export default function MonitorVendasPage() {
  const { t } = useTranslation();
  const [expandedRow, setExpandedRow] = useState<string | null>(null);

  const toggleRow = (id: string) => {
    setExpandedRow(expandedRow === id ? null : id);
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            
            {/* VOLTAR */}
            <Link 
              href="/configuracoes"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              {t("Voltar para Configurações")}
            </Link>

            {/* CABEÇALHO */}
            <div className="flex flex-col md:flex-row md:items-end justify-between gap-[16px] mb-[32px]">
              <div className="flex items-start gap-[16px]">
                <div className="w-[48px] h-[48px] rounded-[12px] bg-[#EEF2FF] flex items-center justify-center shrink-0 border border-[#E0E7FF] shadow-inner relative">
                  <Activity className="w-[24px] h-[24px] text-[#4F46E5]" strokeWidth={2} />
                  {/* PULSING DOT LIVE */}
                  <span className="absolute -top-[4px] -right-[4px] flex h-[12px] w-[12px]">
                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
                    <span className="relative inline-flex rounded-full h-[12px] w-[12px] bg-[#10B981] border-2 border-white"></span>
                  </span>
                </div>
                <div className="flex flex-col">
                  <h1 className="text-[28px] font-[800] text-[#111827] leading-tight tracking-tight flex items-center gap-[8px]">
                    {t("Monitor de Vendas")}
                  </h1>
                  <p className="text-[14px] text-[#6B7280] mt-[2px]">{t("Acompanhe os logs e eventos de webhooks do sistema em tempo real.")}</p>
                </div>
              </div>

              <div className="flex items-center gap-[12px]">
                <div className="relative">
                  <Search className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" />
                  <input 
                    type="text" 
                    placeholder="Buscar evento, ID..." 
                    className="w-[240px] h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[13px] text-[#111827] outline-none focus:border-[#4F46E5] focus:ring-1 focus:ring-[#4F46E5] transition-all"
                  />
                </div>
                <button className="h-[40px] px-[16px] bg-white border border-[#E5E7EB] rounded-[8px] flex items-center justify-center gap-[8px] hover:bg-[#F9FAFB] transition-colors text-[13px] font-[600] text-[#374151] shadow-sm">
                  <Filter className="w-[14px] h-[14px]" />
                  Filtros
                </button>
                <button className="h-[40px] px-[16px] bg-[#4F46E5] hover:bg-[#4338CA] text-white rounded-[8px] flex items-center justify-center gap-[8px] text-[13px] font-[600] shadow-[0_4px_12px_rgba(79,70,229,0.25)] transition-all">
                  <RefreshCcw className="w-[14px] h-[14px]" />
                  Atualizar
                </button>
              </div>
            </div>

            {/* DASHBOARD MINI CARDS */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px] mb-[24px]">
              <div className="bg-white p-[20px] rounded-[16px] border border-[#E5E7EB] shadow-sm flex flex-col gap-[4px] relative overflow-hidden group">
                <div className="absolute top-0 right-0 w-[80px] h-[80px] bg-gradient-to-br from-[#EEF2FF] to-transparent rounded-bl-full -mr-[20px] -mt-[20px] transition-transform group-hover:scale-110"></div>
                <span className="text-[13px] font-[600] text-[#6B7280] uppercase tracking-wider relative z-10">Eventos Hoje</span>
                <span className="text-[32px] font-[800] text-[#111827] leading-none relative z-10 mt-[4px]">1,284</span>
              </div>
              <div className="bg-white p-[20px] rounded-[16px] border border-[#E5E7EB] shadow-sm flex flex-col gap-[4px] relative overflow-hidden group">
                <div className="absolute top-0 right-0 w-[80px] h-[80px] bg-gradient-to-br from-[#ECFDF5] to-transparent rounded-bl-full -mr-[20px] -mt-[20px] transition-transform group-hover:scale-110"></div>
                <span className="text-[13px] font-[600] text-[#6B7280] uppercase tracking-wider relative z-10">Sucesso (200 OK)</span>
                <span className="text-[32px] font-[800] text-[#10B981] leading-none relative z-10 mt-[4px]">1,280</span>
              </div>
              <div className="bg-white p-[20px] rounded-[16px] border border-[#E5E7EB] shadow-sm flex flex-col gap-[4px] relative overflow-hidden group">
                <div className="absolute top-0 right-0 w-[80px] h-[80px] bg-gradient-to-br from-[#FEF2F2] to-transparent rounded-bl-full -mr-[20px] -mt-[20px] transition-transform group-hover:scale-110"></div>
                <span className="text-[13px] font-[600] text-[#6B7280] uppercase tracking-wider relative z-10">Falhas (500 ERR)</span>
                <span className="text-[32px] font-[800] text-[#EF4444] leading-none relative z-10 mt-[4px]">4</span>
              </div>
              <div className="bg-[#1E293B] p-[20px] rounded-[16px] border border-[#334155] shadow-lg flex flex-col gap-[4px] relative overflow-hidden group">
                <div className="absolute top-0 right-0 w-[80px] h-[80px] bg-gradient-to-br from-[#334155] to-transparent rounded-bl-full -mr-[20px] -mt-[20px] transition-transform group-hover:scale-110"></div>
                <span className="text-[13px] font-[600] text-[#94A3B8] uppercase tracking-wider relative z-10">Status do Sistema</span>
                <div className="flex items-center gap-[8px] mt-[4px] relative z-10">
                  <span className="w-[10px] h-[10px] bg-[#10B981] rounded-full shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                  <span className="text-[20px] font-[800] text-white leading-none">Operacional</span>
                </div>
              </div>
            </div>

            {/* TABELA DE LOGS */}
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] overflow-hidden shadow-sm flex flex-col">
              <div className="p-[16px_24px] border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between">
                <div className="flex items-center gap-[8px]">
                  <Terminal className="w-[16px] h-[16px] text-[#4B5563]" />
                  <span className="text-[14px] font-[700] text-[#111827]">Log de Eventos</span>
                </div>
                <span className="text-[12px] font-[600] text-[#6B7280] flex items-center gap-[4px]">
                  <Clock className="w-[14px] h-[14px]" /> Última atualização agora
                </span>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="border-b border-[#E5E7EB]">
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider w-[180px]">Data / Hora</th>
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Status</th>
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Evento</th>
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Origem</th>
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Cliente</th>
                      <th className="p-[12px_24px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">Ação</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-[#E5E7EB]">
                    {mockLogs.map((log) => (
                      <React.Fragment key={log.id}>
                        {/* MAIN ROW */}
                        <tr 
                          onClick={() => toggleRow(log.id)}
                          className={`hover:bg-[#F9FAFB] transition-colors cursor-pointer ${expandedRow === log.id ? 'bg-[#F9FAFB]' : ''}`}
                        >
                          <td className="p-[16px_24px] whitespace-nowrap">
                            <span className="text-[13px] font-[600] text-[#4B5563] font-mono">{log.timestamp}</span>
                          </td>
                          <td className="p-[16px_24px]">
                            {log.status === 200 ? (
                              <div className="inline-flex items-center gap-[6px] px-[8px] py-[4px] rounded-[6px] bg-[#ECFDF5] border border-[#A7F3D0] text-[#059669]">
                                <CheckCircle2 className="w-[12px] h-[12px]" strokeWidth={3} />
                                <span className="text-[11px] font-[800] uppercase">200 OK</span>
                              </div>
                            ) : (
                              <div className="inline-flex items-center gap-[6px] px-[8px] py-[4px] rounded-[6px] bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626]">
                                <AlertCircle className="w-[12px] h-[12px]" strokeWidth={3} />
                                <span className="text-[11px] font-[800] uppercase">500 ERR</span>
                              </div>
                            )}
                          </td>
                          <td className="p-[16px_24px]">
                            <span className={`text-[13px] font-[700] ${log.status === 500 ? 'text-[#EF4444]' : 'text-[#4F46E5]'}`}>
                              {log.event}
                            </span>
                            <div className="text-[11px] text-[#9CA3AF] font-mono mt-[2px]">{log.id}</div>
                          </td>
                          <td className="p-[16px_24px]">
                            <span className="px-[8px] py-[2px] bg-[#F3F4F6] text-[#4B5563] rounded-[4px] text-[12px] font-[600]">
                              {log.source}
                            </span>
                          </td>
                          <td className="p-[16px_24px]">
                            <div className="flex flex-col">
                              <span className="text-[13px] font-[600] text-[#111827]">{log.customer}</span>
                              <span className="text-[12px] font-[600] text-[#10B981]">{log.amount}</span>
                            </div>
                          </td>
                          <td className="p-[16px_24px] text-right">
                            <button className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center text-[#6B7280] hover:bg-[#F3F4F6] hover:text-[#111827] transition-all ml-auto shadow-sm">
                              {expandedRow === log.id ? <ChevronUp className="w-[16px] h-[16px]" /> : <ChevronDown className="w-[16px] h-[16px]" />}
                            </button>
                          </td>
                        </tr>

                        {/* EXPANDED PAYLOAD ROW */}
                        {expandedRow === log.id && (
                          <tr>
                            <td colSpan={6} className="p-0 border-b-0">
                              <div className="bg-[#1E293B] p-[24px] border-y border-[#334155] shadow-inner flex flex-col gap-[12px] animate-in slide-in-from-top-2 duration-200">
                                <div className="flex items-center justify-between">
                                  <div className="flex items-center gap-[8px]">
                                    <Code className="w-[16px] h-[16px] text-[#94A3B8]" />
                                    <span className="text-[13px] font-[700] text-[#E2E8F0] uppercase tracking-wider">Payload / Raw Data</span>
                                  </div>
                                  <button className="text-[12px] font-[600] text-[#38BDF8] hover:text-[#7DD3FC] hover:underline transition-colors">
                                    Copiar JSON
                                  </button>
                                </div>
                                <pre className="w-full bg-[#0F172A] p-[16px] rounded-[8px] border border-[#334155] text-[#A5B4FC] font-mono text-[13px] leading-relaxed overflow-x-auto custom-scrollbar">
                                  <code>{JSON.stringify(log.payload, null, 2)}</code>
                                </pre>
                              </div>
                            </td>
                          </tr>
                        )}
                      </React.Fragment>
                    ))}
                  </tbody>
                </table>
              </div>
              
              <div className="p-[16px_24px] bg-[#F9FAFB] border-t border-[#E5E7EB] flex items-center justify-between">
                <span className="text-[13px] font-[500] text-[#6B7280]">Mostrando os últimos 4 eventos de 1,284 hoje</span>
                <div className="flex items-center gap-[8px]">
                  <button className="px-[12px] py-[6px] border border-[#E5E7EB] bg-white rounded-[6px] text-[13px] font-[600] text-[#374151] hover:bg-[#F3F4F6] transition-colors disabled:opacity-50" disabled>
                    Anterior
                  </button>
                  <button className="px-[12px] py-[6px] border border-[#E5E7EB] bg-white rounded-[6px] text-[13px] font-[600] text-[#374151] hover:bg-[#F3F4F6] transition-colors">
                    Próxima
                  </button>
                </div>
              </div>

            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
