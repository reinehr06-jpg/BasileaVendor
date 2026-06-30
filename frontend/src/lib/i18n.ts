import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import { localeResources } from "@/locales/registry";

/*
 * 🗺️ MAPA DO TESOURO — SISTEMA DE TRADUÇÃO (i18n)
 * 
 * COMO FUNCIONA:
 *   - `localeResources` é gerado por `scripts/generate-registry.cjs`
 *   - Cada arquivo JSON em `src/locales/` vira um resource de tradução
 *   - O fallback padrão é pt-BR (idioma base)
 *   - react-i18next injeta `useTranslation()` → `t()` nos componentes
 * 
 * PARA ADICIONAR UM NOVO IDIOMA:
 *   1. Colocar o JSON traduzido em `src/locales/{codigo}.json`
 *   2. Rodar `node scripts/generate-registry.cjs` para registrar
 *   3. O i18n carrega automaticamente no próximo build
 * 
 * PARA GERAR TRADUÇÕES AUTOMATICAMENTE:
 *   node scripts/translate-all.cjs
 *   (usa Falcon3 para traduzir pt-BR → 100+ idiomas)
 * 
 * FLUXO DE REATIVIDADE:
 *   Configurações → Conta → dropdown de idioma → LocaleContext.setLocale()
 *   → i18n.changeLanguage(code) → react-i18next re-renderiza todos os componentes
 *   → `t("chave")` resolve para o idioma ativo
 */
i18n.use(initReactI18next).init({
  resources: localeResources,
  lng: "pt-BR",
  fallbackLng: "pt-BR",
  interpolation: { escapeValue: false },
  returnObjects: true,
});

export default i18n;
