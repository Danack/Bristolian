// This is an auto-generated file
// DO NOT EDIT

// You'll need to bounce the docker boxes to regenerate.

import { DateTime } from "luxon";

// Bristolian\Model\IncomingEmail
export interface IncomingEmail {
    id: number;
    message_id: string;
    body_plain: string;
    provider_variables: string;
    raw_email: string;
    recipient: string;
    retries: string;
    sender: string;
    status: string;
    stripped_text: string;
    subject: string;
    created_at: DateTime;
    updated_at: DateTime;
}

// Bristolian\Model\RoomLink
export interface RoomLink {
    id: string;
    link_id: string;
    url: string;
    title: string|null;
    description: string|null;
    room_id: string;
    user_id: string;
    created_at: DateTime;
}

// Bristolian\Model\RoomSourceLink
export interface RoomSourceLink {
    id: string;
    user_id: string;
    file_id: string;
    highlights_json: string;
    text: string;
    title: string;
    room_sourcelink_id: string;
}

// Bristolian\Model\ProcessorRunRecord
export interface ProcessorRunRecord {
    id: number;
    debug_info: string;
    processor_type: string;
    created_at: DateTime;
}

