'use client';

import { useState, useEffect } from 'react';
import { getLeads, updateLeadStatus, LeadInbound } from '@/lib/leads';

export default function LeadsPage() {
  const [leads, setLeads] = useState<LeadInbound[]>([]);
  const [filterStatus, setFilterStatus] = useState<string>('novo');
  const [loading, setLoading] = useState(false);
  const [selectedLead, setSelectedLead] = useState<LeadInbound | null>(null);

  useEffect(() => {
    loadLeads();
  }, [filterStatus]);

  const loadLeads = async () => {
    setLoading(true);
    try {
      const data = await getLeads({ status: filterStatus });
      setLeads(data);
    } catch (e) {
      console.error('Failed to load leads', e);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (leadId: number, newStatus: string) => {
    try {
      await updateLeadStatus(leadId, newStatus);
      loadLeads();
      setSelectedLead(null);
    } catch (e) {
      console.error('Failed to update status', e);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'novo': return 'bg-blue-100 text-blue-800';
      case 'contatado': return 'bg-yellow-100 text-yellow-800';
      case 'convertido': return 'bg-green-100 text-green-800';
      case 'perdido': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getSourceIcon = (source: string) => {
    switch (source) {
      case 'meta_ads': return '📘';
      case 'linkedin': return '💼';
      case 'tiktok': return '🎵';
      case 'site': return '🌐';
      default: return '📞';
    }
  };

  return (
    <div className="flex h-screen bg-gray-100">
      <div className="w-1/3 bg-white border-r">
        <div className="p-4 border-b">
          <h1 className="text-xl font-bold">Leads</h1>
          <div className="flex gap-2 mt-2 text-sm">
            {['novo', 'contatado', 'convertido', 'perdido'].map(s => (
              <button
                key={s}
                onClick={() => setFilterStatus(s)}
                className={`px-2 py-1 rounded ${filterStatus === s ? 'bg-blue-500 text-white' : 'bg-gray-100'}`}
              >
                {s.charAt(0).toUpperCase() + s.slice(1)}
              </button>
            ))}
          </div>
        </div>
        <div className="overflow-y-auto h-[calc(100vh-120px)]">
          {loading ? (
            <div className="p-4 text-center text-gray-500">Carregando...</div>
          ) : leads.length === 0 ? (
            <div className="p-4 text-center text-gray-500">Nenhum lead</div>
          ) : (
            leads.map(lead => (
              <div
                key={lead.id}
                className={`p-4 border-b cursor-pointer hover:bg-gray-50 ${selectedLead?.id === lead.id ? 'bg-blue-50' : ''}`}
                onClick={() => setSelectedLead(lead)}
              >
                <div className="flex justify-between items-center">
                  <span className="font-semibold">{lead.name}</span>
                  <span className="text-lg">{getSourceIcon(lead.source)}</span>
                </div>
                <div className="text-sm text-gray-500 mt-1">{lead.phone || lead.email}</div>
                <div className="text-xs text-gray-400 mt-1">
                  {new Date(lead.created_at).toLocaleString('pt-BR')}
                </div>
                {lead.utm_source && (
                  <div className="text-xs text-gray-400">
                    via {lead.utm_source} {!lead.utm_campaign ? '' : `/${lead.utm_campaign}`}
                  </div>
                )}
              </div>
            ))
          )}
        </div>
      </div>

      <div className="flex-1 flex flex-col">
        {selectedLead ? (
          <>
            <div className="p-4 bg-white border-b">
              <h2 className="font-bold text-lg">{selectedLead.name}</h2>
              <div className="text-sm text-gray-500 mt-1">
                {selectedLead.phone && <div>📞 {selectedLead.phone}</div>}
                {selectedLead.email && <div>📧 {selectedLead.email}</div>}
              </div>
              {selectedLead.message && (
                <div className="mt-2 p-2 bg-gray-50 rounded text-sm">
                  {selectedLead.message}
                </div>
              )}
              {selectedLead.meta && (
                <div className="mt-2 text-xs text-gray-400">
                  {Object.entries(selectedLead.meta).map(([k, v]) => (
                    <div key={k}>{k}: {String(v)}</div>
                  ))}
                </div>
              )}
            </div>

            <div className="p-4 bg-white border-t">
              <label className="block text-sm font-medium mb-2">Atualizar Status</label>
              <div className="flex gap-2">
                {['novo', 'contatado', 'convertido', 'perdido'].map(s => (
                  <button
                    key={s}
                    onClick={() => handleStatusChange(selectedLead.id, s)}
                    className={`px-3 py-1 rounded text-sm ${getStatusColor(s)} ${
                      selectedLead.status === s ? 'ring-2 ring-blue-500' : ''
                    }`}
                  >
                    {s.charAt(0).toUpperCase() + s.slice(1)}
                  </button>
                ))}
              </div>
            </div>
          </>
        ) : (
          <div className="flex-1 flex items-center justify-center text-gray-400">
            Selecione um lead para ver detalhes
          </div>
        )}
      </div>
    </div>
  );
}