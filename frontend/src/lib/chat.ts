const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export interface ChatContact {
  id: number;
  phone: string;
  name: string | null;
  avatar_url: string | null;
  source: string;
  conversations: ChatConversation[];
}

export interface ChatConversation {
  id: number;
  contact_id: number;
  vendedor_id: number | null;
  status: 'open' | 'closed';
  atendimento_status: 'nao_atendido' | 'atendido';
  is_resolved: boolean;
  last_inbound_at: string | null;
  last_outbound_at: string | null;
  unread_count: number;
  contact: ChatContact;
  vendedor: { id: number; user: { name: string } } | null;
}

export interface ChatMessage {
  id: number;
  conversation_id: number;
  direction: 'inbound' | 'outbound';
  content: string;
  type: 'text' | 'media';
  created_at: string;
}

export interface ChatStats {
  total: number;
  open: number;
  closed: number;
  nao_atendido: number;
  atendido: number;
  resolved: number;
}

export async function getChatStats(): Promise<ChatStats> {
  const res = await fetch(`${API_URL}/chat/stats`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch stats');
  return res.json();
}

export async function getConversations(params: {
  status?: string;
  atendimento?: string;
}): Promise<{ data: ChatConversation[] }> {
  const query = new URLSearchParams(params as Record<string, string>);
  const res = await fetch(`${API_URL}/chat/conversations?${query}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch conversations');
  return res.json();
}

export async function getConversation(id: number): Promise<{ conversation: ChatConversation; messages: { data: ChatMessage[] } }> {
  const res = await fetch(`${API_URL}/chat/conversations/${id}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch conversation');
  return res.json();
}

export async function sendMessage(conversationId: number, message: string, mediaUrl?: string): Promise<ChatMessage> {
  const res = await fetch(`${API_URL}/chat/conversations/${conversationId}/message`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ message, media_url: mediaUrl }),
  });
  if (!res.ok) throw new Error('Failed to send message');
  return res.json();
}

export async function resolveConversation(id: number): Promise<ChatConversation> {
  const res = await fetch(`${API_URL}/chat/conversations/${id}/resolve`, {
    method: 'POST',
    credentials: 'include',
  });
  if (!res.ok) throw new Error('Failed to resolve conversation');
  return res.json();
}

export async function transferConversation(id: number, vendedorId: number): Promise<ChatConversation> {
  const res = await fetch(`${API_URL}/chat/conversations/${id}/transfer`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ vendedor_id: vendedorId }),
  });
  if (!res.ok) throw new Error('Failed to transfer conversation');
  return res.json();
}

export async function markAsRead(conversationId: number, vendedorId: number): Promise<{ marked: number }> {
  const res = await fetch(`${API_URL}/chat/conversations/${conversationId}/read`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ vendedor_id: vendedorId }),
  });
  if (!res.ok) throw new Error('Failed to mark as read');
  return res.json();
}