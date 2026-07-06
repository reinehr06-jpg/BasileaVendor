import os
import re

source = "/Users/viniciusreinehr/Downloads/Basileia/web/NewFront/src/app/cursos/novo/page.tsx"
target = "/Users/viniciusreinehr/Downloads/Basileia/web/NewFront/src/app/eventos/novo/page.tsx"

os.makedirs(os.path.dirname(target), exist_ok=True)

with open(source, "r") as f:
    content = f.read()

# 1. Basic string replacements
content = content.replace("NovoCursoPage", "NovoEventoPage")
content = content.replace("Novo Curso", "Novo Evento")
content = content.replace('href="/cursos"', 'href="/eventos"')
content = content.replace("do curso.", "do evento.")
content = content.replace("Título do Curso", "Título do Evento")
content = content.replace("Curso de Liderança Avançada", "Acampamento Jovem")
content = content.replace("Este curso tem encontros recorrentes (várias aulas)?", "Este evento tem duração de múltiplos dias?")
content = content.replace("aulasConfig", "diasConfig")
content = content.replace("setAulasConfig", "setDiasConfig")
content = content.replace("isRecorrente", "isMultiplosDias")
content = content.replace("setIsRecorrente", "setIsMultiplosDias")
content = content.replace("Aula ", "Dia ")
content = content.replace("Qtd Aulas", "Qtd Dias")
content = content.replace("Gerar Aulas", "Gerar Dias")
content = content.replace("updateAulaDate", "updateDiaDate")
content = content.replace("Informações Básicas do Curso", "Informações Básicas do Evento")
content = content.replace("flag de curso", "flag de evento")
content = content.replace("cursos e eventos", "eventos")
content = content.replace("deste curso", "deste evento")

# 2. State removal (Faltas e Certificado)
content = re.sub(r'const \[temLimiteFaltas, setTemLimiteFaltas\] = useState\(false\);\n?', '', content)
content = re.sub(r'const \[certificado, setCertificado\] = useState\(true\);\n?', '', content)

# 3. Generating schedule logic: remove interval and just add 1 day
gerar_logic_old = """    const intervalo = parseInt(diasConfig.intervaloDias);
    let current = new Date(diasConfig.dataInicio);
    
    const novoCronograma = [];
    for (let i = 1; i <= qtd; i++) {
      const tzOffset = current.getTimezoneOffset() * 60000;
      const localISOTime = (new Date(current.getTime() - tzOffset)).toISOString().slice(0, 16);
      
      novoCronograma.push({
        id: i,
        titulo: `Dia ${i}`,
        data: localISOTime
      });
      current.setDate(current.getDate() + intervalo);
    }"""
    
gerar_logic_new = """    let current = new Date(diasConfig.dataInicio);
    
    const novoCronograma = [];
    for (let i = 1; i <= qtd; i++) {
      const tzOffset = current.getTimezoneOffset() * 60000;
      const localISOTime = (new Date(current.getTime() - tzOffset)).toISOString().slice(0, 16);
      
      novoCronograma.push({
        id: i,
        titulo: `Dia ${i}`,
        data: localISOTime
      });
      current.setDate(current.getDate() + 1); // Eventos são dias consecutivos
    }"""
content = content.replace(gerar_logic_old, gerar_logic_new)

# Remove intervalo UI
interval_ui = """                           <div className="flex flex-col gap-[6px] w-full md:w-[150px]">
                             <label className="text-[12px] font-[600] text-[#6B7280]">Intervalo</label>
                             <select value={diasConfig.intervaloDias} onChange={e => setDiasConfig({...diasConfig, intervaloDias: e.target.value})} className="w-full h-[40px] rounded-[8px] border border-[#E5E7EB] px-[12px] text-[13px] outline-none focus:border-[#7C3AED] bg-white">
                               <option value="7">Semanal (7 dias)</option>
                               <option value="14">Quinzenal (14 dias)</option>
                               <option value="30">Mensal (30 dias)</option>
                             </select>
                           </div>"""
content = content.replace(interval_ui, "")

# 4. Remove Faltas UI
faltas_ui = """                    <div className="flex flex-col gap-[8px]">
                      <ToggleSwitch label="Tem limite de faltas permitidas?" active={temLimiteFaltas} onChange={setTemLimiteFaltas} />
                      {temLimiteFaltas && <input type="number" placeholder="Máximo de faltas toleradas" className="w-full h-[40px] rounded-[8px] border border-[#7C3AED] px-[12px] text-[13px] outline-none" />}
                    </div>"""
content = content.replace(faltas_ui, "")

# 5. Remove Certificado UI
cert_ui = """                    <div className="flex flex-col gap-[8px]">
                      <ToggleSwitch label="Tem certificado de conclusão?" active={certificado} onChange={setCertificado} />
                    </div>"""
content = content.replace(cert_ui, "")

# 6. Add Responsáveis logic
resp_ui_old = """                  <div className="flex flex-col gap-[6px]">
                    <label className="text-[13px] font-[600] text-[#374151]">Atribuir Responsáveis</label>
                    <CustomSelect options={[{label: "Selecione membros para gerenciar a turma...", value: "Selecione membros para gerenciar a turma..."}]} value="Selecione membros para gerenciar a turma..." onChange={() => {}} />
                  </div>"""

resp_ui_new = """                  <div className="flex flex-col gap-[6px]">
                    <label className="text-[13px] font-[600] text-[#374151]">Atribuir Responsáveis</label>
                    <CustomSelect options={[{label: "Selecione membros para gerenciar a turma...", value: "Selecione membros para gerenciar a turma..."}]} value="Selecione membros para gerenciar a turma..." onChange={() => {}} />
                  </div>
                  {inscritos && Number(inscritos) > 0 && (
                     <div className="mt-[8px] bg-[#EEF2FF] border border-[#C7D2FE] p-[16px] rounded-[8px] flex items-start gap-[12px]">
                        <Info className="w-[20px] h-[20px] text-[#4F46E5] shrink-0 mt-[2px]" />
                        <div className="flex flex-col gap-[4px]">
                          <span className="text-[13px] text-[#4338CA] font-[600]">
                            Regra de Responsáveis
                          </span>
                          <span className="text-[13px] text-[#4F46E5] font-[500] leading-relaxed">
                            Para a capacidade de {inscritos} pessoas, é exigido o cadastro obrigatório de no mínimo <strong>{Math.ceil(Number(inscritos) / 30)} responsável(eis)</strong> (Regra de 1 responsável a cada 30 vagas).
                          </span>
                        </div>
                     </div>
                  )}"""
content = content.replace(resp_ui_old, resp_ui_new)

with open(target, "w") as f:
    f.write(content)
print("done")
