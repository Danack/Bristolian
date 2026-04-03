import { h, Component } from "preact";
import { RoomTag, createRoomTag, createRoom } from "./generated/types";
import { api, GetRoomsDetailsResponse, GetRoomsTagsResponse } from "./generated/api_routes";
import { MAX_TAGS_PER_ROOM } from "./generated/constants";
import { formatDateTimeForContent, spacesToNbsp } from "./functions";
import { patchRoomDetails } from "./api_room_entity_tags";

export interface RoomManagementPanelProps {
    room_id: string;
    room_name?: string;
    room_purpose?: string;
}

interface RoomManagementPanelState {
    tags: RoomTag[];
    error: string | null;
    addText: string;
    addDescription: string;
    addInProgress: boolean;
    roomName: string;
    roomPurpose: string;
    roomDetailsError: string | null;
    roomDetailsLoaded: boolean;
    saveRoomDetailsInProgress: boolean;
    saveRoomDetailsError: string | null;
}

function getDefaultState(props: RoomManagementPanelProps): RoomManagementPanelState {
    return {
        tags: [],
        error: null,
        addText: "",
        addDescription: "",
        addInProgress: false,
        roomName: props.room_name ?? "",
        roomPurpose: props.room_purpose ?? "",
        roomDetailsError: null,
        roomDetailsLoaded: false,
        saveRoomDetailsInProgress: false,
        saveRoomDetailsError: null,
    };
}

function syncRoomPageHeader(roomName: string, roomPurpose: string): void {
    const root = document.querySelector(".roompage");
    if (root === null) {
        return;
    }
    const heading = root.querySelector(":scope > h1");
    const paragraph = root.querySelector(":scope > p");
    if (heading !== null) {
        heading.textContent = roomName;
    }
    if (paragraph !== null) {
        paragraph.textContent = roomPurpose;
    }
}

export class RoomManagementPanel extends Component<RoomManagementPanelProps, RoomManagementPanelState> {
    constructor(props: RoomManagementPanelProps) {
        super(props);
        this.state = getDefaultState(props);
    }

    componentDidMount() {
        this.refreshTags();
        this.refreshRoomDetails();
    }

    refreshRoomDetails() {
        api.rooms
            .details(this.props.room_id)
            .then((data: GetRoomsDetailsResponse) => this.processRoomDetails(data))
            .catch(() => {
                this.setState({
                    roomDetailsError: "Failed to load room details.",
                    roomDetailsLoaded: true,
                });
            });
    }

    processRoomDetails(data: GetRoomsDetailsResponse) {
        if (data.data.room === undefined) {
            this.setState({
                roomDetailsError: "Server response did not contain 'room'.",
                roomDetailsLoaded: true,
            });
            return;
        }
        const room = createRoom(data.data.room);
        this.setState({
            roomName: room.name,
            roomPurpose: room.purpose,
            roomDetailsError: null,
            roomDetailsLoaded: true,
        });
    }

    refreshTags() {
        api.rooms
            .tags(this.props.room_id)
            .then((data: GetRoomsTagsResponse) => this.processData(data))
            .catch((data: unknown) => this.processError(data));
    }

    processData(data: GetRoomsTagsResponse) {
        if (data.data.tags === undefined) {
            this.setState({ error: "Server response did not contain 'tags'." });
            return;
        }
        const tags = data.data.tags.map((t) => createRoomTag(t));
        this.setState({ tags, error: null });
    }

    processError(_data: unknown) {
        this.setState({ error: "Failed to load tags." });
    }

    saveRoomDetails() {
        const trimmedName = this.state.roomName.trim();
        if (!trimmedName || this.state.saveRoomDetailsInProgress) {
            return;
        }
        const trimmedPurpose = this.state.roomPurpose.trim();
        this.setState({ saveRoomDetailsInProgress: true, saveRoomDetailsError: null });
        patchRoomDetails(this.props.room_id, { name: trimmedName, purpose: trimmedPurpose })
            .then(() => {
                this.setState({
                    roomName: trimmedName,
                    roomPurpose: trimmedPurpose,
                    saveRoomDetailsInProgress: false,
                    saveRoomDetailsError: null,
                });
                syncRoomPageHeader(trimmedName, trimmedPurpose);
            })
            .catch((error: unknown) => {
                const message = error instanceof Error ? error.message : "Failed to save room details.";
                this.setState({ saveRoomDetailsInProgress: false, saveRoomDetailsError: message });
            });
    }

    addTag() {
        const { addText, addDescription, addInProgress } = this.state;
        if (!addText.trim() || addInProgress) {
            return;
        }
        this.setState({ addInProgress: true });
        const endpoint = `/api/rooms/${this.props.room_id}/tags`;
        const formData = new FormData();
        formData.append("text", addText.trim());
        formData.append("description", addDescription.trim());

        fetch(endpoint, { method: "POST", body: formData })
            .then((response: Response) => {
                if (response.status !== 200 && response.status !== 400) {
                    throw new Error("Server failed to return an expected response.");
                }
                return response.json();
            })
            .then((data: { result?: string; message?: string }) => {
                if (data.result === "success") {
                    this.setState({ addText: "", addDescription: "", addInProgress: false });
                    this.refreshTags();
                } else {
                    this.setState({
                        addInProgress: false,
                        error: data.message ?? "Failed to add tag.",
                    });
                }
            })
            .catch(() => {
                this.setState({ addInProgress: false, error: "Failed to add tag." });
            });
    }

