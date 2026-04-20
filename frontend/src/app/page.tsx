'use client';

import React from 'react';
import { motion } from 'framer-motion';
import { 
  TrendingUp, 
  Users, 
  DollarSign, 
  Clock,
  ArrowUpRight,
  ArrowDownRight,
  MoreHorizontal
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';

const stats = [
  { name: 'Vendas Totais', value: 'R$ 124.500', change: '+12.5%', trend: 'up', icon: DollarSign, color: 'bg-indigo-500' },
  { name: 'Novos Leads', value: '482', change: '+18.2%', trend: 'up', icon: Users, color: 'bg-purple-500' },
  { name: 'Taxa de Conversão', value: '3.2%', change: '-2.4%', trend: 'down', icon: TrendingUp, color: 'bg-pink-500' },
  { name: 'Tempo Médio Resposta', value: '14 min', change: '+5.1%', trend: 'up', icon: Clock, color: 'bg-amber-500' },
];

export default function HomePage() {
  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-3xl font-bold text-foreground">Bem-vindo de volta, <span className="gradient-text">Vinicius</span></h2>
        <p className="text-purple-600/50 mt-1 font-medium">Aqui está o que está acontecendo na Basiléia hoje.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, i) => (
          <motion.div
            key={stat.name}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.1 }}
            className="p-6 rounded-3xl bg-surface border border-border/50 shadow-sm hover:shadow-md transition-all group"
          >
            <div className="flex items-center justify-between mb-4">
              <div className={cn("p-3 rounded-2xl text-white shadow-lg", stat.color)}>
                <stat.icon className="w-5 h-5" />
              </div>
              <button className="p-2 rounded-lg hover:bg-surface-hover text-purple-400">
                <MoreHorizontal className="w-4 h-4" />
              </button>
            </div>
            <div>
              <p className="text-sm font-medium text-purple-600/50">{stat.name}</p>
              <div className="flex items-end justify-between mt-1">
                <h3 className="text-2xl font-bold text-foreground">{stat.value}</h3>
                <div className={cn(
                  "flex items-center gap-1 text-sm font-bold px-2 py-0.5 rounded-full",
                  stat.trend === 'up' ? "text-emerald-500 bg-emerald-500/10" : "text-pink-500 bg-pink-500/10"
                )}>
                  {stat.trend === 'up' ? <ArrowUpRight className="w-3 h-3" /> : <ArrowDownRight className="w-3 h-3" />}
                  {stat.change}
                </div>
              </div>
            </div>
          </motion.div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Chart Placeholder */}
        <div className="lg:col-span-2 p-8 rounded-3xl bg-surface border border-border/50 shadow-sm h-[400px] flex flex-col">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h3 className="text-lg font-bold text-foreground">Desempenho Comercial</h3>
              <p className="text-sm text-purple-600/50 font-medium">Relatório de leads e vendas nos últimos 30 dias</p>
            </div>
            <select className="bg-surface-hover border border-border/50 rounded-xl px-4 py-2 text-sm font-medium focus:outline-none">
              <option>Últimos 30 dias</option>
              <option>Últimos 90 dias</option>
            </select>
          </div>
          <div className="flex-1 flex items-center justify-center border-2 border-dashed border-border/50 rounded-2xl bg-background/50">
             <div className="text-center">
                <p className="text-purple-300 font-medium italic">Gráfico interativo será renderizado aqui</p>
                <div className="flex gap-2 justify-center mt-4">
                  {[40, 70, 45, 90, 65, 80, 40].map((h, i) => (
                    <motion.div 
                      key={i}
                      initial={{ height: 0 }}
                      animate={{ height: h }}
                      className="w-8 bg-primary/20 rounded-t-lg border-x border-t border-primary/30"
                    />
                  ))}
                </div>
             </div>
          </div>
        </div>

        {/* Recent Activity */}
        <div className="p-8 rounded-3xl bg-surface border border-border/50 shadow-sm flex flex-col">
          <h3 className="text-lg font-bold text-foreground mb-6">Atividade Recente</h3>
          <div className="space-y-6 flex-1">
            {[1, 2, 3, 4].map((_, i) => (
              <div key={i} className="flex gap-4">
                <div className="relative">
                  <div className="w-10 h-10 rounded-full bg-purple-100 border border-purple-200 flex items-center justify-center text-purple-600 font-bold text-sm">
                    {['JS', 'AM', 'RL', 'TC'][i]}
                  </div>
                  {i < 3 && <div className="absolute top-10 left-1/2 -translate-x-1/2 w-0.5 h-6 bg-border/50" />}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-bold text-foreground truncate">
                    {['João Silva', 'Ana Maria', 'Ricardo Lima', 'Tiago Costa'][i]}
                  </p>
                  <p className="text-xs text-purple-600/50 font-medium truncate">
                    {['Nova venda realizada', 'Lead qualificado via WhatsApp', 'Agendamento de reunião', 'Pagamento confirmado'][i]}
                  </p>
                  <p className="text-[10px] text-purple-300 mt-1 uppercase font-bold tracking-tighter">Há {i + 1}5 min</p>
                </div>
              </div>
            ))}
          </div>
          <button className="w-full mt-6 py-3 rounded-2xl bg-surface-hover hover:bg-primary/5 text-purple-600 font-bold text-sm transition-all border border-border/50">
            Ver todo o histórico
          </button>
        </div>
      </div>
    </div>
  );
}
