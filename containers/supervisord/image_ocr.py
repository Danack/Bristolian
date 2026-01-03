#!/usr/bin/env python3

import sys
import json
import easyocr
from pathlib import Path

def main():
    if len(sys.argv) != 2:
        print(f"Usage: {sys.argv[0]} <image_file>", file=sys.stderr)
        sys.exit(1)

    image_path = Path(sys.argv[1])

    if not image_path.exists():
        print(f"File not found: {image_path}", file=sys.stderr)
        sys.exit(1)

    reader = easyocr.Reader(["en"], gpu=False)
    text_lines = reader.readtext(str(image_path), detail=0)

    result = {
        "source_file": str(image_path),
        "text": text_lines,
        "text_joined": "\n".join(text_lines),
    }

    json.dump(result, sys.stdout, ensure_ascii=False, indent=2)
    sys.stdout.write("\n")


if __name__ == "__main__":
    main()
