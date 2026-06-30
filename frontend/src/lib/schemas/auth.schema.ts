import { z } from "zod";

export const loginSchema = z.object({
  email: z.string().email("E-mail inválido"),
  password: z.string().min(6, "Mínimo 6 caracteres"), // Ajustado para aceitar 123456 do mock
  remember: z.boolean(),
  captcha: z.string().min(1, "Por favor, preencha o CAPTCHA"),
});

export const registerSchema = z.object({
  churchName: z.string().min(3),
  documentNumber: z.string().min(11).max(18), // CPF ou CNPJ com máscara
  whatsappPrefix: z.string().startsWith("+"),
  whatsappNumber: z.string().min(10),
  responsibleName: z.string().min(3),
  responsibleEmail: z.string().email(),
  responsiblePhone: z.string().min(10),
  password: z.string().min(6),
  passwordConfirm: z.string(),
  captcha: z.string().min(1, "Por favor, preencha o CAPTCHA"),
}).refine(d => d.password === d.passwordConfirm, {
  message: "Senhas não coincidem",
  path: ["passwordConfirm"],
});

export type LoginPayload    = z.infer<typeof loginSchema>;
export type RegisterPayload = z.infer<typeof registerSchema>;
