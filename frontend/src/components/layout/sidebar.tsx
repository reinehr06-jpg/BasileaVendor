'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { 
  LayoutDashboard, 
  MessageSquare, 
  Users, 
  Calendar, 
  Target, 
  Beaker, 
  Settings, 
  LogOut,
  ChevronRight
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion } from 'framer-motion';

const menuItems = [
  { name: 'Meu Painel', icon: LayoutDashboard, href: '/' },
  { name: 'Chat', icon: MessageSquare, href: '/chat' },
  { name: 'Clientes', icon: Users, href: '/clientes' },
  { name: 'Calendário', icon: Calendar, href: '/calendario' },
  { name: 'Marketing & Leads', icon: Target, href: '/leads' },
  { name: 'IA Lab', icon: Beaker, href: '/ia-lab' },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-72 h-screen sidebar-gradient border-r border-white/10 flex flex-col sticky top-0 z-50">
      <div className="p-8">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-400 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
            <span className="text-white font-bold text-xl">B</span>
          </div>
          <div>
            <h1 className="text-white font-bold text-lg leading-tight">Basiléia</h1>
            <p className="text-purple-300/60 text-xs font-medium uppercase tracking-wider">Vendor Pro</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 px-4 py-4 space-y-2 overflow-y-auto custom-scrollbar">
        {menuItems.map((item) => {
          const isActive = pathname === item.href || (item.href !== '/' && pathname?.startsWith(item.href));
          
          return (
            <Link key={item.name} href={item.href}>
              <motion.div
                whileHover={{ x: 4 }}
                whileTap={{ scale: 0.98 }}
                className={cn(
                  "group relative flex items-center justify-between p-4 rounded-2xl transition-all duration-300",
                  isActive 
                    ? "bg-white/15 text-white shadow-xl shadow-black/5" 
                    : "text-purple-200/60 hover:bg-white/5 hover:text-white"
                )}
              >
                <div className="flex items-center gap-4">
                  <item.icon className={cn(
                    "w-5 h-5 transition-colors duration-300",
                    isActive ? "text-purple-300" : "group-hover:text-purple-300"
                  )} />
                  <span className="font-medium">{item.name}</span>
                </div>
                {isActive && (
                  <motion.div
                    layoutId="active-indicator"
                    className="w-1.5 h-1.5 rounded-full bg-purple-400"
                  />
                )}
                {!isActive && (
                  <ChevronRight className="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity -rotate-45 group-hover:rotate-0" />
                )}
              </motion.div>
            </Link>
          );
        })}
      </nav>

      <div className="p-4 mt-auto">
        <div className="p-4 rounded-2xl bg-white/5 border border-white/10 mb-4">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center">
              <span className="text-purple-300 font-bold">VR</span>
            </div>
            <div>
              <p className="text-white text-sm font-semibold truncate">Vinicius R.</p>
              <p className="text-purple-300/50 text-xs truncate">Gestor Comercial</p>
            </div>
          </div>
          <button className="w-full py-2.5 rounded-xl bg-white/5 hover:bg-red-500/10 text-purple-200/50 hover:text-red-400 border border-white/5 hover:border-red-500/20 transition-all flex items-center justify-center gap-2 text-sm font-medium">
            <LogOut className="w-4 h-4" />
            <span>Sair da conta</span>
          </button>
        </div>
      </div>
    </aside>
  );
}
