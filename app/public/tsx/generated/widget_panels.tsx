// This is an auto-generated file
// DO NOT EDIT

// You'll need to bounce the docker boxes to regenerate.
//
// or run 'php cli.php generate:widget_panels'
// Code for generating this file is in \Bristolian\CliController\GenerateFiles::generateWidgetPanels

// CODEVIEW_GENERATED_BEGIN
// {
//     "generator": "generate:widget_panels",
//     "generator_callable": "Bristolian\\CliController\\GenerateFiles::generateWidgetPanels",
//     "output_file": "app/public/tsx/generated/widget_panels.tsx",
//     "description": "Generate TypeScript widget panel registration from WidgetRegistry into app/public/tsx/generated/widget_panels.tsx (panels + WIDGET_API_CALLS)."
// }
// CODEVIEW_GENERATED_END

import type { WidgetClassBinding } from "../widgety/widgety";
import { BristolStairsPanel } from "../BristolStairsPanel";
import { ChatPanel } from "../ChatPanel";
import { EmailLinkGeneratorPanel } from "../EmailLinkGenerator";
import { FloatingPointPanel } from "../FloatingPointPanel";
import { LoginStatusPanel } from "../LoginStatusPanel";
import { MemeManagementPanel } from "../MemeManagementPanel";
import { MemeUploadPanel } from "../MemeUploadPanel";
import { NotesPanel } from "../NotesPanel";
import { NotificationRegistrationPanel } from "../NotificationRegistrationPanel";
import { NotificationTestPanel } from "../NotificationTestPanel";
import { ProcessorRunRecordPanel } from "../ProcessorRunRecordPanel";
import { QrCodeGeneratorPanel } from "../QrCodeGenerator";
import { RoomFilesPanel } from "../RoomFilesPanel";
import { RoomFileUploadPanel } from "../RoomFileUploadPanel";
import { RoomLinksPanel } from "../RoomLinksPanel";
import { RoomAnnotationsPanel } from "../RoomAnnotationsPanel";
import { RoomVideosPanel } from "../RoomVideosPanel";
import { RoomManagementPanel } from "../RoomManagementPanel";
import { RoomTabsPanel } from "../RoomTabsPanel";
import { ChatBottomPanel } from "../chat/ChatBottomPanel";
import { TeleprompterPanel } from "../TeleprompterPanel";
import { AnnotationPanel } from "../AnnotationPanel";
import { TimeLinePanel } from "../TimeLinePanel";
import { TwitterSplitterPanel } from "../TwitterSplitterPanel";
import { CommitteeSeatsPanel } from "../CommitteeSeatsPanel";
import { UserProfilePanel } from "../UserProfilePanel";
import { CSPViolationReportsPanel } from "../CSPViolationReportsPanel";
import { TinnedFishProductsAdminPanel } from "../TinnedFishProductsAdminPanel";

export const panels: WidgetClassBinding[] = [
    {
        class: "bristol_stairs_panel",
        component: BristolStairsPanel,
    },
    {
        class: "chat_panel",
        component: ChatPanel,
    },
    {
        class: "email_link_generator_panel",
        component: EmailLinkGeneratorPanel,
    },
    {
        class: "floating_point_panel",
        component: FloatingPointPanel,
    },
    {
        class: "login_status_panel",
        component: LoginStatusPanel,
    },
    {
        class: "meme_management_panel",
        component: MemeManagementPanel,
    },
    {
        class: "meme_upload_panel",
        component: MemeUploadPanel,
    },
    {
        class: "notes_panel",
        component: NotesPanel,
    },
    {
        class: "notification_panel",
        component: NotificationRegistrationPanel,
    },
    {
        class: "notification_test_panel",
        component: NotificationTestPanel,
    },
    {
        class: "processor_run_record_panel",
        component: ProcessorRunRecordPanel,
    },
    {
        class: "qr_code_generator_panel",
        component: QrCodeGeneratorPanel,
    },
    {
        class: "room_files_panel",
        component: RoomFilesPanel,
    },
    {
        class: "room_file_upload_panel",
        component: RoomFileUploadPanel,
    },
    {
        class: "room_links_panel",
        component: RoomLinksPanel,
    },
    {
        class: "room_annotations_panel",
        component: RoomAnnotationsPanel,
    },
    {
        class: "room_videos_panel",
        component: RoomVideosPanel,
    },
    {
        class: "room_management_panel",
        component: RoomManagementPanel,
    },
    {
        class: "room_tabs_panel",
        component: RoomTabsPanel,
    },
    {
        class: "chat_bottom_panel",
        component: ChatBottomPanel,
    },
    {
        class: "teleprompter_panel",
        component: TeleprompterPanel,
    },
    {
        class: "annotation_panel",
        component: AnnotationPanel,
    },
    {
        class: "time_line_panel",
        component: TimeLinePanel,
    },
    {
        class: "twitter_splitter_panel",
        component: TwitterSplitterPanel,
    },
    {
        class: "committee_seats_panel",
        component: CommitteeSeatsPanel,
    },
    {
        class: "user_profile_panel",
        component: UserProfilePanel,
    },
    {
        class: "widget_csp_violation_reports",
        component: CSPViolationReportsPanel,
    },
    {
        class: "tinned_fish_products_admin",
        component: TinnedFishProductsAdminPanel,
    },
];

