<?php

declare(strict_types=1);

namespace Bristolian\Cache;

use Bristolian\CliController\Email as EmailController;
use Bristolian\Database\annotation;
use Bristolian\Database\avatar_image_object_info;
use Bristolian\Database\bcc_tro_information;
use Bristolian\Database\bristol_stair_info;
use Bristolian\Database\chat_message;
use Bristolian\Database\email_incoming;
use Bristolian\Database\email_send_queue;
use Bristolian\Database\foi_requests;
use Bristolian\Database\link;
use Bristolian\Database\meme_tag;
use Bristolian\Database\meme_text;
use Bristolian\Database\migrations;
use Bristolian\Database\processor;
use Bristolian\Database\processor_run_record;
use Bristolian\Database\room_annotation;
use Bristolian\Database\room_file_object_info;
use Bristolian\Database\room_link;
use Bristolian\Database\room_tag;
use Bristolian\Database\room_video;
use Bristolian\Database\room_video_tag;
use Bristolian\Database\room_video_transcript;
use Bristolian\Database\stair_image_object_info;
use Bristolian\Database\stored_meme;
use Bristolian\Database\tinned_fish_product;
use Bristolian\Database\user_display_name;
use Bristolian\Database\user_profile;
use Bristolian\Database\video;

/**
 * Maps each SQL query (trimmed) to the tables it reads from or writes to.
 *
 * Entries reference the generated Database constant classes so the mapping
 * stays in sync when table helpers are regenerated.
 */
