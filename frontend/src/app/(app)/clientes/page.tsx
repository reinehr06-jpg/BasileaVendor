"use client";

import React, { useEffect, useState } from "react";
import { 
  CloudDownload, 
  Search, 
  Filter, 
  RotateCw, 
  FileText,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  UserX,
  MoreHorizontal
} from "lucide-react";
import CustomSelect from "@/components/CustomSelect";
import Pagination from "@/components/Pagination";
import { api } from "@/lib/api";

export default function ClientesAsaasPage() {
  const [activeTab, setActiveTab] = useState("todos");
  const [data, setData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [vendedorId, setVendedorId] = useState("");
  const [tipoCobranca, setTipoCobranca] = useState("");

  const fetchData = (tab = activeTab) => {
    setIsLoading(true);
    let url = `/clientes-asaas?aba=${tab}`;
    if (searchTerm) url += `&search=${searchTerm}`;
    if (vendedorId) url += `&vendedor_id=${vendedorId}`;
    if (tipoCobranca) url += `&tipo_cobranca=${tipoCobranca}`;

    api.get<any>(url)
      .then(res => setData(res))
      .catch(err => console.error(err))
      .finally(() => setIsLoading(false));
  };

  useEffect(() => {
    fetchData();
  }, [activeTab]);

  const handleSearch = () => fetchData();

  const totais = data?.kpis || { total: 0, ativos: 0, churn: 0, cancelados: 0, sem_vendedor: 0 };
  const clientes = data?.data || [];
  const vendedores = data?.vendedores || [];

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <CloudDownload className="w-8 h-8 text-purple-600" />
            Clientes Asaas
          </h2>
          <p className="text-gray-500 mt-1 font-medium">
            Sincronize, classifique e atribua comissões aos clientes pré-existentes no Asaas.
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center gap-2 px-4 py-2 bg-white border border-purple-600 text-purple-600 rounded-xl font-bold hover:bg-purple-50 transition-colors">
            <FileText className="w-4 h-4" /> Auditoria
          </button>
          <button className="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-colors">
            <RotateCw className="w-4 h-4" /> Sincronizar
          </button>
        </div>
      </div>

      {/* KPI CARDS */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <KpiCard label="Total" value={totais.total} color="purple" />
        <KpiCard label="Ativos" value={totais.ativos} color="green" />
        <KpiCard label="Churn" value={totais.churn} color="orange" />
        <KpiCard label="Cancelados" value={totais.cancelados} color="red" />
        <KpiCard label="Sem Vendedor" value={totais.sem_vendedor} color="purple" />
        <div className="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
          <div className="text-xl font-black text-emerald-800 mb-1">R$ --</div>
          <div className="text-xs font-bold text-emerald-700 uppercase tracking-wider">Comissão Total</div>
        </div>
      </div>

      {/* FILTROS E ABAS */}
      <div className="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div className="flex overflow-x-auto border-b border-gray-100 bg-gray-50">
          <TabButton active={activeTab === 'todos'} onClick={() => setActiveTab('todos')} label="Todos" count={totais.total} />
          <TabButton active={activeTab === 'ativos'} onClick={() => setActiveTab('ativos')} label="Ativos" count={totais.ativos} />
          <TabButton active={activeTab === 'churn'} onClick={() => setActiveTab('churn')} label="Churn" count={totais.churn} />
          <TabButton active={activeTab === 'cancelados'} onClick={() => setActiveTab('cancelados')} label="Cancelados" count={totais.cancelados} />
          <TabButton active={activeTab === 'sem_vendedor'} onClick={() => setActiveTab('sem_vendedor')} label="Sem Vendedor" count={totais.sem_vendedor} />
        </div>
        
        <div className="p-4 flex flex-col md:flex-row gap-4 items-end">
          <div className="flex-1 w-full">
            <label className="text-xs font-bold text-gray-500 uppercase block mb-1">Buscar</label>
            <div className="relative">
              <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
              <input 
                type="text" 
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Nome, CPF, email..." 
                className="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all"
              />
            </div>
          </div>
          <div className="w-full md:w-48">
            <label className="text-xs font-bold text-gray-500 uppercase block mb-1">Vendedor</label>
            <select 
              value={vendedorId}
              onChange={(e) => setVendedorId(e.target.value)}
              className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 transition-all"
            >
              <option value="">Todos</option>
              <option value="sem_vendedor">Sem vendedor</option>
              {vendedores.map((v: any) => (
                <option key={v.id} value={v.id}>{v.nome}</option>
              ))}
            </select>
          </div>
          <div className="w-full md:w-48">
            <label className="text-xs font-bold text-gray-500 uppercase block mb-1">Cobrança</label>
            <select 
              value={tipoCobranca}
              onChange={(e) => setTipoCobranca(e.target.value)}
              className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 transition-all"
            >
              <option value="">Todos</option>
              <option value="subscription">Assinatura</option>
              <option value="installment">Parcelamento</option>
              <option value="avulso">Avulso</option>
            </select>
          </div>
          <button onClick={handleSearch} className="px-6 py-2 bg-purple-600 text-white rounded-xl font-bold text-sm hover:bg-purple-700 transition-colors flex items-center gap-2 h-10">
            <Filter className="w-4 h-4" /> Filtrar
          </button>
        </div>

        {/* TABELA */}
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm whitespace-nowrap">
            <thead className="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
              <tr>
                <th className="px-6 py-4">Cliente</th>
                <th className="px-6 py-4">Status / Tipo</th>
                <th className="px-6 py-4">Vendedor Atual</th>
                <th className="px-6 py-4 text-center">Cadastro Asaas</th>
                <th className="px-6 py-4 text-right">Ação</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {clientes.map(c => (
                <tr key={c.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="font-bold text-[#111827]">{c.nome}</div>
                    <div className="text-xs text-gray-500">{c.email}</div>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${
                      c.status === 'ATIVO' ? 'bg-emerald-100 text-emerald-800' : 
                      c.status === 'CHURN' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {c.status}
                    </span>
                    <div className="text-xs text-gray-500 mt-1">{c.tipo}</div>
                  </td>
                  <td className="px-6 py-4">
                    {c.vendedor === 'Sem Vendedor' ? (
                      <span className="text-red-500 font-bold text-xs bg-red-50 px-2 py-1 rounded-md flex items-center gap-1 w-max">
                        <UserX className="w-3 h-3"/> {c.vendedor}
                      </span>
                    ) : (
                      <span className="font-medium text-gray-700">{c.vendedor}</span>
                    )}
                  </td>
                  <td className="px-6 py-4 text-center text-gray-500">
                    {c.data}
                  </td>
                  <td className="px-6 py-4 text-right">
                    <button className="px-4 py-1.5 bg-purple-100 text-purple-700 rounded-lg font-bold text-xs hover:bg-purple-200 transition-colors">
                      Detalhes
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div className="p-4 border-t border-gray-100">
          <Pagination currentPage={1} totalPages={5} onPageChange={() => {}} />
        </div>
      </div>
    </div>
  );
}

// Helpers
function KpiCard({ label, value, color }: { label: string, value: number, color: string }) {
  const colorMap: Record<string, string> = {
    purple: "bg-purple-50 border-purple-200 text-purple-700",
    green: "bg-emerald-50 border-emerald-200 text-emerald-700",
    orange: "bg-orange-50 border-orange-200 text-orange-700",
    red: "bg-red-50 border-red-200 text-red-700",
  };
  return (
    <div className={`border rounded-2xl p-4 text-center cursor-pointer hover:-translate-y-1 transition-transform ${colorMap[color] || colorMap.purple}`}>
      <div className="text-2xl font-black mb-1">{value}</div>
      <div className="text-xs font-bold uppercase tracking-wider opacity-80">{label}</div>
    </div>
  );
}

function TabButton({ active, onClick, label, count }: { active: boolean, onClick: () => void, label: string, count: number }) {
  return (
    <button 
      onClick={onClick}
      className={`flex items-center gap-2 px-6 py-4 text-sm font-bold border-b-2 transition-colors ${
        active ? 'border-purple-600 text-purple-600 bg-white' : 'border-transparent text-gray-500 hover:text-purple-600 hover:bg-white'
      }`}
    >
      {label}
      <span className={`px-2 py-0.5 rounded-full text-xs ${active ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'}`}>
        {count}
      </span>
    </button>
  );
}
