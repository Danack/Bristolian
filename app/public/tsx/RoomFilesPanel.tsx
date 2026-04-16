import { h, Component } from "preact";
import { humanFileSize, formatDateTimeForContent, spacesToNbsp } from "./functions";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { PdfSelectionType } from "./constants";
import { api, GetRoomsFilesResponse } from "./generated/api_routes";
import { RoomFileWithTags, createRoomFileWithTags, createRoomTag, RoomTag } from "./generated/types";
import { get_logged_in, subscribe_logged_in } from "./store";
import { patchRoomFile, setFileTags } from "./api_room_entity_tags";
import { fetchRoomFiles, RoomContentSearchParams } from "./api_room_content_list";
import { ROOM_CONTENT_LIST_DEFAULT_LIMIT } from "./generated/constants";
import { RoomContentSearchForm, RoomFilesListSortColumn } from "./RoomContentSearchForm";
import { setRoomFileEditingActive } from "./room_file_editing";

export interface RoomFilesPanelProps {
    room_id: string;
}

function pad2(value: number): string {
    return String(value).padStart(2, "0");
}

/** Value for HTML datetime-local from a Date (local wall time). */
function orderQueryFromSort(
    column: RoomFilesListSortColumn | null,
    direction: "asc" | "desc" | null,
): string | undefined {
    if (column === null || direction === null) {
        return undefined;
    }
    const prefix = direction === "asc" ? "+" : "-";
    return prefix + column;
}

function dateToDatetimeLocalValue(value: Date | null): string {
    if (value === null) {
        return "";
    }
    return (
        `${value.getFullYear()}-${pad2(value.getMonth() + 1)}-${pad2(value.getDate())}` +
        `T${pad2(value.getHours())}:${pad2(value.getMinutes())}`
    );
}

interface RoomFilesPanelState {
    files: RoomFileWithTags[];
    error: string | null;
    logged_in: boolean;
    fileBeingEdited: RoomFileWithTags | null;
    fileSaveInProgress: boolean;
    fileEditError: string | null;
    fileEditorTagsLoading: boolean;
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
    searchSortColumn: RoomFilesListSortColumn | null;
    searchSortDirection: "asc" | "desc" | null;
}

