<!-- BEGIN:nextjs-agent-rules -->
# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` before writing any code. Heed deprecation notices.
<!-- END:nextjs-agent-rules -->

<!-- BEGIN:ui-patterns -->
# Padrão de Select

Use `CustomSelect` (de `@/components/CustomSelect`) para **todos** os campos de seleção do sistema.

**Estrutura padrão:**
```tsx
<div className="flex flex-col gap-[6px]">
  <label className="text-[13px] font-[600] text-[#4B5563]">
    {t("Rótulo")} <span className="text-[#EF4444] ml-0.5">*</span>
  </label>
  <CustomSelect
    value={value}
    onChange={setValue}
    placeholder={t("Placeholder")}
    searchable={true}  {/* true se precisar de busca, false/falso se 2-3 opções curtas */}
    className="h-[42px]"
    options={[
      { value: "x", label: "Opção X" },
    ]}
  />
</div>
```

**Regras:**
- `searchable={true}` para selects com 4+ opções ou que podem receber muitos dados do back-end (pastores, membros, cidades, etc.)
- `searchable` pode ser omitido/falso para 2-3 opções fixas (Sim/Não, Masculino/Feminino)
- `className="h-[42px]"` para campos de formulário
- Paginação/filtros inline compactos podem manter `<select>` nativo
<!-- END:ui-patterns -->
