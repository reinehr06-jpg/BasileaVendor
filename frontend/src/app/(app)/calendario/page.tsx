"use client";

import React, { useState } from "react";
import { 
  Calendar as CalendarIcon, 
  ChevronLeft, 
  ChevronRight, 
  Plus,
  Video,
  Phone,
  Users
} from "lucide-react";

export default function CalendarioPage() {
  const [view, setView] = useState("mês");

  const dias = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
  // Mock simple month
  const grid = Array.from({ length: 35 }, (_, i) => i - 2);

  return (
    <div className="space-y-6 animate-in fade-in duration-500 h-[calc(100vh-8rem)] flex flex-col">
      
      {/* HEADER */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0">
        <div>
          <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
            <CalendarIcon className="w-8 h-8 text-purple-600" />
            Calendário de Reuniões
          </h2>
        </div>
        <div className="flex gap-3 items-center">
          <div className="flex bg-gray-100 rounded-xl p-1">
            {['dia', 'semana', 'mês'].map(v => (
              <button 
                key={v}
                onClick={() => setView(v)}
                className={`px-4 py-1.5 rounded-lg text-sm font-bold capitalize transition-all ${
                  view === v ? 'bg-white text-[#111827] shadow-sm' : 'text-gray-500 hover:text-[#111827]'
                }`}
              >
                {v}
              </button>
            ))}
          </div>
          <button className="flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-600/20">
            <Plus className="w-5 h-5" /> Agendar
          </button>
        </div>
      </div>

      <div className="flex-1 bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm flex flex-col">
        
        {/* Calendar Header */}
        <div className="flex items-center justify-between p-6 border-b border-gray-100 shrink-0">
          <h3 className="text-2xl font-black text-[#111827]">Junho 2026</h3>
          <div className="flex items-center gap-2">
            <button className="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-600">
              <ChevronLeft className="w-5 h-5" />
            </button>
            <button className="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 font-bold text-sm">
              Hoje
            </button>
            <button className="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-600">
              <ChevronRight className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Calendar Grid */}
        <div className="flex-1 flex flex-col bg-gray-50">
          <div className="grid grid-cols-7 border-b border-gray-200 bg-white shrink-0">
            {dias.map(dia => (
              <div key={dia} className="py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">
                {dia}
              </div>
            ))}
          </div>
          <div className="grid grid-cols-7 flex-1 auto-rows-fr">
            {grid.map((dia, i) => {
              const isCurrentMonth = dia > 0 && dia <= 30;
              const isToday = dia === 15;
              
              return (
                <div key={i} className={`border-b border-r border-gray-200 p-2 transition-colors hover:bg-gray-50 ${isCurrentMonth ? 'bg-white' : 'bg-gray-50/50'}`}>
                  <div className={`text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full mb-1 ${
                    isToday ? 'bg-purple-600 text-white shadow-md' : (isCurrentMonth ? 'text-gray-700' : 'text-gray-300')
                  }`}>
                    {dia > 0 && dia <= 30 ? dia : (dia <= 0 ? 31 + dia : dia - 30)}
                  </div>
                  
                  {/* Mocks de Eventos */}
                  {isCurrentMonth && dia === 10 && (
                    <div className="bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold p-1.5 rounded-lg mb-1 truncate cursor-pointer hover:bg-blue-100 transition-colors flex items-center gap-1">
                      <Video className="w-3 h-3 shrink-0" /> Call Igreja Vida
                    </div>
                  )}
                  {isCurrentMonth && dia === 15 && (
                    <>
                      <div className="bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold p-1.5 rounded-lg mb-1 truncate cursor-pointer hover:bg-emerald-100 transition-colors flex items-center gap-1">
                        <Phone className="w-3 h-3 shrink-0" /> Follow-up Pr. Marcos
                      </div>
                      <div className="bg-purple-50 border border-purple-100 text-purple-700 text-xs font-bold p-1.5 rounded-lg truncate cursor-pointer hover:bg-purple-100 transition-colors flex items-center gap-1">
                        <Users className="w-3 h-3 shrink-0" /> Reunião Equipe
                      </div>
                    </>
                  )}
                </div>
              );
            })}
          </div>
        </div>

      </div>
    </div>
  );
}
