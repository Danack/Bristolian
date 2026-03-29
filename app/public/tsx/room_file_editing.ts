/**
 * Tracks whether the room files list is in inline-edit mode so other widgets
 * (e.g. file upload) can stay in sync even if they mount after edit starts.
 */
let roomFileEditingActive = false;

export function setRoomFileEditingActive(active: boolean): void {
    roomFileEditingActive = active;
}

export function getRoomFileEditingActive(): boolean {
    return roomFileEditingActive;
}
