import React, { useState } from "react";
import { maskCpfCnpj, maskPhone } from "@/lib/masks";
import { CountryCodeSelect } from "./CountryCodeSelect";

import { Captcha } from "./Captcha";
import { registerSchema } from "@/lib/schemas/auth.schema";
import { useAuth } from "@/context/AuthContext";
import { toast } from "sonner";
import { AuthService } from "@/services/auth.service";
import { useRouter } from "next/navigation";

export function RegisterStepper({ setIsRegistering }: { setIsRegistering: (val: boolean) => void }) {
  const [registerStep, setRegisterStep] = useState(1);
  const [country, setCountry] = useState("br");
  const [churchName, setChurchName] = useState("");
  const [documentNumber, setDocumentNumber] = useState("");
  const [responsibleName, setResponsibleName] = useState("");
  const [responsibleEmail, setResponsibleEmail] = useState("");
  const [responsiblePhone, setResponsiblePhone] = useState("");
  const [registerPassword, setRegisterPassword] = useState("");
  const [registerPasswordConfirm, setRegisterPasswordConfirm] = useState("");
  const [showRegPassword, setShowRegPassword] = useState(false);
  const [showRegPasswordConfirm, setShowRegPasswordConfirm] = useState(false);
  const [whatsappPrefix, setWhatsappPrefix] = useState("+55");
  const [whatsappNumber, setWhatsappNumber] = useState("");
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);

  const { login } = useAuth();
  const router = useRouter();

  const handleRegister = async () => {
    const payload = {
      churchName,
      documentNumber,
      whatsappPrefix,
      whatsappNumber,
      responsibleName,
      responsibleEmail,
      responsiblePhone,
      password: registerPassword,
      passwordConfirm: registerPasswordConfirm,
      captcha: captchaToken || ""
    };

    const result = registerSchema.safeParse(payload);
    if (!result.success) {
      toast.error("Erro de validação", {
        description: result.error.issues.map(err => err.message).join(", ")
      });
      return;
    }

    try {
      // Cria a conta
      await AuthService.register(result.data);
      // Já loga automaticamente
      await login({ email: responsibleEmail, password: registerPassword, remember: true, captcha: captchaToken || "" });
      toast.success("Igreja cadastrada com sucesso!", {
        description: "Bem-vindo ao Basileia Church OS!"
      });
      router.push("/dashboard");
    } catch (err: any) {
      console.error("Erro no cadastro", err);
      toast.error("Erro ao criar conta", {
        description: err.message || "Não foi possível realizar o cadastro."
      });
    }
  };

  return (
              <div className="fade-in" style={{width: '100%'}}>
                <div className="card-header" style={{ marginBottom: '16px' }}>
                  <h1>Criar conta no Basileia</h1>
                  <p>Cadastre sua igreja para começar a usar a plataforma.</p>
                </div>
                
                <div className="stepper">
                  <div className={`step ${registerStep === 1 ? 'active' : registerStep > 1 ? 'done' : 'inactive'}`}>
                    <div className="step-circle">{registerStep > 1 ? '✓' : '1'}</div>
                    <span className="step-label">Dados da igreja</span>
                  </div>
                  <div className={`step ${registerStep === 2 ? 'active' : registerStep > 2 ? 'done' : 'inactive'}`}>
                    <div className="step-circle">{registerStep > 2 ? '✓' : '2'}</div>
                    <span className="step-label">Responsável</span>
                  </div>
                  <div className={`step ${registerStep === 3 ? 'active' : 'inactive'}`}>
                    <div className="step-circle">3</div>
                    <span className="step-label">Segurança</span>
                  </div>
                </div>

                {registerStep === 1 && (
                  <div className="fade-in">
                    <div className="register-field">
                      <label>Nome da igreja <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap">
                        <input
                          type="text"
                          autoComplete="off"
                          value={churchName}
                          onChange={(e) => setChurchName(e.target.value)}
                          placeholder="Digite o nome da igreja"
                        />
                      </div>
                    </div>

                    <div className="register-field">
                      <label>CPF/CNPJ</label>
                      <div className="input-wrap">
                        <input
                          type="text"
                          autoComplete="off"
                          value={documentNumber}
                          onChange={(e) => setDocumentNumber(maskCpfCnpj(e.target.value))}
                          placeholder="000.000.000-00"
                        />
                      </div>
                    </div>

                    <div className="register-field">
                      <label>WhatsApp</label>
                      <div className="phone-group">
                        <CountryCodeSelect value={whatsappPrefix} onChange={setWhatsappPrefix} />
                        <div className="input-wrap phone-input">

                          <input
                            type="text"
                            autoComplete="off"
                            value={whatsappNumber}
                            onChange={(e) => setWhatsappNumber(maskPhone(e.target.value))}
                            placeholder="(11) 9 9999-9999"
                          />
                        </div>
                      </div>
                    </div>

                    <button type="button" className="btn btn-register" style={{marginTop: '12px'}} onClick={() => setRegisterStep(2)}>
                      Continuar
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{position: 'absolute', right: '16px'}}>
                        <path d="m9 18 6-6-6-6"/>
                      </svg>
                    </button>

                    <div className="login-link">
                      Já possui uma conta? <a onClick={() => setIsRegistering(false)}>Faça o login</a>
                    </div>
                  </div>
                )}

                {registerStep === 2 && (
                  <div className="fade-in">
                    <div className="register-field">
                      <label>Nome completo <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap">
                        <input
                          type="text"
                          autoComplete="off"
                          value={responsibleName}
                          onChange={(e) => setResponsibleName(e.target.value)}
                          placeholder="Nome do responsável"
                        />
                      </div>
                    </div>

                    <div className="register-field">
                      <label>E-mail <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap">
                        <input
                          type="email"
                          autoComplete="off"
                          value={responsibleEmail}
                          onChange={(e) => setResponsibleEmail(e.target.value)}
                          placeholder="email@exemplo.com"
                        />
                      </div>
                    </div>

                    <div className="register-field">
                      <label>Telefone <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap">
                        <input
                          type="tel"
                          autoComplete="off"
                          value={responsiblePhone}
                          onChange={(e) => setResponsiblePhone(maskPhone(e.target.value))}
                          placeholder="(11) 9 9999-9999"
                        />
                      </div>
                    </div>

                    <button type="button" className="btn btn-register" style={{marginTop: '12px'}} onClick={() => setRegisterStep(3)}>
                      Continuar
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{position: 'absolute', right: '16px'}}>
                        <path d="m9 18 6-6-6-6"/>
                      </svg>
                    </button>

                    <div className="login-link">
                      <a onClick={() => setRegisterStep(1)}>← Voltar</a>
                    </div>
                  </div>
                )}

                {registerStep === 3 && (
                  <div className="fade-in">
                    <div className="register-field">
                      <label>Senha <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap" style={{position: 'relative'}}>
                        <input
                          type={showRegPassword ? 'text' : 'password'}
                          autoComplete="new-password"
                          value={registerPassword}
                          onChange={(e) => setRegisterPassword(e.target.value)}
                          placeholder="Crie uma senha forte"
                        />
                        <button type="button" className="toggle" onClick={() => setShowRegPassword(!showRegPassword)} aria-label="Mostrar senha" style={{position: 'absolute', right: '12px', background: 'none', border: 'none', cursor: 'pointer', padding: 0}}>
                          {showRegPassword ? (
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                          ) : (
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          )}
                        </button>
                      </div>
                    </div>

                    <div className="register-field">
                      <label>Confirmar senha <span style={{color: '#EF4444'}}>*</span></label>
                      <div className="input-wrap" style={{position: 'relative'}}>
                        <input
                          type={showRegPasswordConfirm ? 'text' : 'password'}
                          autoComplete="new-password"
                          value={registerPasswordConfirm}
                          onChange={(e) => setRegisterPasswordConfirm(e.target.value)}
                          placeholder="Repita a senha"
                        />
                        <button type="button" className="toggle" onClick={() => setShowRegPasswordConfirm(!showRegPasswordConfirm)} aria-label="Mostrar senha" style={{position: 'absolute', right: '12px', background: 'none', border: 'none', cursor: 'pointer', padding: 0}}>
                          {showRegPasswordConfirm ? (
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                          ) : (
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          )}
                        </button>
                      </div>
                    </div>

                    <Captcha onVerify={setCaptchaToken} />

                    <button type="button" className="btn btn-register" style={{marginTop: '12px'}} onClick={handleRegister}>
                      Criar conta
                    </button>

                    <div className="login-link">
                      <a onClick={() => setRegisterStep(2)}>← Voltar</a>
                    </div>
                  </div>
                )}

              </div>
  );
}
