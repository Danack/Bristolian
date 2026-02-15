#!/usr/bin/env python3
from pathlib import Path
import re
import sys

XML_FILE = Path("/var/app/phpunit.xml")

text = XML_FILE.read_text()

commented_pattern = re.compile(
    r"<!--\s*(<html[^>]+/>)\s*-->", re.DOTALL
)

enabled_pattern = re.compile(
    r"(<html[^>]+/>)", re.DOTALL
)

if commented_pattern.search(text):
    # Uncomment
    text = commented_pattern.sub(r"\1", text, count=1)
    XML_FILE.write_text(text)
    print("enabled")
    sys.exit(0)

if enabled_pattern.search(text):
    # Comment
    text = enabled_pattern.sub(r"<!-- \1 -->", text, count=1)
    XML_FILE.write_text(text)
    print("disabled")
    sys.exit(0)

# Fallback: element not found
print("MISSING")
sys.exit(1)
