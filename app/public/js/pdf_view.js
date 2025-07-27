
// This file contains the code for rendering PDFs and allowing users to
// interact with them, by creating highlights.

// Example files available at tcpdf
// https://tcpdf.org/files/examples/example_028.pdf

// Global variables begin

//
let PAGE_HEIGHT;

// The retrieved PDF document
let g_pdf_document;

// The individual rendered pdf pages
let g_pdf_page = [];

// The div that contains the page
let g_page_container = [];

// The canvas draw contexts
let g_page_canvas_context = [];

// Whether the text layer for each page is drawn
// initialised to array in 'initial_render_scrolling'
let g_textlayer_drawn = null;



let g_zoom_levels = [
    3 / 9,
    4 / 9,
    5 / 9,
    2 / 3,
    7 / 9,
    8 / 9,
    1.0,
    4 / 3,
    5 / 3,
    6 / 3,
    7 / 3,
    8 / 3,
    9 / 3
];

let g_current_zoom_level_index = 6;

// Scale - the size the PDFs are rendered at
let g_scale = g_zoom_levels[g_current_zoom_level_index];

console.log("inital g_scale is ", g_scale);

let g_max_page = 0;

import { TextLayer } from "/js/pdfjs/pdfjs-5.3.31-legacy/pdf.mjs";

// Loaded via <script> tag, create shortcut to access PDF.js exports.
var { pdfjsLib  } = globalThis;

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdfjs/pdfjs-5.3.31-legacy/pdf.worker.mjs';



/**
 * Creates the necessary empty elements to render a PDF page into
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


/**
 *
 * @param pdfPage the new pdfPage created by pdfjs
 * @param pageNum what the page number is.
 */
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
    renderTask.promise.then(() => page_rendered(container, pdfPage, viewport, pageNum));

    page.setAttribute('data-loaded', 'true');
}

function renderPDFPage(pageNum) {
    return g_pdf_document.getPage(pageNum + 1).
      then((pdfPage) => render_page_into_container(pdfPage, pageNum));
}

/**
 *
 * @param pageNum which page has just finished rendering.
 */
function renderingTextLayerForPageIsFinished(pageNum) {
    g_textlayer_drawn[pageNum] = true;
    let next_page = pageNum + 1;
    if (next_page <= g_max_page) {
        renderPDFPage(next_page);
        return;
    }

    console.log("All text layers are drawn.");
    // The function 'processQueue' is written in the iFrame itself, so
    // that it is always available when the iFrame loads.
    // Final text layer is drawn

    // let setupRecevingDrawHightlightsMessage = () => {
    //     console.log("setupRecevingDrawHightlightsMessage has been called");
    //     window.addEventListener("message", receiveDrawHightlightsMessage);
    // }
    setTimeout(processQueue, 1);
    // Inside the processQueue function is where we start listening to
    //
    // and shut the queue down.
}


function page_rendered(container, pdfPage, viewport, pageNum) {

    let render_task = new TextLayer({
        container,
        textContentSource: pdfPage.streamTextContent(),
        viewport,
    });

    render_task.render().then(() => renderingTextLayerForPageIsFinished(pageNum));
}




function start_rendering_pdf_into_document(pdf) {
    g_pdf_document = pdf;

    let viewer = document.getElementById('viewer');

    // Remove all child nodes so that we can we reinitialise the PDf viewer
    viewer.innerHTML = '';

    setupZoomButtons(viewer);

    g_textlayer_drawn = [];

    for (let i = 0; i < pdf.numPages; i += 1) {
        g_textlayer_drawn.push(false);
        let page = createEmptyPage(i);
        viewer.appendChild(page);
    }

    renderPDFPage(0);
}

// This code is not currently needed as all pages are rendered sequentially on
// page load.
// function handleWindowScroll() {
//     let visiblePageNumTop = Math.round((window.scrollY / PAGE_HEIGHT));
//     let visiblePageNumBottom = Math.round(((window.scrollY + window.innerHeight) / PAGE_HEIGHT));
//
//     // Prevent shenanigans if monitor is upside down?
//     if (visiblePageNumBottom < visiblePageNumTop) {
//         visiblePageNumBottom = visiblePageNumTop;
//     }
//
//     // Loop over all the pages that could be visible, and
//     // load those that haven't been loaded.
//     for (let i = visiblePageNumTop; i <= visiblePageNumBottom; i += 1) {
//         let visiblePage = document.querySelector(`.page[data-page-number="${i}"][data-loaded="false"]`);
//
//         if (visiblePage) {
//             setTimeout(function () {
//                 loadPage(i);
//             });
//         }
//     }
// }

function sendSelectionPositionToParent(selection_data) {
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: "selectionData",
            selection_data
        }, "*");
    }
}

function sendDeselectionMessageToParent() {
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: "textDeselected"
        }, "*");
    }
}


