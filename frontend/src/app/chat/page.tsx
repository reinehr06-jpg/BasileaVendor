'use client';

import { useState, useEffect } from 'react';
import { getConversations, getConversation, sendMessage, resolveConversation, getChatStats, ChatConversation, ChatMessage, ChatStats } from '@/lib/chat';

export default function ChatPage() {
  const [activeTab, setActiveTab] = useState<'nao_atendido' | 'atendido'>('nao_atendido');
  const [conversations, setConversations] = useState<ChatConversation[]>([]);
  const [selectedConversation, setSelectedConversation] = useState<ChatConversation | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const [stats, setStats] = useState<ChatStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadStats();
    loadConversations();
  }, [activeTab]);

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
      const data = await getConversations({ atendimento: activeTab });
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
    try {
      const msg = await sendMessage(selectedConversation.id, newMessage);
      setMessages(prev => [...prev, msg]);
      setNewMessage('');
      loadConversations();
    } catch (e) {
      console.error('Failed to send message', e);
      setError('Erro ao enviar mensagem');
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
    }
  };

  const getContactName = (conv: ChatConversation) => {
    return conv.contact?.name || conv.contact?.nome || conv.contact?.phone || conv.contact?.telefone || 'Contato';
  };

  const getStatusLabel = (conv: ChatConversation) => {
    if (conv.is_atendido === false || conv.atendimento_status === 'nao_atendido') return 'Não atendido';
    if (conv.is_atendido === true || conv.atendimento_status === 'atendido') return 'Atendido';
    return 'Pendente';
  };

  const formatTime = (dateStr: string | null | undefined) => {
    if (!dateStr) return 'Sem mensagens';
    return new Date(dateStr).toLocaleString('pt-BR', { 
      day: '2-digit', 
      month: '2-digit', 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };

  return (
    <div className="flex h-screen bg-gray-100">
      <div className="w-1/3 bg-white border-r">
        <div className="p-4 border-b">
          <h1 className="text-xl font-bold">Chat</h1>
          {stats && (
            <div className="flex gap-2 mt-2 text-sm">
              <span className="px-2 py-1 bg-blue-100 rounded">
                {stats.nao_atendido} não atendid{stats.nao_atendido !== 1 ? 'os' : 'o'}
              </span>
              <span className="px-2 py-1 bg-green-100 rounded">
                {stats.atendido} atendiment{stats.atendido !== 1 ? 'os' : 'o'}
              </span>
            </div>
          )}
        </div>
        <div className="flex border-b">
          <button
            className={`flex-1 p-3 text-center ${activeTab === 'nao_atendido' ? 'border-b-2 border-blue-500 font-semibold' : ''}`}
            onClick={() => setActiveTab('nao_atendido')}
          >
            Não Atendido{stats && ` (${stats.nao_atendido})`}
          </button>
          <button
            className={`flex-1 p-3 text-center ${activeTab === 'atendido' ? 'border-b-2 border-blue-500 font-semibold' : ''}`}
            onClick={() => setActiveTab('atendido')}
          >
            Atendido{stats && ` (${stats.atendido})`}
          </button>
        </div>
        <div className="overflow-y-auto h-[calc(100vh-150px)]">
          {loading ? (
            <div className="p-4 text-center text-gray-500">Carregando...</div>
          ) : error ? (
            <div className="p-4 text-center text-red-500">{error}</div>
          ) : conversations.length === 0 ? (
            <div className="p-4 text-center text-gray-500">Nenhuma conversa</div>
          ) : (
            conversations.map(conv => (
              <div
                key={conv.id}
                className={`p-4 border-b cursor-pointer hover:bg-gray-50 ${selectedConversation?.id === conv.id ? 'bg-blue-50' : ''}`}
                onClick={() => selectConversation(conv)}
              >
                <div className="flex justify-between items-center">
                  <span className="font-semibold">{getContactName(conv)}</span>
                  {(conv.unread_count > 0) && (
                    <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                      {conv.unread_count}
                    </span>
                  )}
                </div>
                <div className="text-sm text-gray-500 mt-1">
                  {formatTime(conv.last_message_at || conv.last_inbound_at)}
                </div>
                <div className="text-xs mt-1">
                  <span className={`px-2 py-0.5 rounded ${conv.is_atendido === false ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}`}>
                    {getStatusLabel(conv)}
                  </span>
                </div>
              </div>
            ))
          )}
        </div>
      </div>

      <div className="flex-1 flex flex-col">
        {selectedConversation ? (
          <>
            <div className="p-4 bg-white border-b flex justify-between items-center">
              <div>
                <h2 className="font-bold text-lg">
                  {getContactName(selectedConversation)}
                </h2>
                <div className="text-sm text-gray-500">
                  {selectedConversation.contact?.source} • {getStatusLabel(selectedConversation)}
                </div>
              </div>
              <button
                onClick={handleResolve}
                className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
              >
                Resolver
              </button>
            </div>
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
              {messages.length === 0 ? (
                <div className="text-center text-gray-400">Sem mensagens ainda</div>
              ) : (
                messages.map(msg => (
                  <div
                    key={msg.id}
                    className={`flex ${msg.direction === 'outbound' ? 'justify-end' : 'justify-start'}`}
                  >
                    <div
                      className={`max-w-md px-4 py-2 rounded-lg ${
                        msg.direction === 'outbound' ? 'bg-blue-500 text-white' : 'bg-gray-200'
                      }`}
                    >
                      {msg.content || msg.conteudo}
                      <div className={`text-xs mt-1 ${msg.direction === 'outbound' ? 'text-blue-100' : 'text-gray-500'}`}>
                        {new Date(msg.created_at).toLocaleTimeString('pt-BR')}
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
            <div className="p-4 bg-white border-t flex gap-2">
              <input
                type="text"
                value={newMessage}
                onChange={e => setNewMessage(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && handleSendMessage()}
                placeholder="Digite sua mensagem..."
                className="flex-1 border rounded px-4 py-2"
              />
              <button
                onClick={handleSendMessage}
                className="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                Enviar
              </button>
            </div>
          </>
        ) : (
          <div className="flex-1 flex items-center justify-center text-gray-400">
            Selecione uma conversa para começar
          </div>
        )}
      </div>
    </div>
  );
}