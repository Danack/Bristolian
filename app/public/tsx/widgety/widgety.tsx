// Which React object should be bound to which classes for elements
export type WidgetClassBinding = {
  class: string;
  component: object;
};

const setupWidgetForElement = (element: HTMLOrSVGElement, component: object, h: any, render: any) => {
  let params = {};

  if (Object.prototype.hasOwnProperty.call(element.dataset, 'widgety_json') === true) {
    const json = element.dataset.widgety_json;
    // This check is redundant.
    if (json !== undefined) {
      params = JSON.parse(json);
    }
  }

  // const react_type = <component {...params} />;
  // @ts-ignore: you not helping here.
  const reactType = h(component, params);

  render(
    reactType,
    // @ts-ignore: you not helping here.
    element,
  );
};

const setupWidget = (widgetBindings: WidgetClassBinding, h: any, render: any) => {
  const domElements = document.getElementsByClassName(widgetBindings.class);
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
    // @ts-ignore: you not helping here.
    setupWidgetForElement(element, widgetBindings.component, h, render);
  }
};

const initByClass = (widgetBindings: WidgetClassBinding[], h: any, render: any) => {
  for (const widgetBinding of widgetBindings) {
    setupWidget(widgetBinding, h, render);
  }
};
export default initByClass;