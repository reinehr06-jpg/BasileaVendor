import os

with open("src/app/page.tsx", "r") as f:
    lines = f.readlines()

def write_file(path, content):
    with open(path, "w") as f:
        f.write(content)

# Extract RegisterStepper
register_jsx = "".join(lines[262:490])
register_state = "".join(lines[48:78]) # register states + useEffects
register_comp = f"""import React, {{ useState }} from "react";
import {{ maskCpfCnpj, maskPhone }} from "@/lib/masks";
import {{ countryCodes }} from "@/data/country-codes";

export function RegisterStepper({{ setIsRegistering }}: {{ setIsRegistering: (val: boolean) => void }}) {{
{register_state}
  return (
{register_jsx}
  );
}}
"""
write_file("src/components/auth/RegisterStepper.tsx", register_comp)

# Extract LoginForm
login_jsx = "".join(lines[492:581])
login_state = "".join(lines[42:46]) # login states
login_submit = "".join(lines[81:102]) # handleSubmit
login_comp = f"""import React, {{ useState }} from "react";
import {{ useAuth }} from "@/context/AuthContext";
import {{ useRouter }} from "next/navigation";
import {{ loginSchema }} from "@/lib/schemas/auth.schema";

export function LoginForm({{ setIsRegistering }}: {{ setIsRegistering: (val: boolean) => void }}) {{
{login_state}
{login_submit}

  return (
{login_jsx}
  );
}}
"""
write_file("src/components/auth/LoginForm.tsx", login_comp)

# Rewrite page.tsx
page_top = "".join(lines[0:11]) # imports
page_layout_top = "".join(lines[41:42] + ["  const [isRegistering, setIsRegistering] = useState(false);\n"] + lines[103:261])
page_layout_bottom = "".join(lines[583:592])

new_page = f"""{page_top}
import {{ LoginForm }} from "@/components/auth/LoginForm";
import {{ RegisterStepper }} from "@/components/auth/RegisterStepper";

{page_layout_top}
            {{isRegistering ? (
              <RegisterStepper setIsRegistering={{setIsRegistering}} />
            ) : (
              <LoginForm setIsRegistering={{setIsRegistering}} />
            )}}
{page_layout_bottom}"""

write_file("src/app/page.tsx", new_page)

