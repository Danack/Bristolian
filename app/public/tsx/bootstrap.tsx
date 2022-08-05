import { h, render } from "preact";
import { MotionsPanel } from "./MotionsPanel";
import { FfmpegCalculatorPanel } from "./FfmpegCalculator";
import { CSPViolationReportsPanel | from "./";

import initByClass from "widgety";
import type { WidgetClassBinding } from "widgety";

import { startMessageProcessing } from "danack-message";


let panels: WidgetClassBinding[] = [
    // {
    //     class: 'widget_motions_panel',
    //     component: MotionsPanel
    // },
    {
        class: 'widget_ffmpeg_caculator',
        component: FfmpegCalculatorPanel
    },
    {
        class: 'widget_csp_violation_reports',
        component: CSPViolationReportsPanel
    },
];

initByClass(panels, h, render);

startMessageProcessing();

console.log("Bootstrap finished");