function getDefaultState(): RoomFilesPanelState {
    return {
        files: [],
        error: null,
        logged_in: get_logged_in(),
        fileBeingEdited: null,
        fileSaveInProgress: false,
        fileEditError: null,
        fileEditorTagsLoading: false,
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
        searchSortColumn: null,
        searchSortDirection: null,
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
        if (this.state.fileBeingEdited !== null) {
            setRoomFileEditingActive(false);
            sendMessage(PdfSelectionType.ROOM_FILE_EDITING_ACTIVE, { active: false });
        }
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
            order: orderQueryFromSort(s.searchSortColumn, s.searchSortDirection),
        };
    }

    /**
     * @param cacheBust - use after tag mutations so GET /files is not served from browser cache.
     */
    refreshFiles(cacheBust = false) {
        this.setState({ searchInFlight: true, searchWaiting: false });
        fetchRoomFiles(this.props.room_id, this.buildSearchParams(), cacheBust ? { cacheBust: true } : undefined)
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
            searchDescription: "",
            searchCreatedAfter: "",
            searchCreatedBefore: "",
            searchDocAfter: "",
            searchDocBefore: "",
            searchTagIds: new Set(),
            searchLimit: ROOM_CONTENT_LIST_DEFAULT_LIMIT,
            searchWaiting: false,
            searchSortColumn: null,
            searchSortDirection: null,
        }, () => this.refreshFiles());
    };

    toggleSearchTag(tagId: string) {
        const next = new Set(this.state.searchTagIds);
        if (next.has(tagId)) next.delete(tagId);
        else next.add(tagId);
        this.setState({ searchTagIds: next, searchWaiting: true, searchInFlight: false }, () => this.scheduleSearch());
    }

    cycleSortColumn(column: RoomFilesListSortColumn) {
        this.setState(
            (previousState) => {
                if (previousState.searchSortColumn !== column) {
                    return {
                        searchSortColumn: column,
                        searchSortDirection: "asc" as const,
                    };
                }
                if (previousState.searchSortDirection === "asc") {
                    return { searchSortDirection: "desc" as const };
                }
                return {
                    searchSortColumn: null,
                    searchSortDirection: null,
                };
            },
            () => this.refreshFiles(),
        );
    }

    sortAriaSort(column: RoomFilesListSortColumn): "none" | "ascending" | "descending" {
        if (this.state.searchSortColumn !== column) {
            return "none";
        }
        if (this.state.searchSortDirection === "asc") {
            return "ascending";
        }
        if (this.state.searchSortDirection === "desc") {
            return "descending";
        }
        return "none";
    }

    renderSortIndicator(column: RoomFilesListSortColumn) {
        if (this.state.searchSortColumn !== column) {
            return null;
        }
        if (this.state.searchSortDirection === "asc") {
            return (
                <span className="room_files_sort_indicator" title="Ascending" aria-hidden>
                    ↑
                </span>
            );
        }
        if (this.state.searchSortDirection === "desc") {
            return (
                <span className="room_files_sort_indicator" title="Descending" aria-hidden>
                    ↓
                </span>
            );
        }
        return null;
    }

    renderSortableColumnHeader(column: RoomFilesListSortColumn, label: string) {
        return (
            <th className="room_files_sortable_header" aria-sort={this.sortAriaSort(column)} scope="col">
                <button
                    type="button"
                    className="room_files_sort_header_button"
                    title="Sort: ascending, then descending, then default order"
                    onClick={() => this.cycleSortColumn(column)}
                >
                    {label}
                    {this.renderSortIndicator(column)}
                </button>
            </th>
        );
    }

    processData(data: GetRoomsFilesResponse) {
        if (data.data.files === undefined) {
            this.setState({ error: "Server response did not contains 'files'.", searchInFlight: false });
            return;
        }
        const files: RoomFileWithTags[] = data.data.files.map((file) =>
            createRoomFileWithTags(file)
        );
        this.setState((previousState) => {
            let nextFileBeingEdited = previousState.fileBeingEdited;
            if (nextFileBeingEdited !== null) {
                const updated = files.find((f) => f.id === nextFileBeingEdited.id);
                if (updated !== undefined) {
                    nextFileBeingEdited = updated;
                }
            }
            let nextSelectedTagIds = previousState.selectedTagIds;
            if (nextFileBeingEdited !== null) {
                nextSelectedTagIds = new Set(nextFileBeingEdited.tags.map((t) => t.tag_id));
            }
            return {
                files,
                searchInFlight: false,
                fileBeingEdited: nextFileBeingEdited,
                selectedTagIds: nextSelectedTagIds,
            };
        });
    }

    processError(data: unknown) {
        this.setState({ error: data instanceof Error ? data.message : "Request failed.", searchInFlight: false });
    }

    shareFile(file: RoomFileWithTags, file_url: string) {
        const full_url = window.location.origin + file_url;
        const markdown_link = `[${file.original_filename}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, {text: markdown_link});
    }

    startEditingFile(file: RoomFileWithTags) {
        setRoomFileEditingActive(true);
        sendMessage(PdfSelectionType.ROOM_FILE_EDITING_ACTIVE, { active: true });
        const selectedTagIds = new Set(file.tags.map((t) => t.tag_id));
        const needsRoomTagList = this.state.roomTags.length === 0;
        this.setState({
            fileBeingEdited: file,
            fileEditError: null,
            fileSaveInProgress: false,
            selectedTagIds,
            fileEditorTagsLoading: needsRoomTagList,
        });
        if (needsRoomTagList) {
            api.rooms.tags(this.props.room_id)
                .then((data) => {
                    const roomTags = data.data.tags.map((t) => createRoomTag(t));
                    this.setState({ roomTags });
                })
                .catch(() => this.setState({ roomTags: [] }))
                .finally(() => this.setState({ fileEditorTagsLoading: false }));
        }
    }

    cancelEditingFile() {
        setRoomFileEditingActive(false);
        sendMessage(PdfSelectionType.ROOM_FILE_EDITING_ACTIVE, { active: false });
        this.setState({
            fileBeingEdited: null,
            fileEditError: null,
            fileSaveInProgress: false,
            selectedTagIds: new Set(),
            fileEditorTagsLoading: false,
        });
    }

    persistFileTagToggle(tag_id: string) {
        const { fileBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!fileBeingEdited || tagsSaveInProgress) {
            return;
        }
        const previous = new Set(selectedTagIds);
        const next = new Set(selectedTagIds);
        if (next.has(tag_id)) {
            next.delete(tag_id);
        } else {
            next.add(tag_id);
        }
        this.setState({ selectedTagIds: next, tagsSaveInProgress: true });
        setFileTags(this.props.room_id, fileBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshFiles(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    removeSelectedFileTag(tag_id: string) {
        const { fileBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!fileBeingEdited || tagsSaveInProgress) {
            return;
        }
        const previous = new Set(selectedTagIds);
        const next = new Set(selectedTagIds);
        next.delete(tag_id);
        this.setState({ selectedTagIds: next, tagsSaveInProgress: true });
        setFileTags(this.props.room_id, fileBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshFiles(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    saveEditedFile() {
        const { fileBeingEdited, fileSaveInProgress } = this.state;
        if (!fileBeingEdited || fileSaveInProgress) {
            return;
        }

        this.setState({ fileSaveInProgress: true, fileEditError: null });

        const documentTimestampLocal = dateToDatetimeLocalValue(fileBeingEdited.document_timestamp);
        patchRoomFile(this.props.room_id, fileBeingEdited.id, {
            description: fileBeingEdited.description,
            note: fileBeingEdited.note,
            document_timestamp: documentTimestampLocal,
        })
            .then(() => {
                setRoomFileEditingActive(false);
                sendMessage(PdfSelectionType.ROOM_FILE_EDITING_ACTIVE, { active: false });
                this.setState(
                    {
                        fileBeingEdited: null,
                        fileSaveInProgress: false,
                        selectedTagIds: new Set(),
                        fileEditorTagsLoading: false,
                    },
                    () => this.refreshFiles()
                );
            })
            .catch((error: Error) => {
                this.setState({ fileSaveInProgress: false, fileEditError: error.message });
            });
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
                        <button type="button" className="button_standard button_chat" onClick={() => this.startEditingFile(file)}>Edit</button>
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
                <table className="large_table room_files_table">
                    <thead>
                        <tr>
                            {this.renderSortableColumnHeader("name", "Name")}
                            {this.renderSortableColumnHeader("size", "Size")}
                            {this.renderSortableColumnHeader("added", "Added")}
                            {this.renderSortableColumnHeader("document_date", "Date")}
                            <th scope="col">Tags</th>
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

    renderFileBeingEdited() {
        const {
            fileBeingEdited,
            fileSaveInProgress,
            fileEditError,
            roomTags,
            selectedTagIds,
            tagsSaveInProgress,
            fileEditorTagsLoading,
        } = this.state;
        if (fileBeingEdited === null) {
            return <span></span>;
        }

        const selectedTagsForDisplay = roomTags.filter((tag) => selectedTagIds.has(tag.tag_id));

        return (
            <div className="room_links_add_panel_react">
                <h3>Edit file</h3>
                <p className="room_file_edit_filename">
                    <strong>{fileBeingEdited.original_filename}</strong>
                </p>
                <div className="annotation_edit_title_text_form">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <label htmlFor="room_file_edit_description">Description</label>
                                </td>
                                <td>
                                    <input
                                        id="room_file_edit_description"
                                        name="description"
                                        type="text"
                                        size={80}
                                        value={fileBeingEdited.description ?? ""}
                                        disabled={fileSaveInProgress}
                                        onInput={(event) => {
                                            const value = (event.currentTarget as HTMLInputElement).value;
                                            this.setState({
                                                fileBeingEdited: {
                                                    ...fileBeingEdited,
                                                    description: value,
                                                },
                                                fileEditError: null,
                                            });
                                        }}
                                    />
                                    <span className="room_file_edit_hint">Short label shown in lists instead of the raw file name.</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="room_file_edit_note">Note</label>
                                </td>
                                <td>
                                    <textarea
                                        id="room_file_edit_note"
                                        name="note"
                                        rows={6}
                                        cols={80}
                                        value={fileBeingEdited.note ?? ""}
                                        disabled={fileSaveInProgress}
                                        onInput={(event) => {
                                            const value = (event.currentTarget as HTMLTextAreaElement).value;
                                            this.setState({
                                                fileBeingEdited: {
                                                    ...fileBeingEdited,
                                                    note: value,
                                                },
                                                fileEditError: null,
                                            });
                                        }}
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="room_file_edit_document_timestamp">Date</label>
                                </td>
                                <td>
                                    <input
                                        id="room_file_edit_document_timestamp"
                                        name="document_timestamp"
                                        type="datetime-local"
                                        value={dateToDatetimeLocalValue(fileBeingEdited.document_timestamp)}
                                        disabled={fileSaveInProgress}
                                        onChange={(event) => {
                                            const raw = (event.currentTarget as HTMLInputElement).value;
                                            if (raw === "") {
                                                this.setState({
                                                    fileBeingEdited: {
                                                        ...fileBeingEdited,
                                                        document_timestamp: null,
                                                    },
                                                    fileEditError: null,
                                                });
                                                return;
                                            }
                                            const parsed = new Date(raw);
                                            this.setState({
                                                fileBeingEdited: {
                                                    ...fileBeingEdited,
                                                    document_timestamp: Number.isNaN(parsed.getTime()) ? null : parsed,
                                                },
                                                fileEditError: null,
                                            });
                                        }}
                                    />
                                    <span className="room_file_edit_hint">Document or publication date (optional).</span>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button
                                        type="button"
                                        className="button_standard"
                                        disabled={fileSaveInProgress}
                                        onClick={() => this.saveEditedFile()}
                                    >
                                        Save
                                    </button>
                                    {fileEditError ? <span className="error">{fileEditError}</span> : null}
                                    <button
                                        type="button"
                                        className="button_standard"
                                        onClick={() => this.cancelEditingFile()}
                                    >
                                        Cancel
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div className="annotation_edit_tags_section">
                    <h4>Tags</h4>
                    {fileEditorTagsLoading ? (
                        <p>Loading room tags…</p>
                    ) : (
                        <div className="annotation_edit_tag_boxes">
                            <div className="selected_tags_box">
                                <div className="selected_tags_heading">Selected tags</div>
                                {selectedTagsForDisplay.length === 0 ? (
                                    <p className="annotation_edit_tags_empty">No tags selected.</p>
                                ) : (
                                    <div className="tag_list">
                                        {selectedTagsForDisplay.map((tag) => (
                                            <span
                                                key={tag.tag_id}
                                                className="tag selected_tag"
                                                title="Click to remove"
                                                onClick={() =>
                                                    !tagsSaveInProgress && this.removeSelectedFileTag(tag.tag_id)
                                                }
                                            >
                                                {tag.text} ×
                                            </span>
                                        ))}
                                    </div>
                                )}
                            </div>
                            <div className="suggested_tags_box">
                                <div className="suggested_tags_heading">Room tags</div>
                                {roomTags.length === 0 ? (
                                    <p className="annotation_edit_tags_empty">No tags defined for this room.</p>
                                ) : (
                                    <div className="tag_list">
                                        {roomTags.map((tag) => (
                                            <span
                                                key={tag.tag_id}
                                                className={`tag suggested_tag ${
                                                    selectedTagIds.has(tag.tag_id) ? "tag_selected" : ""
                                                }`}
                                                title={`${tag.text} (Click to add/remove)`}
                                                onClick={() =>
                                                    !tagsSaveInProgress && this.persistFileTagToggle(tag.tag_id)
                                                }
                                            >
                                                {tag.text}
                                            </span>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
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

        let main = (
            <div>
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
                            sortColumn={this.state.searchSortColumn}
                            sortDirection={this.state.searchSortDirection}
                            onSortChange={(
                                column: RoomFilesListSortColumn | null,
                                direction: "asc" | "desc" | null,
                            ) => {
                                this.setState(
                                    {
                                        searchSortColumn: column,
                                        searchSortDirection: direction,
                                    },
                                    () => this.refreshFiles(),
                                );
                            }}
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
            </div>
        );

        if (this.state.fileBeingEdited !== null) {
            main = <div>{this.renderFileBeingEdited()}</div>;
        }

        return (
            <div className="room_files_panel_react">
                {main}
            </div>
        );
    }
}
