"use client";

import React, { useState } from "react";
import { 
  Users, 
  Search, 
  Filter, 
  UserPlus,
  Mail,
  Phone,
  Calendar,
  MessageCircle,
  MoreVertical,
  Activity,
  Flame,
  Snowflake
} from "lucide-react";
import Pagination from "@/components/Pagination";

export default function LeadsPage() {
  const [searchTerm, setSearchTerm] = useState("");
  const [activeTab, setActiveTab] = useState("todos");

  const leads = [
    { id: 1, nome: "Carlos Henrique", igreja: "Igreja Batista Fonte", email: "carlos@batistafonte.com", telefone: "(11) 98888-7777", temp: "hot", origem: "Instagram", data: "30/06/2026", status: "Novo" },
    { id: 2, nome: "Pr. Marcos", igreja: "Assembleia de Deus", email: "pr.marcos@ad.com", telefone: "(21) 97777-6666", temp: "warm", origem: "Google Ads", data: "29/06/2026", status: "Em Contato" },
    { id: 3, nome: "Ana Julia", igreja: "Comunidade Vida", email: "ana.vida@gmail.com", telefone: "(31) 96666-5555", temp: "cold", origem: "Indicação", data: "25/06/2026", status: "Perdido" },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <Users className="w-8 h-8 text-purple-600" />
            Gestão de Leads
          </h2>
          <p className="text-gray-500 mt-1 font-medium">
            Acompanhe as oportunidades, contatos e pipeline de vendas.
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-600/20">
            <UserPlus className="w-5 h-5" /> Novo Lead
          </button>
        </div>
      </div>

      {/* Tabela e Filtros */}
      <div className="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        
        {/* ABAS */}
        <div className="flex overflow-x-auto border-b border-gray-100 bg-gray-50">
          <TabButton active={activeTab === 'todos'} onClick={() => setActiveTab('todos')} label="Todos os Leads" count={342} />
          <TabButton active={activeTab === 'novos'} onClick={() => setActiveTab('novos')} label="Novos" count={28} />
          <TabButton active={activeTab === 'contato'} onClick={() => setActiveTab('contato')} label="Em Contato" count={85} />
          <TabButton active={activeTab === 'quentes'} onClick={() => setActiveTab('quentes')} label="Quentes" count={12} icon={Flame} />
        </div>

        <div className="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div className="relative w-full sm:max-w-md">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input 
              type="text" 
              placeholder="Buscar lead por nome, email ou telefone..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all"
            />
          </div>
          <button className="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
            <Filter className="w-4 h-4" /> Mais Filtros
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm whitespace-nowrap">
            <thead className="bg-white text-gray-500 text-xs uppercase font-bold tracking-wider border-b border-gray-100">
              <tr>
                <th className="px-6 py-4">Contato</th>
                <th className="px-6 py-4">Igreja / Organização</th>
                <th className="px-6 py-4">Temperatura</th>
                <th className="px-6 py-4">Origem</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4">Entrada</th>
                <th className="px-6 py-4 text-right">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {leads.map(lead => (
                <tr key={lead.id} className="hover:bg-gray-50 transition-colors group">
                  <td className="px-6 py-4">
                    <div className="font-bold text-[#111827]">{lead.nome}</div>
                    <div className="flex items-center gap-3 mt-1 text-xs text-gray-500">
                      <span className="flex items-center gap-1"><Mail className="w-3 h-3"/> {lead.email}</span>
                      <span className="flex items-center gap-1"><Phone className="w-3 h-3"/> {lead.telefone}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 font-medium text-gray-700">
                    {lead.igreja}
                  </td>
                  <td className="px-6 py-4">
                    {lead.temp === 'hot' && <span className="flex items-center gap-1 text-rose-600 font-bold bg-rose-50 px-2.5 py-1 rounded-full w-max text-xs"><Flame className="w-3 h-3"/> Quente</span>}
                    {lead.temp === 'warm' && <span className="flex items-center gap-1 text-orange-600 font-bold bg-orange-50 px-2.5 py-1 rounded-full w-max text-xs"><Activity className="w-3 h-3"/> Morno</span>}
                    {lead.temp === 'cold' && <span className="flex items-center gap-1 text-sky-600 font-bold bg-sky-50 px-2.5 py-1 rounded-full w-max text-xs"><Snowflake className="w-3 h-3"/> Frio</span>}
                  </td>
                  <td className="px-6 py-4">
                    <span className="text-xs font-bold text-gray-600 border border-gray-200 bg-white px-2 py-1 rounded-md shadow-sm">
                      {lead.origem}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${
                      lead.status === 'Novo' ? 'bg-purple-100 text-purple-800' : 
                      lead.status === 'Em Contato' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {lead.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-gray-500 flex items-center gap-1 mt-1.5">
                    <Calendar className="w-4 h-4" /> {lead.data}
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button className="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors tooltip-trigger" title="WhatsApp">
                        <MessageCircle className="w-4 h-4" />
                      </button>
                      <button className="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                        <MoreVertical className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="p-4 border-t border-gray-100">
          <Pagination currentPage={1} totalPages={15} onPageChange={() => {}} />
        </div>
      </div>
    </div>
  );
}

function TabButton({ active, onClick, label, count, icon: Icon }: { active: boolean, onClick: () => void, label: string, count: number, icon?: any }) {
  return (
    <button 
      onClick={onClick}
      className={`flex items-center gap-2 px-6 py-4 text-sm font-bold border-b-2 transition-colors ${
        active ? 'border-purple-600 text-purple-600 bg-white' : 'border-transparent text-gray-500 hover:text-purple-600 hover:bg-white'
      }`}
    >
      {Icon && <Icon className={`w-4 h-4 ${active ? 'text-purple-600' : 'text-gray-400'}`} />}
      {label}
      <span className={`px-2 py-0.5 rounded-full text-xs ${active ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'}`}>
        {count}
      </span>
    </button>
  );
}
