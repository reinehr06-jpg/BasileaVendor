'use client';

import { useState, useEffect } from 'react';

interface LeadSchedule {
  id: number;
  scheduled_at: string;
  notes: string | null;
  status: string;
  lead: {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
  };
}

export default function AgendaPage() {
  const [schedules, setSchedules] = useState<LeadSchedule[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadSchedules();
  }, []);

  const loadSchedules = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/leads/agendamentos');
      const data = await res.json();
      setSchedules(data);
    } catch (e) {
      console.error('Failed to load schedules', e);
    } finally {
      setLoading(false);
    }
  };

  const completeSchedule = async (id: number) => {
    try {
      await fetch(`/api/leads/agendamentos/${id}/complete`, { method: 'PATCH' });
      loadSchedules();
    } catch (e) {
      console.error('Failed to complete', e);
    }
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleString('pt-BR', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  };

  const isOverdue = (date: string) => {
    return new Date(date) < new Date();
  };

  const isToday = (date: string) => {
    const d = new Date(date);
    const today = new Date();
    return d.toDateString() === today.toDateString();
  };

  return (
    <div className="h-screen bg-gray-100 p-4">
      <div className="bg-white rounded-lg shadow">
        <div className="p-4 border-b flex justify-between items-center">
          <h1 className="text-xl font-bold">Agenda de Follow-ups</h1>
          <button onClick={loadSchedules} className="px-4 py-2 bg-blue-500 text-white rounded">
            Atualizar
          </button>
        </div>

        {loading ? (
          <div className="p-8 text-center">Carregando...</div>
        ) : schedules.length === 0 ? (
          <div className="p-8 text-center text-gray-500">
            Nenhum agendamento pendente
          </div>
        ) : (
          <div className="divide-y">
            {schedules.map(schedule => (
              <div
                key={schedule.id}
                className={`p-4 flex justify-between items-center ${
                  isOverdue(schedule.scheduled_at) ? 'bg-red-50' : ''
                }`}
              >
                <div className="flex-1">
                  <div className="flex items-center gap-2">
                    <span className="font-medium">{schedule.lead.name}</span>
                    {isToday(schedule.scheduled_at) && (
                      <span className="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">
                        Hoje
                      </span>
                    )}
                    {isOverdue(schedule.scheduled_at) && (
                      <span className="px-2 py-0.5 bg-red-100 text-red-800 text-xs rounded">
                        Atrasado
                      </span>
                    )}
                  </div>
                  <div className="text-sm text-gray-500">
                    {schedule.lead.phone || schedule.lead.email}
                  </div>
                  <div className="text-sm text-gray-400 mt-1">
                    {schedule.notes || 'Ligar para olead'}
                  </div>
                  <div className="text-xs text-gray-400 mt-1">
                    {formatDate(schedule.scheduled_at)}
                  </div>
                </div>
                <button
                  onClick={() => completeSchedule(schedule.id)}
                  className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >
                  Concluir
                </button>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}