    render(_props: RoomManagementPanelProps, state: RoomManagementPanelState) {
        const errorBlock = state.error ? (
            <div class="error">Last error (tags): {state.error}</div>
        ) : (
            <span>&nbsp;</span>
        );

        const roomDetailsErrorBlock = state.roomDetailsError ? (
            <div class="error">Room details: {state.roomDetailsError}</div>
        ) : null;

        const saveRoomDetailsErrorBlock = state.saveRoomDetailsError ? (
            <div class="error">Save room details: {state.saveRoomDetailsError}</div>
        ) : null;

        const tagsList =
            state.tags.length === 0 ? (
                <p>No tags for this room yet.</p>
            ) : (
                <table>
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Description</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        {state.tags.map((tag) => (
                            <tr key={tag.tag_id}>
                                <td>{tag.text}</td>
                                <td>{tag.description}</td>
                                <td>
                                    {spacesToNbsp(
                                        formatDateTimeForContent(
                                            tag.created_at instanceof Date
                                                ? tag.created_at
                                                : new Date(String(tag.created_at))
                                        )
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            );

        const canSaveRoomDetails =
            state.roomDetailsLoaded && state.roomName.trim().length > 0 && !state.saveRoomDetailsInProgress;

        return (
            <div class="room_management_panel_react">
                <h2>Room Management</h2>
                <section>
                    <h3>Room name and description</h3>
                    {roomDetailsErrorBlock}
                    {saveRoomDetailsErrorBlock}
                    <table className="room_management_room_details_form">
                        <tbody>
                            <tr>
                                <td>
                                    <label htmlFor="room_management_room_name">Name:</label>
                                </td>
                                <td>
                                    <input
                                        id="room_management_room_name"
                                        type="text"
                                        maxLength={36}
                                        value={state.roomName}
                                        onInput={(e) =>
                                            this.setState({ roomName: (e.target as HTMLInputElement).value })
                                        }
                                        placeholder="Room name"
                                        disabled={!state.roomDetailsLoaded}
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="room_management_room_purpose">Description:</label>
                                </td>
                                <td>
                                    <textarea
                                        id="room_management_room_purpose"
                                        rows={4}
                                        cols={80}
                                        value={state.roomPurpose}
                                        onInput={(e) =>
                                            this.setState({
                                                roomPurpose: (e.target as HTMLTextAreaElement).value,
                                            })
                                        }
                                        placeholder="What this room is for"
                                        disabled={!state.roomDetailsLoaded}
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td />
                                <td style={{ textAlign: "right" }}>
                                    <button
                                        class="button_standard"
                                        onClick={() => this.saveRoomDetails()}
                                        disabled={!canSaveRoomDetails}
                                    >
                                        {state.saveRoomDetailsInProgress ? "Saving…" : "Save room details"}
                                    </button>
                                    <button
                                        class="button_standard"
                                        onClick={() => this.refreshRoomDetails()}
                                        disabled={state.saveRoomDetailsInProgress}
                                        style={{ marginLeft: "0.5em" }}
                                    >
                                        Reload
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <h2>Tags</h2>
                {errorBlock}
                <section>
                    <h3>Add tag</h3>
                    <table className="room_management_add_tag_form">
                        <tbody>
                            <tr>
                                <td>
                                    <label htmlFor="room_management_tag_text">Text:</label>
                                </td>
                                <td>
                                    <input
                                        id="room_management_tag_text"
                                        type="text"
                                        value={state.addText}
                                        onInput={(e) => this.setState({ addText: (e.target as HTMLInputElement).value })}
                                        placeholder="Tag text"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="room_management_tag_description">Description:</label>
                                </td>
                                <td>
                                    <textarea
                                        id="room_management_tag_description"
                                        rows={4}
                                        cols={80}
                                        value={state.addDescription}
                                        onInput={(e) =>
                                            this.setState({ addDescription: (e.target as HTMLTextAreaElement).value })
                                        }
                                        placeholder="Optional description"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td />
                                <td style={{ textAlign: "right" }}>
                                    <button
                                        class="button_standard"
                                        onClick={() => this.addTag()}
                                        disabled={
                                            !state.addText.trim() ||
                                            state.addInProgress ||
                                            state.tags.length >= MAX_TAGS_PER_ROOM
                                        }
                                    >
                                        {state.addInProgress ? "Adding…" : "Add tag"}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h3>Tags in this room</h3>
                    {tagsList}
                    <button class="button_standard" onClick={() => this.refreshTags()}>
                        Refresh
                    </button>
                </section>
            </div>
        );
    }
}
