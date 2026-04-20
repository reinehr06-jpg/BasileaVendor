'use client';

import { useState, useEffect } from 'react';
import { getClientes, Cliente, ClienteStats } from '@/lib/clientes';
import { 
  Users, 
  Search, 
  Filter, 
  ArrowUpRight, 
  MoreHorizontal, 
  Mail, 
  Phone, 
  Calendar,
  Building2,
  CheckCircle2,
  AlertCircle,
  XCircle,
  Clock
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion } from 'framer-motion';

export default function ClientesPage() {
  const [clientes, setClientes] = useState<Cliente[]>([]);
  const [stats, setStats] = useState<ClienteStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    fetchData();
  }, [searchTerm, statusFilter]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const data = await getClientes({ busca: searchTerm, status: statusFilter });
      setClientes(data.clientes.data);
      setStats(data.cards);
    } catch (e) {
      console.error('Failed to fetch clients', e);
    } finally {
      setLoading(false);
    }
  };

  const getStatusInfo = (status: Cliente['status']) => {
    switch (status) {
      case 'ativo': return { color: 'text-emerald-500 bg-emerald-500/10', icon: CheckCircle2, label: 'Ativo' };
      case 'pendente': return { color: 'text-amber-500 bg-amber-500/10', icon: Clock, label: 'Pendente' };
      case 'inadimplente': return { color: 'text-pink-500 bg-pink-500/10', icon: AlertCircle, label: 'Inadimplente' };
      case 'cancelado': return { color: 'text-slate-400 bg-slate-400/10', icon: XCircle, label: 'Cancelado' };
      case 'churn': return { color: 'text-red-500 bg-red-500/10', icon: XCircle, label: 'Churn' };
      default: return { color: 'text-slate-400 bg-slate-400/10', icon: Clock, label: status };
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Gestão de <span className="gradient-text">Clientes</span></h2>
          <p className="text-purple-600/50 mt-1 font-medium italic">Base de clientes ativos e métricas de retenção.</p>
        </div>
        <button className="px-6 py-3 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
           <Users className="w-5 h-5" />
           Novo Cliente
        </button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {stats && Object.entries(stats).map(([key, value], i) => (
          <motion.div
            key={key}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.05 }}
            className="p-5 rounded-2xl bg-surface border border-border/50 shadow-sm"
          >
            <p className="text-[10px] font-bold text-purple-400 uppercase tracking-widest mb-1">{key}</p>
            <h3 className="text-xl font-bold text-foreground">{value}</h3>
          </motion.div>
        ))}
      </div>

      <div className="glass rounded-3xl overflow-hidden border border-border/50 shadow-xl">
        <div className="p-6 border-b border-border/30 flex flex-col md:flex-row gap-4 justify-between">
          <div className="relative flex-1 max-w-md group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-purple-400 group-focus-within:text-purple-600 transition-colors" />
            <input 
              type="text" 
              placeholder="Buscar por nome, pastor ou documento..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-surface/50 border border-border/50 rounded-2xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>
          <div className="flex gap-3">
             <select 
               value={statusFilter}
               onChange={(e) => setStatusFilter(e.target.value)}
               className="bg-surface/50 border border-border/50 rounded-2xl px-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all min-w-[140px]"
             >
                <option value="">Todos Status</option>
                <option value="ativo">Ativos</option>
                <option value="pendente">Pendentes</option>
                <option value="inadimplente">Inadimplentes</option>
                <option value="churn">Churn</option>
             </select>
             <button className="p-2.5 rounded-2xl bg-surface/50 border border-border/50 text-purple-400 hover:text-primary transition-colors">
               <Filter className="w-5 h-5" />
             </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-surface-hover/50">
                <th className="px-6 py-4 text-[10px] font-bold text-purple-400 uppercase tracking-widest">Igreja / Cliente</th>
                <th className="px-6 py-4 text-[10px] font-bold text-purple-400 uppercase tracking-widest">Status</th>
                <th className="px-6 py-4 text-[10px] font-bold text-purple-400 uppercase tracking-widest text-center">Contatos</th>
                <th className="px-6 py-4 text-[10px] font-bold text-purple-400 uppercase tracking-widest">Documento</th>
                <th className="px-6 py-4 text-[10px] font-bold text-purple-400 uppercase tracking-widest text-right">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border/20">
              {loading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <tr key={i} className="animate-pulse">
                    <td className="px-6 py-5"><div className="h-4 bg-purple-100 rounded w-32" /></td>
                    <td className="px-6 py-5"><div className="h-6 bg-purple-100 rounded-full w-20" /></td>
                    <td className="px-6 py-5"><div className="h-4 bg-purple-100 rounded w-24 mx-auto" /></td>
                    <td className="px-6 py-5"><div className="h-4 bg-purple-100 rounded w-24" /></td>
                    <td className="px-6 py-5 text-right"><div className="h-8 bg-purple-100 rounded w-8 ml-auto" /></td>
                  </tr>
                ))
              ) : clientes.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-10 text-center text-purple-300 font-medium italic">Nenhum cliente encontrado com os filtros aplicados.</td>
                </tr>
              ) : (
                clientes.map((cliente, i) => {
                  const statusInfo = getStatusInfo(cliente.status);
                  return (
                    <motion.tr 
                      key={cliente.id}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      transition={{ delay: i * 0.02 }}
                      className="hover:bg-primary/[0.02] transition-colors group"
                    >
                      <td className="px-6 py-5">
                        <div className="flex items-center gap-3">
                          <div className={cn("w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold", statusInfo.color)}>
                            {cliente.nome_igreja?.[0] || 'I'}
                          </div>
                          <div>
                            <p className="font-bold text-foreground group-hover:text-primary transition-colors">{cliente.nome_igreja}</p>
                            <p className="text-xs text-purple-600/50 font-medium">{cliente.nome_pastor}</p>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-5">
                        <span className={cn("px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter flex items-center gap-1.5 w-fit", statusInfo.color)}>
                          <statusInfo.icon className="w-3 h-3" />
                          {statusInfo.label}
                        </span>
                      </td>
                      <td className="px-6 py-5">
                        <div className="flex justify-center gap-3">
                          {cliente.email && <Mail className="w-4 h-4 text-purple-300 hover:text-primary cursor-pointer transition-colors" />}
                          {cliente.whatsapp && <Phone className="w-4 h-4 text-purple-300 hover:text-emerald-500 cursor-pointer transition-colors" />}
                        </div>
                      </td>
                      <td className="px-6 py-5">
                        <div className="flex items-center gap-2 text-xs font-bold text-purple-600/60">
                           <Building2 className="w-3.5 h-3.5 opacity-40" />
                           {cliente.documento}
                        </div>
                      </td>
                      <td className="px-6 py-5 text-right">
                         <button className="p-2 rounded-xl hover:bg-surface-hover text-purple-400 hover:text-primary transition-all">
                           <MoreHorizontal className="w-5 h-5" />
                         </button>
                      </td>
                    </motion.tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
