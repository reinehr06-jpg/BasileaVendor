"use client";

import React, { useEffect, useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { ClientesService } from "@/services/clientes.service";
import {
  Contact,
  Phone,
  Mail,
  User,
  ShoppingBag,
  CreditCard,
  Wallet,
  TrendingUp,
  FileText,
} from "lucide-react";

type Historico = {
  cliente: any;
  resumo: any;
  vendas: any[];
  pagamentos: any[];
  comissoes: any[];
};

const money = (v: number) =>
  "R$ " + Number(v || 0).toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const data = (d?: string | null) => (d ? new Date(d).toLocaleDateString("pt-BR") : "—");

const statusPagamento = (s: string) => {
  const u = (s || "").toUpperCase();
  if (["RECEIVED", "CONFIRMED", "PAGO"].includes(u)) return { label: "Pago", cls: "bg-[#ECFDF3] text-[#027A48]" };
  if (["OVERDUE", "VENCIDO"].includes(u)) return { label: "Vencido", cls: "bg-[#FEF3F2] text-[#B42318]" };
  return { label: s || "Pendente", cls: "bg-[#FFFAEB] text-[#B54708]" };
};

export default function ClienteProfilePage() {
  const params = useParams();
  const id = params?.id as string;

  const [hist, setHist] = useState<Historico | null>(null);
  const [loading, setLoading] = useState(true);
  const [erro, setErro] = useState<string | null>(null);
  const [tab, setTab] = useState<"vendas" | "pagamentos" | "comissoes">("vendas");

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    ClientesService.historico(id)
      .then((res: any) => setHist(res))
      .catch(() => setErro("Não foi possível carregar o histórico deste cliente."))
      .finally(() => setLoading(false));
  }, [id]);

  const c = hist?.cliente;
  const r = hist?.resumo;

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />
        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            {/* Breadcrumb */}
            <div className="flex items-center text-[13px] text-[#6B7280] mb-[20px]">
              <Link href="/gestao-comercial/clientes" className="hover:text-[#6D28D9] transition-colors">
                Clientes
              </Link>
              <span className="mx-[8px]">/</span>
              <span className="text-[#1A1A2E] font-[600]">{c?.nome ?? "Carregando..."}</span>
            </div>

            {loading && <div className="text-[#6B7280] text-[14px]">Carregando histórico...</div>}
            {erro && <div className="text-[#B42318] text-[14px] bg-[#FEF3F2] p-[16px] rounded-[12px]">{erro}</div>}

            {!loading && !erro && hist && (
              <>
                {/* HEADER */}
                <div className="flex items-center justify-between mb-[24px]">
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[48px] h-[48px] rounded-[12px] bg-[#F4EEFF] flex items-center justify-center shrink-0 border border-[#E9D5FF] shadow-sm">
                      <Contact className="w-[24px] h-[24px] text-[#7C3AED]" strokeWidth={2.2} />
                    </div>
                    <div>
                      <h1 className="text-[20px] font-[700] text-[#1A1A2E]">{c.nome}</h1>
                      <div className="flex items-center gap-[16px] text-[13px] text-[#6B7280] mt-[2px]">
                        <span className="flex items-center gap-[4px]"><User className="w-[14px] h-[14px]" /> Vendedor: {c.vendedor}</span>
                        {c.telefone && <span className="flex items-center gap-[4px]"><Phone className="w-[14px] h-[14px]" /> {c.telefone}</span>}
                        {c.email && <span className="flex items-center gap-[4px]"><Mail className="w-[14px] h-[14px]" /> {c.email}</span>}
                      </div>
                    </div>
                  </div>
                  <span className="px-[12px] py-[6px] rounded-[8px] text-[12px] font-[600] bg-[#F4EEFF] text-[#6D28D9] capitalize">
                    {c.status ?? "—"}
                  </span>
                </div>

                {/* CARDS DE RESUMO */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-[16px] mb-[24px]">
                  <ResumoCard icon={<Wallet className="w-[18px] h-[18px]" />} label="Total pago" valor={money(r.total_pago)} />
                  <ResumoCard icon={<CreditCard className="w-[18px] h-[18px]" />} label="Nº de pagamentos" valor={String(r.num_pagamentos)} />
                  <ResumoCard icon={<TrendingUp className="w-[18px] h-[18px]" />} label="Ticket médio" valor={money(r.ticket_medio)} />
                  <ResumoCard icon={<ShoppingBag className="w-[18px] h-[18px]" />} label="Comissão gerada" valor={money(r.total_comissao_vendedor)} />
                </div>
                <div className="text-[12px] text-[#6B7280] mb-[24px]">
                  Primeiro pagamento: <b>{data(r.primeiro_pagamento)}</b> · Último pagamento: <b>{data(r.ultimo_pagamento)}</b> · Vendas: <b>{r.num_vendas}</b>
                </div>

                {/* TABS */}
                <div className="flex gap-[4px] border-b border-[#E5E7EB] mb-[16px]">
                  {([
                    ["vendas", `Vendas (${hist.vendas.length})`],
                    ["pagamentos", `Pagamentos (${hist.pagamentos.length})`],
                    ["comissoes", `Comissões (${hist.comissoes.length})`],
                  ] as const).map(([key, label]) => (
                    <button
                      key={key}
                      onClick={() => setTab(key)}
                      className={`px-[16px] py-[10px] text-[14px] font-[600] border-b-2 -mb-[1px] transition-colors ${
                        tab === key ? "border-[#7C3AED] text-[#6D28D9]" : "border-transparent text-[#6B7280] hover:text-[#1A1A2E]"
                      }`}
                    >
                      {label}
                    </button>
                  ))}
                </div>

                {/* CONTEUDO DAS TABS */}
                <div className="bg-white rounded-[12px] border border-[#EEE] overflow-hidden">
                  {tab === "vendas" && (
                    <Tabela
                      cabecalho={["Data", "Plano", "Tipo", "Valor", "Parcelas", "Vendedor", "Status"]}
                      vazio="Nenhuma venda registrada."
                      linhas={hist.vendas.map((v) => [
                        data(v.data_venda),
                        v.plano || "—",
                        v.tipo_negociacao || "—",
                        money(v.valor),
                        String(v.parcelas ?? 1),
                        v.vendedor,
                        v.status || "—",
                      ])}
                    />
                  )}
                  {tab === "pagamentos" && (
                    <Tabela
                      cabecalho={["Vencimento", "Pagamento", "Forma", "Valor", "Status"]}
                      vazio="Nenhum pagamento registrado."
                      linhas={hist.pagamentos.map((p) => {
                        const st = statusPagamento(p.status);
                        return [
                          data(p.data_vencimento),
                          data(p.data_pagamento),
                          p.forma || "—",
                          money(p.valor),
                          <span key={p.id} className={`px-[8px] py-[3px] rounded-[6px] text-[12px] font-[600] ${st.cls}`}>{st.label}</span>,
                        ];
                      })}
                    />
                  )}
                  {tab === "comissoes" && (
                    <Tabela
                      cabecalho={["Competência", "Tipo", "Vendedor", "Comissão", "Gestor", "Status"]}
                      vazio="Nenhuma comissão registrada."
                      linhas={hist.comissoes.map((c2) => [
                        c2.competencia || "—",
                        c2.tipo || "—",
                        c2.vendedor,
                        money(c2.valor_comissao),
                        money(c2.valor_gerente),
                        c2.status || "—",
                      ])}
                    />
                  )}
                </div>
              </>
            )}
          </div>
        </main>
      </div>
    </div>
  );
}

