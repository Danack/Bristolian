<?php

declare(strict_types=1);

namespace Bristolian\Widget;

/**
 * Canonical list of frontend widgets registered for widgety bootstrapping.
 */
final class WidgetRegistry
{
    /**
     * @return list<WidgetDefinition>
     */
    public static function getAllDefinitions(): array
    {
        return [
            new WidgetDefinition(
                'bristol_stairs_panel',
                'BristolStairsPanel',
                './BristolStairsPanel',
                [
                    new WidgetApiCall('GET', '/api/bristol_stairs_openmap_nearby'),
                    new WidgetApiCall('POST', '/api/bristol_stairs_update/{bristol_stair_info_id:.*}'),
                    new WidgetApiCall('POST', '/api/bristol_stairs_update_position/{bristol_stair_info_id:.*}'),
                    new WidgetApiCall('POST', '/api/bristol_stairs_image'),
                ],
            ),
            new WidgetDefinition(
                'chat_panel',
                'ChatPanel',
                './ChatPanel',
                [
                    new WidgetApiCall('GET', '/api/chat/room_messages/{room_id:.*}/'),
                    new WidgetApiCall('GET', '/api/users/{user_id:.*}'),
                ],
            ),
            new WidgetDefinition(
                'email_link_generator_panel',
                'EmailLinkGeneratorPanel',
                './EmailLinkGenerator',
            ),
            new WidgetDefinition(
                'floating_point_panel',
                'FloatingPointPanel',
                './FloatingPointPanel',
            ),
            new WidgetDefinition(
                'login_status_panel',
                'LoginStatusPanel',
                './LoginStatusPanel',
                [
                    new WidgetApiCall('GET', '/api/login-status'),
                ],
            ),
            new WidgetDefinition(
                'meme_management_panel',
                'MemeManagementPanel',
                './MemeManagementPanel',
                [
                    new WidgetApiCall('GET', '/api/memes'),
                    new WidgetApiCall('GET', '/api/memes/untagged'),
                    new WidgetApiCall('GET', '/api/memes/search'),
                    new WidgetApiCall('GET', '/api/memes/tag-suggestions'),
                    new WidgetApiCall('GET', '/api/memes/{meme_id:.+}/tags'),
                    new WidgetApiCall('GET', '/api/memes/{meme_id:.+}/text'),
                    new WidgetApiCall('PUT', '/api/memes/{meme_id:.+}/text'),
                    new WidgetApiCall('POST', '/api/meme-tag-add/'),
                    new WidgetApiCall('PUT', '/api/meme-tag-update/'),
                    new WidgetApiCall('POST', '/api/meme-tag-delete/'),
                    new WidgetApiCall('POST', '/api/meme-upload/'),
                ],
            ),
            new WidgetDefinition(
                'meme_upload_panel',
                'MemeUploadPanel',
                './MemeUploadPanel',
                [
                    new WidgetApiCall('POST', '/api/meme-upload/'),
                ],
            ),
            new WidgetDefinition(
                'notes_panel',
                'NotesPanel',
                './NotesPanel',
            ),
            new WidgetDefinition(
                'notification_panel',
                'NotificationRegistrationPanel',
                './NotificationRegistrationPanel',
                [
                    new WidgetApiCall('POST', '/api/save-subscription/'),
                ],
            ),
            new WidgetDefinition(
                'notification_test_panel',
                'NotificationTestPanel',
                './NotificationTestPanel',
                [
                    new WidgetApiCall('GET', '/api/search_users'),
                ],
            ),
            new WidgetDefinition(
                'processor_run_record_panel',
                'ProcessorRunRecordPanel',
                './ProcessorRunRecordPanel',
                [
                    new WidgetApiCall('GET', '/api/log/processor_run_records'),
                ],
            ),
            new WidgetDefinition(
                'qr_code_generator_panel',
                'QrCodeGeneratorPanel',
                './QrCodeGenerator',
            ),
            new WidgetDefinition(
                'room_files_panel',
                'RoomFilesPanel',
                './RoomFilesPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/files'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('PUT', '/api/rooms/{room_id:.*}/files/{file_id:.*}/tags'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/files/{file_id:.*}'),
                ],
            ),
            new WidgetDefinition(
                'room_file_upload_panel',
                'RoomFileUploadPanel',
                './RoomFileUploadPanel',
                [
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/file-upload'),
                ],
            ),
            new WidgetDefinition(
                'room_links_panel',
                'RoomLinksPanel',
                './RoomLinksPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/links'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/links'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/links/{room_link_id:.*}'),
                    new WidgetApiCall('PUT', '/api/rooms/{room_id:.*}/links/{room_link_id:.*}/tags'),
                ],
            ),
            new WidgetDefinition(
                'room_annotations_panel',
                'RoomAnnotationsPanel',
                './RoomAnnotationsPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/annotations'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}'),
                    new WidgetApiCall('PUT', '/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}/tags'),
                ],
            ),
            new WidgetDefinition(
                'room_videos_panel',
                'RoomVideosPanel',
                './RoomVideosPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/videos'),
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/videos'),
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/videos/clips'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/videos/{room_video_id:.*}'),
                    new WidgetApiCall('PUT', '/api/rooms/{room_id:.*}/videos/{room_video_id:.*}/tags'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('GET', '/api/room-videos/{room_video_id:.*}/transcripts'),
                    new WidgetApiCall('GET', '/api/room-videos/{room_video_id:.*}/transcripts/{transcript_id:.*}'),
                    new WidgetApiCall('POST', '/api/room-videos/{room_video_id:.*}/transcripts/fetch'),
                ],
            ),
            new WidgetDefinition(
                'room_management_panel',
                'RoomManagementPanel',
                './RoomManagementPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/details'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/details'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/tags'),
                ],
            ),
            new WidgetDefinition(
                'room_tabs_panel',
                'RoomTabsPanel',
                './RoomTabsPanel',
            ),
            new WidgetDefinition(
                'chat_bottom_panel',
                'ChatBottomPanel',
                './chat/ChatBottomPanel',
                [
                    new WidgetApiCall('POST', '/api/chat/message'),
                ],
            ),
            new WidgetDefinition(
                'teleprompter_panel',
                'TeleprompterPanel',
                './TeleprompterPanel',
            ),
            new WidgetDefinition(
                'annotation_panel',
                'AnnotationPanel',
                './AnnotationPanel',
                [
                    new WidgetApiCall('GET', '/api/rooms/{room_id}/file/{file_id}/annotations'),
                    new WidgetApiCall('POST', '/api/rooms/{room_id:.*}/annotation/{file_id:.*}'),
                    new WidgetApiCall('GET', '/api/rooms/{room_id:.*}/tags'),
                    new WidgetApiCall('PUT', '/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}/tags'),
                    new WidgetApiCall('PATCH', '/api/rooms/{room_id:.*}/annotations/{room_annotation_id:.*}'),
                ],
            ),
            new WidgetDefinition(
                'time_line_panel',
                'TimeLinePanel',
                './TimeLinePanel',
            ),
            new WidgetDefinition(
                'twitter_splitter_panel',
                'TwitterSplitterPanel',
                './TwitterSplitterPanel',
            ),
            new WidgetDefinition(
                'committee_seats_panel',
                'CommitteeSeatsPanel',
                './CommitteeSeatsPanel',
            ),
            new WidgetDefinition(
                'user_profile_panel',
                'UserProfilePanel',
                './UserProfilePanel',
                [
                    new WidgetApiCall('POST', '/api/user/profile'),
                    new WidgetApiCall('POST', '/api/user/avatar'),
                ],
            ),
            new WidgetDefinition(
                'widget_csp_violation_reports',
                'CSPViolationReportsPanel',
                './CSPViolationReportsPanel',
                [
                    new WidgetApiCall('GET', '/api/system/csp/reports_for_page'),
                ],
            ),
            new WidgetDefinition(
                'tinned_fish_products_admin',
                'TinnedFishProductsAdminPanel',
                './TinnedFishProductsAdminPanel',
                [
                    new WidgetApiCall('GET', '/api/tfd/v1/products'),
                    new WidgetApiCall('POST', '/api/tfd/v1/products/{barcode:.*}/validation_status'),
                    new WidgetApiCall('POST', '/api/tfd/v1/admin/api-token/generate'),
                ],
            ),
        ];
    }
}
