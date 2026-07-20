import {describe, expect, test, beforeEach, afterEach} from "@jest/globals";
import {h, render} from "preact";
import initByClass, {
  startWidgetDebugging,
  stopWidgetDebugging,
  resetWidgetDebuggingForTests,
  WIDGETY_DEBUG_ALLOWED_ATTRIBUTE,
  WIDGETY_DEBUG_LABEL_CLASS,
  WIDGETY_DEBUG_TOGGLE_CLASS,
  WIDGETY_START_DEBUGGING_LABEL,
  WIDGETY_STOP_DEBUGGING_LABEL,
  type WidgetClassBinding,
} from "./widgety";

function StubPanel() {
  return h("div", {class: "stub_panel_content"}, "stub panel body");
}

function allowWidgetDebugging(): void {
  document.documentElement.setAttribute(WIDGETY_DEBUG_ALLOWED_ATTRIBUTE, "1");
}

function disallowWidgetDebugging(): void {
  document.documentElement.setAttribute(WIDGETY_DEBUG_ALLOWED_ATTRIBUTE, "0");
}

function createHost(className: string): HTMLElement {
  const host = document.createElement("div");
  host.className = className;
  document.body.appendChild(host);
  return host;
}

function mountWidgets(bindings: WidgetClassBinding[]): HTMLElement[] {
  const hosts: HTMLElement[] = [];
  for (const binding of bindings) {
    hosts.push(createHost(binding.class));
  }
  initByClass(bindings, h, render);
  return hosts;
}

function getDebugToggleButton(): HTMLButtonElement {
  const button = document.querySelector("." + WIDGETY_DEBUG_TOGGLE_CLASS);
  if (button === null || !(button instanceof HTMLButtonElement)) {
    throw new Error("Widget debug toggle button not found");
  }
  return button;
}

describe("widgety debug labels", function () {
  beforeEach(() => {
    document.body.innerHTML = "";
    resetWidgetDebuggingForTests();
    allowWidgetDebugging();
  });

  afterEach(() => {
    resetWidgetDebuggingForTests();
    document.body.innerHTML = "";
    document.documentElement.removeAttribute(WIDGETY_DEBUG_ALLOWED_ATTRIBUTE);
  });

  test("does not show debug labels by default and still renders the panel", () => {
    const hosts = mountWidgets([
      {
        class: "test_widget_panel",
        component: StubPanel,
      },
    ]);

    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)).toBeNull();
    expect(hosts[0].querySelector(".stub_panel_content")?.textContent).toBe("stub panel body");
  });

  test("startWidgetDebugging shows a label with the widget class name", () => {
    const hosts = mountWidgets([
      {
        class: "test_widget_panel",
        component: StubPanel,
      },
    ]);

    startWidgetDebugging();

    const label = hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS);
    expect(label).not.toBeNull();
    expect(label?.textContent).toBe("test_widget_panel");
  });

  test("stopWidgetDebugging removes labels and keeps panel content", () => {
    const hosts = mountWidgets([
      {
        class: "test_widget_panel",
        component: StubPanel,
      },
    ]);

    startWidgetDebugging();
    stopWidgetDebugging();

    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)).toBeNull();
    expect(hosts[0].querySelector(".stub_panel_content")?.textContent).toBe("stub panel body");
  });

  test("each mounted widget shows its own class name when debugging", () => {
    const hosts = mountWidgets([
      {
        class: "first_test_widget_panel",
        component: StubPanel,
      },
      {
        class: "second_test_widget_panel",
        component: StubPanel,
      },
    ]);

    startWidgetDebugging();

    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)?.textContent).toBe(
      "first_test_widget_panel",
    );
    expect(hosts[1].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)?.textContent).toBe(
      "second_test_widget_panel",
    );
  });

  test("toggle button starts and stops debugging", () => {
    const hosts = mountWidgets([
      {
        class: "test_widget_panel",
        component: StubPanel,
      },
    ]);

    const toggleButton = getDebugToggleButton();
    expect(toggleButton.textContent).toBe(WIDGETY_START_DEBUGGING_LABEL);

    toggleButton.click();

    expect(toggleButton.textContent).toBe(WIDGETY_STOP_DEBUGGING_LABEL);
    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)?.textContent).toBe(
      "test_widget_panel",
    );

    toggleButton.click();

    expect(toggleButton.textContent).toBe(WIDGETY_START_DEBUGGING_LABEL);
    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)).toBeNull();
  });

  test("does not mount toggle or allow debugging when production disallows it", () => {
    disallowWidgetDebugging();

    const hosts = mountWidgets([
      {
        class: "test_widget_panel",
        component: StubPanel,
      },
    ]);

    expect(document.querySelector("." + WIDGETY_DEBUG_TOGGLE_CLASS)).toBeNull();

    startWidgetDebugging();

    expect(hosts[0].querySelector("." + WIDGETY_DEBUG_LABEL_CLASS)).toBeNull();
    expect(hosts[0].querySelector(".stub_panel_content")?.textContent).toBe("stub panel body");
  });
});
