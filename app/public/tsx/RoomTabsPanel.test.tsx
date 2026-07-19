import {describe, expect, test} from "@jest/globals";
import {
    hashForRoomTab,
    isRoomTabId,
    roomTabIdFromHash,
} from "./RoomTabsPanel";

describe("room tab hash helpers", () => {
    test("isRoomTabId accepts known tabs", () => {
        expect(isRoomTabId("chat")).toBe(true);
        expect(isRoomTabId("links")).toBe(true);
        expect(isRoomTabId("not-a-tab")).toBe(false);
    });

    test("roomTabIdFromHash reads tab ids and falls back to chat", () => {
        expect(roomTabIdFromHash("#links")).toBe("links");
        expect(roomTabIdFromHash("files")).toBe("files");
        expect(roomTabIdFromHash("")).toBe("chat");
        expect(roomTabIdFromHash("#")).toBe("chat");
        expect(roomTabIdFromHash("#unknown")).toBe("chat");
    });

    test("hashForRoomTab prefixes with #", () => {
        expect(hashForRoomTab("annotations")).toBe("#annotations");
        expect(hashForRoomTab("management")).toBe("#management");
    });
});
