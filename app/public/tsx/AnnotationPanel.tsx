
import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {RoomAnnotationView} from "./generated/types";
import {countWords} from "./functions";
import {ANNOTATION_TITLE_MINIMUM_LENGTH} from "./generated/constants";
import {api, GetRoomsFileAnnotationsResponse} from "./generated/api_routes";
import {PdfSelectionType} from "./constants";

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
  if (event.data && event.data.type === PdfSelectionType.TEXT_SELECTED){ // "selectionData") {
    const message: SelectionMessage = event.data;
    // console.log("Received selection data:", message.selection_data);
    sendMessage("text_selected", message.selection_data);
    return;
  }

  if (event.data && event.data.type === PdfSelectionType.TEXT_DESELECTED){ // "selectionData") {
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
  selection_data: SelectionData|null,
  title: string,
  text: string,
  annotations: RoomAnnotationView[],
  selected_annotation_id: string|null,
  create_status: string|null,
  error: string|null,
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

    this.refreshRoomFileAnnotations();
  }

  componentWillUnmount() {
    unregisterListener(this.message_listener);
    unregisterListener(this.message_listener_deselect);
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

  refreshRoomFileAnnotations() {
    api.rooms.file.annotations(this.props.room_id, this.props.file_id).
    then((data:GetRoomsFileAnnotationsResponse) => this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  processData(data:GetRoomsFileAnnotationsResponse) {
    if (data.data.annotations === undefined) {
      this.setState({error: "Server response did not contains 'annotations'."})
      return;
    }

    this.setState({annotations: data.data.annotations})
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
      (annotation) => annotation.id === this.state.selected_annotation_id
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

    formData.append("title", this.state.title);
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
        this.refreshRoomFileAnnotations();
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

  renderAnnotations() {
    if (this.state.annotations.length === 0) {
      return <span>No annotations</span>;
    }

    let clear_selection = <span></span>;

    if(this.state.selected_annotation_id !== null) {
      clear_selection = <li onClick={() => this.clearSelectedAnnotation()}>Clear selection</li>
    }

    return (
      <ul className="annotations">
        {this.state.annotations.map((annotation, index) => (
          <li
            key={index}
            className={`annotation ${this.state.selected_annotation_id === annotation.id ? 'selected' : ''}`}
            onClick={() => this.setState({ selected_annotation_id: annotation.id })}
          >
            {annotation.title}
          </li>
        ))}

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

    if (this.state.title.length < ANNOTATION_TITLE_MINIMUM_LENGTH) {
      validToSubmit = false;

      if (this.state.title.length !== 0) {
        title_length_error = <span>Title needs {ANNOTATION_TITLE_MINIMUM_LENGTH - this.state.title.length} more characters.</span>
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


    return <div class='annotation_panel_react'>
      <h3>Create Annotation</h3>

      {text_selected_box}

      <h4>Current annotations</h4>
      {annotations_block}
    </div>;
  }
}