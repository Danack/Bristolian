import { h, Component } from "preact";
import { humanFileSize, formatDateTimeForContent, spacesToNbsp } from "./functions";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { PdfSelectionType } from "./constants";
import { api, GetRoomsFilesResponse } from "./generated/api_routes";
import { RoomFileWithTags, createRoomFileWithTags, createRoomTag, RoomTag } from "./generated/types";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setFileTags } from "./api_room_entity_tags";
import { fetchRoomFiles, RoomContentSearchParams } from "./api_room_content_list";
import { ROOM_CONTENT_LIST_DEFAULT_LIMIT } from "./generated/constants";
import { RoomContentSearchForm } from "./RoomContentSearchForm";

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
    searchTitle: string;
    searchDescription: string;
    searchCreatedAfter: string;
    searchCreatedBefore: string;
    searchDocAfter: string;
    searchDocBefore: string;
    searchTagIds: Set<string>;
    searchLimit: number;
    searchWaiting: boolean;
    searchInFlight: boolean;
    searchVisible: boolean;
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
        searchTitle: "",
        searchDescription: "",
        searchCreatedAfter: "",
        searchCreatedBefore: "",
        searchDocAfter: "",
        searchDocBefore: "",
        searchTagIds: new Set(),
        searchLimit: ROOM_CONTENT_LIST_DEFAULT_LIMIT,
        searchWaiting: false,
        searchInFlight: false,
        searchVisible: false,
    };
}

export class RoomFilesPanel extends Component<RoomFilesPanelProps, RoomFilesPanelState> {

    message_listener: number|null;
    unsubscribe_logged_in: (() => void)|null = null;
    private searchTimeout: number | null = null;

    constructor(props: RoomFilesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshFiles();
        this.loadRoomTags();
        this.message_listener = registerMessageListener(PdfSelectionType.ROOM_FILES_CHANGED, () => this.refreshFiles());
        
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    loadRoomTags() {
        api.rooms.tags(this.props.room_id)
            .then((data) => {
                const roomTags = data.data.tags.map((t) => createRoomTag(t));
                this.setState({ roomTags });
            })
            .catch(() => this.setState({ roomTags: [] }));
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
        this.message_listener = null;
        
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }

        if (this.searchTimeout !== null) {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = null;
        }
    }

    buildSearchParams(): RoomContentSearchParams {
        const s = this.state;
        return {
            limit: s.searchLimit,
            title: s.searchTitle.trim() || undefined,
            description: s.searchDescription.trim() || undefined,
            created_at_after: s.searchCreatedAfter.trim() || undefined,
            created_at_before: s.searchCreatedBefore.trim() || undefined,
            document_timestamp_after: s.searchDocAfter.trim() || undefined,
            document_timestamp_before: s.searchDocBefore.trim() || undefined,
            tag_ids: s.searchTagIds.size > 0 ? Array.from(s.searchTagIds) : undefined,
        };
    }

    refreshFiles() {
        this.setState({ searchInFlight: true, searchWaiting: false });
        fetchRoomFiles(this.props.room_id, this.buildSearchParams())
            .then((data: GetRoomsFilesResponse) => this.processData(data))
            .catch((data: unknown) => this.processError(data));
    }

    scheduleSearch = () => {
        if (this.searchTimeout !== null) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = window.setTimeout(() => {
            this.refreshFiles();
        }, 250);
    };

    onClearSearch = () => {
        this.setState({
            searchTitle: "",
            searchCreatedAfter: "",
            searchCreatedBefore: "",
            searchDocAfter: "",
            searchDocBefore: "",
            searchTagIds: new Set(),
            searchLimit: ROOM_CONTENT_LIST_DEFAULT_LIMIT,
            searchWaiting: false,
        }, () => this.refreshFiles());
    };

    toggleSearchTag(tagId: string) {
        const next = new Set(this.state.searchTagIds);
        if (next.has(tagId)) next.delete(tagId);
        else next.add(tagId);
        this.setState({ searchTagIds: next, searchWaiting: true, searchInFlight: false }, () => this.scheduleSearch());
    }

