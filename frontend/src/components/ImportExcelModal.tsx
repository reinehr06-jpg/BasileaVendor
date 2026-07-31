"use client";

import React, { useState, useRef } from "react";
import { X, UploadCloud, DownloadCloud, FileSpreadsheet, CheckCircle } from "lucide-react";
import { toast } from "sonner";
import * as XLSX from "xlsx";
import { api } from "@/lib/api";

interface ImportExcelModalProps {
  isOpen: boolean;
  onClose: () => void;
  type: "clientes" | "comissoes";
}

export default function ImportExcelModal({ isOpen, onClose, type }: ImportExcelModalProps) {
  const [dragActive, setDragActive] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [previewData, setPreviewData] = useState<any[]>([]);
  const inputRef = useRef<HTMLInputElement>(null);

  if (!isOpen) return null;

  const title = type === "clientes" ? "Importar Clientes" : "Importar Comissões";
  const modelFileName = type === "clientes" ? "modelo_clientes.xlsx" : "modelo_comissoes.xlsx";

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(true);
    } else if (e.type === "dragleave") {
      setDragActive(false);
    }
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    e.preventDefault();
    if (e.target.files && e.target.files[0]) {
      handleFile(e.target.files[0]);
    }
  };

  const handleFile = (selectedFile: File) => {
    const validTypes = [
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      "application/vnd.ms-excel",
      "text/csv"
    ];
    if (!validTypes.includes(selectedFile.type) && !selectedFile.name.endsWith('.csv') && !selectedFile.name.endsWith('.xlsx')) {
      toast.error("Formato inválido. Envie um arquivo Excel (.xlsx) ou CSV.");
      return;
    }
    setFile(selectedFile);
    parseExcel(selectedFile);
  };

  const parseExcel = (file: File) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target?.result as ArrayBuffer);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheet];
        const json = XLSX.utils.sheet_to_json(worksheet);
        setPreviewData(json);
      } catch (err) {
        toast.error("Erro ao ler o arquivo Excel.");
        console.error(err);
      }
    };
    reader.readAsArrayBuffer(file);
  };

  const removeFile = () => {
    setFile(null);
    setPreviewData([]);
    if (inputRef.current) inputRef.current.value = "";
  };

  const handleUpload = async () => {
    if (!file) {
      toast.error("Selecione um arquivo primeiro.");
      return;
    }
    setIsUploading(true);
    
    try {
      const payload = {
        clientes: previewData
      };
      
      const res = await api.post("/importacao/excel", payload);
      if (res.success) {
        toast.success(res.message || `${file.name} importado com sucesso!`);
        removeFile();
        onClose();
      } else {
        toast.error(res.message || "Erro na importação.");
      }
    } catch (err) {
      toast.error("Erro ao conectar com o servidor.");
      console.error(err);
    } finally {
      setIsUploading(false);
    }
  };

  const downloadTemplate = () => {
    const templateData = [
      {
        Nome: "João da Silva",
        Documento: "12345678901",
        Email: "joao@email.com",
        Telefone: "11999999999",
        Vendedor_ID: 1,
        Data_Venda: "01/10/2023",
        Valor: 1500.50,
        Tipo_Venda: "mensal"
      }
    ];
    const ws = XLSX.utils.json_to_sheet(templateData);
    
    // Melhorando a estética: ajustando largura das colunas
    ws['!cols'] = [
      { wch: 25 }, // Nome
      { wch: 18 }, // Documento
      { wch: 30 }, // Email
      { wch: 16 }, // Telefone
      { wch: 14 }, // Vendedor_ID
      { wch: 14 }, // Data_Venda
      { wch: 12 }, // Valor
      { wch: 14 }, // Tipo_Venda
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Clientes");
    XLSX.writeFile(wb, modelFileName);
    toast.success(`Download de ${modelFileName} concluído.`);
  };

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-[500px] rounded-[16px] shadow-2xl flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
        
        {/* HEADER */}
        <div className="px-[24px] py-[20px] border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
          <div className="flex items-center gap-[12px]">
            <div className="w-[36px] h-[36px] bg-[#F4EEFF] rounded-[10px] flex items-center justify-center">
              <FileSpreadsheet className="w-[18px] h-[18px] text-[#6D28D9]" />
            </div>
            <h2 className="text-[16px] font-[700] text-[#111827]">{title}</h2>
          </div>
          <button 
            onClick={onClose}
            className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F3F4F6] transition-colors text-[#6B7280]"
          >
            <X className="w-[16px] h-[16px]" />
          </button>
        </div>

        {/* BODY */}
        <div className="p-[24px] flex flex-col gap-[20px]">
          
          <div className="flex flex-col gap-[8px]">
            <p className="text-[13px] text-[#4B5563]">
              Para garantir que os dados sejam importados corretamente, recomendamos baixar e preencher nossa planilha modelo.
            </p>
            <button 
              onClick={downloadTemplate}
              className="flex items-center gap-[8px] w-fit px-[16px] py-[8px] bg-white border border-[#D8B4FE] text-[#6D28D9] rounded-[8px] hover:bg-[#F4EEFF] transition-colors text-[13px] font-[600]"
            >
              <DownloadCloud className="w-[16px] h-[16px]" />
              Baixar Planilha Modelo
            </button>
          </div>

          <div className="w-full h-[1px] bg-[#E5E7EB]"></div>

          <div 
            className={`relative flex flex-col items-center justify-center p-[32px_24px] border-2 border-dashed rounded-[12px] transition-colors cursor-pointer
              ${dragActive ? "border-[#6D28D9] bg-[#F4EEFF]" : "border-[#D1D5DB] bg-[#F9FAFB] hover:border-[#9CA3AF]"}
              ${file ? "border-[#10B981] bg-[#ECFDF5]" : ""}
            `}
            onDragEnter={handleDrag}
            onDragLeave={handleDrag}
            onDragOver={handleDrag}
            onDrop={handleDrop}
            onClick={() => !file && inputRef.current?.click()}
          >
            <input 
              ref={inputRef}
              type="file" 
              accept=".xlsx,.xls,.csv" 
              onChange={handleChange} 
              className="hidden" 
            />
            
            {file ? (
              <div className="flex flex-col items-center gap-[12px] text-center w-full">
                <div className="w-[48px] h-[48px] bg-[#D1FAE5] rounded-full flex items-center justify-center">
                  <CheckCircle className="w-[24px] h-[24px] text-[#10B981]" />
                </div>
                <div className="flex flex-col">
                  <span className="text-[14px] font-[700] text-[#065F46] truncate max-w-[300px] px-4">{file.name}</span>
                  <span className="text-[12px] text-[#059669]">{(file.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
                <button 
                  onClick={(e) => { e.stopPropagation(); removeFile(); }}
                  className="mt-[8px] text-[13px] font-[600] text-[#EF4444] hover:underline"
                >
                  Remover arquivo
                </button>
              </div>
            ) : (
              <div className="flex flex-col items-center gap-[12px] text-center pointer-events-none">
                <div className="w-[48px] h-[48px] bg-white rounded-full flex items-center justify-center shadow-sm border border-[#E5E7EB]">
                  <UploadCloud className="w-[20px] h-[20px] text-[#6B7280]" />
                </div>
                <div className="flex flex-col">
                  <span className="text-[14px] font-[600] text-[#111827]">Clique para selecionar</span>
                  <span className="text-[13px] text-[#6B7280]">ou arraste a planilha aqui</span>
                </div>
                <span className="text-[11px] text-[#9CA3AF] mt-[4px]">Somente arquivos .xlsx ou .csv</span>
              </div>
            )}
          </div>

          {/* Preview */}
          {previewData.length > 0 && (
            <div className="bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] p-3 max-h-[150px] overflow-y-auto">
              <p className="text-[12px] font-[600] text-[#374151] mb-2">{previewData.length} registros encontrados.</p>
              <pre className="text-[10px] text-[#6B7280]">
                {JSON.stringify(previewData.slice(0, 2), null, 2)}
                {previewData.length > 2 && "\n... e mais."}
              </pre>
            </div>
          )}
        </div>

        {/* FOOTER */}
        <div className="px-[24px] py-[16px] border-t border-[#E5E7EB] flex items-center justify-end gap-[12px] bg-[#F9FAFB]">
          <button 
            onClick={onClose}
            className="px-[16px] py-[8px] bg-white border border-[#E5E7EB] text-[#374151] rounded-[8px] hover:bg-[#F3F4F6] transition-colors text-[13px] font-[600]"
          >
            Cancelar
          </button>
          <button 
            onClick={handleUpload}
            disabled={!file || isUploading}
            className="flex items-center gap-[8px] px-[20px] py-[8px] bg-[#6D28D9] text-white rounded-[8px] hover:bg-[#5B21B6] transition-colors text-[13px] font-[600] shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isUploading ? (
              <div className="w-[16px] h-[16px] border-[2px] border-white border-t-transparent rounded-full animate-spin"></div>
            ) : (
              <UploadCloud className="w-[16px] h-[16px]" />
            )}
            Importar Arquivo
          </button>
        </div>

      </div>
    </div>
  );
}
