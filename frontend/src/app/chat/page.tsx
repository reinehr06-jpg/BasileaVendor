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
    try {
      const data = await getConversations({ status: 'open', atendimento: activeTab });
      setConversations(data.data);
    } catch (e) {
      console.error('Failed to load conversations', e);
    } finally {
      setLoading(false);
    }
  };

  const selectConversation = async (conv: ChatConversation) => {
    setSelectedConversation(conv);
    try {
      const data = await getConversation(conv.id);
      setMessages(data.messages.data);
    } catch (e) {
      console.error('Failed to load messages', e);
    }
  };

  const handleSendMessage = async () => {
    if (!newMessage.trim() || !selectedConversation) return;
    try {
      const msg = await sendMessage(selectedConversation.id, newMessage);
      setMessages([...messages, msg]);
      setNewMessage('');
    } catch (e) {
      console.error('Failed to send message', e);
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

  return (
    <div className="flex h-screen bg-gray-100">
      <div className="w-1/3 bg-white border-r">
        <div className="p-4 border-b">
          <h1 className="text-xl font-bold">Chat</h1>
          {stats && (
            <div className="flex gap-2 mt-2 text-sm">
              <span className="px-2 py-1 bg-blue-100 rounded">
                {stats.nao_atendido} não atendido{stats.nao_atendido !== 1 ? 's' : ''}
              </span>
              <span className="px-2 py-1 bg-green-100 rounded">
                {stats.atendido} atendimento{stats.atendido !== 1 ? 's' : ''}
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
          ) : conversations.length === 0 ? (
            <div className="p-4 text-center text-gray-500">Nenhuma conversa</div>
          ) : (
            conversations.map(conv => (
              <div
                key={conv.id}
                className={`p-4 border-b cursor-pointer hover:bg-gray-50 ${selectedConversation?.id === conv.id ? 'bg-blue-50' : ''}`}
                onClick={() => selectConversation(conv)}
              >
                <div className="flex justify-between">
                  <span className="font-semibold">{conv.contact.name || conv.contact.phone}</span>
                  {conv.unread_count > 0 && (
                    <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                      {conv.unread_count}
                    </span>
                  )}
                </div>
                <div className="text-sm text-gray-500 mt-1">
                  {conv.last_inbound_at ? new Date(conv.last_inbound_at).toLocaleString('pt-BR') : 'Sem mensagens'}
                </div>
                {conv.vendedor && (
                  <div className="text-xs text-gray-400 mt-1">
                    Atendente: {conv.vendedor.user.name}
                  </div>
                )}
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
                  {selectedConversation.contact.name || selectedConversation.contact.phone}
                </h2>
                <div className="text-sm text-gray-500">
                  {selectedConversation.contact.source} • {selectedConversation.atendimento_status === 'nao_atendido' ? 'Não atendido' : 'Atendido'}
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
              {messages.map(msg => (
                <div
                  key={msg.id}
                  className={`flex ${msg.direction === 'outbound' ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-md px-4 py-2 rounded-lg ${
                      msg.direction === 'outbound' ? 'bg-blue-500 text-white' : 'bg-gray-200'
                    }`}
                  >
                    {msg.content}
                    <div className={`text-xs mt-1 ${msg.direction === 'outbound' ? 'text-blue-100' : 'text-gray-500'}`}>
                      {new Date(msg.created_at).toLocaleTimeString('pt-BR')}
                    </div>
                  </div>
                </div>
              ))}
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