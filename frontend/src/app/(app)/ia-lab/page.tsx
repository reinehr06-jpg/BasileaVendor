"use client";

import React, { useState } from "react";
import { 
  Bot, 
  Sparkles,
  MessageSquare,
  Wand2,
  FileText,
  Search,
  Send,
  Loader2
} from "lucide-react";

export default function IALabPage() {
  const [prompt, setPrompt] = useState("");
  const [isGenerating, setIsGenerating] = useState(false);

  const handleGenerate = () => {
    if (!prompt) return;
    setIsGenerating(true);
    setTimeout(() => setIsGenerating(false), 2000);
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-500 max-w-5xl mx-auto h-[calc(100vh-8rem)] flex flex-col">
      
      {/* HEADER */}
      <div className="shrink-0 pb-2">
        <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
          <Bot className="w-8 h-8 text-purple-600" />
          IA Lab
        </h2>
        <p className="text-gray-500 mt-1 font-medium">
          Gere scripts de vendas, respostas para WhatsApp e analise perfis de clientes usando IA.
        </p>
      </div>

      <div className="flex-1 flex flex-col md:flex-row gap-6 min-h-0">
        
        {/* FERRAMENTAS RAPIDAS */}
        <div className="w-full md:w-1/3 flex flex-col gap-4 shrink-0 overflow-y-auto pr-2">
          <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Ferramentas Prontas</h3>
          
          <ToolCard 
            icon={MessageSquare} 
            title="Script de Quebra de Objeção" 
            desc="Gere uma resposta persuasiva para clientes dizendo que 'está caro'."
          />
          <ToolCard 
            icon={FileText} 
            title="Apresentação de Produto" 
            desc="Crie um texto formatado para apresentar os planos da Basileia."
          />
          <ToolCard 
            icon={Search} 
            title="Análise de Perfil" 
            desc="Cole o perfil do cliente e descubra os melhores gatilhos mentais."
          />
        </div>

        {/* CHAT / GERADOR */}
        <div className="flex-1 bg-white border border-gray-200 rounded-3xl p-6 shadow-sm flex flex-col">
          
          <div className="flex-1 overflow-y-auto mb-4 space-y-4">
            {/* Empty state animado */}
            {!isGenerating && (
              <div className="h-full flex flex-col items-center justify-center text-center opacity-50">
                <div className="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                  <Sparkles className="w-10 h-10 text-purple-400" />
                </div>
                <h3 className="text-lg font-bold text-gray-700">Como posso te ajudar a vender hoje?</h3>
                <p className="text-sm text-gray-500 max-w-sm mt-1">
                  Digite seu cenário abaixo ou escolha uma das ferramentas rápidas ao lado.
                </p>
              </div>
            )}

            {isGenerating && (
              <div className="flex items-start gap-4 animate-pulse">
                <div className="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                  <Bot className="w-5 h-5 text-purple-600" />
                </div>
                <div className="bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-none p-4 w-3/4">
                  <div className="h-4 bg-gray-200 rounded w-full mb-2"></div>
                  <div className="h-4 bg-gray-200 rounded w-5/6 mb-2"></div>
                  <div className="h-4 bg-gray-200 rounded w-4/6"></div>
                </div>
              </div>
            )}
          </div>

          {/* INPUT */}
          <div className="relative shrink-0 mt-auto">
            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-purple-600">
              <Wand2 className="w-5 h-5" />
            </div>
            <input 
              type="text" 
              placeholder="Ex: Crie um texto curto para convencer um pastor a automatizar sua igreja..." 
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleGenerate()}
              className="w-full pl-12 pr-14 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none transition-all shadow-inner"
            />
            <button 
              onClick={handleGenerate}
              className="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors shadow-md"
            >
              {isGenerating ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
            </button>
          </div>

        </div>

      </div>
    </div>
  );
}

function ToolCard({ icon: Icon, title, desc }: { icon: any, title: string, desc: string }) {
  return (
    <div className="bg-white border border-gray-200 rounded-2xl p-4 cursor-pointer hover:border-purple-300 hover:shadow-md transition-all group">
      <div className="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-3 group-hover:bg-purple-100 transition-colors">
        <Icon className="w-5 h-5 text-purple-600" />
      </div>
      <h4 className="font-bold text-[#111827] text-sm mb-1">{title}</h4>
      <p className="text-xs text-gray-500 leading-relaxed">{desc}</p>
    </div>
  );
}
