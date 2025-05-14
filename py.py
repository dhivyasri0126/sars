import os
from pygments import highlight
from pygments.lexers import get_lexer_for_filename
from pygments.formatters import HtmlFormatter
from pygments.util import ClassNotFound

output_html = ""
formatter = HtmlFormatter(linenos=True, full=True, style="monokai")

for root, _, files in os.walk("."):
    for file in files:
        filepath = os.path.join(root, file)
        if file.endswith((".py", ".js", ".html", ".css", ".ts", ".json", ".jsx", ".tsx")):
            try:
                with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                    code = f.read()
                lexer = get_lexer_for_filename(file)
                formatted_code = highlight(code, lexer, formatter)
                header = f"<h2 style='font-family:sans-serif;'>{filepath}</h2>\n"
                output_html += header + formatted_code + "<hr>"
            except ClassNotFound:
                continue

with open("sars_printable_code.html", "w", encoding="utf-8") as f:
    f.write(output_html)

print("✅ Done! Open 'sars_printable_code.html' in your browser and print.")