"use client";

import React, { useEffect, useState, useRef } from "react";
import { useParams, useRouter } from "next/navigation";
import { LineChart, Line, ResponsiveContainer, YAxis, Tooltip } from "recharts";
import { Terminal, Database, Server, Cpu, HardDrive, Play, Pause, Search, Filter } from "lucide-react";

export default function SysadminDashboard() {
  const params = useParams();
  const router = useRouter();
  const key = params.key as string;

  const [metrics, setMetrics] = useState<any>(null);
  const [metricsHistory, setMetricsHistory] = useState<any[]>([]);
  const [logs, setLogs] = useState<any[]>([]);
  const [isLive, setIsLive] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Filters
  const [sourceFilter, setSourceFilter] = useState("");
  const [levelFilter, setLevelFilter] = useState("");
  const [searchFilter, setSearchFilter] = useState("");

  const logsEndRef = useRef<HTMLDivElement>(null);

  const fetchMetrics = async () => {
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'}/sysadmin/metrics`, {
        headers: { 'X-Sysadmin-Key': key }
      });
      if (res.status === 401) {
        setError("Acesso Negado. Chave inválida.");
        return;
      }
      const data = await res.json();
      setMetrics(data);
      setMetricsHistory(prev => {
        const newHist = [...prev, { ...data, time: new Date().toLocaleTimeString() }];
        return newHist.slice(-20); // Keep last 20 ticks
      });
    } catch (e) {
      console.error(e);
    }
  };

  const fetchLogs = async () => {
    try {
      let url = `${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'}/sysadmin/logs?`;
      if (sourceFilter) url += `source=${sourceFilter}&`;
      if (levelFilter) url += `level=${levelFilter}&`;
      if (searchFilter) url += `search=${searchFilter}&`;

      const res = await fetch(url, {
        headers: { 'X-Sysadmin-Key': key }
      });
      if (res.status === 200) {
        const data = await res.json();
        setLogs(data.data.reverse()); // Show oldest to newest for terminal feel
      }
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    fetchMetrics();
    fetchLogs();
    
    let interval: NodeJS.Timeout;
    if (isLive && !error) {
      interval = setInterval(() => {
        fetchMetrics();
        fetchLogs();
      }, 3000);
    }
    
    return () => clearInterval(interval);
  }, [key, isLive, sourceFilter, levelFilter, searchFilter, error]);

  useEffect(() => {
    if (isLive && logsEndRef.current) {
      logsEndRef.current.scrollIntoView({ behavior: "smooth" });
    }
  }, [logs, isLive]);

  if (error) {
    return (
      <div className="min-h-screen bg-black flex items-center justify-center text-red-500 font-mono text-xl">
        {error}
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#0A0A0A] text-[#E5E7EB] font-mono flex flex-col p-4">
      {/* HEADER */}
      <header className="flex justify-between items-center pb-4 border-b border-[#27272A] mb-4">
        <div className="flex items-center gap-3">
          <Terminal className="text-green-500 w-6 h-6" />
          <h1 className="text-xl font-bold tracking-wider text-green-500">SYSADMIN_CONSOLE</h1>
        </div>
        <div className="flex gap-4">
          <div className="flex items-center gap-2 text-sm">
            <div className={`w-3 h-3 rounded-full ${metrics?.db_status === 'ok' ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`}></div>
            <span>DB_STATUS</span>
          </div>
          <div className="flex items-center gap-2 text-sm">
            <div className="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
            <span>API_STATUS</span>
          </div>
        </div>
      </header>

      {/* METRICS ROW */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 h-[120px]">
        {/* CPU */}
        <div className="bg-[#18181B] border border-[#27272A] rounded-lg p-3 flex flex-col relative overflow-hidden">
          <div className="flex justify-between items-center z-10">
            <span className="text-xs text-gray-400 flex items-center gap-2"><Cpu size={14}/> CPU_LOAD</span>
            <span className="text-sm font-bold text-green-400">{metrics?.cpu || 0}%</span>
          </div>
          <div className="absolute inset-0 pt-6 opacity-30 pointer-events-none">
             <ResponsiveContainer width="100%" height="100%">
               <LineChart data={metricsHistory}>
                 <YAxis domain={[0, 100]} hide />
                 <Line type="monotone" dataKey="cpu" stroke="#4ade80" strokeWidth={2} dot={false} isAnimationActive={false} />
               </LineChart>
             </ResponsiveContainer>
          </div>
        </div>

        {/* RAM */}
        <div className="bg-[#18181B] border border-[#27272A] rounded-lg p-3 flex flex-col relative overflow-hidden">
          <div className="flex justify-between items-center z-10">
            <span className="text-xs text-gray-400 flex items-center gap-2"><Server size={14}/> MEMORY</span>
            <span className="text-sm font-bold text-blue-400">{metrics?.memory_mb || 0} MB</span>
          </div>
          <div className="absolute inset-0 pt-6 opacity-30 pointer-events-none">
             <ResponsiveContainer width="100%" height="100%">
               <LineChart data={metricsHistory}>
                 <YAxis domain={[0, 'dataMax + 50']} hide />
                 <Line type="monotone" dataKey="memory_mb" stroke="#60a5fa" strokeWidth={2} dot={false} isAnimationActive={false} />
               </LineChart>
             </ResponsiveContainer>
          </div>
        </div>

        {/* DISK */}
        <div className="bg-[#18181B] border border-[#27272A] rounded-lg p-3 flex flex-col justify-center">
          <div className="flex justify-between items-center mb-2">
            <span className="text-xs text-gray-400 flex items-center gap-2"><HardDrive size={14}/> DISK_USAGE</span>
            <span className="text-sm font-bold text-yellow-400">{metrics?.disk_percent || 0}%</span>
          </div>
          <div className="w-full bg-[#27272A] rounded-full h-2">
            <div className={`h-2 rounded-full ${metrics?.disk_percent > 80 ? 'bg-red-500' : 'bg-yellow-400'}`} style={{ width: `${metrics?.disk_percent || 0}%` }}></div>
          </div>
        </div>
      </div>

      {/* TERMINAL AREA */}
      <div className="flex-1 flex flex-col bg-[#18181B] border border-[#27272A] rounded-lg overflow-hidden relative shadow-[0_0_20px_rgba(34,197,94,0.05)]">
        {/* Toolbar */}
        <div className="bg-[#09090B] px-4 py-2 border-b border-[#27272A] flex flex-wrap gap-4 items-center text-xs">
          <button 
            onClick={() => setIsLive(!isLive)}
            className={`flex items-center gap-2 px-3 py-1 rounded border ${isLive ? 'border-green-500/50 text-green-400 bg-green-500/10' : 'border-gray-600 text-gray-400 hover:bg-gray-800'}`}
          >
            {isLive ? <Pause size={14} /> : <Play size={14} />}
            {isLive ? 'LIVE' : 'PAUSED'}
          </button>
          
          <div className="flex items-center gap-2 bg-[#18181B] border border-[#27272A] px-2 rounded">
            <Filter size={14} className="text-gray-400" />
            <select value={sourceFilter} onChange={(e) => setSourceFilter(e.target.value)} className="bg-transparent text-gray-300 outline-none py-1">
              <option value="">ALL_SOURCES</option>
              <option value="frontend">FRONTEND</option>
              <option value="backend">BACKEND</option>
              <option value="database">DATABASE</option>
            </select>
          </div>

          <div className="flex items-center gap-2 bg-[#18181B] border border-[#27272A] px-2 rounded">
            <Filter size={14} className="text-gray-400" />
            <select value={levelFilter} onChange={(e) => setLevelFilter(e.target.value)} className="bg-transparent text-gray-300 outline-none py-1">
              <option value="">ALL_LEVELS</option>
              <option value="error">ERROR</option>
              <option value="info">INFO</option>
              <option value="warning">WARNING</option>
            </select>
          </div>

          <div className="flex-1 flex items-center gap-2 bg-[#18181B] border border-[#27272A] px-2 rounded min-w-[200px]">
            <Search size={14} className="text-gray-400" />
            <input 
              type="text" 
              placeholder="Grep logs..." 
              value={searchFilter}
              onChange={(e) => setSearchFilter(e.target.value)}
              className="bg-transparent w-full text-gray-300 outline-none py-1"
            />
          </div>
        </div>

        {/* Logs Scroll View */}
        <div className="flex-1 overflow-y-auto p-4 text-[13px] leading-relaxed font-mono">
          {logs.length === 0 && (
            <div className="text-gray-500 italic">Waiting for incoming logs...</div>
          )}
          {logs.map((log) => (
            <div key={log.id} className="mb-2 group hover:bg-[#27272A]/50 p-1 rounded transition-colors break-words">
              <span className="text-gray-500">[{new Date(log.created_at).toLocaleString()}] </span>
              <span className={`font-bold ${log.source === 'frontend' ? 'text-blue-400' : log.source === 'database' ? 'text-purple-400' : 'text-green-400'}`}>[{log.source.toUpperCase()}] </span>
              <span className={`font-bold ${log.level === 'error' ? 'text-red-500' : log.level === 'warning' ? 'text-yellow-400' : 'text-gray-300'}`}>[{log.level.toUpperCase()}] </span>
              <span className={log.level === 'error' ? 'text-red-400/90' : 'text-gray-300'}>{log.message}</span>
              
              {log.payload && Object.keys(log.payload).length > 0 && (
                <pre className="mt-1 ml-4 p-2 bg-black/50 border border-[#27272A] rounded text-xs text-gray-400 overflow-x-auto">
                  {JSON.stringify(log.payload, null, 2)}
                </pre>
              )}
            </div>
          ))}
          <div ref={logsEndRef} />
        </div>
      </div>
    </div>
  );
}