    processData(data: GetRoomsFilesResponse) {
        if (data.data.files === undefined) {
            this.setState({ error: "Server response did not contains 'files'.", searchInFlight: false });
            return;
        }
        const files: RoomFileWithTags[] = data.data.files.map((file) =>
            createRoomFileWithTags(file)
        );
        this.setState({ files, searchInFlight: false });
    }

    processError(data: unknown) {
        this.setState({ error: data instanceof Error ? data.message : "Request failed.", searchInFlight: false });
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
                this.refreshFiles();
            })
            .catch(() => this.setState({ tagsSaveInProgress: false }));
    }

    renderRoomFile(file: RoomFileWithTags, logged_in: boolean) {
        const file_url = `/rooms/${this.props.room_id}/file/${file.id}/${file.original_filename}`;
        const annotate_url = `/rooms/${this.props.room_id}/file_annotate/${file.id}`;
        const is_pdf = file_url.toLowerCase().endsWith(".pdf");
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
                {logged_in && (
                    <td>
                        {is_pdf ? <a href={annotate_url}>Annotate</a> : <span></span>}
                    </td>
                )}
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
                <table className="large_table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Added</th>
                            <th>Date</th>
                            <th>Tags</th>
                            {logged_in && <th />}
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
        let body;
        if (this.state.searchWaiting) {
            body = <div>Waiting....</div>;
        }
        else if (this.state.searchInFlight) {
            body = <div>Searching....</div>;
        }
        else {
            body = this.renderFiles();
        }
        return (
            <div className="room_files_panel_react">
                {error_block}
                {this.state.searchVisible ? (
                    <div className="room_content_search_container">
                        <button
                            type="button"
                            className="button_standard room_content_search_close"
                            onClick={() => this.setState({ searchVisible: false })}
                        >
                            <img src="/svg/close-icon.svg" alt="Hide search" width={16} height={16} />
                        </button>
                        <RoomContentSearchForm
                            title={this.state.searchTitle}
                            description={this.state.searchDescription}
                            createdAfter={this.state.searchCreatedAfter}
                            createdBefore={this.state.searchCreatedBefore}
                            documentAfter={this.state.searchDocAfter}
                            documentBefore={this.state.searchDocBefore}
                            limit={this.state.searchLimit}
                            roomTags={this.state.roomTags}
                            selectedTagIds={this.state.searchTagIds}
                            titlePlaceholder="Filter by name"
                            onTitleChange={(value: string) =>
                                this.setState(
                                    { searchTitle: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onDescriptionChange={(value: string) =>
                                this.setState(
                                    { searchDescription: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onCreatedAfterChange={(value: string) =>
                                this.setState(
                                    { searchCreatedAfter: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onCreatedBeforeChange={(value: string) =>
                                this.setState(
                                    { searchCreatedBefore: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onDocumentAfterChange={(value: string) =>
                                this.setState(
                                    { searchDocAfter: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onDocumentBeforeChange={(value: string) =>
                                this.setState(
                                    { searchDocBefore: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onLimitChange={(value: number) =>
                                this.setState(
                                    { searchLimit: value, searchWaiting: true, searchInFlight: false },
                                    () => this.scheduleSearch()
                                )
                            }
                            onToggleTag={(tagId: string) => this.toggleSearchTag(tagId)}
                            onClear={this.onClearSearch}
                        />
                    </div>
                ) : (
                    <button
                        type="button"
                        className="button_standard room_content_search_toggle"
                        onClick={() => this.setState({ searchVisible: true })}
                    >
                        <img src="/svg/search-button-icon.svg" alt="Show search" width={16} height={16} />
                    </button>
                )}
                {body}
                <div>Showing {length} files</div>
                <button className="button_standard" onClick={() => this.refreshFiles()}>Refresh</button>
                {this.renderEditTagsModal()}
            </div>
        );
    }
}