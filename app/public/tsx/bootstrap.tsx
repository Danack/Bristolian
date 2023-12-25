import { h, render } from "preact";
import { CSPViolationReportsPanel } from "./CSPViolationReportsPanel";
import { FloatingPointPanel } from "./FloatingPointPanel";
import initByClass from "./widgety/widgety";
import type { WidgetClassBinding } from "./widgety/widgety";
import { startMessageProcessing } from "./message/message";

import { NotificationRegistrationPanel } from "./NotificationRegistrationPanel";
import { NotesPanel } from "./NotesPanel";
import { TimeLinePanel } from "./TimeLinePanel";
import { TwitterSplitterPanel } from "./TwitterSplitterPanel";
import { TeleprompterPanel } from "./TeleprompterPanel";
import { EmailLinkGeneratorPanel } from "./EmailLinkGenerator";
import { QrCodeGeneratorPanel } from "./QrCodeGenerator";

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
        class: 'notification_panel',
        component: NotificationRegistrationPanel
    },
    {
        class: 'twitter_splitter_panel',
        component: TwitterSplitterPanel
    },
    {
        class: 'teleprompter_panel',
        component: TeleprompterPanel
    },
    {
        class: 'email_link_generator_panel',
        component: EmailLinkGeneratorPanel
    },
    {
        class: 'qr_code_generator_panel',
        component: QrCodeGeneratorPanel
    },
];


initByClass(panels, h, render);

startMessageProcessing();

function registerServiceWorker() {

  if (!('serviceWorker' in navigator)) {
    console.log('Service Worker isn\'t supported on this browser.');
    return;
  }

  navigator.serviceWorker.
    // The file needs to be in root, because of scope
    register("/serviceWorker.js").
    then(() => {console.log("service worker registered?")});
}

registerServiceWorker();

console.log("Bootstrap finished");



