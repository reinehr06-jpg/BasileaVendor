'use client';

import { useState, useEffect } from 'react';
import { updateLeadStatus, getLeads, LeadInbound } from '@/lib/leads';

const ETAPAS = [
  { key: 'novo', label: 'Novo', color: 'bg-blue-500' },
  { key: 'contato', label: 'Em Contato', color: 'bg-yellow-500' },
  { key: 'proposta', label: 'Proposta', color: 'bg-purple-500' },
  { key: 'ganho', label: 'Ganho', color: 'bg-green-500' },
  { key: 'perdido', label: 'Perdido', color: 'bg-red-500' },
];

export default function KanbanPage() {
  const [columns, setColumns] = useState<Record<string, LeadInbound[]>>({
    novo: [], contato: [], proposta: [], ganho: [], perdido: []
  });
  const [loading, setLoading] = useState(true);
  const [draggedLead, setDraggedLead] = useState<LeadInbound | null>(null);

  useEffect(() => {
    loadKanban();
  }, []);

  const loadKanban = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/leads/kanban');
      const data = await res.json();
      setColumns(data);
    } catch (e) {
      console.error('Failed to load kanban', e);
    } finally {
      setLoading(false);
    }
  };

  const handleDragStart = (lead: LeadInbound) => {
    setDraggedLead(lead);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
  };

  const handleDrop = async (etapa: string) => {
    if (!draggedLead) return;
    
    try {
      await updateLeadStatus(draggedLead.id, etapa);
      loadKanban();
    } catch (e) {
      console.error('Failed to update', e);
    }
    setDraggedLead(null);
  };

  const getSourceIcon = (source: string) => {
    const icons: Record<string, string> = {
      meta_ads: '📘', linkedin: '💼', tiktok: '🎵', site: '🌐', whatsapp: '💬'
    };
    return icons[source] || '📞';
  };

  const getTimeAgo = (date: string) => {
    const diff = Date.now() - new Date(date).getTime();
    const hours = Math.floor(diff / (1000 * 60 * 60));
    if (hours < 1) return 'agora';
    if (hours < 24) return `${hours}h`;
    const days = Math.floor(hours / 24);
    return `${days}d`;
  };

  const isAtrasado = (createdAt: string) => {
    const hours = (Date.now() - new Date(createdAt).getTime()) / (1000 * 60 * 60);
    return hours > 48;
  };

  return (
    <div className="h-screen bg-gray-100 overflow-x-auto">
      <div className="p-4 bg-white border-b flex justify-between items-center">
        <h1 className="text-xl font-bold">Pipeline de Leads</h1>
        <button onClick={loadKanban} className="px-4 py-2 bg-blue-500 text-white rounded">
          Atualizar
        </button>
      </div>
      
      {loading ? (
        <div className="p-8 text-center">Carregando...</div>
      ) : (
        <div className="flex gap-4 p-4 h-[calc(100vh-80px)]">
          {ETAPAS.map(etapa => (
            <div
              key={etapa.key}
              className="flex-shrink-0 w-72 bg-gray-50 rounded-lg overflow-hidden flex flex-col"
              onDragOver={handleDragOver}
              onDrop={() => handleDrop(etapa.key)}
            >
              <div className={`p-3 ${etapa.color} text-white font-semibold flex justify-between`}>
                <span>{etapa.label}</span>
                <span className="bg-white/20 px-2 rounded">{columns[etapa.key]?.length || 0}</span>
              </div>
              
              <div className="flex-1 overflow-y-auto p-2 space-y-2">
                {columns[etapa.key]?.map(lead => (
                  <div
                    key={lead.id}
                    draggable
                    onDragStart={() => handleDragStart(lead)}
                    className={`p-3 bg-white rounded-lg shadow cursor-move hover:shadow-md transition-shadow ${
                      isAtrasado(lead.created_at) ? 'border-l-4 border-red-500' : ''
                    }`}
                  >
                    <div className="flex justify-between items-start">
                      <span className="font-medium text-sm truncate">{lead.name}</span>
                      <span className="text-lg">{getSourceIcon(lead.source)}</span>
                    </div>
                    <div className="text-xs text-gray-500 mt-1">
                      {lead.phone || lead.email}
                    </div>
                    <div className="flex justify-between items-center mt-2 text-xs text-gray-400">
                      <span>{getTimeAgo(lead.created_at)}</span>
                      {lead.vendedor?.user?.name && (
                        <span className="truncate max-w-[80px]">{lead.vendedor.user.name}</span>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}