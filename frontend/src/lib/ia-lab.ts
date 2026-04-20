const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export interface IAProvider {
  id: number;
  name: string;
  provider: string;
}

export interface IAEvaluation {
  id: number;
  ia_model: string;
  prompt: string;
  response: string;
  approved: boolean;
  disapproval_reason?: string;
  created_at: string;
}

export async function getProviders(): Promise<IAProvider[]> {
  const res = await fetch(`${API_URL}/ia-lab/providers`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch providers');
  return res.json();
}

export async function testPrompt(providerId: number, prompt: string): Promise<{ response: string; model: string }> {
  const res = await fetch(`${API_URL}/ia-lab/test`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ provider_id: providerId, prompt }),
  });
  if (!res.ok) throw new Error('Failed to test prompt');
  return res.json();
}

export async function evaluatePrompt(data: {
  ia_model: string;
  prompt: string;
  response: string;
  approved: boolean;
  disapproval_reason?: string;
}): Promise<any> {
  const res = await fetch(`${API_URL}/ia-lab/evaluate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error('Failed to evaluate');
  return res.json();
}

export async function getHistory(): Promise<{ data: IAEvaluation[] }> {
  const res = await fetch(`${API_URL}/ia-lab/history`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch history');
  return res.json();
}
