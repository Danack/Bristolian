#!/bin/sh -l


python3 - <<'EOF'
import easyocr
easyocr.Reader(['en'], gpu=False)

#with open("/var/app/easyocr_has_run.txt", "w", encoding="utf-8") as f:
#    f.write("je suis complet")
EOF