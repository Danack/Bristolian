
import { h, Component } from "preact";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { RoomAnnotationWithTags, createRoomTag, RoomTag } from "./generated/types";
import { countWords } from "./functions";
import { ANNOTATION_TITLE_MINIMUM_LENGTH } from "./generated/constants";
import { api, GetRoomsFileAnnotationsResponse } from "./generated/api_routes";
import { PdfSelectionType } from "./constants";
import { setAnnotationTags } from "./api_room_entity_tags";
import { get_logged_in, subscribe_logged_in } from "./store";

export interface SelectionPosition {
  top: number;
  left: number;
  bottom: number;
  right: number;
}

interface Highlight {
  page: number;
  left: number;
  top: number;
  right: number;
  bottom: number;
}

export interface SelectionData {
  text: string;
  highlights: Highlight[]
}

export interface SelectionMessage {
  type: string;
  selection_data: SelectionData;
}

// This function transfers global messages to the bespoke widgety
// message handler.
export function receiveSelectionMessage(event: MessageEvent) {
  if (event.data && event.data.type === PdfSelectionType.TEXT_SELECTED){
    const message: SelectionMessage = event.data;
    // console.log("Received selection data:", message.selection_data);
    sendMessage("text_selected", message.selection_data);
    return;
  }

  if (event.data && event.data.type === PdfSelectionType.TEXT_DESELECTED){
    const message: SelectionMessage = event.data;

    sendMessage("text_deselected", message.selection_data);
    return;
  }

  if (event.data && event.data.type === PdfSelectionType.PDF_READY) {
    console.log("pdf ready");
  }

  if (event.data && event.data.type === PdfSelectionType.PDF_RENDERING) {

    console.log("received current page " + event.data.current_page + " total pages " + event.data.total_pages);
  }
}

export interface AnnotationPanelProps {
  room_id: string,
  file_id: string,
  selected_annotation_id: null|string,
}

interface AnnotationPanelState {
  selection_data: SelectionData | null;
  title: string;
  text: string;
  annotations: RoomAnnotationWithTags[];
  selected_annotation_id: string | null;
  create_status: string | null;
  error: string | null;
  editingAnnotationId: string | null;
  roomTags: RoomTag[];
  selectedTagIds: Set<string>;
  tagsSaveInProgress: boolean;
  logged_in: boolean;
}

function getDefaultState(props: AnnotationPanelProps): AnnotationPanelState {
  return {
    selection_data: null,
    title: "",
    text: "",
    annotations: [],
    selected_annotation_id: props.selected_annotation_id || null,
    create_status: null,
    error: null,
    editingAnnotationId: null,
    roomTags: [],
    selectedTagIds: new Set(),
    tagsSaveInProgress: false,
    logged_in: get_logged_in(),
  };
}

function sendDataToPDFJS(data: object) {
  let iframe = document.getElementById('pdf_iframe');
// @ts-ignore:content window does exist
  if (iframe) {
    // @ts-ignore:content window does exist
    if (iframe.contentWindow) {
      // @ts-ignore:content window does exist
      iframe.contentWindow.postMessage(data, '*',);

      console.log("highlights sent");
    } else {
      console.log("iframe.contentWindow is null");
    }
  } else {
    console.log("no iframe");
  }

}


export class AnnotationPanel extends Component<AnnotationPanelProps, AnnotationPanelState> {

  message_listener:null|number = null;

  message_listener_deselect:null|number = null;

  unsubscribe_logged_in: (() => void) | null = null;

  // If the panel is loaded with a selected annotation_id, then we need
  // to remember to render it when we get the data back about annotations.
  pending_annotation_id: null|string = null;

  constructor(props: AnnotationPanelProps) {
    super(props);
    this.state = getDefaultState(props);
    this.pending_annotation_id = props.selected_annotation_id;
  }

