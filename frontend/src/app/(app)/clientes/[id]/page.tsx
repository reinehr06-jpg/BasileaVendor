"use client";

import React, { use } from "react";
import { 
  ArrowLeft,
  User,
  Mail,
  Phone,
  FileText,
  CreditCard,
  Calendar,
  CheckCircle2,
  DollarSign,
  UserCheck
} from "lucide-react";
import Link from "next/link";
import CustomSelect from "@/components/CustomSelect";

export default function ClienteDetalhesPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);

  // Mock
  const cliente = {
    nome: "João Silva",
    asaas_id: "cus_000005123",
    email: "joao@example.com",
    telefone: "47999999999",
    cpfCnpj: "000.000.000-00",
    status: "ATIVO",
    vendedor_atual: "Maria (Gestor)",
    comissao_tipo: "Primeira Venda", // original: inicial
    sincronizado_em: "30/06/2026 15:30",
    data_criacao: "10/05/2026"
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-500 max-w-5xl mx-auto">
      
      {/* HEADER */}
      <div className="flex items-center gap-4">
        <Link href="/clientes" className="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-500">
          <ArrowLeft className="w-5 h-5" />
        </Link>
        <div>
          <h2 className="text-2xl font-bold text-[#111827]">Detalhes do Cliente</h2>
          <p className="text-sm text-gray-500">Visualizando informações de {cliente.nome}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {/* COLUNA ESQUERDA - INFO BASICA */}
        <div className="md:col-span-2 space-y-6">
          <div className="bg-white border border-gray-200 rounded-3xl p-6 shadow-sm">
            <div className="flex items-center justify-between mb-6">
              <h3 className="text-lg font-bold text-[#111827] flex items-center gap-2">
                <User className="w-5 h-5 text-purple-600" />
                Dados do Cliente
              </h3>
              <span className="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-bold">
                {cliente.status}
              </span>
            </div>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4">
              <InfoItem icon={User} label="Nome Completo" value={cliente.nome} />
              <InfoItem icon={FileText} label="CPF/CNPJ" value={cliente.cpfCnpj} />
              <InfoItem icon={Mail} label="Email" value={cliente.email} />
              <InfoItem icon={Phone} label="Telefone" value={cliente.telefone} />
              <InfoItem icon={CreditCard} label="ID Asaas" value={cliente.asaas_id} />
              <InfoItem icon={Calendar} label="Data Criação" value={cliente.data_criacao} />
            </div>
          </div>
          
          <div className="bg-purple-50 border border-purple-100 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div className="absolute -right-10 -top-10 opacity-10">
              <DollarSign className="w-48 h-48 text-purple-600" />
            </div>
            <h3 className="text-lg font-bold text-purple-900 mb-2 relative z-10">
              Classificação de Comissão
            </h3>
            <p className="text-purple-700 mb-4 text-sm relative z-10">
              O sistema detectou que este cliente se enquadra como:
            </p>
            <div className="bg-white/60 backdrop-blur border border-purple-200 rounded-xl p-4 inline-flex items-center gap-3 relative z-10">
              <CheckCircle2 className="w-6 h-6 text-purple-600" />
              <span className="font-black text-purple-900 text-lg">{cliente.comissao_tipo}</span>
            </div>
          </div>
        </div>

        {/* COLUNA DIREITA - ATRIBUICAO */}
        <div className="space-y-6">
          <div className="bg-white border border-gray-200 rounded-3xl p-6 shadow-sm">
            <h3 className="text-lg font-bold text-[#111827] flex items-center gap-2 mb-4">
              <UserCheck className="w-5 h-5 text-purple-600" />
              Atribuição
            </h3>
            <p className="text-sm text-gray-500 mb-6">
              Defina o vendedor responsável por este cliente para que a comissão seja calculada e projetada no Basileia.
            </p>
            
            <div className="space-y-4">
              <div>
                <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Vendedor Atual</label>
                <div className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700">
                  {cliente.vendedor_atual}
                </div>
              </div>
              
              <div>
                <label className="text-xs font-bold text-gray-500 uppercase block mb-2">Transferir para</label>
                <select className="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 transition-all cursor-pointer">
                  <option>Selecione um vendedor...</option>
                  <option>Vinicius (Gestor)</option>
                  <option>Maria (Vendedor)</option>
                </select>
              </div>
              
              <button className="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-colors mt-2 shadow-lg shadow-purple-600/20">
                Salvar Atribuição
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}

function InfoItem({ icon: Icon, label, value }: { icon: any, label: string, value: string }) {
  return (
    <div className="flex gap-3 items-start">
      <div className="mt-0.5 text-gray-400">
        <Icon className="w-4 h-4" />
      </div>
      <div>
        <div className="text-xs font-bold text-gray-500 uppercase">{label}</div>
        <div className="text-sm font-medium text-[#111827] mt-0.5">{value}</div>
      </div>
    </div>
  );
}
