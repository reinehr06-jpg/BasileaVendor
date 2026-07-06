import os

with open("src/components/auth/RegisterStepper.tsx", "r") as f:
    lines = f.readlines()

new_lines = []
skip = False
for i, line in enumerate(lines):
    if "const [ddiOpen" in line or "const [ddiSearch" in line or "const ddiRef" in line or "const ddiSearchRef" in line:
        continue
    if "function handleClickOutside" in line:
        skip = True
    
    if skip:
        if "}, []);" in line:
            skip = False
        continue

    if "if (ddiOpen && ddiSearchRef" in line or "if (!ddiOpen) setDdiSearch" in line or "}, [ddiOpen]);" in line:
        continue

    if "React.useEffect(() => {" in line and ("ddiOpen" in lines[i+1] or "function handleClickOutside" in lines[i+1]):
        continue
    
    new_lines.append(line)

content = "".join(new_lines)
content = content.replace('import { countryCodes } from "@/data/country-codes";', 'import { CountryCodeSelect } from "./CountryCodeSelect";')

# Replace the ddi-picker JSX chunk
import re
pattern = re.compile(r'<div className="ddi-picker".*?</div>\s*</div>\s*</div>\s*</div>', re.DOTALL)

# But wait, python regex for nested divs is impossible.
# Let's do it manually via text block replacement.
