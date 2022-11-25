import { h, render } from "preact";
import { CSPViolationReportsPanel } from "./CSPViolationReportsPanel";
import { FloatingPointPanel } from "./FloatingPointPanel";
import initByClass from "./widgety/widgety";
import type { WidgetClassBinding } from "./widgety/widgety";
import { startMessageProcessing } from "./message/message";

import { NotesPanel } from "./NotesPanel";
import { TimeLinePanel } from "./TimeLinePanel";
import { TwitterSplitterPanel } from "./TwitterSplitterPanel";

let panels: WidgetClassBinding[] = [
    {
         class: 'floating_point_panel',
         component: FloatingPointPanel
    },
    {
        class: 'widget_csp_violation_reports',
        component: CSPViolationReportsPanel
    },
    {
        class: 'time_line_panel',
        component: TimeLinePanel
    },
    {
        class: 'notes_panel',
        component: NotesPanel
    },

    {
        class: 'twitter_splitter_panel',
        component: TwitterSplitterPanel
    },
];


initByClass(panels, h, render);

startMessageProcessing();

console.log("Bootstrap finished");



