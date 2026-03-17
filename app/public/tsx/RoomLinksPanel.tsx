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
    editingLinkId: string | null;
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

function getDefaultState(): RoomLinksPanelState {
    return {
        roomLinks: [],
        linkBeingEdited: null,
        error: null,
        logged_in: get_logged_in(),
        editingLinkId: null,
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

    refreshLinks() {
        this.setState({ searchInFlight: true, searchWaiting: false });
        fetchRoomLinks(this.props.room_id, this.buildSearchParams())
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
        this.setState({ roomLinks, searchInFlight: false });
    }
    processError(data: unknown) {
        this.setState({ error: data instanceof Error ? data.message : "Request failed.", searchInFlight: false });
    }

    restoreState(state_to_restore: object) {
    }

    startEditingRoomLink(roomLink: RoomLinkWithTags) {
        this.setState({linkBeingEdited: roomLink})
    }

    cancelEditingRoomLink() {
        this.setState({linkBeingEdited: null})
    }

    shareLink(link: RoomLinkWithTags) {
        const resolved_title = link.title || link.link_id;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, { text: resolved_title });
    }

    openEditTags(link: RoomLinkWithTags) {
        const selectedTagIds = new Set(link.tags.map((t) => t.tag_id));
        this.setState({ editingLinkId: link.id, selectedTagIds });
        api.rooms.tags(this.props.room_id)
            .then((data) => {
                const roomTags = data.data.tags.map((t) => createRoomTag(t));
                this.setState({ roomTags });
            })
            .catch(() => this.setState({ roomTags: [] }));
    }

    closeEditTags() {
        this.setState({ editingLinkId: null, roomTags: [], selectedTagIds: new Set() });
    }

    toggleTagForEdit(tag_id: string) {
        const next = new Set(this.state.selectedTagIds);
        if (next.has(tag_id)) next.delete(tag_id);
        else next.add(tag_id);
        this.setState({ selectedTagIds: next });
    }

    saveLinkTags() {
        const { editingLinkId, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingLinkId || tagsSaveInProgress) return;
        this.setState({ tagsSaveInProgress: true });
        setLinkTags(this.props.room_id, editingLinkId, { tag_ids: Array.from(selectedTagIds) })
            .then(() => {
                this.closeEditTags();
                this.setState({ tagsSaveInProgress: false });
                this.refreshLinks();
            })
            .catch(() => this.setState({ tagsSaveInProgress: false }));
    }

    renderRoomLink(link: RoomLinkWithTags, logged_in: boolean) {
        const resolved_title = link.title || link.link_id;
        const tagsBlock = link.tags.length > 0
            ? <span className="room_entity_tags">{link.tags.map((t) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : <span className="room_entity_tags empty">—</span>;

        const dateDisplay = link.document_timestamp != null
            ? spacesToNbsp(formatDateTimeForContent(link.document_timestamp))
            : "—";

        return (
            <tr key={link.id}>
                <td><span>{resolved_title}</span></td>
                <td>{spacesToNbsp(formatDateTimeForContent(link.created_at))}</td>
                <td>{dateDisplay}</td>
                <td>{tagsBlock}</td>
                {logged_in && (
                    <td>
                        <button className="button_standard button_chat" onClick={() => this.startEditingRoomLink(link)}>Edit</button>
                        <button className="button_standard button_chat" onClick={() => this.openEditTags(link)}>Edit tags</button>
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

    renderEditTagsModal() {
        const { editingLinkId, roomTags, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingLinkId) return null;
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
                        <button className="button_standard" onClick={() => this.saveLinkTags()} disabled={tagsSaveInProgress}>Save</button>
                        <button className="button_standard" onClick={() => this.closeEditTags()} disabled={tagsSaveInProgress}>Cancel</button>
                    </div>
                </div>
            </div>
        );
    }

    renderLinkBeingEdited() {



        let error_url = <span></span>
        let error_title = <span></span>
        let error_description = <span></span>

        // if (this.state.error_url !== null) {
        //     error_url = <span class="error">{this.state.error_url}</span>
        // }
        //
        // if (this.state.error_title !== null) {
        //     error_title = <span class="error">{this.state.error_title}</span>
        // }
        //
        // if (this.state.error_description !== null) {
        //     error_description = <span class="error">{this.state.error_description}</span>
        // }

        // Note: RoomLink doesn't have a url property, only link_id
        // The URL editing functionality is not currently implemented
        let add_button = <span><button className="button_standard" disabled={true}>Save</button>Editing not fully implemented.</span>

        // @ts-ignore
        return <div class='room_links_add_panel_react'>
            <table>
                <tbody>
                <tr>
                    <td>
                        <label>
                            Link ID
                        </label>
                    </td>
                    <td>
                        <span>{this.state.linkBeingEdited.link_id}</span>
                        {error_url}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            Title
                        </label>
                    </td>
                    <td>
                        <input name="title"
                               size={100}
                               value={this.state.linkBeingEdited.title}
                               onChange={
                                   // @ts-ignore
                                   e => this.setState({title: e.target.value})
                               }/>
                        {error_title}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for={"description"}>Description</label>
                    </td>

                    <td>
              <textarea
                name="description"
                rows={4}
                cols={80}
                value={this.state.linkBeingEdited.description}
                onChange={
                    // @ts-ignore
                    e => this.setState({description: e.target.value})
                }/>

                        {error_description}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        {add_button}
                        <button type="submit" className="button_standard" onClick={() => this.cancelEditingRoomLink()}>Cancel</button>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>

                </tbody>
            </table>
        </div>;



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
                {this.renderEditTagsModal()}
            </div>
        );
    }
}