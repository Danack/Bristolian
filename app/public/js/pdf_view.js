
// If absolute URL from the remote server is provided, configure the CORS
// header on that server.

let PAGE_HEIGHT;

// The retrieved PDF document
let g_pdf_document;

// The individual rendered pdf pages
let g_pdf_page = [];

// The div that contains the page
let g_page_container = [];

// The canvas draw contexts
let g_page_canvas_context = [];

// Scale - the size the PDFs are rendered at
let g_scale = 1.0;

let g_max_page = 0;

import { TextLayer } from "/js/pdf/pdf.mjs";

// Loaded via <script> tag, create shortcut to access PDF.js exports.
var { pdfjsLib  } = globalThis;

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf/pdf.worker.mjs';

/**
 *
 * @param page_number
 * @returns {HTMLDivElement}
 */
function createEmptyPage(page_number) {
    let page = document.createElement('div');
    let canvas = document.createElement('canvas');
    let wrapper = document.createElement('div');
    let textLayer = document.createElement('div');

    page.className = 'page';
    wrapper.className = 'canvasWrapper';
    textLayer.className = 'textLayer';

    page.setAttribute('id', `pageContainer${page_number}`);
    page.setAttribute('data-loaded', 'false');
    page.setAttribute('data-page-number', page_number);

    canvas.setAttribute('id', `page${page_number}`);

    page.appendChild(wrapper);
    page.appendChild(textLayer);
    wrapper.appendChild(canvas);

    g_page_container[page_number] = page;
    g_page_canvas_context[page_number] = canvas.getContext("2d");

    if (page_number > g_max_page) {
        g_max_page = page_number;
    }

    return page;
}



function render_page_into_container(pdfPage, pageNum) {
    var viewport = pdfPage.getViewport({ scale: g_scale, });

    PAGE_HEIGHT = viewport.height;

    g_pdf_page[pageNum] = pdfPage;

    // Support HiDPI-screens.
    var outputScale = window.devicePixelRatio || 1;

    let page = document.getElementById(`pageContainer${pageNum}`);
    let canvas = page.querySelector('canvas');
    var context = canvas.getContext('2d');

    let wrapper = page.querySelector('.canvasWrapper');
    let container = page.querySelector('.textLayer');

    canvas.width = Math.floor(viewport.width * outputScale);
    canvas.height = Math.floor(viewport.height * outputScale);
    canvas.style.width = Math.floor(viewport.width) + "px";
    canvas.style.height =  Math.floor(viewport.height) + "px";

    canvas.width = viewport.width * 2;
    canvas.height = viewport.height * 2;
    page.style.width = `${viewport.width}px`;
    page.style.height = `${viewport.height}px`;
    wrapper.style.width = `${viewport.width}px`;
    wrapper.style.height = `${viewport.height}px`;
    container.style.width = `${viewport.width}px`;
    container.style.height = `${viewport.height}px`;

    var transform = outputScale !== 1
        ? [outputScale, 0, 0, outputScale, 0, 0]
        : null;

    var renderContext = {
        canvasContext: context,
        transform: transform,
        viewport: viewport
    };

    container.style.setProperty('--scale-factor', g_scale);

    var renderTask = pdfPage.render(renderContext);
    renderTask.promise.then(() => page_rendered(container, pdfPage, viewport));

    page.setAttribute('data-loaded', 'true');
}

function loadPage(pageNum) {
    return g_pdf_document.getPage(pageNum + 1).
      then((pdfPage) => render_page_into_container(pdfPage, pageNum));
}

function page_rendered(container, pdfPage, viewport) {

    let render_task = new TextLayer({
        container,
        textContentSource: pdfPage.streamTextContent(),
        viewport,
    });

    render_task.render();

    // Note - calling
    // document.getElementById('text-layer');
    // does not work in this callback. Apparently.
}

function initial_render_scrolling(pdf) {

    let viewer = document.getElementById('viewer');
    for (let i = 0; i < pdf.numPages; i += 1) {
        let page = createEmptyPage(i);
        viewer.appendChild(page);
    }

    g_pdf_document = pdf;

    loadPage(0);
    setTimeout(processQueue, 1000);
    // processQueue();
}

