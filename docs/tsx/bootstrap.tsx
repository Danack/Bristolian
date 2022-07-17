import { h, render } from "preact";
import { MotionsPanel } from "./MotionsPanel";
import { CommentsPanel } from "./CommentsPanel";

import initByClass from "widgety";
import type { WidgetClassBinding } from "widgety";
import { startMessageProcessing } from "danack-message";

// This is left over stuff from source project.
let panels: WidgetClassBinding[] = [
    {
        class: 'widget_motions_panel',
        component: MotionsPanel
    },
    {
        class: 'widget_php_bugs_comments',
        component: CommentsPanel
    },
];

initByClass(panels, h, render);

startMessageProcessing();

console.log("Bootstrap finished");



