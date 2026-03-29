import { h, Component } from "preact";
import { RoomLinkAddPanel } from "./RoomLinkAddPanel";
import { registerMessageListener, sendMessage } from "./message/message";
import { PdfSelectionType } from "./constants";
import { api, GetRoomsLinksResponse } from "./generated/api_routes";
import { RoomLinkWithTags, createRoomLinkWithTags, createRoomTag, RoomTag } from "./generated/types";
import { formatDateTimeForContent, spacesToNbsp } from "./functions";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setLinkTags } from "./api_room_entity_tags";
import { fetchRoomLinks, RoomContentSearchParams } from "./api_room_content_list";
import { ROOM_CONTENT_LIST_DEFAULT_LIMIT } from "./generated/constants";
import { RoomContentSearchForm } from "./RoomContentSearchForm";

export interface RoomLinksPanelProps {
    room_id: string;
}

interface RoomLinksPanelState {
    roomLinks: RoomLinkWithTags[];
    linkBeingEdited: RoomLinkWithTags | null;
    error: string | null;
    logged_in: boolean;
    linkSaveInProgress: boolean;
    linkEditError: string | null;
    linkEditorTagsLoading: boolean;
    roomTags: RoomTag[];
    /** Tag membership for the link inline editor (when linkBeingEdited is set). */
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

function getDefaultState(): RoomLinksPanelState {
    return {
        roomLinks: [],
        linkBeingEdited: null,
        error: null,
        logged_in: get_logged_in(),
        linkSaveInProgress: false,
        linkEditError: null,
        linkEditorTagsLoading: false,
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

export class RoomLinksPanel extends Component<RoomLinksPanelProps, RoomLinksPanelState> {

    message_listener: number|null;
    unsubscribe_logged_in: (() => void)|null = null;
    private searchTimeout: number | null = null;

    constructor(props: RoomLinksPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshLinks();
        this.loadRoomTags();
        this.message_listener = registerMessageListener(
          PdfSelectionType.ROOM_LINKS_CHANGED,
          () => this.refreshLinks()
        );
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

    /**
     * @param cacheBust - use after tag (or other) mutations so GET /links is not served from browser cache.
     */
    refreshLinks(cacheBust = false) {
        this.setState({ searchInFlight: true, searchWaiting: false });
        fetchRoomLinks(this.props.room_id, this.buildSearchParams(), cacheBust ? { cacheBust: true } : undefined)
            .then((data: GetRoomsLinksResponse) => this.processData(data))
            .catch((data: unknown) => this.processError(data));
    }

    scheduleSearch = () => {
        if (this.searchTimeout !== null) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = window.setTimeout(() => {
            this.refreshLinks();
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
        }, () => this.refreshLinks());
    };

    toggleSearchTag(tagId: string) {
        const next = new Set(this.state.searchTagIds);
        if (next.has(tagId)) next.delete(tagId);
        else next.add(tagId);
        this.setState({ searchTagIds: next, searchWaiting: true, searchInFlight: false }, () => this.scheduleSearch());
    }

    processData(data: GetRoomsLinksResponse) {
        if (data.data.links === undefined) {
            this.setState({ error: "Server response did not contains 'links'.", searchInFlight: false });
            return;
        }
        const roomLinks: RoomLinkWithTags[] = data.data.links.map((link) =>
            createRoomLinkWithTags(link)
        );
        this.setState((previousState) => {
            let nextLinkBeingEdited = previousState.linkBeingEdited;
            if (nextLinkBeingEdited !== null) {
                const updated = roomLinks.find((l) => l.id === nextLinkBeingEdited.id);
                if (updated !== undefined) {
                    nextLinkBeingEdited = updated;
                }
            }
            let nextSelectedTagIds = previousState.selectedTagIds;
            if (nextLinkBeingEdited !== null) {
                nextSelectedTagIds = new Set(nextLinkBeingEdited.tags.map((t) => t.tag_id));
            }
            return {
                roomLinks,
                searchInFlight: false,
                linkBeingEdited: nextLinkBeingEdited,
                selectedTagIds: nextSelectedTagIds,
            };
        });
    }
    processError(data: unknown) {
        this.setState({ error: data instanceof Error ? data.message : "Request failed.", searchInFlight: false });
    }

    restoreState(state_to_restore: object) {
    }

    startEditingRoomLink(roomLink: RoomLinkWithTags) {
        const selectedTagIds = new Set(roomLink.tags.map((t) => t.tag_id));
        const needsRoomTagList = this.state.roomTags.length === 0;
        this.setState({
            linkBeingEdited: roomLink,
            linkEditError: null,
            linkSaveInProgress: false,
            selectedTagIds,
            linkEditorTagsLoading: needsRoomTagList,
        });
        if (needsRoomTagList) {
            api.rooms.tags(this.props.room_id)
                .then((data) => {
                    const roomTags = data.data.tags.map((t) => createRoomTag(t));
                    this.setState({ roomTags });
                })
                .catch(() => this.setState({ roomTags: [] }))
                .finally(() => this.setState({ linkEditorTagsLoading: false }));
        }
    }

    cancelEditingRoomLink() {
        this.setState({
            linkBeingEdited: null,
            linkEditError: null,
            linkSaveInProgress: false,
            selectedTagIds: new Set(),
            linkEditorTagsLoading: false,
        });
    }

    persistLinkTagToggle(tag_id: string) {
        const { linkBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!linkBeingEdited || tagsSaveInProgress) {
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
        setLinkTags(this.props.room_id, linkBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshLinks(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    removeSelectedLinkTag(tag_id: string) {
        const { linkBeingEdited, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!linkBeingEdited || tagsSaveInProgress) {
            return;
        }
        const previous = new Set(selectedTagIds);
        const next = new Set(selectedTagIds);
        next.delete(tag_id);
        this.setState({ selectedTagIds: next, tagsSaveInProgress: true });
        setLinkTags(this.props.room_id, linkBeingEdited.id, { tag_ids: Array.from(next) })
            .then(() => this.refreshLinks(true))
            .catch(() => this.setState({ selectedTagIds: previous }))
            .finally(() => this.setState({ tagsSaveInProgress: false }));
    }

    saveEditedRoomLink() {
        const { linkBeingEdited, linkSaveInProgress } = this.state;
        if (!linkBeingEdited || linkSaveInProgress) {
            return;
        }

        this.setState({ linkSaveInProgress: true, linkEditError: null });

        const url = `/api/rooms/${this.props.room_id}/links/${linkBeingEdited.id}`;
        const body = {
            // Backend params use DataType rules that treat empty strings as NULL.
            title: linkBeingEdited.title ?? '',
            description: linkBeingEdited.description ?? '',
        };

        fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        })
            .then(async (response) => {
                if (response.status === 200) {
                    // SuccessResponse body is not needed; still consume to complete.
                    await response.json().catch((): undefined => undefined);
                    return;
                }

                if (response.status === 400) {
                    const data = await response.json().catch(() => ({}));
                    const titleError = data?.data?.['/title'];
                    const descriptionError = data?.data?.['/description'];
                    throw new Error(titleError || descriptionError || 'Validation failed.');
                }

                if (response.status === 404) {
                    throw new Error('Link not found in room.');
                }

                throw new Error('Server failed to update link.');
            })
            .then(() => {
                this.setState(
                    {
                        linkBeingEdited: null,
                        linkSaveInProgress: false,
                        selectedTagIds: new Set(),
                        linkEditorTagsLoading: false,
                    },
                    () => this.refreshLinks()
                );
            })
            .catch((error: Error) => {
                this.setState({ linkSaveInProgress: false, linkEditError: error.message });
            });
    }

    shareLink(link: RoomLinkWithTags) {
        const resolved_title = link.title || link.url;
        const markdown_link = `[${resolved_title}](${link.url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, { text: markdown_link });
    }

    renderRoomLink(link: RoomLinkWithTags, logged_in: boolean) {
        const resolved_title = link.title || link.url;
        const tagsBlock = link.tags.length > 0
            ? <span className="room_entity_tags">{link.tags.map((t) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : <span className="room_entity_tags empty">—</span>;

        const dateDisplay = link.document_timestamp != null
            ? spacesToNbsp(formatDateTimeForContent(link.document_timestamp))
            : "—";

        return (
            <tr key={link.id}>
                <td><a href={link.url} target="_blank">{resolved_title}</a></td>
                <td>{spacesToNbsp(formatDateTimeForContent(link.created_at))}</td>
                <td>{dateDisplay}</td>
                <td>{tagsBlock}</td>
                {logged_in && (
                    <td>
                        <button className="button_standard button_chat" onClick={() => this.startEditingRoomLink(link)}>Edit</button>
                        <button className="button_standard button_chat" onClick={() => this.shareLink(link)} title="Share link to chat">Post&nbsp;to&nbsp;chat</button>
                    </td>
                )}
            </tr>
        );
    }

    renderLinks() {
        if (this.state.roomLinks.length === 0) {
            return <span>No links.</span>;
        }
        const logged_in = this.state.logged_in;
        return (
            <table className="large_table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Added</th>
                        <th>Date</th>
                        <th>Tags</th>
                        {logged_in && <th />}
                    </tr>
                </thead>
                <tbody>
                    {this.state.roomLinks.map((roomLink) => this.renderRoomLink(roomLink, logged_in))}
                </tbody>
            </table>
        );
    }

    renderLinkBeingEdited() {
        const {
            linkBeingEdited,
            linkSaveInProgress,
            linkEditError,
            roomTags,
            selectedTagIds,
            tagsSaveInProgress,
            linkEditorTagsLoading,
        } = this.state;
        if (linkBeingEdited === null) {
            return <span></span>;
        }

        const selectedTagsForDisplay = roomTags.filter((tag) => selectedTagIds.has(tag.tag_id));

        return (
            <div className="room_links_add_panel_react">
                <h3>Edit link</h3>
                <div className="annotation_edit_title_text_form">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <label>URL</label>
                                </td>
                                <td>
                                    <span>{linkBeingEdited.url}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Title</label>
                                </td>
                                <td>
                                    <input
                                        name="title"
                                        size={100}
                                        value={linkBeingEdited.title ?? ''}
                                        disabled={linkSaveInProgress}
                                        onChange={(event) =>
                                            this.setState({
                                                linkBeingEdited: {
                                                    ...linkBeingEdited,
                                                    title: (event.currentTarget as HTMLInputElement).value,
                                                },
                                                linkEditError: null,
                                            })
                                        }
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label htmlFor="link_edit_description">Description</label>
                                </td>
                                <td>
                                    <textarea
                                        id="link_edit_description"
                                        name="description"
                                        rows={4}
                                        cols={80}
                                        value={linkBeingEdited.description ?? ''}
                                        disabled={linkSaveInProgress}
                                        onChange={(event) =>
                                            this.setState({
                                                linkBeingEdited: {
                                                    ...linkBeingEdited,
                                                    description: (event.currentTarget as HTMLTextAreaElement).value,
                                                },
                                                linkEditError: null,
                                            })
                                        }
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button
                                        type="button"
                                        className="button_standard"
                                        disabled={linkSaveInProgress}
                                        onClick={() => this.saveEditedRoomLink()}
                                    >
                                        Save
                                    </button>
                                    {linkEditError ? <span className="error">{linkEditError}</span> : null}
                                    <button
                                        type="button"
                                        className="button_standard"
                                        onClick={() => this.cancelEditingRoomLink()}
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
                    {linkEditorTagsLoading ? (
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
                                                    !tagsSaveInProgress && this.removeSelectedLinkTag(tag.tag_id)
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
                                                    selectedTagIds.has(tag.tag_id) ? 'tag_selected' : ''
                                                }`}
                                                title={`${tag.text} (Click to add/remove)`}
                                                onClick={() =>
                                                    !tagsSaveInProgress && this.persistLinkTagToggle(tag.tag_id)
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

    renderTableOfLinks() {

        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }
        let length = this.state.roomLinks.length;
        let links_block;
        if (this.state.searchWaiting) {
            links_block = <div>Waiting....</div>;
        }
        else if (this.state.searchInFlight) {
            links_block = <div>Searching....</div>;
        }
        else {
            links_block = this.renderLinks();
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
                        titlePlaceholder="Filter by title"
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
            {links_block}
            <div>Showing {length} links</div>
            <button className="button_standard" onClick={() => this.refreshLinks()}>Refresh</button>
            <RoomLinkAddPanel room_id={this.props.room_id}/>
        </div>
    }

    render(props: RoomLinksPanelProps, state: RoomLinksPanelState) {

        let content = this.renderTableOfLinks();

        if (this.state.linkBeingEdited !== null) {
            content = this.renderLinkBeingEdited();
        }

        return (
            <div className="room_links_panel_react">
                <h2>Links</h2>
                <div>{content}</div>
            </div>
        );
    }
}