// function getSelectionPage(container) {
//     // If the container is a text node, get its parent element
//     if (container.nodeType === Node.TEXT_NODE) {
//         container = container.parentElement;
//     }
//
//     // Traverse up the DOM tree to find a suitable parent (e.g., with a specific class or tag)
//     let pageElement = container;
//     while (pageElement && pageElement.classList && !pageElement.classList.contains('page')) {
//         pageElement = pageElement.parentElement;
//     }
//
//     let page_number = pageElement.getAttribute('data-page-number')
//
//     return page_number;
// }


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
        sendDeselectionMessageToParent();
        return;
    }
    const range = selection.getRangeAt(0);

    // let start_page = getSelectionPage(range.startContainer);
    // let end_page = getSelectionPage(range.endContainer);
    const rects = range.getClientRects();

    if (rects.length === 0) {
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


    let simpleRects = [];
    for(let i = 0; i < rects.length; i +=1) {
        const rect = rects[i];

        // We seem to have some rectangles that are invisible, with
        // both left and right being set to zero. Skip saving these.
        if (rect.width <= 0.0000001) {
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

        if (pageNum === null) {
            console.warn("Rect not associated with any page.", rect);
            continue;
        }

        // Convert from absolute position to position on page.
        const canvasRect = pageRects[pageNum].boundingRect;
        simpleRects.push({
            page: pageNum,
            left: rect.left - canvasRect.left,
            top: rect.top - canvasRect.top,
            right: rect.right - canvasRect.left,
            bottom: rect.bottom - canvasRect.top
        });
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


function clearPageHighlights(pageNum) {
    // if (pageNum < 0 || pageNum > g_max_page) {
    //     console.warn("Invalid page number:", pageNum);
    //     return;
    // }
    //
    // // Clear the canvas
    // const canvas = document.querySelector(`#page${pageNum}`);
    // if (canvas) {
    //     const context = canvas.getContext("2d");
    //     context.clearRect(0, 0, canvas.width, canvas.height);
    // }
    //
    // // Re-render the page
    // const pdfPage = g_pdf_page[pageNum];
    // if (pdfPage) {
    //     render_page_into_container(pdfPage, pageNum);
    // } else {
    //     console.warn("Page data not available for page:", pageNum);
    // }
}

function clearAllHighlights() {

    start_rendering_pdf_into_document(g_pdf_document);

    // for (let i = 0; i <= g_max_page; i++) {
    //     clearPageHighlights(i);
    // }
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

        if (g_textlayer_drawn[highlight.page] === false) {
            setTimeout(function () {
                console.log(
                    `Some highlights on page ${highlight.page} not loaded yet. Delaying drawing from highlight ${i}`
                );
                let remaining_highlights = highlights.slice(i);
                drawHighlights((remaining_highlights))
            }, 100)
            return;
        }

        var context = g_page_canvas_context[highlight.page];
        context.fillStyle = 'rgba(255,221,0,0.25)';

        context.fillRect(
            (highlight.left) * outputScale,
            (highlight.top) * outputScale,
            (highlight.right - highlight.left) * outputScale,
            (highlight.bottom - highlight.top) * outputScale
        );
    }
}


export function receiveDrawHightlightsMessage(data) {

    if (!data) {
        console.log("Received unknown message", data);
        return;
    }

    if (data.type === "draw_highlights") {
        console.log("Received draw highlights message", data);
        let highlights = data.highlights;
        if (highlights === undefined) {
            console.warn("No highlights received.");
            return;
        }
        // TODO - this interacts with drawHighlights, really badly.
        // clearAllHighlights();
        drawHighlights(highlights);
    }

    if (data.type === "clear_highlights") {
        console.log("Received clear highlights message");
        clearAllHighlights();
    }
}

function adjustZoomLevel(change)
{
    g_current_zoom_level_index = g_current_zoom_level_index + change;

    if (g_current_zoom_level_index < 0) {
        g_current_zoom_level_index = 0;
    }

    if (g_current_zoom_level_index > g_zoom_levels.length) {
        g_current_zoom_level_index = g_zoom_levels.length;
    }

    g_scale = g_zoom_levels[g_current_zoom_level_index];
    console.log("g_scale is now ", g_scale);
    // need to redraw page
}


function setupZoomButtons(viewer_element) {
// Create zoom controls container
    const zoomControls = document.createElement('div');
    zoomControls.className = 'zoom-controls';

    // Create Zoom In button
    const zoomInBtn = document.createElement('button');
    zoomInBtn.innerText = '+';
    zoomInBtn.title = 'Zoom In';
    zoomInBtn.className = 'zoom-btn';

    // Create Zoom Out button
    const zoomOutBtn = document.createElement('button');
    zoomOutBtn.innerText = '−';
    zoomOutBtn.title = 'Zoom Out';
    zoomOutBtn.className = 'zoom-btn';

    zoomControls.appendChild(zoomInBtn);
    zoomControls.appendChild(zoomOutBtn);
    viewer_element.appendChild(zoomControls);

    // Apply zoom by scaling the viewer content
    function applyZoom() {
        redrawWholePage();
    }

    // Zoom In
    zoomInBtn.addEventListener('click', () => {
            adjustZoomLevel(1);
            applyZoom();
    });

    // Zoom Out
    zoomOutBtn.addEventListener('click', () => {
        adjustZoomLevel(-1);
        applyZoom();
    });
}


// When your script is ready to handle messages, process the queue
export function processQueue() {
    // isReady = true;

    console.log("Queue length for receiveDrawHightlightsMessage was " + messageQueue.length)
    // Process all queued messages
    while (messageQueue.length > 0) {
        const message = messageQueue.shift();
        receiveDrawHightlightsMessage(message);
    }

    window.addEventListener("message", (event) => receiveDrawHightlightsMessage(event.data));
    // Replace the queueMessages listener with the real handler
    window.removeEventListener('message', queueMessage);
}


function redrawWholePage() {

    loadingTask.promise.then(start_rendering_pdf_into_document);

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
loadingTask.promise.then(start_rendering_pdf_into_document);

// window.addEventListener('scroll', handleWindowScroll);


let selectionChangeTimeout;
document.addEventListener("selectionchange", () => {
    // Clear any existing timeout
    clearTimeout(selectionChangeTimeout);

    // Set a new timeout to call the function after a short delay.
    // The delay lowers CPU use.
    selectionChangeTimeout = setTimeout(processSelectionChange, 25);
});
