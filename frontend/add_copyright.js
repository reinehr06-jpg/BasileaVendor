const fs = require('fs');
const path = require('path');

const copyrightBlock = `
          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[22px] pb-[12px]">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Basiléia")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>
`;

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = path.join(dir, file);
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(file));
    } else if (file.endsWith('page.tsx')) {
      results.push(file);
    }
  });
  return results;
}

const files = walk('src/app');

files.forEach(file => {
  let content = fs.readFileSync(file, 'utf8');
  if (!content.includes('COPYRIGHT')) {
    if (content.includes('</main>')) {
      content = content.replace('</main>', copyrightBlock + '        </main>');
      // Also ensure useTranslation is imported and t is defined if needed?
      // actually, if `t(` is used, we need to ensure `t` is defined.
      // Most files already have useTranslation.
      // But let's check.
      if (!content.includes('useTranslation')) {
        content = `import { useTranslation } from "react-i18next";\n` + content;
      }
      if (!content.includes('const { t } = useTranslation();') && content.includes('export default function')) {
        content = content.replace(/(export default function [^)]+\)\s*\{)/, '$1\n  const { t } = useTranslation();');
      }
      fs.writeFileSync(file, content);
      console.log('Added to', file);
    } else {
      console.log('Skipped (no main):', file);
    }
  }
});
