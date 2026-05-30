import { h, render } from "preact";

// Preact panels: do not annotate render() or render helpers with h.JSX.Element — that is
// React's JSX namespace. Prefer no return type (Component already types render), or
// import type { VNode } from "preact" when an explicit type is needed. See
// docs/developing/front_end_design_rules.md ("Preact render return types").

import initByClass from "./widgety/widgety";
import { registerMessageListener, sendMessage, startMessageProcessing } from "./message/message";
import type { WidgetClassBinding } from "./widgety/widgety";


import { BristolStairsPanel } from "./BristolStairsPanel";
import { CSPViolationReportsPanel } from "./CSPViolationReportsPanel";
import { ChatPanel } from "./ChatPanel";
import { ChatBottomPanel } from "./chat/ChatBottomPanel";
import { UserProfilePanel } from "./UserProfilePanel";
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
import { RoomAnnotationsPanel } from "./RoomAnnotationsPanel";
import { RoomVideosPanel } from "./RoomVideosPanel";
import { RoomManagementPanel } from "./RoomManagementPanel";
import { AnnotationPanel } from "./AnnotationPanel";
import { TeleprompterPanel } from "./TeleprompterPanel";
import { TimeLinePanel } from "./TimeLinePanel";
import { TinnedFishProductsAdminPanel } from "./TinnedFishProductsAdminPanel";
import { TwitterSplitterPanel } from "./TwitterSplitterPanel";
import { CommitteeSeatsPanel } from "./CommitteeSeatsPanel";
import { receiveSelectionMessage } from "./AnnotationPanel";

// Widget registration: each `class` must match a PHP-rendered element class. When the server has
// data for the panel, PHP may set `data-widgety_json` (parsed in widgety/widgety.tsx and passed as
// constructor props). Many tools only mount an empty div and keep all data in TypeScript instead.
// See docs/developing/front_end_design_rules.md ("Optional initial data from PHP").

let panels: WidgetClassBinding[] = [
    {
        class: 'bristol_stairs_panel',
        component: BristolStairsPanel
    },
    {
        class: 'chat_panel',
        component: ChatPanel
    },
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
        class: 'room_annotations_panel',
        component: RoomAnnotationsPanel
    },
    {
        class: 'room_videos_panel',
        component: RoomVideosPanel
    },
    {
        class: 'room_management_panel',
        component: RoomManagementPanel
    },
    {
        class: 'chat_bottom_panel',
        component: ChatBottomPanel
    },
    {
        class: 'teleprompter_panel',
        component: TeleprompterPanel
    },
    {
        class: 'annotation_panel',
        component: AnnotationPanel
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
        class: 'committee_seats_panel',
        component: CommitteeSeatsPanel
    },
    {
        class: 'user_profile_panel',
        component: UserProfilePanel
    },
    {
        class: 'widget_csp_violation_reports',
        component: CSPViolationReportsPanel
    },
    {
        class: 'tinned_fish_products_admin',
        component: TinnedFishProductsAdminPanel
    },
];




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

