/**
 * Types of meme tags.
 * User tags are created and managed by users.
 * System tags (e.g., NSFW, age rating) are managed by the system and cannot be edited by users.
 */
export const MemeTagType = {
    USER_TAG: 'user_tag',
} as const;

export type MemeTagType = typeof MemeTagType[keyof typeof MemeTagType];

