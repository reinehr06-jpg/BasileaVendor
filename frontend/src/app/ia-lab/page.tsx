'use client';

import { useState, useEffect } from 'react';
import { getProviders, testPrompt, evaluatePrompt, getHistory, IAProvider, IAEvaluation } from '@/lib/ia-lab';
import { 
  Beaker, 
  Cpu, 
  Send, 
  ThumbsUp, 
  ThumbsDown, 
  History, 
  Sparkles,
  Zap,
  ShieldCheck,
  AlertTriangle,
  RefreshCw,
  MessageSquare
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { motion, AnimatePresence } from 'framer-motion';

export default function IALabPage() {
  const [providers, setProviders] = useState<IAProvider[]>([]);
  const [selectedProvider, setSelectedProvider] = useState<IAProvider | null>(null);
  const [prompt, setPrompt] = useState('');
  const [response, setResponse] = useState('');
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState<IAEvaluation[]>([]);
  const [showFeedbackModal, setShowFeedbackModal] = useState(false);
  const [approved, setApproved] = useState<boolean | null>(null);
  const [disapprovalReason, setDisapprovalReason] = useState('');

  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      const provs = await getProviders();
      setProviders(provs);
      if (provs.length > 0) setSelectedProvider(provs[0]);
      
      const hist = await getHistory();
      setHistory(hist.data);
    } catch (e) {
      console.error('Failed to load initial data', e);
    }
  };

  const handleTest = async () => {
    if (!selectedProvider || !prompt.trim()) return;
    setLoading(true);
    setResponse('');
    try {
      const data = await testPrompt(selectedProvider.id, prompt);
      setResponse(data.response);
    } catch (e) {
      console.error('Failed to test prompt', e);
    } finally {
      setLoading(false);
    }
  };

  const handleEvaluate = async (isApproved: boolean) => {
    setApproved(isApproved);
    if (isApproved) {
      await submitEvaluation(true, '');
    } else {
      setShowFeedbackModal(true);
    }
  };

  const submitEvaluation = async (isApproved: boolean, reason: string) => {
    if (!selectedProvider) return;
    try {
      await evaluatePrompt({
        ia_model: selectedProvider.name,
        prompt: prompt,
        response: response,
        approved: isApproved,
        disapproval_reason: reason
      });
      setShowFeedbackModal(false);
      setDisapprovalReason('');
      setResponse('');
      setPrompt('');
      setApproved(null);
      loadInitialData(); // Refresh history
    } catch (e) {
      console.error('Failed to evaluate', e);
    }
  };

  return (
    <div className="space-y-8 pb-12">
      <div className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Laboratório de <span className="gradient-text">IA</span></h2>
          <p className="text-purple-600/50 mt-1 font-medium italic">Treine seu assistente, teste prompts e dê feedback para evolução.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Sandbox Area */}
        <div className="lg:col-span-2 space-y-6">
           <div className="glass p-8 rounded-3xl border border-border/50 shadow-xl bg-white/40">
              <div className="flex items-center gap-4 mb-8">
                 <div className="p-3 rounded-2xl bg-primary/10 text-primary">
                    <Sparkles className="w-6 h-6" />
                 </div>
                 <div>
                    <h3 className="text-xl font-bold text-foreground">Ambiente de Testes</h3>
                    <p className="text-sm text-purple-600/50 font-medium">Experimente novos comportamentos para sua IA.</p>
                 </div>
              </div>

              <div className="space-y-6">
                 <div>
                    <label className="block text-xs font-bold text-purple-400 uppercase tracking-widest mb-2">Selecione o Modelo</label>
                    <div className="flex gap-3 flex-wrap">
                       {providers.map(p => (
                          <button
                            key={p.id}
                            onClick={() => setSelectedProvider(p)}
                            className={cn(
                              "px-4 py-2.5 rounded-2xl text-sm font-bold border transition-all flex items-center gap-2",
                              selectedProvider?.id === p.id
                                ? "bg-primary text-white border-primary shadow-lg shadow-primary/20"
                                : "bg-white text-purple-600 border-border/50 hover:border-primary/30"
                            )}
                          >
                             <Cpu className="w-4 h-4" />
                             {p.name}
                          </button>
                       ))}
                    </div>
                 </div>

                 <div className="space-y-2">
                    <label className="block text-xs font-bold text-purple-400 uppercase tracking-widest">Seu Prompt</label>
                    <div className="relative group">
                       <textarea 
                         value={prompt}
                         onChange={(e) => setPrompt(e.target.value)}
                         rows={4}
                         placeholder="Descreva o que você deseja que a IA faça, ou faça uma pergunta direta para testar o contexto..."
                         className="w-full glass bg-white/50 border border-border/50 rounded-2xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                       />
                       <button 
                         onClick={handleTest}
                         disabled={loading || !prompt.trim()}
                         className="absolute bottom-4 right-4 p-3 bg-primary text-white rounded-xl shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 disabled:opacity-50 transition-all"
                       >
                          {loading ? <RefreshCw className="w-5 h-5 animate-spin" /> : <Send className="w-5 h-5" />}
                       </button>
                    </div>
                 </div>

                 <AnimatePresence>
                    {response && (
                      <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="space-y-4 pt-4 border-t border-border/30"
                      >
                         <label className="block text-xs font-bold text-purple-400 uppercase tracking-widest">Resposta da IA</label>
                         <div className="p-6 bg-primary/5 border border-primary/20 rounded-2xl text-sm leading-relaxed text-foreground italic shadow-inner">
                            {response}
                         </div>
                         <div className="flex items-center justify-between p-4 glass rounded-2xl border border-primary/20 bg-primary/5">
                            <p className="text-sm font-bold text-primary">Esta resposta atingiu sua expectativa?</p>
                            <div className="flex gap-3">
                               <button 
                                 onClick={() => handleEvaluate(true)}
                                 className="px-4 py-2 bg-emerald-500 text-white font-bold text-xs rounded-xl flex items-center gap-2 hover:bg-emerald-600 transition-all shadow-md shadow-emerald-500/20"
                               >
                                  <ThumbsUp className="w-4 h-4" />
                                  Aprovar
                               </button>
                               <button 
                                  onClick={() => handleEvaluate(false)}
                                  className="px-4 py-2 bg-pink-500 text-white font-bold text-xs rounded-xl flex items-center gap-2 hover:bg-pink-600 transition-all shadow-md shadow-pink-500/20"
                               >
                                  <ThumbsDown className="w-4 h-4" />
                                  Reprovar
                               </button>
                            </div>
                         </div>
                      </motion.div>
                    )}
                 </AnimatePresence>
              </div>
           </div>
        </div>

        {/* History / Memory Area */}
        <div className="space-y-6">
           <div className="glass p-8 rounded-3xl border border-border/50 shadow-xl bg-white/40 h-full flex flex-col">
              <div className="flex items-center gap-4 mb-8">
                 <div className="p-3 rounded-2xl bg-purple-500/10 text-purple-600">
                    <History className="w-6 h-6" />
                 </div>
                 <div>
                    <h3 className="text-xl font-bold text-foreground">Memória Local</h3>
                    <p className="text-sm text-purple-600/50 font-medium">Histórico de aprendizado da sua IA.</p>
                 </div>
              </div>

              <div className="flex-1 space-y-4 overflow-y-auto pr-2 custom-scrollbar">
                 {history.length === 0 ? (
                   <div className="py-20 text-center opacity-40">
                      <MessageSquare className="w-12 h-12 mx-auto mb-4" />
                      <p className="text-sm font-medium italic">Nenhum feedback registrado ainda.</p>
                   </div>
                 ) : history.map((h, i) => (
                   <div key={h.id} className="p-4 rounded-2xl bg-white/50 border border-border/30 hover:shadow-md transition-all">
                      <div className="flex justify-between items-start mb-2">
                         <span className="text-[9px] font-black text-purple-300 uppercase tracking-tighter">
                            {new Date(h.created_at).toLocaleDateString()}
                         </span>
                         {h.approved ? (
                           <ShieldCheck className="w-4 h-4 text-emerald-500" />
                         ) : (
                           <AlertTriangle className="w-4 h-4 text-pink-500" />
                         )}
                      </div>
                      <p className="text-xs font-bold text-foreground truncate mb-1">{h.prompt}</p>
                      <p className="text-[10px] text-purple-600/50 line-clamp-2 italic">{h.response}</p>
                      {!h.approved && h.disapproval_reason && (
                        <div className="mt-2 text-[9px] font-bold text-pink-600/60 p-1 px-2 rounded bg-pink-50">
                           Motivo: {h.disapproval_reason}
                        </div>
                      )}
                   </div>
                 ))}
              </div>
           </div>
        </div>
      </div>

      {/* Disapproval Feedback Modal */}
      <AnimatePresence>
        {showFeedbackModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
             <motion.div 
               initial={{ opacity: 0 }} 
               animate={{ opacity: 1 }} 
               exit={{ opacity: 0 }}
               className="absolute inset-0 bg-black/40 backdrop-blur-sm"
               onClick={() => setShowFeedbackModal(false)}
             />
             <motion.div
               initial={{ scale: 0.9, opacity: 0 }}
               animate={{ scale: 1, opacity: 1 }}
               exit={{ scale: 0.9, opacity: 0 }}
               className="relative glass w-full max-w-md p-8 rounded-[2rem] border border-white/20 shadow-2xl"
             >
                <div className="p-4 bg-pink-500/10 text-pink-500 rounded-2xl w-fit mb-6">
                   <ThumbsDown className="w-8 h-8" />
                </div>
                <h3 className="text-2xl font-bold text-foreground mb-2">Como podemos melhorar?</h3>
                <p className="text-sm text-purple-600/50 font-medium mb-6">
                   Para que a IA aprenda corretamente, descreva o que faltou na resposta ou o que estava errado.
                </p>

                <textarea
                  value={disapprovalReason}
                  onChange={(e) => setDisapprovalReason(e.target.value)}
                  rows={3}
                  placeholder="Ex: A resposta foi muito formal, ou as informações de cobrança estavam incorretas..."
                  className="w-full glass bg-white/50 border border-border/50 rounded-2xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 transition-all resize-none mb-6"
                />

                <div className="flex gap-3">
                   <button 
                     onClick={() => setShowFeedbackModal(false)}
                     className="flex-1 py-3 px-6 bg-surface border border-border/50 text-purple-600 font-bold rounded-2xl hover:bg-surface-hover transition-all"
                   >
                     Cancelar
                   </button>
                   <button 
                     onClick={() => submitEvaluation(false, disapprovalReason)}
                     className="flex-1 py-3 px-6 bg-pink-500 text-white font-bold rounded-2xl shadow-lg shadow-pink-500/20 hover:bg-pink-600 transition-all"
                   >
                     Salvar Feedback
                   </button>
                </div>
             </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
}
