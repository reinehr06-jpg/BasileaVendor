import os
import re

def replace_header(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Check if Topbar is already imported
    if "import Topbar" not in content:
        # Insert import after Sidebar
        content = re.sub(r'(import Sidebar from "@/components/Sidebar";)', r'\1\nimport Topbar from "@/components/Topbar";', content)

    # Replace <header ...> ... </header> with <Topbar />
    pattern = re.compile(r'<header.*?</header>', re.DOTALL)
    if pattern.search(content):
        content = pattern.sub('<Topbar />', content)
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, _, files in os.walk('src/app'):
    for file in files:
        if file == 'page.tsx':
            filepath = os.path.join(root, file)
            replace_header(filepath)

