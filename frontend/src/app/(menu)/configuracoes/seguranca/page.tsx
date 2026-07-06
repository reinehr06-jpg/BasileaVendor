"use client";

import React from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft, 
  Settings, 
  Key, 
  Smartphone, 
  History, 
  Trash2,
  Check
} from "lucide-react";
import { toast } from "sonner";

export default function SegurancaPage() {

  const Toggle = ({ active }: { active: boolean }) => (
    <div className={`w-[40px] h-[22px] rounded-full flex items-center p-[2px] transition-colors cursor-not-allowed ${active ? 'bg-[#A78BFA]' : 'bg-[#E5E7EB]'}`}>
      <div className={`w-[18px] h-[18px] bg-white rounded-full shadow-sm transform transition-transform ${active ? 'translate-x-[18px]' : 'translate-x-0'}`} />
    </div>
  );

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden relative">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-[24px] overflow-y-auto custom-scrollbar pb-[80px]">
          
          {/* 🏷️ CABEÇALHO PADRÃO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <Link href="/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Segurança</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerencie autenticação, senhas e configurações de acesso.</p>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-[24px]">
            
            {/* CARD 1: Configurações de Segurança */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
              <div className="flex items-center gap-[12px] mb-[16px]">
                <Settings className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.5} />
                <h2 className="text-[18px] font-[700] text-[#4B5563]">Configurações de Segurança</h2>
              </div>
              
              <hr className="border-[#F3F4F6] mb-[24px]" />

              <div className="grid grid-cols-1 md:grid-cols-2 gap-[32px] mb-[24px]">
                
                {/* 2FA Master */}
                <div className="flex flex-col gap-[12px]">
                  <label className="text-[14px] font-[700] text-[#4B5563]">2FA Obrigatório - Master</label>
                  <Toggle active={true} />
                  <p className="text-[12px] text-[#9CA3AF] font-[500]">Sempre ativo por política de segurança</p>
                </div>

                {/* 2FA Gestor */}
                <div className="flex flex-col gap-[12px]">
                  <label className="text-[14px] font-[700] text-[#4B5563]">2FA Obrigatório - Gestor</label>
                  <Toggle active={true} />
                  <p className="text-[12px] text-[#9CA3AF] font-[500]">Sempre ativo por política de segurança</p>
                </div>

                {/* 2FA Vendedor */}
                <div className="flex flex-col gap-[12px]">
                  <label className="text-[14px] font-[700] text-[#4B5563]">2FA Obrigatório - Vendedor</label>
                  <Toggle active={true} />
                  <p className="text-[12px] text-[#9CA3AF] font-[500]">Sempre ativo por política de segurança</p>
                </div>

                {/* TentativasMáx */}
                <div className="flex flex-col gap-[12px]">
                  <label className="text-[14px] font-[700] text-[#4B5563]">TentativasMáx de Login</label>
                  <select className="w-full max-w-[400px] h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-white appearance-none">
                    <option value="5">5</option>
                    <option value="10">10</option>
                  </select>
                  <p className="text-[12px] text-[#9CA3AF] font-[500]">Após exceder, conta é bloqueada</p>
                </div>

              </div>

              <button onClick={() => toast.success("Configurações de segurança salvas!")} className="bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors text-white px-[20px] py-[10px] rounded-[6px] text-[13px] font-[600] shadow-sm">
                Salvar Configurações
              </button>
            </div>

            {/* CARD 2: Alterar Minha Senha */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
              <div className="flex items-center gap-[12px] mb-[16px]">
                <Key className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.5} />
                <h2 className="text-[18px] font-[700] text-[#4B5563]">Alterar Minha Senha</h2>
              </div>
              
              <hr className="border-[#F3F4F6] mb-[24px]" />

              <div className="flex flex-col gap-[20px] mb-[24px]">
                <div className="flex flex-col gap-[8px]">
                  <label className="text-[13px] font-[700] text-[#4B5563]">Senha Atual</label>
                  <input type="password" placeholder="••••••••" className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                  <div className="flex flex-col gap-[8px]">
                    <label className="text-[13px] font-[700] text-[#4B5563]">Nova Senha</label>
                    <input type="password" placeholder="Mínimo 8 caracteres" className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                  </div>
                  <div className="flex flex-col gap-[8px]">
                    <label className="text-[13px] font-[700] text-[#4B5563]">Confirmar Nova Senha</label>
                    <input type="password" placeholder="Confirme a nova senha" className="w-full h-[40px] border border-[#E5E7EB] rounded-[6px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                  </div>
                </div>
              </div>

              <button onClick={() => toast.success("Senha atualizada com sucesso!")} className="bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors text-white px-[20px] py-[10px] rounded-[6px] text-[13px] font-[600] shadow-sm">
                Salvar Nova Senha
              </button>
            </div>

            {/* CARD 3: Gerenciar Autenticação 2FA */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden">
              <div className="p-[24px] flex items-center justify-between">
                <div className="flex items-center gap-[12px]">
                  <Smartphone className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.5} />
                  <h2 className="text-[18px] font-[700] text-[#4B5563]">Gerenciar Autenticação 2FA</h2>
                </div>
                <button className="bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors text-white px-[16px] py-[8px] rounded-[6px] text-[13px] font-[600] shadow-sm flex items-center gap-[6px]">
                  <span className="text-[16px] leading-none mb-[2px]">+</span> Novo Dispositivo
                </button>
              </div>

              <div className="w-full overflow-x-auto">
                <table className="w-full text-left border-collapse min-w-[800px]">
                  <thead>
                    <tr className="bg-[#F9FAFB] border-y border-[#E5E7EB]">
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Dispositivo</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Usuário</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Perfil</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Status</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ações</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-[#E5E7EB]">
                    
                    {[
                      { nome: "Administrador Master", email: "basileia.vendas@basileia.com", disp: "Dispositivo Principal", perfil: "MASTER", badgeBg: "bg-[#F3E8FF]", badgeText: "text-[#6D28D9]" },
                      { nome: "Administrador Master", email: "basileia.vendas@basileia.com", disp: "Fernando Laudino", perfil: "MASTER", badgeBg: "bg-[#F3E8FF]", badgeText: "text-[#6D28D9]" },
                      { nome: "Anthony Cardoso", email: "anthony.cardoso@basileia.global", disp: "Dispositivo Principal", perfil: "GESTOR", badgeBg: "bg-[#E0E7FF]", badgeText: "text-[#4338CA]" },
                      { nome: "Gestor de testes", email: "teste@gestor.com", disp: "Dispositivo Principal", perfil: "GESTOR", badgeBg: "bg-[#E0E7FF]", badgeText: "text-[#4338CA]" },
                      { nome: "Pamela Gimenez", email: "pame.ag.88@gmail.com", disp: "Pamela Gimenez", perfil: "VENDEDOR", badgeBg: "bg-[#DCFCE7]", badgeText: "text-[#15803D]" },
                      { nome: "Vendedor de Testes", email: "vinicius@basileia.global", disp: "Dispositivo Principal", perfil: "VENDEDOR", badgeBg: "bg-[#DCFCE7]", badgeText: "text-[#15803D]" },
                    ].map((row, i) => (
                      <tr key={i} className="hover:bg-[#F9FAFB] transition-colors">
                        <td className="px-[24px] py-[16px]">
                          <div className="flex items-center gap-[8px]">
                            <Smartphone className="w-[16px] h-[16px] text-[#8B5CF6]" strokeWidth={2.5} />
                            <span className="text-[14px] font-[700] text-[#4B5563]">{row.disp}</span>
                          </div>
                        </td>
                        <td className="px-[24px] py-[16px]">
                          <div className="flex flex-col">
                            <span className="text-[14px] font-[700] text-[#4B5563]">{row.nome}</span>
                            <span className="text-[12px] text-[#9CA3AF]">{row.email}</span>
                          </div>
                        </td>
                        <td className="px-[24px] py-[16px]">
                          <span className={`px-[8px] py-[4px] rounded-[6px] text-[11px] font-[800] tracking-wide ${row.badgeBg} ${row.badgeText}`}>
                            {row.perfil}
                          </span>
                        </td>
                        <td className="px-[24px] py-[16px]">
                          <span className="px-[8px] py-[4px] bg-[#DCFCE7] text-[#15803D] rounded-[6px] text-[11px] font-[800] flex items-center gap-[4px] w-fit tracking-wide">
                            <Check className="w-[12px] h-[12px]" strokeWidth={3} /> ATIVO
                          </span>
                        </td>
                        <td className="px-[24px] py-[16px]">
                          <button onClick={() => toast.success("Dispositivo removido!")} className="flex items-center gap-[6px] px-[12px] py-[6px] text-[#EF4444] border border-[#FECACA] rounded-[6px] text-[13px] font-[600] hover:bg-[#FEF2F2] transition-colors">
                            <Trash2 className="w-[14px] h-[14px]" /> Remover
                          </button>
                        </td>
                      </tr>
                    ))}

                  </tbody>
                </table>
              </div>
            </div>

            {/* CARD 4: Histórico de Acessos */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden">
              <div className="p-[24px] flex items-center justify-between border-b border-[#F3F4F6]">
                <div className="flex items-center gap-[12px]">
                  <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.5} />
                  <h2 className="text-[18px] font-[700] text-[#4B5563]">Histórico de Acessos</h2>
                </div>
                <div className="flex items-center gap-[16px] text-[13px] font-[600]">
                  <span className="text-[#10B981] flex items-center gap-[4px]">✓ 0 Hoje</span>
                  <span className="text-[#EF4444]">X 0 Falhas</span>
                </div>
              </div>

              <div className="w-full overflow-x-auto">
                <table className="w-full text-left border-collapse min-w-[800px]">
                  <thead>
                    <tr className="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data/Hora</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Usuário</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">IP</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Dispositivo</th>
                      <th className="px-[24px] py-[16px] text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colSpan={5} className="px-[24px] py-[40px] text-center text-[14px] text-[#9CA3AF] font-[500]">
                        Nenhum registro de login encontrado.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
