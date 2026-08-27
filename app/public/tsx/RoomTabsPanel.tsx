import { Component } from "preact";
import { ChatPanel } from "./ChatPanel";
import { RoomLinksPanel } from "./RoomLinksPanel";
import { RoomFilesPanel } from "./RoomFilesPanel";
import { RoomFileUploadPanel } from "./RoomFileUploadPanel";
import { RoomAnnotationsPanel } from "./RoomAnnotationsPanel";
import { RoomVideosPanel } from "./RoomVideosPanel";
import { RoomManagementPanel } from "./RoomManagementPanel";

export interface RoomTabsPanelProps {
    room_id: string;
    room_name: string;
    room_purpose: string;
    accepted_file_extensions: string[];
}

export type RoomTabId = "chat" | "links" | "files" | "annotations" | "video" | "management";

interface RoomTabsPanelState {
    activeTab: RoomTabId;
}

const TAB_LABELS: ReadonlyArray<{ id: RoomTabId; label: string }> = [
    { id: "chat", label: "Chat" },
    { id: "links", label: "Links" },
    { id: "files", label: "Files" },
    { id: "annotations", label: "Annotations" },
    { id: "video", label: "Videos" },
    { id: "management", label: "Room Management" },
];

const VALID_ROOM_TAB_IDS: ReadonlyArray<RoomTabId> = TAB_LABELS.map((tab) => tab.id);

export function isRoomTabId(value: string): value is RoomTabId {
    return (VALID_ROOM_TAB_IDS as ReadonlyArray<string>).includes(value);
}

/**
 * Resolve the active room tab from a URL hash (e.g. "#links").
 * Empty or unknown hashes fall back to the chat tab.
 */
export function roomTabIdFromHash(hash: string): RoomTabId {
    const withoutHash = hash.startsWith("#") ? hash.slice(1) : hash;
    if (isRoomTabId(withoutHash)) {
        return withoutHash;
    }
    return "chat";
}

export function hashForRoomTab(tabId: RoomTabId): string {
    return "#" + tabId;
}

function getInitialState(): RoomTabsPanelState {
    return {
        activeTab: roomTabIdFromHash(window.location.hash),
    };
}

export class RoomTabsPanel extends Component<RoomTabsPanelProps, RoomTabsPanelState> {

    private hashChangeListener: (() => void) | null = null;

    constructor(props: RoomTabsPanelProps) {
        super(props);
        this.state = getInitialState();
    }

    componentDidMount() {
        this.hashChangeListener = () => {
            const tabId = roomTabIdFromHash(window.location.hash);
            if (tabId !== this.state.activeTab) {
                this.setState({ activeTab: tabId });
            }
        };
        window.addEventListener("hashchange", this.hashChangeListener);
    }

    componentWillUnmount() {
        if (this.hashChangeListener !== null) {
            window.removeEventListener("hashchange", this.hashChangeListener);
            this.hashChangeListener = null;
        }
    }

    selectTab = (tabId: RoomTabId) => {
        this.setState({ activeTab: tabId });
        const desiredHash = hashForRoomTab(tabId);
        if (window.location.hash !== desiredHash) {
            window.location.hash = tabId;
        }
    }

    private panelClass(tabId: RoomTabId): string {
        const base = `room_tab_panel room_tab_panel_${tabId}`;
        return this.state.activeTab === tabId ? `${base} active` : base;
    }

    render() {
        const { room_id, room_name, room_purpose } = this.props;

        return (
            <div className="roompage_tabs">
                <div className="room_tab_strip" role="tablist">
                    {TAB_LABELS.map((tab) => (
                        <button
                            key={tab.id}
                            type="button"
                            role="tab"
                            aria-selected={this.state.activeTab === tab.id}
                            className={
                                this.state.activeTab === tab.id
                                    ? "room_tab_label active"
                                    : "room_tab_label"
                            }
                            onClick={() => this.selectTab(tab.id)}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="room_tabs_panels">
                    <div className={this.panelClass("chat")}>
                        <div className="chat_panel">
                            <ChatPanel room_id={room_id} username="" />
                        </div>
                    </div>

                    <div className={this.panelClass("links")}>
                        <RoomLinksPanel room_id={room_id} />
                    </div>

                    <div className={this.panelClass("files")}>
                        <RoomFilesPanel room_id={room_id} />
                        <RoomFileUploadPanel room_id={room_id} />
                    </div>

                    <div className={this.panelClass("annotations")}>
                        <RoomAnnotationsPanel room_id={room_id} />
                    </div>

                    <div className={this.panelClass("video")}>
                        <RoomVideosPanel room_id={room_id} />
                    </div>

                    <div className={this.panelClass("management")}>
                        <RoomManagementPanel
                            room_id={room_id}
                            room_name={room_name}
                            room_purpose={room_purpose}
                        />
                    </div>
                </div>
            </div>
        );
    }
}
