const API_URL = process.env.NEXT_PUBLIC_API_URL || '/vendedor'; // Note: Clientes is often under vendedor/master prefix

export interface Cliente {
  id: number;
  nome_igreja: string;
  nome_pastor: string;
  email: string | null;
  whatsapp: string | null;
  status: 'ativo' | 'pendente' | 'inadimplente' | 'cancelado' | 'churn';
  documento: string;
  created_at: string;
  vendas_count?: number;
}

export interface ClienteStats {
  total: number;
  ativos: number;
  pendentes: number;
  inadimplentes: number;
  churn: number;
  cancelados: number;
}

export async function getClientes(params: {
  busca?: string;
  status?: string;
  page?: number;
} = {}): Promise<{ clientes: { data: Cliente[] }; cards: ClienteStats }> {
  const query = new URLSearchParams(params as any);
  const res = await fetch(`${API_URL}/clientes?${query}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
  });
  if (!res.ok) throw new Error('Failed to fetch clientes');
  return res.json();
}

export async function getCliente(id: number): Promise<{ 
  cliente: Cliente; 
  vendas: any[]; 
  pagamentos: any[]; 
  metrics: any 
}> {
  const res = await fetch(`${API_URL}/clientes/${id}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
  });
  if (!res.ok) throw new Error('Failed to fetch cliente details');
  return res.json();
}

export async function updateClienteStatus(id: number, status: string): Promise<any> {
  const res = await fetch(`${API_URL}/clientes/${id}/status`, {
    method: 'PATCH',
    headers: { 
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': (typeof document !== 'undefined' ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') : '') || ''
    },
    credentials: 'include',
    body: JSON.stringify({ status })
  });
  if (!res.ok) throw new Error('Failed to update status');
  return res.json();
}
