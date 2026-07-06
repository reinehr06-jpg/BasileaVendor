"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { ArrowLeft, User, Mail, Phone, Lock, Upload, Save, IdCard } from "lucide-react";
import { toast } from "sonner";

export default function ContaPage() {
  const [formData, setFormData] = useState({
    nome: "Administrador Master",
    email: "basileia.vendas@basileia.com",
    telefone: "(11) 98765-4321",
    cpf: "123.456.789-00"
  });

  const handleSalvarTudo = () => {
    toast.success("Todas as configurações salvas com sucesso!");
  };

  const handleAtualizarPerfil = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Perfil atualizado com sucesso!");
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-y-auto custom-scrollbar">
          
          {/* 🏷️ CABEÇALHO PADRÃO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <Link href="/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Minha Conta</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerencie seus dados pessoais, foto de perfil e credenciais de acesso.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button onClick={handleSalvarTudo} className="bg-[#1A1A2E] hover:bg-[#000000] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Salvar Alterações
              </button>
            </div>
          </div>

          <div className="flex flex-col lg:flex-row gap-[24px]">
            
            {/* LADO ESQUERDO: CONTEÚDO PRINCIPAL (DADOS + SEGURANÇA) */}
            <div className="flex-1 flex flex-col gap-[24px]">
              
              {/* 📝 INFORMAÇÕES DO ADMINISTRADOR COM FOTO DE PERFIL */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[32px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                
                <div className="flex items-center gap-[12px] mb-[16px]">
                  <IdCard className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                  <h2 className="text-[16px] font-[700] text-[#4B5563]">
                    Informações do Administrador
                  </h2>
                </div>
                
                <hr className="border-[#F3F4F6] mb-[32px]" />

                <div className="flex flex-col md:flex-row gap-[32px] items-start">
                  
                  {/* Avatar Upload Interativo */}
                  <div className="relative group shrink-0 cursor-pointer">
                    <div className="w-[100px] h-[100px] bg-[#F3E8FF] rounded-[16px] flex items-center justify-center text-[#6D28D9] text-[32px] font-[700] border-2 border-[#E9D5FF] overflow-hidden transition-all group-hover:border-[#6D28D9]">
                      AM
                    </div>
                    {/* Overlay Camera Icon na Esquerda */}
                    <div 
                      onClick={() => document.getElementById('avatar-upload')?.click()}
                      className="absolute inset-0 bg-black/40 rounded-[16px] flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <Upload className="w-[24px] h-[24px] text-white mb-1" />
                      <span className="text-[10px] font-[700] text-white uppercase tracking-wider">Alterar</span>
                    </div>
                    <input type="file" id="avatar-upload" className="hidden" accept="image/*" onChange={() => toast.success("Foto atualizada!")} />
                  </div>

                  <form onSubmit={handleAtualizarPerfil} className="flex-1 flex flex-col gap-[24px] w-full">
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      {/* Nome Completo */}
                      <div className="flex flex-col gap-[8px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Nome Completo
                        </label>
                        <input 
                          type="text" 
                          value={formData.nome} 
                          onChange={(e) => setFormData({...formData, nome: e.target.value})} 
                          className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" 
                        />
                      </div>

                      {/* Email de Acesso */}
                      <div className="flex flex-col gap-[8px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Email de Acesso
                        </label>
                        <input 
                          type="email" 
                          value={formData.email} 
                          onChange={(e) => setFormData({...formData, email: e.target.value})} 
                          className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" 
                        />
                      </div>
                    </div>

                    <div className="pt-[8px] flex items-center justify-between">
                      <p className="text-[12px] text-[#9CA3AF] max-w-[200px]">Formatos: PNG, JPG, GIF até 2MB.</p>
                      <button 
                        type="submit"
                        className="bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors text-white px-[20px] py-[10px] rounded-[6px] text-[13px] font-[600] shadow-sm"
                      >
                        Atualizar Perfil
                      </button>
                    </div>

                  </form>
                </div>
              </div>
              
              {/* 🔐 SEGURANÇA (Bloco Original Restaurado) */}
              <div className="bg-white border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] rounded-[12px] p-6">
                <div className="flex items-center justify-between">
                  <div className="flex flex-col gap-[4px]">
                    <h3 className="text-[15px] font-[600] text-[#1A1A2E] flex items-center gap-[8px]">
                      <Lock className="w-[16px] h-[16px] text-[#4B5563]" /> Segurança e Senha
                    </h3>
                    <p className="text-[13px] text-[#6B7280]">Proteja sua conta atualizando sua senha periodicamente.</p>
                  </div>
                  <button onClick={() => toast.info("Abrir modal de senha")} className="px-[16px] py-[10px] border border-[#E5E7EB] hover:bg-[#F9FAFB] rounded-[8px] text-[13px] font-[600] text-[#374151] flex items-center gap-[8px] transition-colors shadow-sm">
                    Alterar Minha Senha
                  </button>
                </div>
              </div>

            </div>

            {/* LADO DIREITO: INFOS EXTRAS / DADOS SENSÍVEIS */}
            <div className="w-full lg:w-[320px] flex flex-col gap-[24px]">
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <h3 className="text-[15px] font-[600] text-[#1A1A2E] mb-[16px]">Outras Informações</h3>
                
                <div className="flex flex-col gap-[16px]">
                  <div className="flex flex-col gap-[8px]">
                    <label className="text-[13px] font-[600] text-[#4B5563]">Telefone</label>
                    <div className="relative">
                      <input type="text" value={formData.telefone} onChange={(e) => setFormData({...formData, telefone: e.target.value})} className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] pl-[36px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                      <Phone className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" />
                    </div>
                  </div>

                  <div className="flex flex-col gap-[8px]">
                    <label className="text-[13px] font-[600] text-[#4B5563]">CPF</label>
                    <input type="text" value={formData.cpf} disabled className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] bg-[#F9FAFB] cursor-not-allowed text-center tracking-wider font-[500]" />
                    <span className="text-[11px] text-[#9CA3AF] leading-tight mt-1">
                      Para alterar o CPF, entre em contato com o administrador da igreja.
                    </span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
