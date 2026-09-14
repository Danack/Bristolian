import { Component } from "preact";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { PdfSelectionType } from "./constants";
import { api, GetRoomsNotesResponse } from "./generated/api_routes";
import { createRoomNoteWithTags, createRoomTag, RoomNoteWithTags, RoomTag } from "./generated/types";
import { formatDateTimeForContent, spacesToNbsp } from "./functions";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setNoteTags } from "./api_room_entity_tags";
import { fetchRoomNotes, RoomContentSearchParams } from "./api_room_content_list";
import { ROOM_CONTENT_LIST_DEFAULT_LIMIT } from "./generated/constants";
import { RoomContentSearchForm } from "./RoomContentSearchForm";

export interface RoomNotesPanelProps {
    room_id: string;
}

interface RoomNotesPanelState {
    roomNotes: RoomNoteWithTags[];
    noteBeingEdited: RoomNoteWithTags | null;
    error: string | null;
    logged_in: boolean;
    noteSaveInProgress: boolean;
    noteEditError: string | null;
    noteEditorTagsLoading: boolean;
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
    createTitle: string;
    createMarkdown: string;
    createInProgress: boolean;
    createError: string | null;
    createResult: string | null;
    deleteInProgress: boolean;
}

function pad2(value: number): string {
    return value < 10 ? "0" + String(value) : String(value);
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

function getDefaultState(): RoomNotesPanelState {
    return {
        roomNotes: [],
        noteBeingEdited: null,
        error: null,
        logged_in: get_logged_in(),
        noteSaveInProgress: false,
        noteEditError: null,
        noteEditorTagsLoading: false,
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
        createTitle: "",
        createMarkdown: "",
        createInProgress: false,
        createError: null,
        createResult: null,
        deleteInProgress: false,
    };
}

export class RoomNotesPanel extends Component<RoomNotesPanelProps, RoomNotesPanelState> {

    message_listener: number | null = null;
    unsubscribe_logged_in: (() => void) | null = null;
    private searchTimeout: number | null = null;

    constructor(props: RoomNotesPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshNotes();
        this.loadRoomTags();
        this.message_listener = registerMessageListener(
            PdfSelectionType.ROOM_NOTES_CHANGED,
            () => this.refreshNotes()
        );
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({ logged_in: logged_in });
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
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
        if (this.message_listener !== null) {
            unregisterListener(this.message_listener);
            this.message_listener = null;
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

    refreshNotes(cacheBust = false) {
        this.setState({ searchInFlight: true, searchWaiting: false });
        fetchRoomNotes(this.props.room_id, this.buildSearchParams(), cacheBust ? { cacheBust: true } : undefined)
            .then((data: GetRoomsNotesResponse) => this.processData(data))
            .catch((data: unknown) => this.processError(data));
    }

    scheduleSearch = () => {
        if (this.searchTimeout !== null) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = window.setTimeout(() => {
            this.refreshNotes();
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
        }, () => this.refreshNotes());
    };

    toggleSearchTag(tagId: string) {
        const next = new Set(this.state.searchTagIds);
        if (next.has(tagId)) {
            next.delete(tagId);
        }
        else {
            next.add(tagId);
        }
        this.setState({ searchTagIds: next, searchWaiting: true, searchInFlight: false }, () => this.scheduleSearch());
    }

    processData(data: GetRoomsNotesResponse) {
        if (data.data.notes === undefined) {
            this.setState({ error: "Server response did not contains 'notes'.", searchInFlight: false });
            return;
        }
        const roomNotes: RoomNoteWithTags[] = data.data.notes.map((note) =>
            createRoomNoteWithTags(note)
        );
        this.setState((previousState) => {
            let nextNoteBeingEdited = previousState.noteBeingEdited;
            if (nextNoteBeingEdited !== null) {
                const updated = roomNotes.find((note) => note.id === nextNoteBeingEdited.id);
                if (updated !== undefined) {
                    nextNoteBeingEdited = updated;
                }
            }
            let nextSelectedTagIds = previousState.selectedTagIds;
            if (nextNoteBeingEdited !== null) {
                nextSelectedTagIds = new Set(nextNoteBeingEdited.tags.map((t) => t.tag_id));
            }
            return {
                roomNotes,
                searchInFlight: false,
                noteBeingEdited: nextNoteBeingEdited,
                selectedTagIds: nextSelectedTagIds,
            };
        });
    }

    processError(data: unknown) {
        this.setState({ error: data instanceof Error ? data.message : "Request failed.", searchInFlight: false });
    }

    startEditingRoomNote(note: RoomNoteWithTags) {
        const selectedTagIds = new Set(note.tags.map((t) => t.tag_id));
        const needsRoomTagList = this.state.roomTags.length === 0;
        this.setState({
            noteBeingEdited: note,
            noteEditError: null,
            noteSaveInProgress: false,
            selectedTagIds,
            noteEditorTagsLoading: needsRoomTagList,
        });
        if (needsRoomTagList) {
            api.rooms.tags(this.props.room_id)
                .then((data) => {
                    const roomTags = data.data.tags.map((t) => createRoomTag(t));
                    this.setState({ roomTags });
                })
                .catch(() => this.setState({ roomTags: [] }))
                .finally(() => this.setState({ noteEditorTagsLoading: false }));
        }
    }

    cancelEditingRoomNote() {
        this.setState({
            noteBeingEdited: null,
            noteEditError: null,
            noteSaveInProgress: false,
            selectedTagIds: new Set(),
            noteEditorTagsLoading: false,
        });
    }

    persistNoteTagToggle(tag_id: string) {
        const { noteBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!noteBeingEdited || tagsSaveInProgress) {
            return;
        }
        const previous = new Set(selectedTagIds);
        const next = new Set(selectedTagIds);
        if (next.has(tag_id)) {
            next.delete(tag_id);
        }
        else {
            next.add(tag_id);
        }
        this.setState({ selectedTagIds: next, tagsSaveInProgress: true });
        setNoteTags(this.props.room_id, noteBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshNotes(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    removeSelectedNoteTag(tag_id: string) {
        const { noteBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!noteBeingEdited || tagsSaveInProgress) {
            return;
        }
        const previous = new Set(selectedTagIds);
        const next = new Set(selectedTagIds);
        next.delete(tag_id);
        this.setState({ selectedTagIds: next, tagsSaveInProgress: true });
        setNoteTags(this.props.room_id, noteBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshNotes(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    saveEditedRoomNote() {
        const { noteBeingEdited, noteSaveInProgress } = this.state;
        if (!noteBeingEdited || noteSaveInProgress) {
            return;
        }

        this.setState({ noteSaveInProgress: true, noteEditError: null });

        const url = `/api/rooms/${this.props.room_id}/notes/${noteBeingEdited.id}`;
        const body = {
            title: noteBeingEdited.title,
            markdown: noteBeingEdited.markdown,
            document_timestamp: dateToDatetimeLocalValue(noteBeingEdited.document_timestamp),
        };

        fetch(url, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(body),
        })
            .then(async (response) => {
                if (response.status === 200) {
                    await response.json().catch((): undefined => undefined);
                    return;
                }
                if (response.status === 400) {
                    const data = await response.json().catch(() => ({}));
                    const titleError = data?.data?.["/title"];
                    const markdownError = data?.data?.["/markdown"];
                    throw new Error(titleError || markdownError || "Validation failed.");
                }
                if (response.status === 404) {
                    throw new Error("Note not found in room.");
                }
                throw new Error("Server failed to update note.");
            })
            .then(() => {
                this.setState(
                    {
                        noteBeingEdited: null,
                        noteSaveInProgress: false,
                        selectedTagIds: new Set(),
                        noteEditorTagsLoading: false,
                    },
                    () => this.refreshNotes()
                );
            })
            .catch((error: Error) => {
                this.setState({ noteSaveInProgress: false, noteEditError: error.message });
            });
    }

    createNote() {
        if (this.state.createInProgress) {
            return;
        }
        this.setState({ createInProgress: true, createError: null, createResult: null });
        fetch(`/api/rooms/${this.props.room_id}/notes`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: this.state.createTitle,
                markdown: this.state.createMarkdown,
            }),
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (response.status === 200 && data.result === "success") {
                    this.setState({
                        createTitle: "",
                        createMarkdown: "",
                        createInProgress: false,
                        createResult: "Note added",
                    });
                    sendMessage(PdfSelectionType.ROOM_NOTES_CHANGED, {});
                    this.refreshNotes(true);
                    return;
                }
                if (response.status === 400) {
                    const titleError = data?.data?.["/title"];
                    const markdownError = data?.data?.["/markdown"];
                    throw new Error(titleError || markdownError || "Validation failed.");
                }
                throw new Error("Server failed to create note.");
            })
            .catch((error: Error) => {
                this.setState({ createInProgress: false, createError: error.message });
            });
    }

    deleteNote(note: RoomNoteWithTags) {
        if (this.state.deleteInProgress) {
            return;
        }
        const confirmed = window.confirm(`Delete note “${note.title}”?`);
        if (!confirmed) {
            return;
        }
        this.setState({ deleteInProgress: true, error: null });
        fetch(`/api/rooms/${this.props.room_id}/notes/${note.id}`, {
            method: "DELETE",
        })
            .then(async (response) => {
                if (response.status === 200) {
                    await response.json().catch((): undefined => undefined);
                    this.setState({ deleteInProgress: false });
                    this.refreshNotes(true);
                    return;
                }
                if (response.status === 404) {
                    throw new Error("Note not found in room.");
                }
                throw new Error("Server failed to delete note.");
            })
            .catch((error: Error) => {
                this.setState({ deleteInProgress: false, error: error.message });
            });
    }

    shareNote(note: RoomNoteWithTags) {
        const full_url = window.location.origin + `/rooms/${this.props.room_id}/notes/${note.id}`;
        const markdown_link = `[${note.title}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, { text: markdown_link });
    }

    renderRoomNote(note: RoomNoteWithTags, logged_in: boolean) {
        const note_url = `/rooms/${this.props.room_id}/notes/${note.id}`;
        const tagsBlock = note.tags.length > 0
            ? <span className="room_entity_tags">{note.tags.map((t) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : <span className="room_entity_tags empty">—</span>;

        const dateDisplay = note.document_timestamp != null
            ? spacesToNbsp(formatDateTimeForContent(note.document_timestamp))
            : "—";

        return (
            <tr key={note.id}>
                <td><a href={note_url}>{note.title}</a></td>
                <td>{spacesToNbsp(formatDateTimeForContent(note.created_at))}</td>
                <td>{dateDisplay}</td>
                <td>{tagsBlock}</td>
                {logged_in && (
                    <td>
                        <button className="button_standard button_chat" onClick={() => this.startEditingRoomNote(note)}>Edit</button>
                        <button className="button_standard button_chat" onClick={() => this.shareNote(note)} title="Share note to chat">Post&nbsp;to&nbsp;chat</button>
                        <button className="button_standard button_chat" onClick={() => this.deleteNote(note)}>Delete</button>
                    </td>
                )}
            </tr>
        );
    }

    renderNotes() {
        if (this.state.roomNotes.length === 0) {
            return <span>No notes.</span>;
        }
        const logged_in = this.state.logged_in;
        return (
            <table className="large_table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Added</th>
                        <th>Date</th>
                        <th>Tags</th>
                        {logged_in && <th />}
                    </tr>
                </thead>
                <tbody>
                    {this.state.roomNotes.map((roomNote) => this.renderRoomNote(roomNote, logged_in))}
                </tbody>
            </table>
        );
    }

    renderNoteBeingEdited() {
        const {
            noteBeingEdited,
            noteSaveInProgress,
            noteEditError,
            roomTags,
            selectedTagIds,
            tagsSaveInProgress,
            noteEditorTagsLoading,
        } = this.state;
        if (noteBeingEdited === null) {
            return <span></span>;
        }

        const selectedTagsForDisplay = roomTags.filter((tag) => selectedTagIds.has(tag.tag_id));

        return (
            <div className="room_notes_add_form">
                <h3>Edit note</h3>
                <div className="annotation_edit_title_text_form">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <label>Name</label>
                                </td>
                                <td>
                                    <input
                                        name="title"
                                        size={100}
                                        value={noteBeingEdited.title}
                                        disabled={noteSaveInProgress}
                                        onChange={(event) =>
                                            this.setState({
                                                noteBeingEdited: {
                                                    ...noteBeingEdited,
                                                    title: (event.currentTarget as HTMLInputElement).value,
                                                },
                                                noteEditError: null,
                                            })
                                        }
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="note_edit_markdown">Markdown</label>
                                </td>
                                <td>
                                    <textarea
                                        id="note_edit_markdown"
                                        className="room_note_markdown_input"
                                        name="markdown"
                                        rows={16}
                                        cols={80}
                                        value={noteBeingEdited.markdown}
                                        disabled={noteSaveInProgress}
                                        onChange={(event) =>
                                            this.setState({
                                                noteBeingEdited: {
                                                    ...noteBeingEdited,
                                                    markdown: (event.currentTarget as HTMLTextAreaElement).value,
                                                },
                                                noteEditError: null,
                                            })
                                        }
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="note_edit_document_timestamp">Date</label>
                                </td>
                                <td>
                                    <input
                                        id="note_edit_document_timestamp"
                                        type="datetime-local"
                                        value={dateToDatetimeLocalValue(noteBeingEdited.document_timestamp)}
                                        disabled={noteSaveInProgress}
                                        onChange={(event) => {
                                            const value = (event.currentTarget as HTMLInputElement).value;
                                            const parsed = value === "" ? null : new Date(value);
                                            this.setState({
                                                noteBeingEdited: {
                                                    ...noteBeingEdited,
                                                    document_timestamp: parsed !== null && Number.isNaN(parsed.getTime()) ? null : parsed,
                                                },
                                            });
                                        }}
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button
                                        type="button"
                                        className="button_standard"
                                        disabled={noteSaveInProgress}
                                        onClick={() => this.saveEditedRoomNote()}
                                    >
                                        Save
                                    </button>
                                    {noteEditError ? <span className="error">{noteEditError}</span> : null}
                                    <button
                                        type="button"
                                        className="button_standard"
                                        onClick={() => this.cancelEditingRoomNote()}
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
                    {noteEditorTagsLoading ? (
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
                                                    !tagsSaveInProgress && this.removeSelectedNoteTag(tag.tag_id)
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
                                                    !tagsSaveInProgress && this.persistNoteTagToggle(tag.tag_id)
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

    renderCreateForm() {
        if (this.state.logged_in !== true) {
            return <span></span>;
        }

        return (
            <div className="room_notes_add_form">
                <h3>Add note</h3>
                <table>
                    <tbody>
                        <tr>
                            <td><label htmlFor="room_note_create_title">Name</label></td>
                            <td>
                                <input
                                    id="room_note_create_title"
                                    size={100}
                                    value={this.state.createTitle}
                                    disabled={this.state.createInProgress}
                                    onInput={(event) => this.setState({
                                        createTitle: (event.currentTarget as HTMLInputElement).value,
                                        createError: null,
                                    })}
                                />
                            </td>
                        </tr>
                        <tr>
                            <td><label htmlFor="room_note_create_markdown">Markdown</label></td>
                            <td>
                                <textarea
                                    id="room_note_create_markdown"
                                    className="room_note_markdown_input"
                                    rows={8}
                                    cols={80}
                                    value={this.state.createMarkdown}
                                    disabled={this.state.createInProgress}
                                    onInput={(event) => this.setState({
                                        createMarkdown: (event.currentTarget as HTMLTextAreaElement).value,
                                        createError: null,
                                    })}
                                />
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <button
                                    type="button"
                                    className="button_standard"
                                    disabled={this.state.createInProgress || this.state.createTitle.trim() === ""}
                                    onClick={() => this.createNote()}
                                >
                                    Add note
                                </button>
                                {this.state.createError ? <span className="error">{this.state.createError}</span> : null}
                                {this.state.createResult ? <div className="room_note_add_success">{this.state.createResult}</div> : null}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        );
    }

    renderTableOfNotes() {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>;
        }
        let notes_block;
        if (this.state.searchWaiting) {
            notes_block = <div>Waiting....</div>;
        }
        else if (this.state.searchInFlight) {
            notes_block = <div>Searching....</div>;
        }
        else {
            notes_block = this.renderNotes();
        }

        return <div>
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
                        titleLabel="Name"
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
            {notes_block}
            <div>Showing {this.state.roomNotes.length} notes</div>
            <button className="button_standard" onClick={() => this.refreshNotes()}>Refresh</button>
            {this.renderCreateForm()}
        </div>;
    }

    render() {
        let content = this.renderTableOfNotes();

        if (this.state.noteBeingEdited !== null) {
            content = this.renderNoteBeingEdited();
        }

        return (
            <div className="room_notes_panel_react">
                <h2>Notes</h2>
                <div>{content}</div>
            </div>
        );
    }
}