function handleWindowScroll() {
    let visiblePageNumTop = Math.round((window.scrollY / PAGE_HEIGHT));
    let visiblePageNumBottom = Math.round(((window.scrollY + window.innerHeight) / PAGE_HEIGHT));

    // Prevent shenanigans if monitor is upside down?
    if (visiblePageNumBottom < visiblePageNumTop) {
        visiblePageNumBottom = visiblePageNumTop;
    }

    // Loop over all the pages that could be visible, and
    // load those that haven't been loaded.
    for (let i = visiblePageNumTop; i <= visiblePageNumBottom; i += 1) {
        let visiblePage = document.querySelector(`.page[data-page-number="${i}"][data-loaded="false"]`);

        if (visiblePage) {
            setTimeout(function () {
                loadPage(i);
            });
        }
    }
}

function sendSelectionPositionToParent(selection_data) {
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: "selectionData",
            selection_data
        }, "*");
    }
}

function getSelectionPage(container) {
    // If the container is a text node, get its parent element
    if (container.nodeType === Node.TEXT_NODE) {
        container = container.parentElement;
    }

    // Traverse up the DOM tree to find a suitable parent (e.g., with a specific class or tag)
    let pageElement = container;
    while (pageElement && pageElement.classList && !pageElement.classList.contains('page')) {
        pageElement = pageElement.parentElement;
    }

    let page_number = pageElement.getAttribute('data-page-number')

    return page_number;
}



function mergeRectsOnSameLine(rectangles, tolerance = 1) {
    // Group rectangles by lines based on vertical proximity
    const lines = [];

    rectangles.forEach(rect => {
        let line = lines.find(group =>
            Math.abs(group.top - rect.top) <= tolerance &&
            Math.abs(group.bottom - rect.bottom) <= tolerance
        );

        if (line) {
            // Update line bounds
            line.top = Math.min(line.top, rect.top);
            line.bottom = Math.max(line.bottom, rect.bottom);
            line.rects.push(rect);
        } else {
            // Create a new line group
            lines.push({
                top: rect.top,
                bottom: rect.bottom,
                rects: [rect]
            });
        }
    });

    // Merge overlapping or adjacent rectangles within each line
    const mergedRects = lines.flatMap(line => {
        const rects = line.rects;
        rects.sort((a, b) => a.left - b.left); // Sort by horizontal position

        const merged = [];
        rects.forEach(rect => {
            const last = merged[merged.length - 1];
            if (last && rect.left <= last.right + tolerance) {
                // Merge overlapping or adjacent rectangles
                last.right = Math.max(last.right, rect.right);
                last.top = Math.min(last.top, rect.top);
                last.bottom = Math.max(last.bottom, rect.bottom);
            } else {
                // Add as a new rectangle
                merged.push({ ...rect });
            }
        });
        return merged;
    });

    return mergedRects;
}


