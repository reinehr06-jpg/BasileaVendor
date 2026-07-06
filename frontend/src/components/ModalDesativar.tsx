import React, { useState } from "react";
import { 
  AlertTriangle, 
  X 
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";

interface ModalDesativarProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (motivo: string) => void;
  itemName?: string;
  title?: string;
  description?: string;
}

export default function ModalDesativar({ isOpen, onClose, onConfirm, itemName, title, description }: ModalDesativarProps) {
  const { t } = useTranslation();
  const [motivo, setMotivo] = useState("");

  if (!isOpen) return null;

  const handleConfirm = () => {
    if (!motivo.trim()) {
      toast.error(t("Por favor, informe o motivo da desativação."));
      return;
    }
    onConfirm(motivo);
    setMotivo("");
  };

  return (
    <div className="fixed inset-0 bg-[#1A1A2E]/50 backdrop-blur-sm z-[999] flex items-center justify-center p-[20px]">
      <div className="bg-white rounded-[20px] w-full max-w-[500px] shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        {/* Header */}
        <div className="p-[24px] flex items-start justify-between border-b border-[#F1F1F4] bg-[#F4EEFF]">
          <div className="flex items-center gap-[16px]">
            <div className="w-[48px] h-[48px] rounded-[12px] bg-white border border-[#E9D5FF] flex items-center justify-center shrink-0 shadow-sm">
              <AlertTriangle className="w-[24px] h-[24px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col">
              <h2 className="text-[18px] font-[800] text-[#5B21B6] leading-tight">{t(title || "Desativar Item")}</h2>
              {itemName && <p className="text-[13px] text-[#6D28D9] font-[500] mt-0.5">{itemName}</p>}
            </div>
          </div>
          <button 
            onClick={onClose}
            className="w-[32px] h-[32px] rounded-full bg-white flex items-center justify-center hover:bg-[#F4EEFF] transition-colors"
          >
            <X className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.5} />
          </button>
        </div>

        {/* Body */}
        <div className="p-[24px] flex flex-col gap-[20px]">
          <p className="text-[14px] text-[#4B5563] leading-relaxed">
            {description ? t(description) : (
              <>{t("Ao desativar este item, ele ")}<strong className="text-[#1A1A2E]">{t("perderá imediatamente")}</strong>{t(" o acesso ao sistema. O histórico será mantido, mas ele não poderá registrar novas movimentações.")}</>
            )}
          </p>

          <div className="flex flex-col gap-[8px]">
            <label className="text-[12px] font-[700] text-[#4B5563] uppercase tracking-wider">
              {t("Motivo da desativação")} <span className="text-[#6D28D9]">*</span>
            </label>
            <textarea
              value={motivo}
              onChange={(e) => setMotivo(e.target.value)}
              placeholder={t("Descreva brevemente o motivo... (Este motivo ficará salvo no histórico)")}
              className="w-full min-h-[100px] bg-white border border-[#E5E7EB] rounded-[10px] p-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all resize-y placeholder-[#9CA3AF]"
            />
          </div>
        </div>

        {/* Footer */}
        <div className="p-[20px_24px] border-t border-[#F1F1F4] flex items-center justify-end gap-[12px] bg-[#F9FAFB]">
          <button 
            onClick={onClose}
            className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[10px] hover:bg-[#F3F4F6] transition-colors"
          >
            {t("Cancelar")}
          </button>
          <button 
            onClick={handleConfirm}
            className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[700] text-[14px] rounded-[10px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center shadow-sm"
          >
            {t("Confirmar Desativação")}
          </button>
        </div>

      </div>
    </div>
  );
}
