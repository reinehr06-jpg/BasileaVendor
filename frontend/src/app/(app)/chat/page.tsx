"use client";

import React, { useState } from "react";
import { 
  MessageSquare, 
  Search, 
  Phone, 
  Video, 
  MoreVertical,
  Paperclip,
  Image as ImageIcon,
  Send,
  Check,
  CheckCheck
} from "lucide-react";

export default function ChatPage() {
  const [activeChat, setActiveChat] = useState(1);

  const contatos = [
    { id: 1, nome: "Pr. Marcos (Vida Nova)", msg: "Podemos fechar amanhã?", time: "10:42", unread: 2, online: true, avatar: "M" },
    { id: 2, nome: "Ana Julia", msg: "Entendi, vou falar com a liderança.", time: "Ontem", unread: 0, online: false, avatar: "A" },
    { id: 3, nome: "Equipe Comercial", msg: "Vinicius: Fechado o contrato!", time: "Terça", unread: 0, online: true, avatar: "E" },
  ];

  return (
    <div className="animate-in fade-in duration-500 h-[calc(100vh-6rem)] flex -mx-4 md:-mx-6 lg:-mx-8 -my-4 md:-my-6 lg:-my-8">
      
      {/* SIDEBAR DE CONTATOS */}
      <div className="w-full md:w-80 lg:w-96 bg-white border-r border-gray-200 flex flex-col shrink-0">
        <div className="p-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="text-xl font-bold text-[#111827] flex items-center gap-2">
            <MessageSquare className="w-6 h-6 text-emerald-500" />
            WhatsApp
          </h2>
          <button className="p-2 text-gray-400 hover:bg-gray-50 rounded-full transition-colors">
            <MoreVertical className="w-5 h-5" />
          </button>
        </div>

        <div className="p-4 border-b border-gray-100 bg-gray-50/50">
          <div className="relative">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input 
              type="text" 
              placeholder="Buscar conversa..." 
              className="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all"
            />
          </div>
        </div>

        <div className="flex-1 overflow-y-auto">
          {contatos.map(c => (
            <div 
              key={c.id} 
              onClick={() => setActiveChat(c.id)}
              className={`p-4 flex gap-3 cursor-pointer transition-colors border-b border-gray-50 ${
                activeChat === c.id ? 'bg-emerald-50' : 'hover:bg-gray-50'
              }`}
            >
              <div className="relative">
                <div className="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center text-lg">
                  {c.avatar}
                </div>
                {c.online && <div className="w-3 h-3 bg-emerald-500 border-2 border-white rounded-full absolute bottom-0 right-0"></div>}
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex justify-between items-baseline mb-0.5">
                  <h3 className="font-bold text-[#111827] truncate text-sm">{c.nome}</h3>
                  <span className={`text-xs ${c.unread > 0 ? 'text-emerald-600 font-bold' : 'text-gray-400'}`}>{c.time}</span>
                </div>
                <div className="flex justify-between items-center">
                  <p className={`text-sm truncate ${c.unread > 0 ? 'text-[#111827] font-medium' : 'text-gray-500'}`}>
                    {c.msg}
                  </p>
                  {c.unread > 0 && (
                    <span className="w-5 h-5 bg-emerald-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center shrink-0 ml-2">
                      {c.unread}
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* ÁREA DO CHAT */}
      <div className="flex-1 bg-[#EFEAE2] flex flex-col relative hidden md:flex">
        {/* Pattern de Fundo do WhatsApp (simulado) */}
        <div className="absolute inset-0 opacity-10 bg-[url('https://i.pinimg.com/originals/8f/ba/cb/8fbacbd464e996966eb9d4a6b7a9c21e.jpg')] bg-repeat" style={{ backgroundSize: '400px' }}></div>
        
        {/* Chat Header */}
        <div className="bg-white px-6 py-3 border-b border-gray-200 flex justify-between items-center relative z-10 shrink-0">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center">
              M
            </div>
            <div>
              <h3 className="font-bold text-[#111827]">Pr. Marcos (Vida Nova)</h3>
              <p className="text-xs text-emerald-600 font-medium">Online</p>
            </div>
          </div>
          <div className="flex items-center gap-4 text-gray-500">
            <button className="hover:text-emerald-600 transition-colors"><Video className="w-5 h-5" /></button>
            <button className="hover:text-emerald-600 transition-colors"><Phone className="w-5 h-5" /></button>
            <div className="w-px h-6 bg-gray-200 mx-1"></div>
            <button className="hover:text-emerald-600 transition-colors"><Search className="w-5 h-5" /></button>
            <button className="hover:text-emerald-600 transition-colors"><MoreVertical className="w-5 h-5" /></button>
          </div>
        </div>

        {/* Mensagens */}
        <div className="flex-1 overflow-y-auto p-6 flex flex-col gap-4 relative z-10">
          <div className="text-center my-2">
            <span className="bg-[#E1F3FB] text-gray-600 text-xs px-3 py-1 rounded-lg uppercase shadow-sm">Hoje</span>
          </div>

          {/* Msg Recebida */}
          <div className="flex justify-start">
            <div className="bg-white max-w-[70%] rounded-2xl rounded-tl-none p-3 shadow-sm relative">
              <p className="text-[#111827] text-sm leading-relaxed mb-3">
                Bom dia, Vinicius! Tudo bem? Dei uma olhada na proposta que você enviou.
              </p>
              <div className="text-[10px] text-gray-400 text-right absolute bottom-1 right-2">
                10:40
              </div>
            </div>
          </div>

          {/* Msg Recebida */}
          <div className="flex justify-start">
            <div className="bg-white max-w-[70%] rounded-2xl rounded-tl-none p-3 shadow-sm relative">
              <p className="text-[#111827] text-sm leading-relaxed mb-3">
                Achei bem interessante a parte da automatização do dízimo pelo Asaas. Podemos fechar amanhã?
              </p>
              <div className="text-[10px] text-gray-400 text-right absolute bottom-1 right-2">
                10:42
              </div>
            </div>
          </div>

          {/* Msg Enviada (Digitando...) */}
          <div className="flex justify-end mt-4">
            <div className="bg-[#D9FDD3] max-w-[70%] rounded-2xl rounded-tr-none p-3 shadow-sm relative">
              <p className="text-[#111827] text-sm leading-relaxed mb-3">
                Maravilha Pastor! Sim, amanhã de manhã posso enviar o contrato. 
              </p>
              <div className="flex items-center gap-1 text-[10px] text-gray-500 absolute bottom-1 right-2">
                10:45 <CheckCheck className="w-3 h-3 text-[#53BDEB]" />
              </div>
            </div>
          </div>

        </div>

        {/* Input Area */}
        <div className="bg-[#F0F2F5] px-4 py-3 flex items-end gap-3 relative z-10 shrink-0">
          <button className="p-2.5 text-gray-500 hover:text-emerald-600 transition-colors">
            <Paperclip className="w-6 h-6" />
          </button>
          <button className="p-2.5 text-gray-500 hover:text-emerald-600 transition-colors hidden sm:block">
            <ImageIcon className="w-6 h-6" />
          </button>
          
          <div className="flex-1 bg-white rounded-2xl border border-gray-200 focus-within:border-emerald-500 overflow-hidden flex items-center px-2">
            <input 
              type="text" 
              placeholder="Digite uma mensagem..." 
              className="w-full py-3 px-2 outline-none text-sm"
            />
          </div>

          <button className="w-12 h-12 bg-emerald-500 text-white rounded-full flex items-center justify-center hover:bg-emerald-600 transition-colors shadow-md shrink-0">
            <Send className="w-5 h-5 ml-1" />
          </button>
        </div>

      </div>
    </div>
  );
}
