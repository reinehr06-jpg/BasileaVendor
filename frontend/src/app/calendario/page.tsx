'use client';

import React, { useState, useEffect } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';
import { getEvents, CalendarEvent, createEvent } from '@/lib/calendar';
import { 
  Calendar as CalendarIcon, 
  Plus, 
  Users, 
  MessageSquare, 
  Clock, 
  MapPin,
  ChevronLeft,
  ChevronRight,
  Filter
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion, AnimatePresence } from 'framer-motion';

export default function CalendarioPage() {
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    setLoading(true);
    try {
      const data = await getEvents();
      setEvents(data);
    } catch (e) {
      console.error('Failed to fetch events', e);
    } finally {
      setLoading(false);
    }
  };

  const calendarEvents = events.map(ev => ({
    id: String(ev.id),
    title: ev.lead?.name || 'Agendamento',
    start: ev.scheduled_at,
    extendedProps: { ...ev },
    backgroundColor: '#6d28d9',
    borderColor: '#5b21b6',
  }));

  return (
    <div className="space-y-8 pb-12">
      <div className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Agenda de <span className="gradient-text">Follow-ups</span></h2>
          <p className="text-purple-600/50 mt-1 font-medium italic">Gerencie seus compromissos e acompanhamentos com leads.</p>
        </div>
        <div className="flex gap-4">
           <button className="px-5 py-2.5 bg-surface border border-border/50 text-purple-600 font-bold rounded-2xl flex items-center gap-2 hover:bg-surface-hover transition-all">
              <Filter className="w-4 h-4" />
              Filtros
           </button>
           <button 
             onClick={() => setIsModalOpen(true)}
             className="px-6 py-2.5 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2"
           >
              <Plus className="w-5 h-5" />
              Novo Follow-up
           </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {/* Sidebar / Mini Stats */}
        <div className="space-y-6">
           <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl">
              <h3 className="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
                 <Clock className="w-5 h-5 text-primary" />
                 Próximas Horas
              </h3>
              <div className="space-y-4">
                 {events.slice(0, 3).map((ev, i) => (
                    <div key={ev.id} className="p-3 rounded-2xl bg-surface/50 border border-border/30 hover:border-primary/30 transition-all group cursor-pointer">
                       <p className="text-[10px] font-bold text-primary uppercase tracking-widest mb-1">
                          {new Date(ev.scheduled_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                       </p>
                       <p className="text-sm font-bold text-foreground group-hover:text-primary transition-colors truncate">{ev.lead?.name}</p>
                       <p className="text-[10px] text-purple-600/40 font-medium truncate mt-0.5">{ev.notes || 'Sem observações'}</p>
                    </div>
                 ))}
                 {events.length === 0 && (
                   <p className="text-xs text-purple-300 italic text-center py-4">Sem compromissos próximos</p>
                 )}
              </div>
           </div>

           <div className="glass p-6 rounded-3xl border border-border/50 shadow-xl bg-primary/5">
              <h3 className="text-lg font-bold text-primary mb-2">Metas de Contato</h3>
              <div className="space-y-3">
                 <div className="h-2 bg-white/50 rounded-full overflow-hidden">
                    <motion.div initial={{ width: 0 }} animate={{ width: '65%' }} className="h-full bg-primary" />
                 </div>
                 <div className="flex justify-between text-[10px] font-bold text-purple-600/60 uppercase">
                    <span>13 feitos</span>
                    <span>Meta: 20</span>
                 </div>
              </div>
           </div>
        </div>

        {/* Main Calendar */}
        <div className="lg:col-span-3 glass p-8 rounded-3xl border border-border/50 shadow-2xl bg-white/40">
          <div className="calendar-container premium-calendar">
            <FullCalendar
              plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
              initialView="dayGridMonth"
              headerToolbar={{
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
              }}
              locale={ptBrLocale}
              events={calendarEvents}
              height="700px"
              expandRows={true}
              handleWindowResize={true}
              dayMaxEvents={true}
              selectable={true}
              eventClassNames="rounded-lg shadow-sm border-none px-2 py-0.5 text-xs font-bold"
              dayHeaderClassNames="text-[10px] uppercase font-bold text-purple-400 py-3 tracking-widest"
            />
          </div>
        </div>
      </div>

      <style jsx global>{`
        .premium-calendar .fc {
          --fc-border-color: rgba(233, 228, 255, 0.5);
          --fc-daygrid-event-dot-width: 8px;
          --fc-today-bg-color: rgba(109, 40, 217, 0.05);
          font-family: inherit;
        }
        .premium-calendar .fc-toolbar-title {
          font-weight: 800;
          font-size: 1.3rem !important;
          background: linear-gradient(135deg, #6d28d9 0%, #a78bfa 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
        .premium-calendar .fc-button {
          background: white !important;
          border: 1px solid rgba(233, 228, 255, 0.8) !important;
          color: #6d28d9 !important;
          font-weight: 700 !important;
          text-transform: capitalize !important;
          border-radius: 12px !important;
          padding: 8px 16px !important;
          font-size: 0.8rem !important;
          transition: all 0.2s !important;
          box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        .premium-calendar .fc-button:hover {
          background: #f5f3ff !important;
          border-color: #6d28d9 !important;
        }
        .premium-calendar .fc-button-active {
          background: #6d28d9 !important;
          color: white !important;
          border-color: #6d28d9 !important;
        }
        .premium-calendar .fc-scrollgrid {
          border-radius: 20px !important;
          overflow: hidden !important;
          border: none !important;
        }
        .premium-calendar .fc-col-header-cell {
          background: rgba(245, 243, 255, 0.5) !important;
        }
      `}</style>
    </div>
  );
}
