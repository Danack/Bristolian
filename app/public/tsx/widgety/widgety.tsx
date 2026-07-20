// Which React object should be bound to which classes for elements
export type WidgetClassBinding = {
  class: string;
  component: object;
};

export const WIDGETY_DEBUG_LABEL_CLASS = "widgety_debug_label";
export const WIDGETY_DEBUG_TOGGLE_CLASS = "widgety_debug_toggle";
export const WIDGETY_DEBUG_TOGGLE_HOST_CLASS = "widgety_debug_toggle_host";
export const WIDGETY_PANEL_MOUNT_CLASS = "widgety_panel_mount";
export const WIDGETY_WIDGET_NAME_ATTRIBUTE = "data-widgety-name";
export const WIDGETY_DEBUG_ALLOWED_ATTRIBUTE = "data-widgety-debug-allowed";
export const WIDGETY_START_DEBUGGING_LABEL = "Start debugging";
export const WIDGETY_STOP_DEBUGGING_LABEL = "Stop debugging";

let widgetDebugging = false;
const registeredWidgetHosts: HTMLElement[] = [];
let debugToggleHost: HTMLElement | null = null;
let debugToggleButton: HTMLButtonElement | null = null;

export function isWidgetDebuggingAllowed(): boolean {
  return document.documentElement.getAttribute(WIDGETY_DEBUG_ALLOWED_ATTRIBUTE) === "1";
}

export function isWidgetDebugging(): boolean {
  return widgetDebugging;
}

function createDebugLabel(widgetClass: string): HTMLElement {
  const label = document.createElement("div");
  label.className = WIDGETY_DEBUG_LABEL_CLASS;
  label.textContent = widgetClass;
  return label;
}

function ensureDebugLabel(host: HTMLElement): void {
  if (host.querySelector("." + WIDGETY_DEBUG_LABEL_CLASS) !== null) {
    return;
  }

  const widgetClass = host.getAttribute(WIDGETY_WIDGET_NAME_ATTRIBUTE);
  if (widgetClass === null) {
    return;
  }

  host.insertBefore(createDebugLabel(widgetClass), host.firstChild);
}

function removeDebugLabel(host: HTMLElement): void {
  const label = host.querySelector("." + WIDGETY_DEBUG_LABEL_CLASS);
  if (label !== null) {
    label.remove();
  }
}

function refreshDebugLabels(): void {
  for (const host of registeredWidgetHosts) {
    if (widgetDebugging) {
      ensureDebugLabel(host);
    } else {
      removeDebugLabel(host);
    }
  }
}

function updateDebugToggleButton(): void {
  if (debugToggleButton === null) {
    return;
  }

  debugToggleButton.textContent = widgetDebugging
    ? WIDGETY_STOP_DEBUGGING_LABEL
    : WIDGETY_START_DEBUGGING_LABEL;
}

export function startWidgetDebugging(): void {
  if (isWidgetDebuggingAllowed() !== true) {
    return;
  }

  widgetDebugging = true;
  refreshDebugLabels();
  updateDebugToggleButton();
}

export function stopWidgetDebugging(): void {
  widgetDebugging = false;
  refreshDebugLabels();
  updateDebugToggleButton();
}

export function resetWidgetDebuggingForTests(): void {
  stopWidgetDebugging();
  registeredWidgetHosts.length = 0;

  if (debugToggleHost !== null) {
    debugToggleHost.remove();
    debugToggleHost = null;
  }
  debugToggleButton = null;
}

const setupWidgetForElement = (
  element: HTMLElement,
  widgetClass: string,
  component: object,
  h: any,
  render: any,
) => {
  let params = {};

  if (Object.prototype.hasOwnProperty.call(element.dataset, "widgety_json") === true) {
    const json = element.dataset.widgety_json;
    // This check is redundant.
    if (json !== undefined) {
      params = JSON.parse(json);
    }
  }

  element.setAttribute(WIDGETY_WIDGET_NAME_ATTRIBUTE, widgetClass);
  element.classList.add("widgety_widget_root");

  const panelMount = document.createElement("div");
  panelMount.className = WIDGETY_PANEL_MOUNT_CLASS;
  element.appendChild(panelMount);

  registeredWidgetHosts.push(element);
  if (widgetDebugging) {
    ensureDebugLabel(element);
  }

  // const react_type = <component {...params} />;
  // @ts-ignore: you not helping here.
  const reactType = h(component, params);

  render(
    reactType,
    panelMount,
  );
};

const setupWidget = (widgetBinding: WidgetClassBinding, h: any, render: any) => {
  const domElements = document.getElementsByClassName(widgetBinding.class);
  const domElementsSnapshot = [];

  // take a static snapshot of the domElementsSnapshot, to prevent
  // yo'dawging of widgetBindings creation.
  for (let i = 0; i < domElements.length; i += 1) {
    domElementsSnapshot.push(domElements.item(i));
  }

  for (const j in domElementsSnapshot) {
    if (Object.prototype.hasOwnProperty.call(domElementsSnapshot, j) !== true) {
      // This will never happen.
      continue;
    }

    const element = domElementsSnapshot[j];
    // TODO - type check this properly but JS is terrible
    // if(!(element as HTMLOrSVGElement)){
    //     continue;
    // }
    if (!(element instanceof HTMLElement)) {
      continue;
    }
    setupWidgetForElement(element, widgetBinding.class, widgetBinding.component, h, render);
  }
};

function mountDebugToggle(): void {
  if (debugToggleHost !== null) {
    return;
  }

  debugToggleHost = document.createElement("div");
  debugToggleHost.className = WIDGETY_DEBUG_TOGGLE_HOST_CLASS;

  debugToggleButton = document.createElement("button");
  debugToggleButton.type = "button";
  debugToggleButton.className = WIDGETY_DEBUG_TOGGLE_CLASS;
  debugToggleButton.textContent = WIDGETY_START_DEBUGGING_LABEL;
  debugToggleButton.addEventListener("click", () => {
    if (widgetDebugging) {
      stopWidgetDebugging();
    } else {
      startWidgetDebugging();
    }
  });

  debugToggleHost.appendChild(debugToggleButton);
  document.body.appendChild(debugToggleHost);
}

const initByClass = (widgetBindings: WidgetClassBinding[], h: any, render: any) => {
  for (const widgetBinding of widgetBindings) {
    setupWidget(widgetBinding, h, render);
  }

  if (isWidgetDebuggingAllowed() !== true) {
    return;
  }

  mountDebugToggle();

  // @ts-ignore: expose helpers for console use during development
  window.startWidgetDebugging = startWidgetDebugging;
  // @ts-ignore: expose helpers for console use during development
  window.stopWidgetDebugging = stopWidgetDebugging;
};

export default initByClass;
