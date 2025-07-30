import { h, render } from "preact";

import initByClass from "./widgety/widgety";
import { startMessageProcessing } from "./message/message";
import type { WidgetClassBinding } from "./widgety/widgety";

import { CSPViolationReportsPanel } from "./CSPViolationReportsPanel";
import { EmailLinkGeneratorPanel } from "./EmailLinkGenerator";
import { FloatingPointPanel } from "./FloatingPointPanel";
import { LoginStatusPanel } from "./LoginStatusPanel";
import { MemeManagementPanel } from "./MemeManagementPanel";
import { MemeUploadPanel } from "./MemeUploadPanel";
import { NotesPanel } from "./NotesPanel";
import { NotificationRegistrationPanel } from "./NotificationRegistrationPanel";
import { NotificationTestPanel } from "./NotificationTestPanel";
import { ProcessorRunRecordPanel } from "./ProcessorRunRecordPanel";
import { QrCodeGeneratorPanel } from "./QrCodeGenerator";
import { RoomFilesPanel } from "./RoomFilesPanel";
import { RoomFileUploadPanel } from "./RoomFileUploadPanel";
import { RoomLinksPanel } from "./RoomLinksPanel";
import { RoomSourcelinksPanel } from "./RoomSourcelinksPanel";
import { SourceLinkPanel } from "./SourceLinkPanel";
import { TeleprompterPanel } from "./TeleprompterPanel";
import { TimeLinePanel } from "./TimeLinePanel";
import { TwitterSplitterPanel } from "./TwitterSplitterPanel";
import { receiveSelectionMessage } from "./SourceLinkPanel";


let panels: WidgetClassBinding[] = [


    // {
    //     class: 'admin_email_panel',
    //     component: AdminEmailPanel
    // },
    {
        class: 'email_link_generator_panel',
        component: EmailLinkGeneratorPanel
    },
    {
         class: 'floating_point_panel',
         component: FloatingPointPanel
    },
    {
        class: 'login_status_panel',
        component: LoginStatusPanel
    },
    {
        class: 'meme_management_panel',
        component: MemeManagementPanel
    },
    {
        class: 'meme_upload_panel',
        component: MemeUploadPanel
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
        class: 'notification_test_panel',
        component: NotificationTestPanel
    },

    {
        class: 'notification_test_panel',
        component: NotificationTestPanel
    },
    {
        class: 'processor_run_record_panel',
        component: ProcessorRunRecordPanel,
    },
    {
        class: 'qr_code_generator_panel',
        component: QrCodeGeneratorPanel
    },
    {
        class: 'room_files_panel',
        component: RoomFilesPanel
    },
    {
        class: 'room_file_upload_panel',
        component: RoomFileUploadPanel
    },
    {
        class: 'room_links_panel',
        component: RoomLinksPanel
    },
    {
        class: 'room_sourcelinks_panel',
        component: RoomSourcelinksPanel
    },



    {
        class: 'teleprompter_panel',
        component: TeleprompterPanel
    },
    {
        class: 'source_link_panel',
        component: SourceLinkPanel
    },

    {
        class: 'time_line_panel',
        component: TimeLinePanel
    },
    {
        class: 'twitter_splitter_panel',
        component: TwitterSplitterPanel
    },


    {
        class: 'widget_csp_violation_reports',
        component: CSPViolationReportsPanel
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


// Add an event listener to receive messages
window.addEventListener("message", receiveSelectionMessage);

console.log("Bootstrap finished");



