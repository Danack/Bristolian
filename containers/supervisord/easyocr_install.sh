#!/bin/sh -l

set -e  # Exit on error
set -x  # Print commands as they execute

echo "========================================="
echo "EasyOCR Installation Script Starting"
echo "Timestamp: $(date)"
echo "========================================="

# Check available disk space
echo ""
echo "Checking disk space..."
df -h / | tail -1
AVAILABLE_SPACE=$(df / | tail -1 | awk '{print $4}')
echo "Available space: ${AVAILABLE_SPACE}KB"

# Check memory
echo ""
echo "Checking memory..."
free -h || true

echo ""
echo "Starting EasyOCR model download..."
echo "This may take several minutes and download large model files."
echo ""

python3 - <<'EOF'
import sys
import os
import time
from datetime import datetime

def log(message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] {message}", flush=True)

try:
    log("Importing easyocr module...")
    import easyocr
    log("EasyOCR module imported successfully")
    
    log("Initializing EasyOCR Reader with English language support...")
    log("This will download detection and recognition models if not already present.")
    log("Model download location: ~/.EasyOCR/model/")
    
    start_time = time.time()
    reader = easyocr.Reader(['en'], gpu=False, verbose=True)
    elapsed_time = time.time() - start_time
    
    log(f"EasyOCR Reader initialized successfully in {elapsed_time:.2f} seconds")
    log("Model download completed")
    
    # Verify models were downloaded
    model_dir = os.path.expanduser("~/.EasyOCR/model/")
    if os.path.exists(model_dir):
        log(f"Model directory exists: {model_dir}")
        model_files = os.listdir(model_dir)
        log(f"Found {len(model_files)} files in model directory")
        for f in model_files:
            file_path = os.path.join(model_dir, f)
            if os.path.isfile(file_path):
                size = os.path.getsize(file_path)
                log(f"  - {f}: {size / (1024*1024):.2f} MB")
    else:
        log(f"WARNING: Model directory not found at {model_dir}")
    
    log("EasyOCR installation and model download completed successfully")
    
except ImportError as e:
    log(f"ERROR: Failed to import easyocr: {e}")
    sys.exit(1)
except Exception as e:
    log(f"ERROR: Failed to initialize EasyOCR Reader: {e}")
    log(f"Error type: {type(e).__name__}")
    import traceback
    log(f"Traceback:\n{traceback.format_exc()}")
    sys.exit(1)
EOF

EXIT_CODE=$?

echo ""
echo "========================================="
if [ $EXIT_CODE -eq 0 ]; then
    echo "EasyOCR Installation Completed Successfully"
else
    echo "EasyOCR Installation Failed with exit code: $EXIT_CODE"
fi
echo "Timestamp: $(date)"
echo "========================================="

# Check disk space after installation
echo ""
echo "Checking disk space after installation..."
df -h / | tail -1
AVAILABLE_SPACE_AFTER=$(df / | tail -1 | awk '{print $4}')
echo "Available space after: ${AVAILABLE_SPACE_AFTER}KB"

# Check memory after
echo ""
echo "Checking memory after installation..."
free -h || true

if [ $EXIT_CODE -ne 0 ]; then
    echo ""
    echo "ERROR: Installation failed. Check the logs above for details."
    exit $EXIT_CODE
fi

echo ""
echo "Installation script completed successfully."