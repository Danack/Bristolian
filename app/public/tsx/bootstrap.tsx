import { h, render } from "preact";
import { CSPViolationReportsPanel } from "./CSPViolationReportsPanel";
import { FloatingPointPanel } from "./FloatingPointPanel";
import initByClass from "widgety";
import type { WidgetClassBinding } from "widgety";
import { startMessageProcessing } from "danack-message";


let panels: WidgetClassBinding[] = [
    {
         class: 'floating_point_panel',
         component: FloatingPointPanel
    },
    {
        class: 'widget_csp_violation_reports',
        component: CSPViolationReportsPanel
    },
];

initByClass(panels, h, render);

startMessageProcessing();

console.log("Bootstrap finished");