function ResumoCard({ icon, label, valor }: { icon: React.ReactNode; label: string; valor: string }) {
  return (
    <div className="bg-white rounded-[12px] border border-[#EEE] p-[16px]">
      <div className="flex items-center gap-[8px] text-[#7C3AED] mb-[8px]">
        <div className="w-[32px] h-[32px] rounded-[8px] bg-[#F4EEFF] flex items-center justify-center">{icon}</div>
        <span className="text-[12px] text-[#6B7280]">{label}</span>
      </div>
      <div className="text-[20px] font-[700] text-[#1A1A2E]">{valor}</div>
    </div>
  );
}

function Tabela({
  cabecalho,
  linhas,
  vazio,
}: {
  cabecalho: string[];
  linhas: React.ReactNode[][];
  vazio: string;
}) {
  if (linhas.length === 0) {
    return (
      <div className="p-[32px] text-center text-[#6B7280] text-[14px] flex flex-col items-center gap-[8px]">
        <FileText className="w-[24px] h-[24px] text-[#D1D5DB]" />
        {vazio}
      </div>
    );
  }
  return (
    <table className="w-full text-[13px]">
      <thead>
        <tr className="bg-[#FAFAFB] text-[#6B7280] text-left">
          {cabecalho.map((h) => (
            <th key={h} className="px-[16px] py-[12px] font-[600]">{h}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {linhas.map((linha, i) => (
          <tr key={i} className="border-t border-[#F0F0F2] text-[#1A1A2E]">
            {linha.map((cel, j) => (
              <td key={j} className="px-[16px] py-[12px]">{cel}</td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}
