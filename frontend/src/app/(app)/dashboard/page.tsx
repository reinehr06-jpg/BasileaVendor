"use client";

import React, { useEffect, useState } from "react";
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
import { api } from '@/lib/api';

export default function DashboardPage() {
  const [data, setData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api.get<any>("/dashboard")
      .then(res => {
        setData(res);
      })
      .catch(err => console.error(err))
      .finally(() => setIsLoading(false));
  }, []);

  if (isLoading) {
    return <div className="p-8 text-center animate-pulse text-gray-500">Carregando dashboard...</div>;
  }

  const kpis = data?.kpis || {};

  const stats = [
    { name: 'Vendas Totais', value: kpis.total_vendas || 0, change: '+0%', trend: 'up', icon: DollarSign, color: 'bg-indigo-500' },
    { name: 'Receita Bruta', value: `R$ ${Number(kpis.receita_bruta || 0).toLocaleString('pt-BR')}`, change: '+0%', trend: 'up', icon: TrendingUp, color: 'bg-emerald-500' },
    { name: 'Vendas Ativas', value: kpis.vendas_ativas || 0, change: '+0%', trend: 'up', icon: Clock, color: 'bg-amber-500' },
    { name: 'Clientes Totais', value: kpis.total_clientes || 0, change: '+0%', trend: 'up', icon: Users, color: 'bg-purple-500' },
  ];

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      <div>
        <h2 className="text-3xl font-bold text-[#111827]">Dashboard Comercial</h2>
        <p className="text-gray-500 mt-1 font-medium">Resultados reais puxados diretamente da nova API Laravel.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, i) => (
          <motion.div
            key={stat.name}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.1 }}
            className="p-6 rounded-3xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-all group"
          >
            <div className="flex items-center justify-between mb-4">
              <div className={cn("p-3 rounded-2xl text-white shadow-lg", stat.color)}>
                <stat.icon className="w-5 h-5" />
              </div>
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">{stat.name}</p>
              <div className="flex items-end justify-between mt-1">
                <h3 className="text-2xl font-bold text-[#111827]">{stat.value}</h3>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  );
}