function processSelectionChange() {
    const selection = window.getSelection();

    if (!selection || selection.isCollapsed) {
        console.log("No text is selected.");
        return;
    }
    const range = selection.getRangeAt(0);

    let start_page = getSelectionPage(range.startContainer);
    let end_page = getSelectionPage(range.endContainer);
    const rects = range.getClientRects();

    if (rects.length == 0) {
        console.log("No visible rect for the selection.");
        return;
    }

    // Get bounding rectangles of all pages
    const pageRects = Array.from(g_page_container).map((page, index) => {
        const canvas = page.querySelector('canvas');
        return {
            pageIndex: index,
            boundingRect: canvas.getBoundingClientRect()
        };
    });


    // let canvas_rect = canvas.getBoundingClientRect()
    let simpleRects = [];
    for(let i = 0; i < rects.length; i +=1) {
        const rect = rects[i];

        if (rect.width <= 0.0000001) {
            // We seem to have some rectangles that are invisible, with
            // both left and right being set to zero. Skip saving these.
            continue;
        }

        let pageNum = null;

        // Determine the page that contains this rect
        for (let { pageIndex, boundingRect } of pageRects) {
            if (
                rect.top >= boundingRect.top &&
                rect.bottom <= boundingRect.bottom &&
                rect.left >= boundingRect.left &&
                rect.right <= boundingRect.right
            ) {
                pageNum = pageIndex;
                break;
            }
        }

        if (pageNum !== null) {
            const canvasRect = pageRects[pageNum].boundingRect;
            simpleRects.push({
                page: pageNum,
                left: rect.left - canvasRect.left,
                top: rect.top - canvasRect.top,
                right: rect.right - canvasRect.left,
                bottom: rect.bottom - canvasRect.top
            });
        } else {
            console.warn("Rect not associated with any page.", rect);
        }
    }

    let reduced_simple_rects = mergeRectsOnSameLine(simpleRects, 1)

    // Reduce rectangles to be integer values, as this is accurate enough,
    // and significantly reduces the data size when sent to the server.
    let reduced_simple_approx_rects = reduced_simple_rects.map((rect, i) => {
        return {
            page: rect.page,
            left: Math.floor(rect.left),
            top: Math.floor(rect.top),
            right: Math.ceil(rect.right),
            bottom: Math.ceil(rect.bottom)

        };
    });

    const selection_data = {
        text: window.getSelection().toString(),
        highlights: reduced_simple_approx_rects,
    }

    // Send the position to the parent window
    sendSelectionPositionToParent(selection_data);
}


function drawHighlights(highlights) {
    var outputScale = window.devicePixelRatio || 1;

    console.log("Drawing highlights", highlights);
    if (highlights.length > 0) {
        let element = g_page_container[highlights[0].page];

        if (element) {
            element.scrollIntoView({
                behavior: 'smooth', // Options: 'auto' (default) or 'smooth' for smooth scrolling
                block: 'center',    // Options: 'start', 'center', 'end', or 'nearest'
                inline: 'nearest'   // Options: 'start', 'center', 'end', or 'nearest'
            });
        }
    }



    for (let i = 0; i < highlights.length; i += 1) {
        let highlight = highlights[i];

        if (highlight.page < 0 || highlight.page > g_max_page) {
            console.warn("Highlight rectangle is on invalid page", highlight.page);
            continue;
        }

        var context = g_page_canvas_context[highlight.page];

        if (!context) {
            setTimeout(function () {
                console.log(
                    `Some highlights on pages not loaded yet. Delaying drawing from highlight ${i}`
                );
                let remaining_highlights = highlights.slice(i);
                drawHighlights((remaining_highlights))
            }, 100)
        }

        context.fillStyle = 'rgba(255,221,0,0.42)';

        context.fillRect(
            (highlight.left) * outputScale,
            (highlight.top) * outputScale,
            (highlight.right - highlight.left) * outputScale,
            (highlight.bottom - highlight.top) * outputScale
        );
    }
}


function receiveDrawHightlightsMessage(event) {

    if (event.data && event.data.type === "draw_highlights") {
        console.log("Received draw highlights message", event);
        let highlights = event.data.highlights;
        if (highlights === undefined) {
            console.warn("No highlights received.");
            return;
        }
        drawHighlights(highlights);
    }
}

let viewer_element = document.getElementById('viewer');
if (!viewer_element) {
    throw new Error("Failed to find 'viewer'.");
}

let json = viewer_element.getAttribute('data-widgety_json')

let params = [];
if (json !== undefined) {
    params = JSON.parse(json);
}

var url = params['stored_file_url'];

// Asynchronously downloads PDF.
var loadingTask = pdfjsLib.getDocument(url);
let selectionChangeTimeout;

loadingTask.promise.then(initial_render_scrolling);

document.addEventListener("selectionchange", () => {
    // Clear any existing timeout
    clearTimeout(selectionChangeTimeout);

    // Set a new timeout to call the function after 0.1 seconds
    selectionChangeTimeout = setTimeout(processSelectionChange, 25);
});


window.addEventListener('scroll', handleWindowScroll);

window.addEventListener("message", receiveDrawHightlightsMessage);