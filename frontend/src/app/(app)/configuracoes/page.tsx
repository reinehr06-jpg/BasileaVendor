"use client";

import React, { useState } from "react";
import { 
  Settings, 
  Key, 
  Link as LinkIcon, 
  ShieldCheck, 
  CreditCard,
  Bell,
  UserCog,
  Save,
  CheckCircle2,
  Plug
} from "lucide-react";

export default function ConfiguracoesPage() {
  const [activeTab, setActiveTab] = useState("geral");

  const tabs = [
    { id: "geral", label: "Geral", icon: Settings },
    { id: "integracoes", label: "Integrações", icon: Plug },
    { id: "pagamentos", label: "Pagamentos (Asaas)", icon: CreditCard },
    { id: "seguranca", label: "Segurança", icon: ShieldCheck },
    { id: "notificacoes", label: "Notificações", icon: Bell },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-500 max-w-6xl mx-auto">
      
      {/* HEADER */}
      <div>
        <h2 className="text-3xl font-bold text-[#111827] flex items-center gap-3">
          <Settings className="w-8 h-8 text-purple-600" />
          Configurações
        </h2>
        <p className="text-gray-500 mt-1 font-medium">
          Gerencie as integrações, tokens e preferências gerais do Basileia Vendor.
        </p>
      </div>

      <div className="flex flex-col md:flex-row gap-8 items-start">
        
        {/* SIDE MENU */}
        <div className="w-full md:w-64 flex flex-col gap-1">
          {tabs.map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all ${
                activeTab === tab.id 
                  ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/20' 
                  : 'text-gray-500 hover:bg-gray-100 hover:text-[#111827]'
              }`}
            >
              <tab.icon className={`w-5 h-5 ${activeTab === tab.id ? 'text-white' : 'text-gray-400'}`} />
              {tab.label}
            </button>
          ))}
        </div>

        {/* CONTENT AREA */}
        <div className="flex-1 w-full bg-white border border-gray-200 rounded-3xl p-6 shadow-sm min-h-[500px]">
          
          {activeTab === 'geral' && (
            <div className="space-y-6 animate-in slide-in-from-right-4 duration-300">
              <div>
                <h3 className="text-lg font-bold text-[#111827]">Configurações Gerais</h3>
                <p className="text-sm text-gray-500">Ajustes básicos da plataforma e regras de negócio.</p>
              </div>
              <hr className="border-gray-100" />
              
              <div className="space-y-4 max-w-lg">
                <div>
                  <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Mês de Início Operacional</label>
                  <input 
                    type="month" 
                    defaultValue="2026-04" 
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 transition-all"
                  />
                  <p className="text-xs text-gray-400 mt-1">Define o ponto de corte para considerar "Primeira Venda" nos clientes legados do Asaas.</p>
                </div>
                
                <div>
                  <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Nome da Instância</label>
                  <input 
                    type="text" 
                    defaultValue="Basileia Global - Comercial" 
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 transition-all"
                  />
                </div>
              </div>
              
              <div className="pt-4">
                <button className="flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-600/20">
                  <Save className="w-4 h-4" /> Salvar Alterações
                </button>
              </div>
            </div>
          )}

          {activeTab === 'integracoes' && (
            <div className="space-y-6 animate-in slide-in-from-right-4 duration-300">
              <div>
                <h3 className="text-lg font-bold text-[#111827]">Integrações & API</h3>
                <p className="text-sm text-gray-500">Conecte o Basileia Vendor com outros sistemas.</p>
              </div>
              <hr className="border-gray-100" />
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {/* Integration Card */}
                <div className="border border-gray-200 rounded-2xl p-5 hover:border-purple-300 transition-colors group relative overflow-hidden">
                  <div className="flex justify-between items-start mb-4 relative z-10">
                    <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-xl">
                      W
                    </div>
                    <span className="flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                      <CheckCircle2 className="w-3 h-3" /> Conectado
                    </span>
                  </div>
                  <h4 className="font-bold text-[#111827] text-lg relative z-10">Weni / WhatsApp</h4>
                  <p className="text-sm text-gray-500 mt-1 mb-4 relative z-10">Integração para disparo de mensagens e recuperação de boletos.</p>
                  <button className="w-full py-2 bg-gray-50 text-gray-700 font-bold rounded-lg text-sm border border-gray-200 group-hover:bg-purple-50 group-hover:text-purple-700 group-hover:border-purple-200 transition-all relative z-10">
                    Configurar Token
                  </button>
                </div>

                <div className="border border-gray-200 rounded-2xl p-5 hover:border-purple-300 transition-colors group relative overflow-hidden">
                  <div className="flex justify-between items-start mb-4 relative z-10">
                    <div className="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center font-bold text-xl">
                      C
                    </div>
                    <span className="flex items-center gap-1 text-xs font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full border border-gray-200">
                      Desconectado
                    </span>
                  </div>
                  <h4 className="font-bold text-[#111827] text-lg relative z-10">Clube de Vantagens</h4>
                  <p className="text-sm text-gray-500 mt-1 mb-4 relative z-10">Integração com o hub de parcerias para clientes VIP.</p>
                  <button className="w-full py-2 bg-purple-600 text-white font-bold rounded-lg text-sm hover:bg-purple-700 transition-all shadow-md relative z-10">
                    Conectar Agora
                  </button>
                </div>

              </div>
            </div>
          )}

          {activeTab === 'pagamentos' && (
            <div className="space-y-6 animate-in slide-in-from-right-4 duration-300">
              <div>
                <h3 className="text-lg font-bold text-[#111827]">Configurações do Asaas</h3>
                <p className="text-sm text-gray-500">Chaves de API e webhooks para sincronização de pagamentos.</p>
              </div>
              <hr className="border-gray-100" />
              
              <div className="space-y-4 max-w-xl">
                <div>
                  <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Ambiente</label>
                  <div className="flex gap-4">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="radio" name="env" className="w-4 h-4 text-purple-600" defaultChecked />
                      <span className="text-sm font-medium">Produção</span>
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer opacity-50">
                      <input type="radio" name="env" className="w-4 h-4 text-purple-600" />
                      <span className="text-sm font-medium">Sandbox (Testes)</span>
                    </label>
                  </div>
                </div>

                <div className="pt-2">
                  <label className="text-xs font-bold text-gray-500 uppercase block mb-2">API Key (Produção)</label>
                  <div className="relative">
                    <Key className="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input 
                      type="password" 
                      defaultValue="****************************************" 
                      className="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 transition-all font-mono"
                    />
                  </div>
                </div>

                <div className="pt-2">
                  <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Webhook URL</label>
                  <div className="flex gap-2">
                    <input 
                      type="text" 
                      readOnly
                      defaultValue="https://vendor.basileia.global/api/asaas/webhook" 
                      className="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm outline-none text-gray-500 font-mono"
                    />
                    <button className="px-4 bg-gray-100 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                      Copiar
                    </button>
                  </div>
                </div>
              </div>

              <div className="pt-4">
                <button className="flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-600/20">
                  <Save className="w-4 h-4" /> Salvar API Key
                </button>
              </div>
            </div>
          )}

          {/* Fallback for others */}
          {['seguranca', 'notificacoes'].includes(activeTab) && (
            <div className="flex flex-col items-center justify-center h-full text-gray-400 min-h-[300px]">
              <Plug className="w-16 h-16 opacity-20 mb-4" />
              <p className="font-medium">Em construção.</p>
            </div>
          )}
          
        </div>
      </div>
    </div>
  );
}
