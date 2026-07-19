import { h, render } from "preact";

// Preact panels: do not annotate render() or render helpers with h.JSX.Element — that is
// React's JSX namespace. Prefer no return type (Component already types render), or
// import type { VNode } from "preact" when an explicit type is needed. See
// notes/developing/front_end_design_rules.md ("Preact render return types").

import initByClass from "./widgety/widgety";
import { registerMessageListener, sendMessage, startMessageProcessing } from "./message/message";
import { panels } from "./generated/widget_panels";
import { receiveSelectionMessage } from "./AnnotationPanel";

// Widget registration lives in WidgetRegistry (PHP) and is emitted to generated/widget_panels.tsx.
// Each `class` must match a PHP-rendered element class. When the server has data for the panel,
// PHP may set `data-widgety_json` (parsed in widgety/widgety.tsx and passed as constructor props).
// See notes/developing/front_end_design_rules.md ("Optional initial data from PHP").

function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) {
    console.log('Service Worker isn\'t supported on this browser.');
    return;
  }

  navigator.serviceWorker.
    // The file needs to be in root, because of scope
    register("/serviceWorker.js").
    then(() => {console.log("service worker registered")});
}

// Register service worker first, before initializing widgets that make API requests
registerServiceWorker();

initByClass(panels, h, render);

// Add an event listener to receive messages
window.addEventListener("message", receiveSelectionMessage);

// @ts-ignore: bind send message to the actual function.
window.sendMessage = sendMessage;
// @ts-ignore: bind send message to the actual function.
window.registerMessageListener = registerMessageListener;

addEventListener("DOMContentLoaded", (event) => {
    startMessageProcessing();
})

console.log("Bootstrap finished");
