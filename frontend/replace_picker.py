with open("src/components/auth/RegisterStepper.tsx", "r") as f:
    text = f.read()

import re

# Match the entire ddi-picker block (lines 90 to 135)
pattern = re.compile(r'<div className="ddi-picker" ref=\{ddiRef\}>.*?</div>\s*</div>\s*</div>\s*</div>\s*</div>\s*</div>', re.DOTALL)
# It's easier to use split string
parts = text.split('<div className="ddi-picker" ref={ddiRef}>')

if len(parts) > 1:
    before = parts[0]
    after = parts[1].split('<div className="input-wrap phone-input">')[1]
    new_text = before + '<CountryCodeSelect value={whatsappPrefix} onChange={setWhatsappPrefix} />\n                        <div className="input-wrap phone-input">\n' + after
    
    # Also clean up imports
    new_text = new_text.replace('import { countryCodes } from "@/data/country-codes";', 'import { CountryCodeSelect } from "./CountryCodeSelect";')
    # Also remove React.useEffect hooks
    new_text = re.sub(r'React\.useEffect\(\(\) => \{.*?\}, \[\]\);', '', new_text, flags=re.DOTALL)
    new_text = re.sub(r'React\.useEffect\(\(\) => \{.*?\}, \[ddiOpen\]\);', '', new_text, flags=re.DOTALL)
    new_text = re.sub(r'const \[ddiOpen.*?;\n', '', new_text)
    new_text = re.sub(r'const \[ddiSearch.*?;\n', '', new_text)
    new_text = re.sub(r'const ddiRef.*?;\n', '', new_text)
    new_text = re.sub(r'const ddiSearchRef.*?;\n', '', new_text)
    
    with open("src/components/auth/RegisterStepper.tsx", "w") as f:
        f.write(new_text)
        print("Success")
else:
    print("Failed to find block")

