import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { LocaleProvider } from "@/context/LocaleContext";
import { AuthProvider } from "@/context/AuthContext";
import { Toaster } from "sonner";

const inter = Inter({ subsets: ["latin"], variable: "--font-inter" });

export const metadata: Metadata = {
  title: "Basiléia Vendor Pro",
  description: "Sistema de Gestão Comercial de Alta Precisão",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt-BR">
      <body className={`${inter.variable} font-sans bg-[#F9FAFB] text-[#111827] antialiased`}>
        <LocaleProvider>
          <AuthProvider>
            {children}
          </AuthProvider>
        </LocaleProvider>
        <Toaster 
          position="top-center" 
          toastOptions={{
            style: {
              background: '#F8F7FC',
              color: '#3B0764',
              borderColor: '#E9D5FF',
              boxShadow: '0 4px 12px rgba(124, 58, 237, 0.15)',
              borderRadius: '12px',
              fontWeight: 500,
              fontSize: '14px',
            },
          }} 
        />
      </body>
    </html>
  );
}

