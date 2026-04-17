export interface LeadInbound {
  id: number;
  name: string;
  phone: string | null;
  email: string | null;
  message: string | null;
  source: string;
  status: 'novo' | 'contatado' | 'convertido' | 'perdido';
  etapa: 'novo' | 'contato' | 'proposta' | 'ganho' | 'perdido';
  vendedor: { id: number; user: { name: string } } | null;
  created_at: string;
  meta: Record<string, unknown>;
  utm_source: string | null;
  utm_medium: string | null;
  utm_campaign: string | null;
  page_url: string | null;
}

export interface LeadDashboard {
  total: number;
  por_etapa: Record<string, number>;
  por_canal: Record<string, number>;
  tempo_medio_primeiro_contato_min: number;
  atrasados: number;
}

const API_URL = process.env.NEXT_PUBLIC_API_URL || '/api';

export async function getLeads(params: {
  status?: string;
  etapa?: string;
  source?: string;
  search?: string;
}): Promise<any> {
  const query = new URLSearchParams(params as Record<string, string>);
  const res = await fetch(`${API_URL}/leads?${query}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch leads');
  return res.json();
}

export async function getLead(id: number): Promise<LeadInbound> {
  const res = await fetch(`${API_URL}/leads/${id}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch lead');
  return res.json();
}

export async function updateLeadStatus(id: number, status: string): Promise<LeadInbound> {
  const res = await fetch(`${API_URL}/leads/${id}/status`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ status }),
  });
  if (!res.ok) throw new Error('Failed to update lead');
  return res.json();
}

export async function updateLeadEtapa(id: number, etapa: string, motivoPerda?: string): Promise<LeadInbound> {
  const res = await fetch(`${API_URL}/leads/${id}/etapa`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ etapa, motivo_perda: motivoPerda }),
  });
  if (!res.ok) throw new Error('Failed to update etapa');
  return res.json();
}

export async function assignLeadToVendedor(id: number, vendedorId: number, motivo?: string): Promise<LeadInbound> {
  const res = await fetch(`${API_URL}/leads/${id}/transferir`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ vendedor_id: vendedorId, motivo }),
  });
  if (!res.ok) throw new Error('Failed to assign lead');
  return res.json();
}

export async function scheduleLead(id: number, scheduledAt: string, notes?: string): Promise<any> {
  const res = await fetch(`${API_URL}/leads/${id}/agendar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ scheduled_at: scheduledAt, notes }),
  });
  if (!res.ok) throw new Error('Failed to schedule');
  return res.json();
}

export async function getDashboard(): Promise<LeadDashboard> {
  const res = await fetch(`${API_URL}/leads/dashboard`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch dashboard');
  return res.json();
}

export async function exportLeads(params: {
  status?: string;
  etapa?: string;
  source?: string;
}): Promise<Blob> {
  const query = new URLSearchParams(params as Record<string, string>);
  const res = await fetch(`${API_URL}/leads/export?${query}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to export');
  return res.blob();
}