class QueryTagMapping
{
    /**
     * @return array<string, array{read: string[], write: string[]}>
     */
    public static function getExactMappings(): array
    {
        return [
            // ===== AdminRepo =====
            trim(<<<SQL
insert into user (
    id
)
values (
    :id
)
SQL) => ['read' => [], 'write' => ['user']],

            trim(<<<SQL
insert into user_auth_email_password (
    user_id,
    email_address,
    password_hash
)
values (
    :user_id,
    :email_address,
    :password_hash
)
SQL) => ['read' => [], 'write' => ['user_auth_email_password']],

            trim(<<<SQL
select user_id, email_address
from user_auth_email_password
where email_address = :email_address
SQL) => ['read' => ['user_auth_email_password'], 'write' => []],

            trim(<<<SQL
select user_id, email_address, password_hash
from user_auth_email_password
where email_address = :email_address
SQL) => ['read' => ['user_auth_email_password'], 'write' => []],

            // ===== ApiTokenRepo =====
            trim(<<<SQL
INSERT INTO api_token (
    id,
    token,
    name,
    created_at,
    is_revoked,
    revoked_at
)
VALUES (
    :id,
    :token,
    :name,
    NOW(),
    false,
    NULL
)
SQL) => ['read' => [], 'write' => ['api_token']],

            trim(<<<SQL
SELECT
    id,
    token,
    name,
    created_at,
    is_revoked,
    revoked_at
FROM
    api_token
WHERE
    token = :token
LIMIT 1
SQL) => ['read' => ['api_token'], 'write' => []],

            trim(<<<SQL
UPDATE api_token
SET is_revoked = true, revoked_at = NOW()
WHERE id = :id
LIMIT 1
SQL) => ['read' => [], 'write' => ['api_token']],

            // ===== AvatarImageStorageInfoRepo =====
            trim(avatar_image_object_info::SELECT . " WHERE normalized_name = :normalized_name")
                => ['read' => ['avatar_image_object_info'], 'write' => []],

            trim(avatar_image_object_info::SELECT . " WHERE id = :id")
                => ['read' => ['avatar_image_object_info'], 'write' => []],

            trim(avatar_image_object_info::INSERT)
                => ['read' => [], 'write' => ['avatar_image_object_info']],

            trim(<<<SQL
update
  avatar_image_object_info
set
  state = :filestate
where
  id = :id
SQL) => ['read' => [], 'write' => ['avatar_image_object_info']],

            // ===== BccTroRepo =====
            trim(bcc_tro_information::INSERT)
                => ['read' => [], 'write' => ['bcc_tro_information']],

            // ===== BristolStairImageStorageInfoRepo =====
            trim(stair_image_object_info::SELECT . " WHERE normalized_name = :normalized_name")
                => ['read' => ['stair_image_object_info'], 'write' => []],

            trim(stair_image_object_info::SELECT . " WHERE id = :id")
                => ['read' => ['stair_image_object_info'], 'write' => []],

            trim(stair_image_object_info::INSERT)
                => ['read' => [], 'write' => ['stair_image_object_info']],

            trim(<<<SQL
update
  stair_image_object_info
set
  state = :filestate
where
  id = :id
SQL) => ['read' => [], 'write' => ['stair_image_object_info']],

            // ===== BristolStairsRepo =====
            trim(<<<SQL
update
  bristol_stair_info
set 
  description = :description,
  steps = :steps
where
  id = :id
  limit 1
SQL) => ['read' => [], 'write' => ['bristol_stair_info']],

            trim(<<<SQL
update
  bristol_stair_info
set 
  latitude = :latitude,
  longitude = :longitude
where
  id = :id
  limit 1
SQL) => ['read' => [], 'write' => ['bristol_stair_info']],

            trim(bristol_stair_info::INSERT)
                => ['read' => [], 'write' => ['bristol_stair_info']],

            trim(bristol_stair_info::SELECT . "where is_deleted = 0")
                => ['read' => ['bristol_stair_info'], 'write' => []],

            trim(bristol_stair_info::SELECT . "where id = :id and is_deleted = 0")
                => ['read' => ['bristol_stair_info'], 'write' => []],

            trim(<<<SQL
select sum(1) as flights_of_stairs, sum(steps) as total_steps from bristol_stair_info where is_deleted = 0
SQL) => ['read' => ['bristol_stair_info'], 'write' => []],

            // ===== ChatMessageRepo =====
            trim(chat_message::INSERT)
                => ['read' => [], 'write' => ['chat_message']],

            trim(chat_message::SELECT . " where id = :message_id")
                => ['read' => ['chat_message'], 'write' => []],

            trim(chat_message::SELECT . " where room_id = :room_id ORDER BY id DESC LIMIT 50")
                => ['read' => ['chat_message'], 'write' => []],

            // ===== DbInfo =====
            trim(migrations::SELECT . " order by created_at ASC, ID ASC")
                => ['read' => ['migrations'], 'write' => []],

            // ===== EmailIncoming =====
            trim(email_incoming::INSERT)
                => ['read' => [], 'write' => ['email_incoming']],

            // ===== EmailQueue =====
            trim(<<<SQL
insert into email_send_queue (
    body,
    recipient,
    retries,
    status,
    subject
)
values (
    :body,
    :recipient,
    :retries,
    :status,
    :subject
)
SQL) => ['read' => [], 'write' => ['email_send_queue']],

            trim(email_send_queue::SELECT . sprintf(
                " WHERE status in ('%s', '%s') limit 1 FOR UPDATE",
                EmailController::STATE_INITIAL,
                EmailController::STATE_RETRY
            )) => ['read' => ['email_send_queue'], 'write' => []],

            trim(<<<SQL
update
  email_send_queue
set
   status = :status
where
  id = :id
limit 1
SQL) => ['read' => [], 'write' => ['email_send_queue']],

            trim(<<<SQL
update
  email_send_queue
set
   status = :status,
   retries = retries + 1
where
  id = :id
limit 1
SQL) => ['read' => [], 'write' => ['email_send_queue']],

            trim(sprintf(
                <<<SQL
update 
  email_send_queue
set
  status = '%s'
where
  status in ('%s', '%s', '%s')
SQL,
                EmailController::STATE_SKIPPED,
                EmailController::STATE_INITIAL,
                EmailController::STATE_SENDING,
                EmailController::STATE_RETRY,
            )) => ['read' => [], 'write' => ['email_send_queue']],

            trim('DELETE FROM email_send_queue')
                => ['read' => [], 'write' => ['email_send_queue']],

            // ===== FoiRequestRepo =====
            trim(foi_requests::SELECT . " where foi_request_id = :foi_request_id limit 1")
                => ['read' => ['foi_requests'], 'write' => []],

            trim(foi_requests::INSERT)
                => ['read' => [], 'write' => ['foi_requests']],

            trim(foi_requests::SELECT)
                => ['read' => ['foi_requests'], 'write' => []],

            // ===== LinkRepo =====
            trim(link::INSERT)
                => ['read' => [], 'write' => ['link']],

            // ===== MemeStorageRepo =====
            trim(stored_meme::SELECT . " where id = :id AND deleted = 0")
                => ['read' => ['stored_meme'], 'write' => []],

            trim(stored_meme::SELECT . " WHERE normalized_name = :normalized_name AND deleted = 0")
                => ['read' => ['stored_meme'], 'write' => []],

            trim(stored_meme::SELECT . <<<SQL
where
  user_id = :user_id and
  state = :state and
  deleted = 0
SQL) => ['read' => ['stored_meme'], 'write' => []],

            trim(stored_meme::SELECT . <<<SQL
where
  state = :state and
  deleted = 0
SQL) => ['read' => ['stored_meme'], 'write' => []],

            trim(stored_meme::SELECT . <<<SQL
 where
  user_id = :user_id and
  state = :state and
  deleted = 0 and
  not exists (
    select 1 from meme_tag mt
    where mt.meme_id = stored_meme.id and mt.type = :user_tag_type
  )
SQL) => ['read' => ['stored_meme', 'meme_tag'], 'write' => []],

            // searchMemesForUser - without query filter
            trim(<<<SQL
SELECT DISTINCT
  sm.id,
  sm.normalized_name,
  sm.original_filename,
  sm.state,
  sm.size,
  sm.user_id,
    sm.created_at
FROM
  stored_meme sm
JOIN
  meme_tag mt ON sm.id = mt.meme_id
WHERE
  sm.user_id = :user_id AND
  sm.state = :state AND
  sm.deleted = 0 AND
  mt.type = :user_tag_type
SQL) => ['read' => ['stored_meme', 'meme_tag'], 'write' => []],

            // searchMemesForUser - with query filter
            trim(<<<SQL
SELECT DISTINCT
  sm.id,
  sm.normalized_name,
  sm.original_filename,
  sm.state,
  sm.size,
  sm.user_id,
    sm.created_at
FROM
  stored_meme sm
JOIN
  meme_tag mt ON sm.id = mt.meme_id
WHERE
  sm.user_id = :user_id AND
  sm.state = :state AND
  sm.deleted = 0 AND
  mt.type = :user_tag_type AND mt.text LIKE :query
SQL) => ['read' => ['stored_meme', 'meme_tag'], 'write' => []],

            trim(stored_meme::INSERT)
                => ['read' => [], 'write' => ['stored_meme']],

            trim(<<<SQL
update
  stored_meme 
set
  state = :filestate
where
  id = :id
SQL) => ['read' => [], 'write' => ['stored_meme']],

            trim(<<<SQL
update
  stored_meme 
set
  deleted = 1
where
  id = :id
SQL) => ['read' => [], 'write' => ['stored_meme']],

            trim(stored_meme::SELECT . " WHERE user_id = :user_id AND original_filename = :original_filename AND deleted = 0")
                => ['read' => ['stored_meme'], 'write' => []],

            // ===== MemeTagRepo =====
            trim(meme_tag::INSERT)
                => ['read' => [], 'write' => ['meme_tag']],

            trim(<<<SQL
select
    mt.id,
    mt.user_id,
    mt.meme_id,
    mt.type,
    mt.text,
    mt.created_at
from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.meme_id = :meme_id
SQL) => ['read' => ['meme_tag', 'stored_meme'], 'write' => []],

            trim(<<<SQL
update
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
set
  mt.type = :user_tag_type,
  mt.text = :text
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id and
  mt.type = :user_tag_type_check
SQL) => ['read' => [], 'write' => ['meme_tag']],

            trim(<<<SQL
delete mt from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id and
  mt.type = :user_tag_type
SQL) => ['read' => [], 'write' => ['meme_tag']],

            trim(<<<SQL
SELECT 
    mt.text,
    COUNT(*) as count
FROM
    meme_tag mt
INNER JOIN stored_meme sm ON mt.meme_id = sm.id
WHERE
    sm.user_id = :user_id AND
    mt.type = :user_tag_type AND
    sm.state = :state
GROUP BY
    mt.text
ORDER BY
    count DESC,
    mt.text ASC
LIMIT :limit
SQL) => ['read' => ['meme_tag', 'stored_meme'], 'write' => []],

            // ===== MemeTextRepo =====
            trim(meme_text::INSERT)
                => ['read' => [], 'write' => ['meme_text']],

            trim(<<<SQL
select 
    sm.id,                 
    sm.normalized_name,    
    sm.original_filename,  
    sm.state,              
    sm.size,               
    sm.user_id,            
    sm.created_at,         
    sm.deleted             
from                       
  stored_meme sm
left join 
    meme_text mt on sm.id = mt.meme_id
where 
  mt.id is null and
  sm.deleted = 0
order by 
  sm.created_at asc
limit 1
SQL) => ['read' => ['stored_meme', 'meme_text'], 'write' => []],

            trim(<<<SQL
SELECT DISTINCT
  sm.id
FROM
  stored_meme sm
JOIN
  meme_text mt ON sm.id = mt.meme_id
WHERE
  sm.user_id = :user_id AND
  sm.deleted = 0 AND
  LOWER(mt.text) LIKE LOWER(:search_text)
SQL) => ['read' => ['stored_meme', 'meme_text'], 'write' => []],

            trim(meme_text::SELECT . <<<SQL
where
  meme_id = :meme_id
order by
  created_at desc
limit 1
SQL) => ['read' => ['meme_text'], 'write' => []],

            trim(meme_text::UPDATE)
                => ['read' => [], 'write' => ['meme_text']],

            // ===== ProcessorRepo =====
            trim(processor::SELECT)
                => ['read' => ['processor'], 'write' => []],

            trim("insert into processor (
    enabled,
    type
)
values (
    :enabled,
    :type
)" . " ON DUPLICATE KEY UPDATE enabled = :enabled_again")
                => ['read' => [], 'write' => ['processor']],

            trim("select enabled from processor where type = :type")
                => ['read' => ['processor'], 'write' => []],

            // ===== ProcessorRunRecordRepo =====
            trim(processor_run_record::SELECT . "where processor_type = :processor_type" . " order by start_time desc limit 1")
                => ['read' => ['processor_run_record'], 'write' => []],

            trim(processor_run_record::INSERT)
                => ['read' => [], 'write' => ['processor_run_record']],

            trim(<<<SQL
update
  processor_run_record
set 
  end_time = NOW(),
  debug_info = :debug_info,
  status = :status
where
  id = :id
limit 1
SQL) => ['read' => [], 'write' => ['processor_run_record']],

            trim(processor_run_record::SELECT . " order by id desc limit 50")
                => ['read' => ['processor_run_record'], 'write' => []],

            trim(processor_run_record::SELECT . " where processor_type = :processor_type order by id desc limit 50")
                => ['read' => ['processor_run_record'], 'write' => []],

            // ===== RoomAnnotationRepo =====
            trim(annotation::INSERT)
                => ['read' => [], 'write' => ['annotation']],

            trim(room_annotation::INSERT)
                => ['read' => [], 'write' => ['room_annotation']],

            trim(<<<SQL
select  
    a.id,
    a.user_id,
    a.file_id,
    a.highlights_json,
    a.text,
    ra.title,
    ra.id as room_annotation_id
from
  annotation a
left join
  room_annotation ra
on 
 a.id = ra.annotation_id
where
  room_id = :room_id
SQL) => ['read' => ['annotation', 'room_annotation'], 'write' => []],

            trim(<<<SQL
select  
    a.id,
    a.user_id,
    a.file_id,
    a.highlights_json,
    a.text,
    ra.title,
    ra.id as room_annotation_id
from
  annotation a
left join
  room_annotation ra
on 
 a.id = ra.annotation_id
where
  ra.room_id = :room_id
and
  a.file_id = :file_id
SQL) => ['read' => ['annotation', 'room_annotation'], 'write' => []],

            // ===== RoomAnnotationTagRepo =====
            trim("SELECT tag_id FROM room_annotation_tag WHERE room_annotation_id = :room_annotation_id")
                => ['read' => ['room_annotation_tag'], 'write' => []],

            trim('DELETE FROM room_annotation_tag WHERE room_annotation_id = :room_annotation_id')
                => ['read' => [], 'write' => ['room_annotation_tag']],

            trim('INSERT INTO room_annotation_tag (room_annotation_id, tag_id) VALUES (:room_annotation_id, :tag_id)')
                => ['read' => [], 'write' => ['room_annotation_tag']],

            // ===== RoomFileObjectInfoRepo =====
            trim(room_file_object_info::INSERT)
                => ['read' => [], 'write' => ['room_file_object_info']],

            trim(<<<SQL
update
  room_file_object_info
set
  state = :filestate
where
  id = :id
SQL) => ['read' => [], 'write' => ['room_file_object_info']],

            // ===== RoomFileRepo =====
            trim(<<<SQL
insert into room_file (
    room_id,
    stored_file_id
)
values (
    :room_id,
    :stored_file_id
)
SQL) => ['read' => [], 'write' => ['room_file']],

            trim(<<<SQL
select
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at
from room_file_object_info as sf
left join room_file as rf on sf.id = rf.stored_file_id
where room_id = :room_id
SQL) => ['read' => ['room_file_object_info', 'room_file'], 'write' => []],

            trim(<<<SQL
select
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at
from room_file_object_info as sf
left join room_file as rf on sf.id = rf.stored_file_id
where room_id = :room_id
and sf.id = :file_id
SQL) => ['read' => ['room_file_object_info', 'room_file'], 'write' => []],

            // ===== RoomFileTagRepo =====
            trim("SELECT tag_id FROM room_file_tag WHERE room_id = :room_id AND stored_file_id = :stored_file_id")
                => ['read' => ['room_file_tag'], 'write' => []],

            trim('DELETE FROM room_file_tag WHERE room_id = :room_id AND stored_file_id = :stored_file_id')
                => ['read' => [], 'write' => ['room_file_tag']],

            trim('INSERT INTO room_file_tag (room_id, stored_file_id, tag_id) VALUES (:room_id, :stored_file_id, :tag_id)')
                => ['read' => [], 'write' => ['room_file_tag']],

            // ===== RoomLinkRepo =====
            trim(room_link::INSERT)
                => ['read' => [], 'write' => ['room_link']],

            trim(room_link::SELECT . "where id = :id")
                => ['read' => ['room_link'], 'write' => []],

            trim(room_link::SELECT . "where room_id = :room_id")
                => ['read' => ['room_link'], 'write' => []],

            // ===== RoomLinkTagRepo =====
            trim("SELECT tag_id FROM room_link_tag WHERE room_link_id = :room_link_id")
                => ['read' => ['room_link_tag'], 'write' => []],

            trim('DELETE FROM room_link_tag WHERE room_link_id = :room_link_id')
                => ['read' => [], 'write' => ['room_link_tag']],

            trim('INSERT INTO room_link_tag (room_link_id, tag_id) VALUES (:room_link_id, :tag_id)')
                => ['read' => [], 'write' => ['room_link_tag']],

            // ===== RoomRepo =====
            trim(<<<SQL
insert into room (
    id,
    owner_user_id,
    name,
    purpose
)
values (
    :id,
    :owner_user_id,
    :name,
    :purpose
)
SQL) => ['read' => [], 'write' => ['room']],

            trim(<<<SQL
select
    id,
    owner_user_id,
    name,
    purpose,
    created_at
from room
where id = :room_id
SQL) => ['read' => ['room'], 'write' => []],

            trim(<<<SQL
select
    id,
    owner_user_id,
    name,
    purpose,
    created_at
from room
SQL) => ['read' => ['room'], 'write' => []],

            // ===== RoomTagRepo =====
            trim(room_tag::INSERT)
                => ['read' => [], 'write' => ['room_tag']],

            trim(room_tag::SELECT . " where tag_id = :tag_id")
                => ['read' => ['room_tag'], 'write' => []],

            trim(room_tag::SELECT . " where room_id = :room_id")
                => ['read' => ['room_tag'], 'write' => []],

            // ===== RoomVideoRepo =====
            trim(room_video::SELECT . " where room_id = :room_id order by created_at asc")
                => ['read' => ['room_video'], 'write' => []],

            trim(room_video_tag::SELECT . " where room_video_id = :room_video_id")
                => ['read' => ['room_video_tag'], 'write' => []],

            trim(video::SELECT . " where id = :id")
                => ['read' => ['video'], 'write' => []],

            trim(room_video::SELECT . " where id = :id")
                => ['read' => ['room_video'], 'write' => []],

            trim(room_video::INSERT)
                => ['read' => [], 'write' => ['room_video']],

            // ===== RoomVideoTagRepo =====
            trim('DELETE FROM room_video_tag WHERE room_video_id = :room_video_id')
                => ['read' => [], 'write' => ['room_video_tag']],

            trim(room_video_tag::INSERT)
                => ['read' => [], 'write' => ['room_video_tag']],

            // ===== RoomVideoTranscriptRepo =====
            trim(room_video_transcript::SELECT . " where room_video_id = :room_video_id order by transcript_number asc")
                => ['read' => ['room_video_transcript'], 'write' => []],

            trim(<<<SQL
insert into room_video_transcript (id, room_video_id, transcript_number, language, vtt_content)
select :id, :room_video_id, sub.next_num, :language, :vtt_content
from (
    select coalesce(max(transcript_number), 0) + 1 as next_num
    from room_video_transcript
    where room_video_id = :room_video_id_subquery
) sub
SQL) => ['read' => [], 'write' => ['room_video_transcript']],

            trim(room_video_transcript::SELECT . " where id = :id")
                => ['read' => ['room_video_transcript'], 'write' => []],

            // ===== TinnedFishProductRepo =====
            trim(tinned_fish_product::SELECT . " WHERE barcode = :barcode")
                => ['read' => ['tinned_fish_product'], 'write' => []],

            trim(tinned_fish_product::SELECT . " ORDER BY created_at DESC")
                => ['read' => ['tinned_fish_product'], 'write' => []],

            trim(tinned_fish_product::INSERT . " ON DUPLICATE KEY UPDATE
            name = :name_update,
            brand = :brand_update,
            species = :species_update,
            weight = :weight_update,
            weight_drained = :weight_drained_update,
            product_code = :product_code_update,
            image_url = :image_url_update,
            validation_status = :validation_status_update")
                => ['read' => [], 'write' => ['tinned_fish_product']],

            trim("UPDATE tinned_fish_product 
                SET validation_status = :validation_status 
                WHERE barcode = :barcode 
                LIMIT 1")
                => ['read' => [], 'write' => ['tinned_fish_product']],

            // ===== UserProfileRepo =====
            trim(user_display_name::SELECT . " WHERE user_id = :user_id ORDER BY version DESC LIMIT 1")
                => ['read' => ['user_display_name'], 'write' => []],

            trim(user_profile::SELECT . " WHERE user_id = :user_id")
                => ['read' => ['user_profile'], 'write' => []],

            trim(user_display_name::SELECT . " WHERE user_id = :user_id ORDER BY version DESC")
                => ['read' => ['user_display_name'], 'write' => []],

            trim(<<<SQL
INSERT INTO user_display_name 
  (user_id, display_name, version)
SELECT 
  :user_id,
  :display_name,
  COALESCE(MAX(version), 0) + 1
FROM 
  user_display_name
WHERE 
  user_id = :user_id_for_select
SQL) => ['read' => [], 'write' => ['user_display_name']],

            trim(<<<SQL
INSERT INTO user_profile 
  (user_id, about_me)
VALUES 
  (:user_id, :about_me)
ON DUPLICATE KEY UPDATE
  about_me = VALUES(about_me)
SQL) => ['read' => [], 'write' => ['user_profile']],

            trim(<<<SQL
INSERT INTO user_profile 
  (user_id, avatar_image_id)
VALUES 
  (:user_id, :avatar_image_id)
ON DUPLICATE KEY UPDATE
  avatar_image_id = VALUES(avatar_image_id)
SQL) => ['read' => [], 'write' => ['user_profile']],

            // ===== UserSearch =====
            trim(<<<SQL
select
  email_address
from
  user_auth_email_password
where
  email_address like :like_string
limit :limit_number
SQL) => ['read' => ['user_auth_email_password'], 'write' => []],

            // ===== VideoRepo =====
            trim(video::INSERT)
                => ['read' => [], 'write' => ['video']],

            trim(video::SELECT . " where id = :id")
                => ['read' => ['video'], 'write' => []],

            // ===== WebPushSubscriptionRepo =====
            trim(<<<SQL
select
  endpoint,
  expiration_time,
  raw
from
  user_webpush_subscription
where
  user_id = :user_id
SQL) => ['read' => ['user_webpush_subscription'], 'write' => []],

            trim(<<<SQL
insert into user_webpush_subscription (
    user_id,
    endpoint,
    expiration_time,
    raw
)
values (
    :user_id,
    :endpoint,
    :expiration_time,
    :raw
)
SQL) => ['read' => [], 'write' => ['user_webpush_subscription']],
        ];
    }

    /**
     * Regex patterns for dynamic queries (e.g., those with variable IN clauses).
     * Checked only when exact match fails.
     *
     * @return array<array{pattern: string, read: string[], write: string[]}>
     */
    public static function getPatternMappings(): array
    {
        return [
            // MemeStorageRepo::searchMemesByExactTags - dynamic IN clause
            [
                'pattern' => '#SELECT DISTINCT\s+sm\.id.*FROM\s+stored_meme\s+sm\s+WHERE.*mt\.text\s+IN\s*\(.*\)\s*\)\s*=\s*:tag_count#s',
                'read' => ['stored_meme', 'meme_tag'],
                'write' => [],
            ],
            // MemeTagRepo::getMostCommonTagsForMemes - dynamic IN clause
            [
                'pattern' => '#SELECT\s+mt\.text.*COUNT\(\*\)\s+as\s+count.*FROM\s+meme_tag\s+mt.*sm\.id\s+IN\s*\(.*\).*GROUP\s+BY.*LIMIT\s+:limit#s',
                'read' => ['meme_tag', 'stored_meme'],
                'write' => [],
            ],
        ];
    }
}
