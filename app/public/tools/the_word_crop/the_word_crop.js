(function () {
    'use strict';

    const MAX_DISPLAY_WIDTH = 1200;
    const MIN_BOX_SIZE = 4;

    const canvas = document.getElementById('canvas');
    const canvasContainer = document.getElementById('canvas-container');
    const fileInput = document.getElementById('file-input');
    const undoButton = document.getElementById('undo-button');
    const clearButton = document.getElementById('clear-button');
    const downloadImageButton = document.getElementById('download-image-button');
    const downloadScriptButton = document.getElementById('download-script-button');
    const boxList = document.getElementById('box-list');
    const imageInfo = document.getElementById('image-info');

    const context = canvas.getContext('2d');

    let loadedImage = null;
    let sourceFilename = 'source.png';
    let sourceFile = null;
    let objectUrl = null;
    let scale = 1;
    let boxes = [];
    let dragStart = null;
    let dragCurrent = null;

    function revokeObjectUrl() {
        if (objectUrl !== null) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function sanitizeFilename(filename) {
        const trimmed = filename.trim();
        if (trimmed === '') {
            return 'source.png';
        }
        return trimmed.replace(/[^\w.\-()+ ]/g, '_');
    }

    function padBoxNumber(index) {
        return String(index + 1).padStart(3, '0');
    }

    function formatBoxLabel(box, index) {
        return 'the_' + padBoxNumber(index) + ': ' +
            box.width + '×' + box.height + ' at (' + box.x + ', ' + box.y + ')';
    }

    function normalizeBox(startX, startY, endX, endY) {
        const x = Math.min(startX, endX);
        const y = Math.min(startY, endY);
        const width = Math.abs(endX - startX);
        const height = Math.abs(endY - startY);
        return { x, y, width, height };
    }

    function canvasPointFromEvent(event) {
        const rectangle = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rectangle.width;
        const scaleY = canvas.height / rectangle.height;
        return {
            x: (event.clientX - rectangle.left) * scaleX,
            y: (event.clientY - rectangle.top) * scaleY,
        };
    }

    function imagePointFromCanvasPoint(canvasPoint) {
        return {
            x: Math.round(canvasPoint.x / scale),
            y: Math.round(canvasPoint.y / scale),
        };
    }

    function imageBoxFromCanvasPoints(startPoint, endPoint) {
        const startImage = imagePointFromCanvasPoint(startPoint);
        const endImage = imagePointFromCanvasPoint(endPoint);
        return normalizeBox(
            startImage.x,
            startImage.y,
            endImage.x,
            endImage.y
        );
    }

    function imageBoxToCanvasBox(box) {
        return {
            x: box.x * scale,
            y: box.y * scale,
            width: box.width * scale,
            height: box.height * scale,
        };
    }

    function computeDisplaySize(naturalWidth, naturalHeight) {
        if (naturalWidth <= MAX_DISPLAY_WIDTH) {
            return { width: naturalWidth, height: naturalHeight };
        }
        const ratio = MAX_DISPLAY_WIDTH / naturalWidth;
        return {
            width: Math.round(naturalWidth * ratio),
            height: Math.round(naturalHeight * ratio),
        };
    }

    function updateButtons() {
        const hasImage = loadedImage !== null;
        const hasBoxes = boxes.length > 0;

        undoButton.disabled = !hasBoxes;
        clearButton.disabled = !hasBoxes;
        downloadImageButton.disabled = !hasImage;
        downloadScriptButton.disabled = !hasImage || !hasBoxes;
    }

    function renderBoxList() {
        boxList.innerHTML = '';

        if (boxes.length === 0) {
            const emptyItem = document.createElement('li');
            emptyItem.className = 'empty-list';
            emptyItem.textContent = 'No boxes yet.';
            boxList.appendChild(emptyItem);
            updateButtons();
            return;
        }

        boxes.forEach(function (box, index) {
            const listItem = document.createElement('li');

            const label = document.createElement('span');
            label.className = 'box-label';
            label.textContent = formatBoxLabel(box, index);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'delete-box';
            deleteButton.textContent = 'Delete';
            deleteButton.addEventListener('click', function () {
                boxes.splice(index, 1);
                renderBoxList();
                redrawCanvas();
            });

            listItem.appendChild(label);
            listItem.appendChild(deleteButton);
            boxList.appendChild(listItem);
        });

        updateButtons();
    }

    function drawBoxOutline(canvasBox, strokeStyle, lineWidth, dashed) {
        context.save();
        context.strokeStyle = strokeStyle;
        context.lineWidth = lineWidth;
        if (dashed) {
            context.setLineDash([6, 4]);
        }
        context.strokeRect(canvasBox.x, canvasBox.y, canvasBox.width, canvasBox.height);
        context.restore();
    }

    function drawBoxLabel(canvasBox, label) {
        context.save();
        context.fillStyle = 'rgba(0, 0, 0, 0.65)';
        context.font = '12px ui-monospace, monospace';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(
            label,
            canvasBox.x + canvasBox.width / 2,
            canvasBox.y + canvasBox.height / 2
        );
        context.restore();
    }

    function redrawCanvas() {
        if (loadedImage === null) {
            return;
        }

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(loadedImage, 0, 0, canvas.width, canvas.height);

        boxes.forEach(function (box, index) {
            const canvasBox = imageBoxToCanvasBox(box);
            drawBoxOutline(canvasBox, '#16a34a', 2, false);
            drawBoxLabel(canvasBox, padBoxNumber(index));
        });

        if (dragStart !== null && dragCurrent !== null) {
            const previewBox = normalizeBox(
                dragStart.x,
                dragStart.y,
                dragCurrent.x,
                dragCurrent.y
            );
            drawBoxOutline(previewBox, '#ea580c', 2, true);
        }
    }

    function resizeCanvasToImage() {
        if (loadedImage === null) {
            return;
        }

        const displaySize = computeDisplaySize(
            loadedImage.naturalWidth,
            loadedImage.naturalHeight
        );

        canvas.width = displaySize.width;
        canvas.height = displaySize.height;
        scale = canvas.width / loadedImage.naturalWidth;

        imageInfo.textContent =
            sourceFilename + ' — ' +
            loadedImage.naturalWidth + '×' + loadedImage.naturalHeight + ' px';

        redrawCanvas();
    }

    function resetBoxes() {
        boxes = [];
        dragStart = null;
        dragCurrent = null;
        renderBoxList();
    }

    function loadImageFromFile(file) {
        if (!file.type.startsWith('image/')) {
            return;
        }

        revokeObjectUrl();
        resetBoxes();

        sourceFile = file;
        sourceFilename = sanitizeFilename(file.name);

        objectUrl = URL.createObjectURL(file);
        const image = new Image();

        image.onload = function () {
            loadedImage = image;
            canvas.hidden = false;
            canvasContainer.classList.remove('empty');
            canvasContainer.querySelector('p')?.remove();
            resizeCanvasToImage();
            updateButtons();
        };

        image.onerror = function () {
            loadedImage = null;
            sourceFile = null;
            revokeObjectUrl();
            updateButtons();
        };

        image.src = objectUrl;
    }

    function triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function buildCropScript() {
        const lines = [
            '#!/usr/bin/env bash',
            'set -eu',
            '',
            '# Crop each boxed "the" from the source image using ImageMagick.',
            '# Place this script in the same directory as the source image.',
            '# Run with: bash ' + sourceFilename.replace(/\.[^.]+$/, '') + '_crop_the_words.sh',
            '# Requires ImageMagick 7 (magick). For ImageMagick 6, set: MAGICK=convert',
            '',
            'MAGICK=magick',
            '',
            'SOURCE_IMAGE="' + sourceFilename + '"',
            'OUTPUT_DIR="the_crops"',
            '',
            'mkdir -p "$OUTPUT_DIR"',
            '',
        ];

        boxes.forEach(function (box, index) {
            const outputName = 'the_' + padBoxNumber(index) + '.png';
            const cropArgument = box.width + 'x' + box.height + '+' + box.x + '+' + box.y;
            lines.push(
                '"$MAGICK" "$SOURCE_IMAGE" -crop ' + cropArgument + ' +repage "$OUTPUT_DIR/' + outputName + '"'
            );
        });

        lines.push('');
        return lines.join('\n');
    }

    fileInput.addEventListener('change', function () {
        const file = fileInput.files[0];
        if (file) {
            loadImageFromFile(file);
        }
    });

    undoButton.addEventListener('click', function () {
        boxes.pop();
        renderBoxList();
        redrawCanvas();
    });

    clearButton.addEventListener('click', function () {
        resetBoxes();
        redrawCanvas();
    });

    downloadImageButton.addEventListener('click', function () {
        if (sourceFile !== null) {
            triggerDownload(sourceFile, sourceFilename);
        }
    });

    downloadScriptButton.addEventListener('click', function () {
        const scriptContent = buildCropScript();
        const blob = new Blob([scriptContent], { type: 'text/x-shellscript' });
        const scriptName = sourceFilename.replace(/\.[^.]+$/, '') + '_crop_the_words.sh';
        triggerDownload(blob, scriptName);
    });

    canvas.addEventListener('mousedown', function (event) {
        if (loadedImage === null) {
            return;
        }
        event.preventDefault();
        dragStart = canvasPointFromEvent(event);
        dragCurrent = dragStart;
        redrawCanvas();
    });

    canvas.addEventListener('mousemove', function (event) {
        if (dragStart === null) {
            return;
        }
        dragCurrent = canvasPointFromEvent(event);
        redrawCanvas();
    });

    function finishDrag() {
        if (dragStart === null || dragCurrent === null) {
            return;
        }

        const box = imageBoxFromCanvasPoints(dragStart, dragCurrent);
        dragStart = null;
        dragCurrent = null;

        if (box.width >= MIN_BOX_SIZE && box.height >= MIN_BOX_SIZE) {
            boxes.push(box);
            renderBoxList();
        }

        redrawCanvas();
    }

    canvas.addEventListener('mouseup', finishDrag);
    canvas.addEventListener('mouseleave', finishDrag);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && dragStart !== null) {
            dragStart = null;
            dragCurrent = null;
            redrawCanvas();
        }
    });

    canvasContainer.addEventListener('dragover', function (event) {
        event.preventDefault();
        canvasContainer.classList.add('drag-over');
    });

    canvasContainer.addEventListener('dragleave', function (event) {
        if (!canvasContainer.contains(event.relatedTarget)) {
            canvasContainer.classList.remove('drag-over');
        }
    });

    canvasContainer.addEventListener('drop', function (event) {
        event.preventDefault();
        canvasContainer.classList.remove('drag-over');

        const file = event.dataTransfer.files[0];
        if (file) {
            loadImageFromFile(file);
        }
    });

    window.addEventListener('resize', function () {
        resizeCanvasToImage();
    });

    updateButtons();
})();
