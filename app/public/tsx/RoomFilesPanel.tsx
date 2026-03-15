import { h, Component } from "preact";
import { humanFileSize, formatDateTimeForContent, spacesToNbsp } from "./functions";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { PdfSelectionType } from "./constants";
import { api, GetRoomsFilesResponse } from "./generated/api_routes";
import { RoomFileWithTags, createRoomFileWithTags, createRoomTag, RoomTag } from "./generated/types";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setFileTags } from "./api_room_entity_tags";

export interface RoomFilesPanelProps {
    room_id: string;
}

interface RoomFilesPanelState {
    files: RoomFileWithTags[];
    error: string | null;
    logged_in: boolean;
    editingFileId: string | null;
    roomTags: RoomTag[];
    selectedTagIds: Set<string>;
    tagsSaveInProgress: boolean;
}

function getDefaultState(): RoomFilesPanelState {
    return {
        files: [],
        error: null,
        logged_in: get_logged_in(),
        editingFileId: null,
        roomTags: [],
        selectedTagIds: new Set(),
        tagsSaveInProgress: false,
    };
}

export class RoomFilesPanel extends Component<RoomFilesPanelProps, RoomFilesPanelState> {

    message_listener: number|null;
    unsubscribe_logged_in: (() => void)|null = null;

    constructor(props: RoomFilesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshFiles();
        this.message_listener = registerMessageListener(PdfSelectionType.ROOM_FILES_CHANGED, () => this.refreshFiles(true));
        
        // Subscribe to login state changes to re-render when login status changes
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
        this.message_listener = null;
        
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
    }

    refreshFiles(cacheBust?: boolean) {
        api.rooms.files(this.props.room_id, cacheBust ? { cacheBust: true } : undefined).
        then((data:GetRoomsFilesResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data: GetRoomsFilesResponse) {
        if (data.data.files === undefined) {
            this.setState({ error: "Server response did not contains 'files'." });
            return;
        }
        const files: RoomFileWithTags[] = data.data.files.map((file) =>
            createRoomFileWithTags(file)
        );
        this.setState({ files });
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    shareFile(file: RoomFileWithTags, file_url: string) {
        // Build the full URL including the origin
        const full_url = window.location.origin + file_url;
        const markdown_link = `[${file.original_filename}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, {text: markdown_link});
    }

    openEditTags(file: RoomFileWithTags) {
        const selectedTagIds = new Set(file.tags.map((t) => t.tag_id));
        this.setState({ editingFileId: file.id, selectedTagIds });
        api.rooms.tags(this.props.room_id)
            .then((data) => {
                const roomTags = data.data.tags.map((t) => createRoomTag(t));
                this.setState({ roomTags });
            })
            .catch(() => this.setState({ roomTags: [] }));
    }

    closeEditTags() {
        this.setState({ editingFileId: null, roomTags: [], selectedTagIds: new Set() });
    }

    toggleTagForEdit(tag_id: string) {
        const next = new Set(this.state.selectedTagIds);
        if (next.has(tag_id)) next.delete(tag_id);
        else next.add(tag_id);
        this.setState({ selectedTagIds: next });
    }

    saveFileTags() {
        const { editingFileId, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingFileId || tagsSaveInProgress) return;
        this.setState({ tagsSaveInProgress: true });
        setFileTags(this.props.room_id, editingFileId, { tag_ids: Array.from(selectedTagIds) })
            .then(() => {
                this.closeEditTags();
                this.setState({ tagsSaveInProgress: false });
                this.refreshFiles(true);
            })
            .catch(() => this.setState({ tagsSaveInProgress: false }));
    }

    renderRoomFile(file: RoomFileWithTags, logged_in: boolean) {
        const file_url = `/rooms/${this.props.room_id}/file/${file.id}/${file.original_filename}`;
        const annotate_url = `/rooms/${this.props.room_id}/file_annotate/${file.id}`;
        let annotate_block: preact.VNode = <a href={annotate_url}>Annotate</a>;
        if (!file_url.toLowerCase().endsWith(".pdf")) {
            annotate_block = <span></span>;
        }
        const tagsBlock = file.tags.length > 0
            ? <span className="room_entity_tags">{file.tags.map((t) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : <span className="room_entity_tags empty">—</span>;

        const dateDisplay = file.document_timestamp != null
            ? spacesToNbsp(formatDateTimeForContent(file.document_timestamp))
            : "—";

        return (
            <tr key={file.id}>
                <td>
                    <a href={file_url} target="_blank">{file.original_filename}</a>
                </td>
                <td>{spacesToNbsp(humanFileSize(file.size, true))}</td>
                <td>{spacesToNbsp(formatDateTimeForContent(file.created_at))}</td>
                <td>{dateDisplay}</td>
                <td>{tagsBlock}</td>
                <td>{annotate_block}</td>
                {logged_in && (
                    <td>
                        <button className="button_standard button_chat" onClick={() => this.openEditTags(file)}>Edit tags</button>
                        <button className="button_standard button_chat" onClick={() => this.shareFile(file, file_url)} title="Share file link to chat">Post&nbsp;to&nbsp;chat</button>
                    </td>
                )}
            </tr>
        );
    }

    renderFiles() {
        if (this.state.files.length === 0) {
            return <span>No files.</span>;
        }
        const logged_in = this.state.logged_in;
        return (
            <span>
                <span className="section-heading">Files</span>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Added</th>
                            <th>Date</th>
                            <th>Tags</th>
                            <th />
                            {logged_in && <th />}
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.files.map((roomFile) => this.renderRoomFile(roomFile, logged_in))}
                    </tbody>
                </table>
            </span>
        );
    }

    renderEditTagsModal() {
        const { editingFileId, roomTags, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingFileId) return null;
        return (
            <div className="room_edit_tags_modal_overlay" onClick={() => !tagsSaveInProgress && this.closeEditTags()}>
                <div className="room_edit_tags_modal" onClick={(e) => e.stopPropagation()}>
                    <h3>Edit tags</h3>
                    {roomTags.length === 0 ? (
                        <p>Loading room tags…</p>
                    ) : (
                        <div className="room_edit_tags_checkboxes">
                            {roomTags.map((tag) => (
                                <label key={tag.tag_id}>
                                    <input
                                        type="checkbox"
                                        checked={selectedTagIds.has(tag.tag_id)}
                                        onChange={() => this.toggleTagForEdit(tag.tag_id)}
                                    />
                                    {tag.text}
                                </label>
                            ))}
                        </div>
                    )}
                    <div className="room_edit_tags_actions">
                        <button className="button_standard" onClick={() => this.saveFileTags()} disabled={tagsSaveInProgress}>Save</button>
                        <button className="button_standard" onClick={() => this.closeEditTags()} disabled={tagsSaveInProgress}>Cancel</button>
                    </div>
                </div>
            </div>
        );
    }

    render(_props: RoomFilesPanelProps, _state: RoomFilesPanelState) {
        const error_block = this.state.error != null
            ? <div className="error">Last error: {this.state.error}</div>
            : <span>&nbsp;</span>;
        const length = this.state.files.length;
        return (
            <div className="room_files_panel_react">
                {error_block}
                {this.renderFiles()}
                <div>There are {length} files</div>
                <button className="button_standard" onClick={() => this.refreshFiles()}>Refresh</button>
                {this.renderEditTagsModal()}
            </div>
        );
    }
}