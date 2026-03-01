import { h, Component } from "preact";
import { RoomAnnotationWithTags, createRoomTag, RoomTag } from "./generated/types";
import { api, GetRoomsAnnotationsResponse } from "./generated/api_routes";
import { sendMessage } from "./message/message";
import { PdfSelectionType } from "./constants";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setAnnotationTags } from "./api_room_entity_tags";

export interface RoomAnnotationPanelProps {
    room_id: string;
}

interface RoomAnnotationPanelState {
    annotations: RoomAnnotationWithTags[];
    error: string | null;
    logged_in: boolean;
    editingAnnotationId: string | null;
    roomTags: RoomTag[];
    selectedTagIds: Set<string>;
    tagsSaveInProgress: boolean;
}

function getDefaultState(): RoomAnnotationPanelState {
    return {
        annotations: [],
        error: null,
        logged_in: get_logged_in(),
        editingAnnotationId: null,
        roomTags: [],
        selectedTagIds: new Set(),
        tagsSaveInProgress: false,
    };
}



export class RoomAnnotationsPanel extends Component<RoomAnnotationPanelProps, RoomAnnotationPanelState> {

    unsubscribe_logged_in: (() => void)|null = null;

    constructor(props: RoomAnnotationPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshRoomAnnotations();
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
    }

    refreshRoomAnnotations() {
        api.rooms.annotations(this.props.room_id).
        then((data:GetRoomsAnnotationsResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data: GetRoomsAnnotationsResponse) {
        if (data.data.annotations === undefined) {
            this.setState({ error: "Server response did not contains 'annotations'." });
            return;
        }
        this.setState({ annotations: data.data.annotations });
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    shareAnnotation(annotation: RoomAnnotationWithTags, annotationUrl: string) {
        const full_url = window.location.origin + annotationUrl;
        const title = annotation.title || "Unnamed Link";
        const markdown_link = `[${title}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, { text: markdown_link });
    }

    openEditTags(annotation: RoomAnnotationWithTags) {
        const selectedTagIds = new Set((annotation.tags || []).map((t: RoomTag) => t.tag_id));
        this.setState({ editingAnnotationId: annotation.room_annotation_id, selectedTagIds });
        api.rooms.tags(this.props.room_id)
            .then((data) => {
                const roomTags = data.data.tags.map((t) => createRoomTag(t));
                this.setState({ roomTags });
            })
            .catch(() => this.setState({ roomTags: [] }));
    }

    closeEditTags() {
        this.setState({ editingAnnotationId: null, roomTags: [], selectedTagIds: new Set() });
    }

    toggleTagForEdit(tag_id: string) {
        const next = new Set(this.state.selectedTagIds);
        if (next.has(tag_id)) next.delete(tag_id);
        else next.add(tag_id);
        this.setState({ selectedTagIds: next });
    }

    saveAnnotationTags() {
        const { editingAnnotationId, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingAnnotationId || tagsSaveInProgress) return;
        this.setState({ tagsSaveInProgress: true });
        setAnnotationTags(this.props.room_id, editingAnnotationId, { tag_ids: Array.from(selectedTagIds) })
            .then(() => {
                this.closeEditTags();
                this.setState({ tagsSaveInProgress: false });
                this.refreshRoomAnnotations();
            })
            .catch(() => this.setState({ tagsSaveInProgress: false }));
    }

    renderRoomAnnotation(annotation: RoomAnnotationWithTags, logged_in: boolean) {
        const annotationUrl = `/rooms/${this.props.room_id}/file/${annotation.file_id}/annotations/${annotation.room_annotation_id}/view`;
        const tags = annotation.tags || [];
        const tagsBlock = tags.length > 0
            ? <span className="room_entity_tags">{tags.map((t: RoomTag) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : <span className="room_entity_tags empty">—</span>;

        return (
            <tr key={annotation.room_annotation_id}>
                <td>
                    <a href={annotationUrl} target="_blank">{annotation.title || "Unnamed Link"}</a>
                </td>
                <td>{tagsBlock}</td>
                <td><a href={annotationUrl}>View</a></td>
                {logged_in && (
                    <td>
                        <button className="button_standard button_chat" onClick={() => this.openEditTags(annotation)}>Edit tags</button>
                        <button className="button_standard button_chat" onClick={() => this.shareAnnotation(annotation, annotationUrl)} title="Share annotation to chat">Post&nbsp;to&nbsp;chat</button>
                    </td>
                )}
            </tr>
        );
    }

    renderAnnotations() {
        if (this.state.annotations.length === 0) {
            return (
                <div>
                    <h2>Annotations</h2>
                    <span>No annotations.</span>
                </div>
            );
        }
        const logged_in = this.state.logged_in;
        return (
            <div>
                <h2>Annotations</h2>
                <table>
                    <tbody>
                        <tr>
                            <td>Name</td>
                            <td>Tags</td>
                            <td></td>
                            {logged_in && <td></td>}
                        </tr>
                        {this.state.annotations.map((annotation) => this.renderRoomAnnotation(annotation, logged_in))}
                    </tbody>
                </table>
            </div>
        );
    }

    renderEditTagsModal() {
        const { editingAnnotationId, roomTags, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingAnnotationId) return null;
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
                        <button className="button_standard" onClick={() => this.saveAnnotationTags()} disabled={tagsSaveInProgress}>Save</button>
                        <button className="button_standard" onClick={() => this.closeEditTags()} disabled={tagsSaveInProgress}>Cancel</button>
                    </div>
                </div>
            </div>
        );
    }

    render(_props: RoomAnnotationPanelProps, _state: RoomAnnotationPanelState) {
        const error_block = this.state.error != null
            ? <div className="error">Last error: {this.state.error}</div>
            : <span>&nbsp;</span>;
        return (
            <div className="room_annotations_panel_react">
                {error_block}
                {this.renderAnnotations()}
                <button className="button_standard" onClick={() => this.refreshRoomAnnotations()}>Refresh</button>
                {this.renderEditTagsModal()}
            </div>
        );
    }
}
