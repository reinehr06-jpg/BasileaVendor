'use client';

import { useState, useEffect, useRef } from 'react';
import { 
  getConversations, 
  getConversation, 
  sendMessage, 
  resolveConversation, 
  getChatStats, 
  ChatConversation, 
  ChatMessage, 
  ChatStats 
} from '@/lib/chat';
import { 
  Search, 
  MoreVertical, 
  Send, 
  Paperclip, 
  CheckCheck, 
  User, 
  Hash, 
  Clock,
  Filter,
  ChevronLeft,
  AlertCircle,
  MessageSquare
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion, AnimatePresence } from 'framer-motion';

export default function ChatPage() {
  const [activeTab, setActiveTab] = useState<'nao_atendido' | 'atendido'>('nao_atendido');
  const [conversations, setConversations] = useState<ChatConversation[]>([]);
  const [selectedConversation, setSelectedConversation] = useState<ChatConversation | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const [stats, setStats] = useState<ChatStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    loadStats();
    loadConversations();
  }, [activeTab]);

  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
    }
  }, [messages]);

  const loadStats = async () => {
    try {
      const data = await getChatStats();
      setStats(data);
    } catch (e) {
      console.error('Failed to load stats', e);
    }
  };

  const loadConversations = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await getConversations({ status: 'open', atendimento: activeTab });
      setConversations(data.data);
    } catch (e) {
      console.error('Failed to load conversations', e);
      setError('Erro ao carregar conversas');
    } finally {
      setLoading(false);
    }
  };

  const selectConversation = async (conv: ChatConversation) => {
    setSelectedConversation(conv);
    setError(null);
    try {
      const data = await getConversation(conv.id);
      setMessages(data.messages?.data || []);
    } catch (e) {
      console.error('Failed to load messages', e);
      setError('Erro ao carregar mensagens');
    }
  };

  const handleSendMessage = async () => {
    if (!newMessage.trim() || !selectedConversation) return;
    
    const text = newMessage;
    const optimisticMessage: ChatMessage = {
      id: Date.now(),
      conversation_id: selectedConversation.id,
      direction: 'outbound',
      content: text,
      type: 'text',
      created_at: new Date().toISOString(),
    };
    
    setMessages(prev => [...prev, optimisticMessage]);
    setNewMessage('');
    setError(null);

    try {
      const msg = await sendMessage(selectedConversation.id, text);
      // Replace optimistic message with actual if needed, but for now just wait for next poll
      // Or update messages with the real one
      setMessages(prev => prev.map(m => m.id === optimisticMessage.id ? msg : m));
    } catch (e) {
      console.error('Failed to send message', e);
      setError('Erro ao enviar mensagem');
      setMessages(prev => prev.filter(m => m.id !== optimisticMessage.id));
      setNewMessage(text); // Put back the text
    }
  };

  const handleResolve = async () => {
    if (!selectedConversation) return;
    try {
      await resolveConversation(selectedConversation.id);
      setSelectedConversation(null);
      loadConversations();
      loadStats();
    } catch (e) {
      console.error('Failed to resolve', e);
      setError('Erro ao finalizar atendimento');
    }
  };

  const filteredConversations = conversations.filter(conv => {
    const name = conv.contact?.name || conv.contact?.nome || '';
    const phone = conv.contact?.phone || conv.contact?.telefone || '';
    return (name + phone).toLowerCase().includes(searchQuery.toLowerCase());
  });

  return (
    <div className="h-[calc(100vh-140px)] flex gap-6">
      {/* Sidebar - Conversation List */}
      <div className="w-[380px] flex flex-col glass rounded-3xl overflow-hidden border border-border/50 shadow-xl">
        <div className="p-6 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-foreground">Conversas</h2>
            <div className="flex items-center gap-2">
              <button className="p-2 rounded-xl hover:bg-surface-hover text-purple-400 transition-colors">
                <Filter className="w-4 h-4" />
              </button>
              <button className="p-2 rounded-xl hover:bg-surface-hover text-purple-400 transition-colors">
                <MoreVertical className="w-4 h-4" />
              </button>
            </div>
          </div>

          <div className="relative group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-purple-400 group-focus-within:text-purple-600 transition-colors" />
            <input 
              type="text" 
              placeholder="Buscar por nome ou telefone..." 
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-surface/50 border border-border/50 rounded-2xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>

          <div className="flex p-1 bg-surface-hover rounded-2xl">
            <button
              onClick={() => setActiveTab('nao_atendido')}
              className={cn(
                "flex-1 py-2 px-3 text-xs font-bold rounded-xl transition-all",
                activeTab === 'nao_atendido' 
                  ? "bg-white text-primary shadow-sm" 
                  : "text-purple-400 hover:text-purple-600"
              )}
            >
              Novos {stats && <span className="ml-1 opacity-60">({stats.nao_atendido})</span>}
            </button>
            <button
              onClick={() => setActiveTab('atendido')}
              className={cn(
                "flex-1 py-2 px-3 text-xs font-bold rounded-xl transition-all",
                activeTab === 'atendido' 
                  ? "bg-white text-primary shadow-sm" 
                  : "text-purple-400 hover:text-purple-600"
              )}
            >
              Em aberto {stats && <span className="ml-1 opacity-60">({stats.atendido})</span>}
            </button>
          </div>
        </div>

        {error && (
          <div className="mx-6 mb-4 p-3 rounded-xl bg-red-50 border border-red-100 flex items-center gap-2 text-xs font-bold text-red-500">
            <AlertCircle className="w-4 h-4" />
            {error}
          </div>
        )}

        <div className="flex-1 overflow-y-auto px-3 pb-6 space-y-1 custom-scrollbar">
          {loading ? (
            <div className="py-20 text-center animate-pulse">
               <div className="w-12 h-12 bg-purple-100 rounded-full mx-auto mb-4" />
               <p className="text-sm text-purple-300 font-medium">Carregando...</p>
            </div>
          ) : filteredConversations.length === 0 ? (
            <div className="py-20 text-center">
               <p className="text-sm text-purple-300 font-medium italic">Nenhuma conversa encontrada</p>
            </div>
          ) : (
            filteredConversations.map(conv => {
              const name = conv.contact?.name || conv.contact?.nome || conv.contact?.phone || conv.contact?.telefone || 'Contato';
              return (
                <motion.div
                  key={conv.id}
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  onClick={() => selectConversation(conv)}
                  className={cn(
                    "p-4 rounded-2xl cursor-pointer transition-all border border-transparent",
                    selectedConversation?.id === conv.id 
                      ? "bg-white shadow-md border-border/50" 
                      : "hover:bg-white/40"
                  )}
                >
                  <div className="flex gap-4">
                    <div className="relative">
                      <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-100 to-purple-50 flex items-center justify-center text-primary font-bold shadow-inner uppercase">
                        {name[0]}
                      </div>
                      {conv.unread_count > 0 && (
                        <span className="absolute -top-1 -right-1 w-5 h-5 bg-pink-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full border-2 border-white">
                          {conv.unread_count}
                        </span>
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex justify-between items-start mb-0.5">
                        <h4 className="font-bold text-foreground truncate">{name}</h4>
                        <span className="text-[10px] text-purple-300 font-bold uppercase translate-y-1">
                          {conv.last_inbound_at ? new Date(conv.last_inbound_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}
                        </span>
                      </div>
                      <p className="text-xs text-purple-600/50 font-medium truncate">
                        {conv.contact?.source} • {conv.atendimento_status === 'nao_atendido' ? 'Aguardando' : 'Em atendimento'}
                      </p>
                      {conv.vendedor && (
                        <div className="mt-2 flex items-center gap-1.5">
                           <div className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                           <span className="text-[10px] font-bold text-emerald-600/70 uppercase tracking-tighter">Atendente: {conv.vendedor?.user?.name}</span>
                        </div>
                      )}
                    </div>
                  </div>
                </motion.div>
              );
            })
          )}
        </div>
      </div>

      {/* Main Chat Area */}
      <div className="flex-1 flex flex-col glass rounded-3xl overflow-hidden border border-border/50 shadow-2xl relative">
        <AnimatePresence mode="wait">
          {selectedConversation ? (
            <motion.div 
              key={selectedConversation.id}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.98 }}
              className="flex flex-col h-full"
            >
              {/* Header */}
              <div className="p-6 glass border-b border-border/30 flex justify-between items-center z-10">
                <div className="flex items-center gap-4">
                  <button onClick={() => setSelectedConversation(null)} className="lg:hidden p-2 rounded-xl bg-surface-hover text-primary">
                    <ChevronLeft className="w-5 h-5" />
                  </button>
                  <div className="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-bold text-lg shadow-sm uppercase">
                    {(selectedConversation.contact?.name || selectedConversation.contact?.nome || 'U')[0]}
                  </div>
                  <div>
                    <h3 className="font-bold text-lg text-foreground leading-tight">
                      {selectedConversation.contact?.name || selectedConversation.contact?.nome || selectedConversation.contact?.phone}
                    </h3>
                    <div className="flex items-center gap-2 text-xs font-bold text-purple-600/50">
                      <span className="bg-purple-100 px-2 py-0.5 rounded text-primary">{selectedConversation.contact?.source}</span>
                      <span className="flex items-center gap-1">
                        <div className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                        Online agora
                      </span>
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <button 
                    onClick={handleResolve}
                    className="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2"
                  >
                    <CheckCircle2 className="w-4 h-4" />
                    <span className="hidden sm:inline">Finalizar Atendimento</span>
                  </button>
                  <button className="p-2.5 rounded-xl bg-surface-hover text-purple-400 border border-border/50">
                    <MoreVertical className="w-5 h-5" />
                  </button>
                </div>
              </div>

              {/* Messages area */}
              <div ref={scrollRef} className="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar bg-white/10">
                {messages.length === 0 ? (
                  <div className="h-full flex items-center justify-center">
                    <div className="text-center p-12 rounded-3xl border-2 border-dashed border-border/50 max-w-sm">
                      <MessageSquare className="w-10 h-10 text-purple-200 mx-auto mb-4" />
                      <p className="text-purple-300 font-medium">Inicie uma conversa agora mesmo enviando uma mensagem.</p>
                    </div>
                  </div>
                ) : (
                  messages.map((msg, i) => (
                    <motion.div
                      key={msg.id}
                      initial={{ opacity: 0, y: 10, scale: 0.95 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      className={cn(
                        "flex w-full mb-4",
                        msg.direction === 'outbound' ? "justify-end" : "justify-start"
                      )}
                    >
                      <div className={cn(
                        "flex gap-3 max-w-[80%]",
                        msg.direction === 'outbound' ? "flex-row-reverse" : "flex-row"
                      )}>
                        <div className={cn(
                          "w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-[10px] font-bold shadow-sm uppercase",
                          msg.direction === 'outbound' ? "bg-primary text-white" : "bg-white text-purple-600"
                        )}>
                          {msg.direction === 'outbound' ? 'EU' : (selectedConversation.contact?.name?.[0] || 'U')}
                        </div>
                        <div>
                          <div
                            className={cn(
                              "px-5 py-3 rounded-2xl text-sm leading-relaxed shadow-sm",
                              msg.direction === 'outbound' 
                                ? "bg-primary text-white rounded-tr-none" 
                                : "bg-white text-foreground rounded-tl-none border border-border/30"
                            )}
                          >
                            {msg.content || msg.conteudo}
                          </div>
                          <div className={cn(
                            "flex items-center gap-1.5 mt-1.5 text-[10px] font-bold",
                            msg.direction === 'outbound' ? "justify-end text-purple-600/40" : "text-purple-600/40"
                          )}>
                            <Clock className="w-3 h-3" />
                            {new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            {msg.direction === 'outbound' && <CheckCheck className="w-3 h-3 text-emerald-500" />}
                          </div>
                        </div>
                      </div>
                    </motion.div>
                  ))
                )}
              </div>

              {/* Input Area */}
              <div className="p-6 bg-white/40 border-t border-border/30 backdrop-blur-md">
                <div className="bg-white rounded-2xl p-2 shadow-xl shadow-black/5 flex items-end gap-2 border border-border/50">
                  <button className="p-3 rounded-xl hover:bg-surface-hover text-purple-400 transition-colors">
                    <Paperclip className="w-5 h-5" />
                  </button>
                  <textarea
                    rows={1}
                    value={newMessage}
                    onChange={e => setNewMessage(e.target.value)}
                    onKeyDown={e => {
                      if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        handleSendMessage();
                      }
                    }}
                    placeholder="Escreva sua mensagem aqui..."
                    className="flex-1 bg-transparent border-none focus:ring-0 text-sm py-3 px-2 resize-none placeholder:text-purple-200"
                  />
                  <div className="flex items-center gap-2">
                    <button className="p-3 rounded-xl hover:bg-surface-hover text-purple-400 transition-colors hidden sm:flex">
                      <Hash className="w-5 h-5" />
                    </button>
                    <button
                      onClick={handleSendMessage}
                      disabled={!newMessage.trim()}
                      className="p-3 bg-primary text-white rounded-xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:scale-100 transition-all"
                    >
                      <Send className="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>
            </motion.div>
          ) : (
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="flex-1 flex flex-col items-center justify-center p-12 text-center"
            >
              <div className="w-32 h-32 rounded-full bg-primary/5 flex items-center justify-center mb-8 pulse-slow">
                 <MessageSquare className="w-16 h-16 text-primary/20" />
              </div>
              <h3 className="text-2xl font-bold text-foreground mb-4">Escolha uma conversa</h3>
              <p className="text-purple-600/50 max-w-sm font-medium">
                Selecione um cliente na lista lateral para visualizar o histórico de mensagens e iniciar um novo atendimento.
              </p>
              <div className="mt-10 flex gap-4">
                 <div className="px-4 py-2 rounded-2xl bg-white/50 border border-border/50 text-xs font-bold text-purple-400 uppercase tracking-widest">Alt + N para novo</div>
                 <div className="px-4 py-2 rounded-2xl bg-white/50 border border-border/50 text-xs font-bold text-purple-400 uppercase tracking-widest">Esc para fechar</div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
}