/**
 * Breadcrumb: APIs this widget is declared to call.
 * Declared on WidgetDefinition::$apiCalls in WidgetRegistry; emitted by GenerateFiles::generateWidgetApiCallsBreadcrumbBlock().
 * Keys are widget CSS class names. Values use the same METHOD + path strings as api_routes.
 */
export const WIDGET_API_CALLS: Record<string, ReadonlyArray<{ method: string; path: string }>> = {
    "bristol_stairs_panel": [
        { method: "GET", path: "/api/bristol_stairs_openmap_nearby" },
        { method: "POST", path: "/api/bristol_stairs_update/{bristol_stair_info_id:.*}" },
        { method: "POST", path: "/api/bristol_stairs_update_position/{bristol_stair_info_id:.*}" },
        { method: "POST", path: "/api/bristol_stairs_image" },
    ],
    "chat_panel": [
        { method: "GET", path: "/api/chat/room_messages/{room_id:.*}/" },
        { method: "GET", path: "/api/users/{user_id:.*}" },
    ],
    "email_link_generator_panel": [
    ],
    "floating_point_panel": [
    ],
    "login_status_panel": [
        { method: "GET", path: "/api/login-status" },
    ],
    "meme_management_panel": [
        { method: "GET", path: "/api/memes" },
        { method: "GET", path: "/api/memes/untagged" },
        { method: "GET", path: "/api/memes/search" },
        { method: "GET", path: "/api/memes/tag-suggestions" },
        { method: "GET", path: "/api/memes/{meme_id:.+}/tags" },
        { method: "GET", path: "/api/memes/{meme_id:.+}/text" },
        { method: "PUT", path: "/api/memes/{meme_id:.+}/text" },
        { method: "POST", path: "/api/meme-tag-add/" },
        { method: "PUT", path: "/api/meme-tag-update/" },
        { method: "POST", path: "/api/meme-tag-delete/" },
        { method: "POST", path: "/api/meme-upload/" },
    ],
    "meme_upload_panel": [
        { method: "POST", path: "/api/meme-upload/" },
    ],
    "notes_panel": [
    ],
    "notification_panel": [
        { method: "POST", path: "/api/save-subscription/" },
    ],
    "notification_test_panel": [
        { method: "GET", path: "/api/search_users" },
    ],
    "processor_run_record_panel": [
        { method: "GET", path: "/api/log/processor_run_records" },
    ],
    "qr_code_generator_panel": [
    ],
    "room_files_panel": [
        { method: "GET", path: "/api/rooms/{room_id:.*}/files" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "PUT", path: "/api/rooms/{room_id:.*}/files/{file_id:.*}/tags" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/files/{file_id:.*}" },
    ],
    "room_file_upload_panel": [
        { method: "POST", path: "/api/rooms/{room_id:.*}/file-upload" },
    ],
    "room_links_panel": [
        { method: "GET", path: "/api/rooms/{room_id:.*}/links" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "POST", path: "/api/rooms/{room_id:.*}/links" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/links/{room_link_id:.*}" },
        { method: "PUT", path: "/api/rooms/{room_id:.*}/links/{room_link_id:.*}/tags" },
    ],
    "room_annotations_panel": [
        { method: "GET", path: "/api/rooms/{room_id:.*}/annotations" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}" },
        { method: "PUT", path: "/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}/tags" },
    ],
    "room_videos_panel": [
        { method: "GET", path: "/api/rooms/{room_id:.*}/videos" },
        { method: "POST", path: "/api/rooms/{room_id:.*}/videos" },
        { method: "POST", path: "/api/rooms/{room_id:.*}/videos/clips" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/videos/{room_video_id:.*}" },
        { method: "PUT", path: "/api/rooms/{room_id:.*}/videos/{room_video_id:.*}/tags" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "GET", path: "/api/room-videos/{room_video_id:.*}/transcripts" },
        { method: "GET", path: "/api/room-videos/{room_video_id:.*}/transcripts/{transcript_id:.*}" },
        { method: "POST", path: "/api/room-videos/{room_video_id:.*}/transcripts/fetch" },
    ],
    "room_management_panel": [
        { method: "GET", path: "/api/rooms/{room_id:.*}/details" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/details" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "POST", path: "/api/rooms/{room_id:.*}/tags" },
    ],
    "room_tabs_panel": [
    ],
    "chat_bottom_panel": [
        { method: "POST", path: "/api/chat/message" },
    ],
    "teleprompter_panel": [
    ],
    "annotation_panel": [
        { method: "GET", path: "/api/rooms/{room_id}/file/{file_id}/annotations" },
        { method: "POST", path: "/api/rooms/{room_id:.*}/annotation/{file_id:.*}" },
        { method: "GET", path: "/api/rooms/{room_id:.*}/tags" },
        { method: "PUT", path: "/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}/tags" },
        { method: "PATCH", path: "/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}" },
    ],
    "time_line_panel": [
    ],
    "twitter_splitter_panel": [
    ],
    "committee_seats_panel": [
    ],
    "user_profile_panel": [
        { method: "POST", path: "/api/user/profile" },
        { method: "POST", path: "/api/user/avatar" },
    ],
    "widget_csp_violation_reports": [
        { method: "GET", path: "/api/system/csp/reports_for_page" },
    ],
    "tinned_fish_products_admin": [
        { method: "GET", path: "/api/tfd/v1/products" },
        { method: "POST", path: "/api/tfd/v1/products/{barcode:.*}/validation_status" },
        { method: "POST", path: "/api/tfd/v1/admin/api-token/generate" },
    ],
};
