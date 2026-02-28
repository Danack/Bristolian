import { h, Component } from "preact";
import { RoomTag, createRoomTag } from "./generated/types";
import { api, GetRoomsTagsResponse } from "./generated/api_routes";
import { MAX_TAGS_PER_ROOM } from "./generated/constants";

export interface RoomManagementPanelProps {
    room_id: string;
}

interface RoomManagementPanelState {
    tags: RoomTag[];
    error: string | null;
    addText: string;
    addDescription: string;
    addInProgress: boolean;
}

function getDefaultState(): RoomManagementPanelState {
    return {
        tags: [],
        error: null,
        addText: "",
        addDescription: "",
        addInProgress: false,
    };
}

export class RoomManagementPanel extends Component<RoomManagementPanelProps, RoomManagementPanelState> {
    constructor(props: RoomManagementPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshTags();
    }

    refreshTags() {
        api.rooms.tags(this.props.room_id)
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
            <div class="error">Last error: {state.error}</div>
        ) : (
            <span>&nbsp;</span>
        );

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
                                    {tag.created_at instanceof Date
                                        ? tag.created_at.toLocaleString()
                                        : String(tag.created_at)}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            );

        return (
            <div class="room_management_panel_react">
                <h2>Room Management — Tags</h2>
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
