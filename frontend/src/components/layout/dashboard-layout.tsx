'use client';

import React from 'react';
import { Sidebar } from './sidebar';
import { motion } from 'framer-motion';
import { Search, Bell, HelpCircle } from 'lucide-react';

interface DashboardLayoutProps {
  children: React.ReactNode;
}

export function DashboardLayout({ children }: DashboardLayoutProps) {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 flex flex-col min-w-0">
        {/* Topbar */}
        <header className="h-20 glass sticky top-0 z-40 px-8 flex items-center justify-between border-b border-border/50">
          <div className="flex items-center gap-4 flex-1 max-w-xl">
            <div className="relative w-full group">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-purple-400 group-focus-within:text-purple-600 transition-colors" />
              <input 
                type="text" 
                placeholder="Pesquisar por clientes, leads ou mensagens..." 
                className="w-full bg-surface/50 border border-border/50 rounded-2xl py-2.5 pl-12 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button className="p-2.5 rounded-xl hover:bg-surface transition-colors relative group">
              <Bell className="w-5 h-5 text-purple-600/70 group-hover:text-purple-600" />
              <span className="absolute top-2.5 right-2.5 w-2 h-2 bg-pink-500 rounded-full border-2 border-white shadow-sm" />
            </button>
            <button className="p-2.5 rounded-xl hover:bg-surface transition-colors group">
              <HelpCircle className="w-5 h-5 text-purple-600/70 group-hover:text-purple-600" />
            </button>
            <div className="h-8 w-px bg-border/50 mx-2" />
            <div className="flex items-center gap-3 pl-2">
              <div className="text-right hidden sm:block">
                <p className="text-sm font-semibold text-foreground">Relatório Semanal</p>
                <p className="text-xs font-medium text-emerald-500">+12.4% vs last week</p>
              </div>
            </div>
          </div>
        </header>

        {/* Content Area */}
        <motion.div 
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, ease: "easeOut" }}
          className="flex-1 p-8 overflow-y-auto"
        >
          {children}
        </motion.div>
      </main>
    </div>
  );
}
