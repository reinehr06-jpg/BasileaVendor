'use client';

import { useState, useEffect } from 'react';
import { getLeads, updateLeadEtapa, getDashboard, LeadInbound, LeadDashboard } from '@/lib/leads';
import { 
  Target, 
  TrendingUp, 
  Clock, 
  AlertCircle, 
  MoreHorizontal, 
  Plus, 
  Search,
  ChevronRight,
  User,
  Facebook,
  Linkedin,
  Globe,
  Share2,
  Calendar,
  CheckCircle2
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion, AnimatePresence } from 'framer-motion';

const etapas = [
  { id: 'novo', name: 'Novo Lead', color: 'bg-blue-500' },
  { id: 'contato', name: 'Em Contato', color: 'bg-amber-500' },
  { id: 'proposta', name: 'Proposta Enviada', color: 'bg-indigo-500' },
  { id: 'ganho', name: 'Fechamento', color: 'bg-emerald-500' },
  { id: 'perdido', name: 'Perdido', color: 'bg-pink-500' },
];

export default function LeadsPage() {
  const [leads, setLeads] = useState<Record<string, LeadInbound[]>>({});
  const [dashboard, setDashboard] = useState<LeadDashboard | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedLead, setSelectedLead] = useState<LeadInbound | null>(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setLoading(true);
    try {
      const dash = await getDashboard();
      setDashboard(dash);
      
      const allLeads = await getLeads({});
      // Group by etapa
      const grouped = allLeads.data.reduce((acc: any, lead: any) => {
        const etaq = lead.etapa || 'novo';
        if (!acc[etaq]) acc[etaq] = [];
        acc[etaq].push(lead);
        return acc;
      }, {});
      setLeads(grouped);
    } catch (e) {
      console.error('Failed to load leads', e);
    } finally {
      setLoading(false);
    }
  };

  const moveLead = async (leadId: number, targetEtapa: string) => {
    try {
      await updateLeadEtapa(leadId, targetEtapa);
      loadData();
    } catch (e) {
      console.error('Failed to move lead', e);
    }
  };

  const getSourceIcon = (source: string) => {
    switch (source) {
      case 'meta_ads': return <Facebook className="w-4 h-4 text-blue-500" />;
      case 'linkedin': return <Linkedin className="w-4 h-4 text-primary" />;
      case 'site': return <Globe className="w-4 h-4 text-emerald-500" />;
      default: return <Share2 className="w-4 h-4 text-purple-400" />;
    }
  };

  return (
    <div className="space-y-8 pb-12">
      <div className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Pipeline de <span className="gradient-text">Vendas</span></h2>
          <p className="text-purple-600/50 mt-1 font-medium italic">Gerencie leads e oportunidades em tempo real.</p>
        </div>
        <div className="flex gap-4">
           <button className="px-6 py-2.5 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
              <Plus className="w-5 h-5" />
              Novo Lead
           </button>
        </div>
      </div>

      {/* Dashboard Summary */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl bg-white/40">
           <div className="flex items-center gap-4">
              <div className="p-3 rounded-2xl bg-primary/10 text-primary">
                 <Target className="w-6 h-6" />
              </div>
              <div>
                 <p className="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Total de Leads</p>
                 <h3 className="text-2xl font-bold text-foreground">{dashboard?.total || 0}</h3>
              </div>
           </div>
        </div>
        <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl bg-white/40">
           <div className="flex items-center gap-4">
              <div className="p-3 rounded-2xl bg-emerald-500/10 text-emerald-500">
                 <CheckCircle2 className="w-6 h-6" />
              </div>
              <div>
                 <p className="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Convertidos</p>
                 <h3 className="text-2xl font-bold text-foreground">{dashboard?.por_etapa?.['ganho'] || 0}</h3>
              </div>
           </div>
        </div>
        <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl bg-white/40">
           <div className="flex items-center gap-4">
              <div className="p-3 rounded-2xl bg-amber-500/10 text-amber-500">
                 <Clock className="w-6 h-6" />
              </div>
              <div>
                 <p className="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Atrasados</p>
                 <h3 className="text-2xl font-bold text-foreground">{dashboard?.atrasados || 0}</h3>
              </div>
           </div>
        </div>
        <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl bg-white/40">
           <div className="flex items-center gap-4">
              <div className="p-3 rounded-2xl bg-pink-500/10 text-pink-500">
                 <TrendingUp className="w-6 h-6" />
              </div>
              <div>
                 <p className="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Ticket Médio</p>
                 <h3 className="text-2xl font-bold text-foreground">R$ 2.450</h3>
              </div>
           </div>
        </div>
      </div>

      {/* Kanban Board */}
      <div className="flex gap-6 overflow-x-auto pb-8 custom-scrollbar min-h-[600px] items-start">
        {etapas.map((etapa) => (
          <div key={etapa.id} className="min-w-[320px] w-[320px] shrink-0">
            <div className="flex items-center justify-between mb-4 px-2">
               <div className="flex items-center gap-2">
                 <div className={cn("w-2 h-2 rounded-full", etapa.color)} />
                 <h3 className="text-sm font-bold text-foreground uppercase tracking-wider">{etapa.name}</h3>
                 <span className="text-[10px] font-black text-purple-300 bg-surface px-2 py-0.5 rounded-full border border-border/50">
                    {leads[etapa.id]?.length || 0}
                 </span>
               </div>
               <button className="p-1 px-2 rounded-lg hover:bg-surface-hover text-purple-300 transition-colors">
                  <MoreHorizontal className="w-4 h-4" />
               </button>
            </div>

            <div className="space-y-4">
               {loading ? (
                 Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="h-24 glass rounded-3xl border border-border/30 animate-pulse" />
                 ))
               ) : (
                 leads[etapa.id]?.map((lead, i) => (
                    <motion.div
                      key={lead.id}
                      layoutId={`lead-${lead.id}`}
                      initial={{ opacity: 0, scale: 0.9 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className="group cursor-grab active:cursor-grabbing"
                    >
                      <div className="glass p-4 rounded-3xl border border-border/50 shadow-sm hover:shadow-xl hover:border-primary/30 transition-all bg-white/60">
                         <div className="flex justify-between items-start mb-3">
                            <div className="flex gap-2">
                               {getSourceIcon(lead.source)}
                               <span className="text-[10px] font-bold text-purple-300 uppercase truncate max-w-[100px]">{lead.source}</span>
                            </div>
                            <span className="text-[9px] font-black text-purple-300 uppercase tracking-tighter">
                               {new Date(lead.created_at).toLocaleDateString([], { day: '2-digit', month: 'short' })}
                            </span>
                         </div>
                         <h4 className="font-bold text-foreground group-hover:text-primary transition-colors mb-1">{lead.name}</h4>
                         <p className="text-xs text-purple-600/50 font-medium truncate mb-4">{lead.phone || lead.email}</p>
                         
                         <div className="flex justify-between items-center pt-3 border-t border-border/30">
                            <div className="flex -space-x-2">
                               <div className="w-6 h-6 rounded-full bg-purple-100 border border-white flex items-center justify-center text-[10px] font-bold text-primary">
                                  {lead.vendedor?.user?.name?.[0] || <User className="w-3 h-3" />}
                               </div>
                            </div>
                            <div className="flex items-center gap-3">
                               {etapa.id !== 'ganho' && (
                                 <button onClick={() => moveLead(lead.id, 'ganho')} className="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-500 opacity-0 group-hover:opacity-100 transition-all">
                                    <CheckCircle2 className="w-4 h-4" />
                                 </button>
                               )}
                               <button className="p-1.5 rounded-lg hover:bg-surface-hover text-purple-300 opacity-0 group-hover:opacity-100 transition-all">
                                  <ChevronRight className="w-4 h-4" />
                               </button>
                            </div>
                         </div>
                      </div>
                    </motion.div>
                 ))
               )}
               {!loading && !leads[etapa.id]?.length && (
                 <div className="py-12 border-2 border-dashed border-border/30 rounded-3xl text-center">
                    <Target className="w-8 h-8 text-purple-100 mx-auto mb-2" />
                    <p className="text-xs text-purple-300 font-medium italic">Nenhum lead nesta etapa</p>
                 </div>
               )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}