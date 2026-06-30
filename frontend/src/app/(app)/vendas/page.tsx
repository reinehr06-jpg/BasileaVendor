"use client";

import React, { useState } from "react";
import { 
  ShoppingBag, 
  Search, 
  Filter, 
  Download,
  FileSpreadsheet,
  FileText,
  DollarSign,
  CheckCircle2,
  Clock,
  MoreHorizontal
} from "lucide-react";
import Pagination from "@/components/Pagination";

export default function VendasGlobaisPage() {
  const [searchTerm, setSearchTerm] = useState("");

  const stats = { ativas: 125, valorTotal: "R$ 45.230,00", pagas: 98, aguardando: 27 };
  const vendas = [
    { id: 1, cliente: "Igreja Vida Nova", pastor: "Pr. Marcos", vendedor: "Vinicius", plano: "Plano Pro", valor: "R$ 197,00", status: "PAGO", pagamento: "PIX", data: "30/06/2026" },
    { id: 2, cliente: "Igreja Esperança", pastor: "Pr. João", vendedor: "Maria", plano: "Plano Starter", valor: "R$ 97,00", status: "AGUARDANDO", pagamento: "Boleto", data: "29/06/2026" },
    { id: 3, cliente: "Comunidade da Graça", pastor: "Pr. Lucas", vendedor: "Vinicius", plano: "Plano Avançado", valor: "R$ 497,00", status: "CANCELADO", pagamento: "Cartão", data: "28/06/2026" },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <ShoppingBag className="w-8 h-8 text-purple-600" />
            Vendas Globais
          </h2>
          <p className="text-gray-500 mt-1 font-medium">
            Todas as vendas e assinaturas da operação.
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl font-bold hover:bg-emerald-100 transition-colors">
            <FileSpreadsheet className="w-4 h-4" /> Excel
          </button>
          <button className="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl font-bold hover:bg-rose-100 transition-colors">
            <FileText className="w-4 h-4" /> PDF
          </button>
        </div>
      </div>

      {/* STATS */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard icon={ShoppingBag} label="Ativas" value={stats.ativas} color="purple" />
        <StatCard icon={DollarSign} label="Valor Total" value={stats.valorTotal} color="emerald" />
        <StatCard icon={CheckCircle2} label="Pagas" value={stats.pagas} color="emerald" />
        <StatCard icon={Clock} label="Aguardando" value={stats.aguardando} color="orange" />
      </div>

      {/* Tabela e Filtros */}
      <div className="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div className="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between bg-gray-50/50">
          <div className="relative w-full sm:max-w-md">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input 
              type="text" 
              placeholder="Buscar por cliente, pastor ou vendedor..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all"
            />
          </div>
          <button className="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
            <Filter className="w-4 h-4" /> Filtros Avançados
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm whitespace-nowrap">
            <thead className="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
              <tr>
                <th className="px-6 py-4">Cliente</th>
                <th className="px-6 py-4">Vendedor</th>
                <th className="px-6 py-4">Plano</th>
                <th className="px-6 py-4">Valor</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4">Pagamento</th>
                <th className="px-6 py-4">Data</th>
                <th className="px-6 py-4 text-right">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {vendas.map(v => (
                <tr key={v.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="font-bold text-[#111827]">{v.cliente}</div>
                    <div className="text-xs text-gray-500">{v.pastor}</div>
                  </td>
                  <td className="px-6 py-4 font-medium text-gray-700">{v.vendedor}</td>
                  <td className="px-6 py-4">
                    <span className="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-md text-xs font-bold border border-purple-100">
                      {v.plano}
                    </span>
                  </td>
                  <td className="px-6 py-4 font-black text-[#111827]">{v.valor}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${
                      v.status === 'PAGO' ? 'bg-emerald-100 text-emerald-800' : 
                      v.status === 'AGUARDANDO' ? 'bg-orange-100 text-orange-800' : 'bg-rose-100 text-rose-800'
                    }`}>
                      {v.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <span className="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                      {v.pagamento}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-gray-500">{v.data}</td>
                  <td className="px-6 py-4 text-right">
                    <button className="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                      <MoreHorizontal className="w-5 h-5" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="p-4 border-t border-gray-100">
          <Pagination total={100} currentPage={1} pageSize={10} onPageChange={() => {}} onPageSizeChange={() => {}} />
        </div>
      </div>
    </div>
  );
}

function StatCard({ icon: Icon, label, value, color }: { icon: any, label: string, value: string | number, color: string }) {
  const colorMap: Record<string, string> = {
    purple: "text-purple-600 bg-purple-50 border-purple-100",
    emerald: "text-emerald-600 bg-emerald-50 border-emerald-100",
    orange: "text-orange-600 bg-orange-50 border-orange-100",
  };
  
  return (
    <div className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
      <div className={`p-4 rounded-2xl border ${colorMap[color]}`}>
        <Icon className="w-6 h-6" />
      </div>
      <div>
        <div className="text-2xl font-black text-[#111827]">{value}</div>
        <div className="text-sm font-bold text-gray-500 uppercase tracking-wider">{label}</div>
      </div>
    </div>
  );
}
