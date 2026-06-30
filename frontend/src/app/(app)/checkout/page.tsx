"use client";

import React, { useState } from "react";
import { 
  ShoppingCart, 
  CreditCard, 
  Copy,
  Link as LinkIcon,
  CheckCircle2,
  AlertCircle
} from "lucide-react";

export default function CheckoutPage() {
  const [plano, setPlano] = useState("basileia_pro");

  return (
    <div className="space-y-6 animate-in fade-in duration-500 max-w-4xl mx-auto">
      
      {/* HEADER */}
      <div className="text-center pb-6">
        <h2 className="text-3xl font-bold text-[#111827] flex items-center justify-center gap-3">
          <ShoppingCart className="w-8 h-8 text-purple-600" />
          Gerador de Checkout
        </h2>
        <p className="text-gray-500 mt-2 font-medium">
          Crie links de pagamento personalizados e atribua as vendas ao seu código.
        </p>
      </div>

      <div className="bg-white border border-gray-200 rounded-3xl p-6 md:p-10 shadow-sm">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
          
          {/* FORMULÁRIO */}
          <div className="space-y-6">
            <div>
              <h3 className="text-lg font-bold text-[#111827] mb-1">Detalhes da Venda</h3>
              <p className="text-sm text-gray-500">Configure o plano que será vendido.</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Selecione o Plano</label>
                <select 
                  value={plano}
                  onChange={(e) => setPlano(e.target.value)}
                  className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 transition-all cursor-pointer"
                >
                  <option value="basileia_starter">Basiléia Starter (R$ 97/mês)</option>
                  <option value="basileia_pro">Basiléia Pro (R$ 197/mês)</option>
                  <option value="basileia_enterprise">Basiléia Enterprise (Sob Consulta)</option>
                </select>
              </div>

              <div>
                <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Forma de Cobrança</label>
                <div className="grid grid-cols-2 gap-3">
                  <button className="py-2.5 border-2 border-purple-600 bg-purple-50 text-purple-700 font-bold rounded-xl text-sm transition-colors">
                    Assinatura
                  </button>
                  <button className="py-2.5 border-2 border-transparent bg-gray-50 text-gray-500 font-bold rounded-xl text-sm hover:bg-gray-100 transition-colors">
                    Parcelamento
                  </button>
                </div>
              </div>

              <div>
                <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Seu Código de Vendedor</label>
                <input 
                  type="text" 
                  disabled
                  defaultValue="VINICIUS_R" 
                  className="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 outline-none"
                />
                <p className="text-xs text-gray-400 mt-1 flex items-center gap-1">
                  <AlertCircle className="w-3 h-3" /> Este código garante a sua comissão.
                </p>
              </div>
            </div>
          </div>

          {/* PREVIEW DO LINK */}
          <div className="space-y-6">
            <div className="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-purple-600/20 h-full flex flex-col justify-center">
              <div className="bg-white/10 p-4 rounded-2xl border border-white/20 mb-6">
                <div className="flex items-center gap-3 mb-2">
                  <CreditCard className="w-6 h-6 text-purple-200" />
                  <span className="font-bold text-lg">Resumo</span>
                </div>
                <div className="text-sm text-purple-100 mb-1">Plano Selecionado:</div>
                <div className="font-black text-2xl mb-4">Basiléia Pro</div>
                <div className="flex justify-between items-end border-t border-white/20 pt-4">
                  <span className="text-purple-200 text-sm">Cobrança</span>
                  <span className="font-bold text-lg">R$ 197,00 / mês</span>
                </div>
              </div>

              <div className="space-y-3">
                <button className="w-full flex items-center justify-center gap-2 py-3.5 bg-white text-purple-700 font-black rounded-xl hover:bg-purple-50 transition-colors shadow-lg">
                  <LinkIcon className="w-5 h-5" />
                  Gerar Link de Pagamento
                </button>
                <div className="text-center text-xs text-purple-200">
                  O link será copiado automaticamente para sua área de transferência.
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