  componentDidMount() {
    this.message_listener = registerMessageListener(
      "text_selected",
      (selection_data: SelectionData) => this.receiveTextSelected(selection_data)
    );

    this.message_listener_deselect = registerMessageListener(
      "text_deselected",
      () => this.receiveTextDeselected()
    );

    this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
      this.setState({ logged_in });
    });

    this.refreshRoomFileAnnotations();
  }

  componentWillUnmount() {
    unregisterListener(this.message_listener);
    unregisterListener(this.message_listener_deselect);
    if (this.unsubscribe_logged_in) {
      this.unsubscribe_logged_in();
      this.unsubscribe_logged_in = null;
    }
  }

  receiveTextSelected(selection_data: SelectionData) {
    this.setState({
      create_status: null,
      selection_data:selection_data
    });
  }

  receiveTextDeselected() {
    this.setState({selection_data:null});
  }

  handleCheckboxChange(event: any, id: string) {
  }

  restoreState(state_to_restore: object) {
  }

  refreshRoomFileAnnotations(cacheBust?: boolean) {
    api.rooms.file.annotations(this.props.room_id, this.props.file_id, cacheBust ? { cacheBust: true } : undefined).
    then((data:GetRoomsFileAnnotationsResponse) => this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  processData(data: GetRoomsFileAnnotationsResponse) {
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



  sendRectsToDraw() {
    let iframe = document.getElementById('pdf_iframe');

    console.log("sendRectsToDraw");
    // @ts-ignore:content window does exist
    if (iframe && iframe.contentWindow) {
      // @ts-ignore:content window does exist
      iframe.contentWindow.postMessage({
        type: 'draw_highlights',
        rects: this.state.selection_data.highlights
      }, '*',);
    }
  }

  componentDidUpdate(prevProps: AnnotationPanelProps, prevState: AnnotationPanelState) {
    // The whole lifecycle and interaction between this panel and
    // the iframe containing the PDF could do with some re-thinking.
    if (this.pending_annotation_id && this.state.annotations) {
      console.log("we had pending_annotation_id and data is now loaded, so sending highlights");
      this.sendHighlightsToDraw();
      this.pending_annotation_id = null;
    }
    else if (this.state.selected_annotation_id !== prevState.selected_annotation_id) {
      this.sendHighlightsToDraw();
    }
  }

  sendHighlightsToDraw() {
    const selectedAnnotation = this.state.annotations.find(
      (annotation: RoomAnnotationWithTags) => annotation.id === this.state.selected_annotation_id
    );

    if (!selectedAnnotation) {
      return;
    }

    // RoomAnnotationView has highlights_json directly from the joined query
    const selectionDataJson = selectedAnnotation?.highlights_json || null;
    if (!selectionDataJson) {
      console.warn("highlights_json not available for selected annotation");
      return;
    }
    let highlights = JSON.parse(selectionDataJson)

    // console.log("sendHighlightsToDraw");

    sendDataToPDFJS({
      type: 'draw_highlights',
      highlights: highlights
    });
  }


  addAnnotation() {

    let highlights_json = JSON.stringify(this.state.selection_data.highlights);

    const formData = new FormData();

    formData.append("title", this.state.title.trim());
    formData.append("highlights_json", highlights_json);
    formData.append("text", this.state.text);

    let url = `/api/rooms/${this.props.room_id}/annotation/${this.props.file_id}`

    let params = {
      method: 'POST',
      body: formData
    }
    fetch(url, params).
    then((response:Response) => response.json()).
    then((data:any) => this.processAddAnnotationResponse(data));
  }

  processAddAnnotationResponse(data: any) {
      console.log("Annotation Response");
      console.log(data);
      // New expected shape: SuccessResponse => { result: "success" }
      if (data.result === 'success') {
        this.setState({create_status: "Annotation created. Make a new selection to create another.", error: null})
        this.refreshRoomFileAnnotations(true);
        return;
      }

      // If validation errors are provided, surface title error when present
      if (data.data && data.data["/title"]) {
        this.setState({error: data.data["/title"]})
        return;
      }

      // Unknown response shape
      this.setState({error: "Failed to create annotation"});
  }
  clearSelectedAnnotation() {
    this.setState({ selected_annotation_id: null })
    sendDataToPDFJS({
      type: 'clear_highlights'
    });
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
        this.refreshRoomFileAnnotations(true);
      })
      .catch(() => this.setState({ tagsSaveInProgress: false }));
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

  renderAnnotations() {
    if (this.state.annotations.length === 0) {
      return <span>No annotations</span>;
    }

    let clear_selection: preact.VNode = <span></span>;
    if (this.state.selected_annotation_id !== null) {
      clear_selection = <li onClick={() => this.clearSelectedAnnotation()}>Clear selection</li>;
    }

    return (
      <ul className="annotations">
        {this.state.annotations.map((annotation, index) => {
          const tags = annotation.tags || [];
          const tagsBlock = tags.length > 0
            ? <span className="room_entity_tags">{tags.map((t: RoomTag) => <span key={t.tag_id} className="room_entity_tag_chip">{t.text}</span>)}</span>
            : null;
          return (
            <li
              key={index}
              className={`annotation ${this.state.selected_annotation_id === annotation.id ? 'selected' : ''}`}
              onClick={() => this.setState({ selected_annotation_id: annotation.id })}
            >
              <span>{annotation.title}</span>
              {tagsBlock}
              {this.state.logged_in && (
                <button type="button" className="button_standard button_chat" onClick={(e) => { e.stopPropagation(); this.openEditTags(annotation); }}>Edit tags</button>
              )}
            </li>
          );
        })}

        {clear_selection}

      </ul>
    );
  }

  render(props: AnnotationPanelProps, state: AnnotationPanelState) {

    let validToSubmit = true;

    let error_title = <span></span>
    if (this.state.error !== null) {
      error_title = <span class="error">{this.state.error}</span>
    }

    let title_length_error = <span></span>
    const titleTrimmed = this.state.title.trim();

    if (titleTrimmed.length < ANNOTATION_TITLE_MINIMUM_LENGTH) {
      validToSubmit = false;

      if (titleTrimmed.length !== 0) {
        title_length_error = <span>Title needs {ANNOTATION_TITLE_MINIMUM_LENGTH - titleTrimmed.length} more characters.</span>
      }
    }



    let add_button = <span></span>
    if (validToSubmit === true) {
      add_button = <div>
        <button type="submit" className="button_standard" onClick={() => this.addAnnotation()}>Add annotation</button>
      </div>
    }

    let annotations_block = this.renderAnnotations();

    let text_selected_box = <div>
      <span>First, select some text in the PDF.</span>
    </div>

    if (this.state.selection_data !== null) {
      let json = JSON.stringify(this.state.selection_data.highlights);
      if (json.length > 16 * 1024) {
        return <div>
          <h3>This is the text note panel.</h3>
          <div>
            <span>Too many lines selected. Please select fewer.</span>
          </div>
        </div>
      }

      text_selected_box = <div>
        <div>
          {/*<div>*/}
          {/*<label>You have selected {countWords(this.state.selection_data.text)} words</label>*/}
          {/*</div>*/}

          <label>
            Enter a title <input name="title" size={100} onInput={
            // @ts-ignore
            e => this.setState({title: e.target.value})
          }/>
            <br/>
            {error_title}
            {title_length_error}
          </label>
          {add_button}
        </div>
      </div>
    }


    // If already created, don't allow creating again.
    if (this.state.create_status !== null) {
      text_selected_box = <div>
        <div>
          <span>{this.state.create_status}</span>
        </div>
      </div>
    }


    return (
      <div className="annotation_panel_react">
        <h3>Create Annotation</h3>
        {text_selected_box}
        <h4>Current annotations</h4>
        {annotations_block}
        {this.renderEditTagsModal()}
      </div>
    );
  }
}