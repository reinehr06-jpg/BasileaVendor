"use client";

import React, { useState } from "react";
import { 
  Users, 
  Search, 
  Filter, 
  UserPlus,
  Mail,
  Phone,
  Briefcase,
  Star,
  MoreVertical,
  TrendingUp,
  Award
} from "lucide-react";
import Pagination from "@/components/Pagination";

export default function VendedoresPage() {
  const [searchTerm, setSearchTerm] = useState("");

  const vendedores = [
    { id: 1, nome: "Vinicius Reinehr", email: "vinicius@basileia.global", telefone: "(11) 99999-9999", cargo: "Gestor Comercial", status: "Ativo", vendasMes: 45, conversao: "12%", avatar: "V" },
    { id: 2, nome: "Maria Silva", email: "maria@basileia.global", telefone: "(11) 98888-8888", cargo: "Vendedora Senior", status: "Ativo", vendasMes: 32, conversao: "9%", avatar: "M" },
    { id: 3, nome: "João Pedro", email: "joao@basileia.global", telefone: "(21) 97777-7777", cargo: "Vendedor Junior", status: "Inativo", vendasMes: 0, conversao: "0%", avatar: "J" },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <Users className="w-8 h-8 text-purple-600" />
            Equipe Comercial
          </h2>
          <p className="text-gray-500 mt-1 font-medium">
            Gerencie seus vendedores, metas e comissionamento.
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-600/20">
            <UserPlus className="w-5 h-5" /> Novo Vendedor
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {/* RANKING HIGHLIGHT */}
        <div className="lg:col-span-1 space-y-4">
          <div className="bg-gradient-to-br from-amber-400 to-orange-500 rounded-3xl p-6 text-white shadow-xl shadow-orange-500/20">
            <div className="flex justify-between items-start mb-4">
              <Award className="w-8 h-8 text-amber-100" />
              <span className="text-xs font-bold uppercase tracking-wider bg-black/10 px-2 py-1 rounded-lg">Top 1</span>
            </div>
            <h3 className="font-bold text-2xl mb-1">Vinicius Reinehr</h3>
            <p className="text-amber-100 text-sm font-medium mb-6">Destaque do Mês</p>
            
            <div className="space-y-2">
              <div className="flex justify-between items-center text-sm">
                <span className="text-amber-100">Vendas</span>
                <span className="font-bold">45 fechadas</span>
              </div>
              <div className="flex justify-between items-center text-sm">
                <span className="text-amber-100">Conversão</span>
                <span className="font-bold">12%</span>
              </div>
            </div>
          </div>
        </div>

        {/* LISTAGEM DE VENDEDORES */}
        <div className="lg:col-span-3 bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
          <div className="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div className="relative w-full sm:max-w-md">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input 
                type="text" 
                placeholder="Buscar vendedor..." 
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all"
              />
            </div>
            <button className="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
              <Filter className="w-4 h-4" /> Filtros
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                <tr>
                  <th className="px-6 py-4">Vendedor</th>
                  <th className="px-6 py-4">Cargo / Nível</th>
                  <th className="px-6 py-4">Performance (Mês)</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Ações</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {vendedores.map(v => (
                  <tr key={v.id} className="hover:bg-gray-50 transition-colors group">
                    <td className="px-6 py-4 flex items-center gap-3">
                      <div className="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
                        {v.avatar}
                      </div>
                      <div>
                        <div className="font-bold text-[#111827]">{v.nome}</div>
                        <div className="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                          <span className="flex items-center gap-1"><Mail className="w-3 h-3"/> {v.email}</span>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-1.5 font-medium text-gray-700">
                        <Briefcase className="w-4 h-4 text-gray-400" /> {v.cargo}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-4">
                        <div>
                          <div className="text-xs text-gray-400 font-bold uppercase mb-0.5">Vendas</div>
                          <div className="font-black text-[#111827]">{v.vendasMes}</div>
                        </div>
                        <div>
                          <div className="text-xs text-gray-400 font-bold uppercase mb-0.5">Tx. Conv.</div>
                          <div className="font-bold text-emerald-600 flex items-center gap-1">
                            <TrendingUp className="w-3 h-3" /> {v.conversao}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${
                        v.status === 'Ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {v.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button className="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                        <MoreVertical className="w-5 h-5" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          
          <div className="p-4 border-t border-gray-100">
            <Pagination total={10} currentPage={1} pageSize={10} onPageChange={() => {}} onPageSizeChange={() => {}} />
          </div>
        </div>

      </div>
    </div>
  );
}
