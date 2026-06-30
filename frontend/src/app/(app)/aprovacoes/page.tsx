"use client";

import React, { useState } from "react";
import { 
  CheckSquare, 
  Search, 
  Filter, 
  Check,
  X,
  Clock,
  AlertTriangle
} from "lucide-react";
import Pagination from "@/components/Pagination";

export default function AprovacoesPage() {
  const [searchTerm, setSearchTerm] = useState("");

  const solicitacoes = [
    { id: 1, tipo: "Estorno", cliente: "João Silva", valor: "R$ 197,00", vendedor: "Vinicius", data: "30/06/2026", motivo: "Cliente pediu cancelamento no dia seguinte.", status: "pendente" },
    { id: 2, tipo: "Desconto", cliente: "Igreja Esperança", valor: "15% off", vendedor: "Maria", data: "29/06/2026", motivo: "Negociação especial fechamento trimestral.", status: "pendente" },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <CheckSquare className="w-8 h-8 text-purple-600" />
            Aprovações Pendentes
          </h2>
          <p className="text-gray-500 mt-1 font-medium">
            Gerencie as solicitações de estorno, descontos e alterações contratuais.
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {/* LISTA DE SOLICITAÇÕES */}
        <div className="md:col-span-2 space-y-4">
          
          <div className="relative w-full">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input 
              type="text" 
              placeholder="Buscar solicitação..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all shadow-sm"
            />
          </div>

          {solicitacoes.map(sol => (
            <div key={sol.id} className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
              <div className="flex justify-between items-start mb-4">
                <div className="flex items-center gap-3">
                  <div className={`p-2.5 rounded-xl text-white ${sol.tipo === 'Estorno' ? 'bg-rose-500' : 'bg-orange-500'}`}>
                    {sol.tipo === 'Estorno' ? <AlertTriangle className="w-5 h-5" /> : <Clock className="w-5 h-5" />}
                  </div>
                  <div>
                    <h3 className="font-bold text-[#111827] text-lg">Solicitação de {sol.tipo}</h3>
                    <p className="text-xs text-gray-500">Enviado por <span className="font-bold text-purple-600">{sol.vendedor}</span> em {sol.data}</p>
                  </div>
                </div>
                <div className="text-right">
                  <div className="text-sm text-gray-500 font-bold uppercase">Valor / Impacto</div>
                  <div className="font-black text-[#111827] text-xl">{sol.valor}</div>
                </div>
              </div>
              
              <div className="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-4">
                <div className="text-xs font-bold text-gray-400 uppercase mb-1">Cliente</div>
                <div className="font-medium text-[#111827] mb-3">{sol.cliente}</div>
                
                <div className="text-xs font-bold text-gray-400 uppercase mb-1">Motivo / Justificativa</div>
                <div className="text-sm text-gray-600 italic">"{sol.motivo}"</div>
              </div>
              
              <div className="flex gap-3 justify-end pt-2">
                <button className="flex items-center gap-2 px-5 py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl font-bold hover:bg-rose-50 transition-colors">
                  <X className="w-4 h-4" /> Recusar
                </button>
                <button className="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                  <Check className="w-4 h-4" /> Aprovar
                </button>
              </div>
            </div>
          ))}

          <Pagination total={10} currentPage={1} pageSize={10} onPageChange={() => {}} onPageSizeChange={() => {}} />

        </div>

        {/* SIDEBAR INFORMATIVA */}
        <div className="space-y-6">
          <div className="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-purple-600/20">
            <h3 className="font-bold text-xl mb-2">Regras de Aprovação</h3>
            <p className="text-sm text-purple-100 mb-4">
              Apenas gestores comerciais podem aprovar estornos ou descontos superiores a 10%.
            </p>
            <ul className="text-sm space-y-2 text-purple-50">
              <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-emerald-300" /> Estornos impactam comissões já pagas.</li>
              <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-emerald-300" /> Descontos alteram o LTV do cliente.</li>
            </ul>
          </div>
        </div>

      </div>
    </div>
  );
}

function CheckCircle2(props: any) {
  return (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
  )
}
