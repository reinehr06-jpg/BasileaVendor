"use client";

import React, { useState } from "react";
import CustomSelect from "@/components/CustomSelect";
import { useAuth } from "@/context/AuthContext";
import { useRouter } from "next/navigation";
import { loginSchema } from "@/lib/schemas/auth.schema";
import { countryCodes } from "@/data/country-codes";
import { maskCpfCnpj, maskPhone } from "@/lib/masks";
import { LoginStyles } from "@/components/auth/LoginStyles";

import { LoginForm } from "@/components/auth/LoginForm";
import { RegisterStepper } from "@/components/auth/RegisterStepper";

export default function LoginPage() {
  const [isRegistering, setIsRegistering] = useState(false);
  return (
    <>
      <LoginStyles />
      <div className="split">

        {/* ========================================
            LEFT PANEL
        ======================================== */}
        <div className="left">
          
          {/* Fundo abstrato futurista com luzes e linhas */}
          <div className="bg-base" />
          <div className="bg-glow-center" />
          <div className="bg-glow-logo" />
          <div className="bg-line line-1" />
          <div className="bg-line line-2" />
          <div className="bg-line line-3" />

          <div className="brand-logo-container">
            <img 
              src="https://dash.basileia.global/images/logo-basileia.png?0b669f9a5d54a07b37941d0c8db9ac64" 
              alt="Basileia Church OS" 
              className="brand-logo"
            />
          </div>

          <div className="benefits-container">
            <div className="benefit-card">
              <div className="benefit-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M18 22V11"/><path d="M6 22V11"/><path d="M12 2v5"/><path d="M9 5h6"/><path d="M12 7l-9 7v8h18v-8z"/><path d="M10 22v-5a2 2 0 0 1 4 0v5"/>
                </svg>
              </div>
              <div className="benefit-text">Igreja<br/>cadastrada</div>
            </div>
            
            <div className="benefit-card">
              <div className="benefit-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                </svg>
              </div>
              <div className="benefit-text">Acesso<br/>administrativo</div>
            </div>

            <div className="benefit-card">
              <div className="benefit-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
              </div>
              <div className="benefit-text">Gestão<br/>centralizada</div>
            </div>
          </div>

          <div className="preview-wrapper">
            <div className="preview-sidebar">
              <div className="preview-sidebar-logo" />
              <div className="preview-nav-item active">
                <div className="preview-nav-icon" />
                <div className="preview-nav-text" />
              </div>
              <div className="preview-nav-item">
                <div className="preview-nav-icon" />
                <div className="preview-nav-text short" />
              </div>
              <div className="preview-nav-item">
                <div className="preview-nav-icon" />
                <div className="preview-nav-text medium" />
              </div>
              <div className="preview-nav-item">
                <div className="preview-nav-icon" />
                <div className="preview-nav-text short" />
              </div>
            </div>
            
            <div className="preview-main">
              <div className="preview-header">
                <div className="preview-header-titles">
                  <div className="preview-header-title" />
                  <div className="preview-header-sub" />
                </div>
                <div className="preview-avatar" />
              </div>

              <div className="preview-content">
                <div className="preview-stats-row">
                  <div className="preview-stat-card"><div className="preview-stat-icon"/><div className="preview-stat-line"/></div>
                  <div className="preview-stat-card"><div className="preview-stat-icon"/><div className="preview-stat-line"/></div>
                  <div className="preview-stat-card"><div className="preview-stat-icon"/><div className="preview-stat-line"/></div>
                  <div className="preview-stat-card"><div className="preview-stat-icon"/><div className="preview-stat-line"/></div>
                </div>

                <div className="preview-charts-row">
                  <div className="preview-line-chart">
                    <div className="preview-line-chart-grid" />
                    <div className="preview-chart-tooltip" />
                    <svg viewBox="0 0 100 40" preserveAspectRatio="none" style={{width: '100%', height: '100%', position: 'absolute', inset: 0, padding: '16px', boxSizing: 'border-box'}}>
                      <defs>
                        <linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="0%" stopColor="rgba(196, 181, 253, 0.4)" />
                          <stop offset="100%" stopColor="rgba(196, 181, 253, 0)" />
                        </linearGradient>
                      </defs>
                      <polygon points="0,40 0,35 15,25 30,30 45,15 60,20 75,5 90,12 100,5 100,40" fill="url(#chart-grad)" />
                      <polyline points="0,35 15,25 30,30 45,15 60,20 75,5 90,12 100,5" fill="none" stroke="rgba(196, 181, 253, 1)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                      <polyline points="0,38 15,30 30,34 45,22 60,26 75,12 90,18 100,10" fill="none" stroke="rgba(196, 181, 253, 0.3)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round" strokeDasharray="2 2" />
                      <circle cx="45" cy="15" r="1.5" fill="#C4B5FD" stroke="#16003B" strokeWidth="0.5" />
                      <circle cx="75" cy="5" r="2" fill="#C4B5FD" stroke="#16003B" strokeWidth="0.5" />
                      <circle cx="100" cy="5" r="1.5" fill="#C4B5FD" />
                    </svg>
                  </div>
                  <div className="preview-donut-chart">
                    <svg viewBox="0 0 40 40" style={{width: '60px', height: '60px', position: 'relative', zIndex: 1}}>
                      <circle cx="20" cy="20" r="15" fill="none" stroke="rgba(255,255,255,0.05)" strokeWidth="5" />
                      <circle cx="20" cy="20" r="15" fill="none" stroke="rgba(167,139,250,0.8)" strokeWidth="5" strokeDasharray="30 70" strokeDashoffset="0" strokeLinecap="round" transform="rotate(-90 20 20)" style={{filter: 'drop-shadow(0 2px 4px rgba(167,139,250,0.4))'}}/>
                      <circle cx="20" cy="20" r="15" fill="none" stroke="rgba(196,181,253,0.3)" strokeWidth="5" strokeDasharray="15 80" strokeDashoffset="-35" strokeLinecap="round" transform="rotate(-90 20 20)" />
                    </svg>
                    <div className="preview-donut-center">
                      <div className="preview-donut-text-1" />
                      <div className="preview-donut-text-2" />
                    </div>
                  </div>
                </div>

                <div className="preview-list">
                  <div className="preview-list-item">
                    <div className="preview-list-dot" /><div className="preview-list-line" />
                  </div>
                  <div className="preview-list-item">
                    <div className="preview-list-dot" /><div className="preview-list-line" style={{width: '60%'}}/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* ========================================
            RIGHT PANEL
        ======================================== */}
        <div className="right">
          <div className="mobile-logo">
            <div className="mobile-logo-row">
              <div className="mobile-logo-icon"><span>B</span></div>
              <span className="mobile-logo-text">Basileia</span>
            </div>
          </div>

          <div className="card">
            {/* Logo dentro do card */}
            <div className="card-logo">
              <img 
                src="https://dash.basileia.global/images/logo-basileia.png?0b669f9a5d54a07b37941d0c8db9ac64" 
                alt="Basileia Church OS" 
                style={{ width: '145px', height: 'auto', filter: 'brightness(0) invert(18%) sepia(87%) saturate(3015%) hue-rotate(253deg) brightness(85%) contrast(108%)' }}
              />
            </div>


            {isRegistering ? (
              <RegisterStepper setIsRegistering={setIsRegistering} />
            ) : (
              <LoginForm setIsRegistering={setIsRegistering} />
            )}
          </div>

          <p className="footer">© 2026 Basileia Church OS. Todos os direitos reservados.</p>
        </div>
      </div>
    </>
  );
}
