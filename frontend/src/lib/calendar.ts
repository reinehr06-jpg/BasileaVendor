const API_URL = process.env.NEXT_PUBLIC_API_URL || '/api';

export interface CalendarEvent {
  id: number;
  lead_id: number;
  vendedor_id: number;
  scheduled_at: string;
  notes: string | null;
  status: 'pending' | 'completed' | 'cancelled';
  lead?: {
    id: number;
    name: string;
    phone: string;
    email: string;
  };
}

export async function getEvents(): Promise<CalendarEvent[]> {
  const res = await fetch(`${API_URL}/leads/agendamentos`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
  });
  if (!res.ok) throw new Error('Failed to fetch calendar events');
  return res.json();
}

export async function createEvent(data: {
  lead_id: number;
  scheduled_at: string;
  notes?: string;
}): Promise<CalendarEvent> {
  const res = await fetch(`${API_URL}/leads/${data.lead_id}/agendar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data)
  });
  if (!res.ok) throw new Error('Failed to create event');
  return res.json();
}

export async function completeEvent(id: number): Promise<CalendarEvent> {
  const res = await fetch(`${API_URL}/leads/agendamentos/${id}/complete`, {
    method: 'PATCH',
    headers: { 'Accept': 'application/json' },
    credentials: 'include'
  });
  if (!res.ok) throw new Error('Failed to complete event');
  return res.json();
}
