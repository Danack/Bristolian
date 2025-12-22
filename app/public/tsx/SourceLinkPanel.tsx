
import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {RoomSourceLink} from "./generated/types";
import {countWords} from "./functions";
import {SOURCELINK_TITLE_MINIMUM_LENGTH} from "./generated/constants";
import {api, GetRoomsFileSourcelinksResponse} from "./generated/api_routes";
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

export interface SourceLinkPanelProps {
  room_id: string,
  file_id: string,
  selected_sourcelink_id: null|string,
}

interface SourceLinkPanelState {
  selection_data: SelectionData|null,
  title: string,
  text: string,
  sourcelinks: RoomSourceLink[],
  selected_sourcelink_id: string|null,
  create_status: string|null,
  error: string|null,
}

function getDefaultState(props: SourceLinkPanelProps): SourceLinkPanelState {
  return {
    selection_data: null,
    title: "",
    text: "",
    sourcelinks: [],
    selected_sourcelink_id: props.selected_sourcelink_id || null,
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


export class SourceLinkPanel extends Component<SourceLinkPanelProps, SourceLinkPanelState> {

  message_listener:null|number = null;

  message_listener_deselect:null|number = null;

  // If the panel is loaded with a selected sourcelink_id, then we need
  // to remember to render it when we get the data back about sourcelinks.
  pending_sourcelink_id: null|string = null;

  constructor(props: SourceLinkPanelProps) {
    super(props);
    this.state = getDefaultState(props);
    this.pending_sourcelink_id = props.selected_sourcelink_id;
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

    this.refreshRoomFileSourcelinks();
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

  refreshRoomFileSourcelinks() {
    api.rooms.file.sourcelinks(this.props.room_id, this.props.file_id).
    then((data:GetRoomsFileSourcelinksResponse) => this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  processData(data:GetRoomsFileSourcelinksResponse) {
    if (data.data.sourcelinks === undefined) {
      this.setState({error: "Server response did not contains 'sourcelinks'."})
      return;
    }

    this.setState({sourcelinks: data.data.sourcelinks})
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

  componentDidUpdate(prevProps: SourceLinkPanelProps, prevState: SourceLinkPanelState) {
    // The whole lifecycle and interaction between this panel and
    // the iframe containing the PDF could do with some re-thinking.
    if (this.pending_sourcelink_id && this.state.sourcelinks) {
      console.log("we had pending_sourcelink_id and data is now loaded, so sending highlights");
      this.sendHighlightsToDraw();
      this.pending_sourcelink_id = null;
    }
    else if (this.state.selected_sourcelink_id !== prevState.selected_sourcelink_id) {
      this.sendHighlightsToDraw();
    }
  }

  sendHighlightsToDraw() {
    const selectedSourcelink = this.state.sourcelinks.find(
      (sourcelink) => sourcelink.id === this.state.selected_sourcelink_id
    );

    if (!selectedSourcelink) {
      return;
    }

    const selectionDataJson = selectedSourcelink ? selectedSourcelink.highlights_json : null;
    let highlights = JSON.parse(selectionDataJson)

    // console.log("sendHighlightsToDraw");

    sendDataToPDFJS({
      type: 'draw_highlights',
      highlights: highlights
    });
  }


  addSourceLink() {

    let highlights_json = JSON.stringify(this.state.selection_data.highlights);

    const formData = new FormData();

    formData.append("title", this.state.title);
    formData.append("highlights_json", highlights_json);
    formData.append("text", this.state.text);

    let url = `/api/rooms/${this.props.room_id}/source_link/${this.props.file_id}`

    let params = {
      method: 'POST',
      body: formData
    }
    fetch(url, params).
    then((response:Response) => response.json()).
    then((data:any) => this.processAddSourceLinkResponse(data));
  }

  processAddSourceLinkResponse(data: any) {
      console.log("Source link Response");
      console.log(data);
      if (data.data["/title"]) {
        this.setState({error: data.data["/title"]})
      }
      else {
        this.setState({create_status: "Source Link created. Make a new selection to create another."})
        this.refreshRoomFileSourcelinks();
      }


  }
  clearSelectedSourceLink() {
    this.setState({ selected_sourcelink_id: null })
    sendDataToPDFJS({
      type: 'clear_highlights'
    });
  }

  renderSourcelinks() {
    if (this.state.sourcelinks.length === 0) {
      return <span>No sourcelinks</span>;
    }

    let clear_selection = <span></span>;

    if(this.state.selected_sourcelink_id !== null) {
      clear_selection = <li onClick={() => this.clearSelectedSourceLink()}>Clear selection</li>
    }

    return (
      <ul className="sourcelinks">
        {this.state.sourcelinks.map((sourcelink, index) => (
          <li
            key={index}
            className={`sourcelink ${this.state.selected_sourcelink_id === sourcelink.id ? 'selected' : ''}`}
            onClick={() => this.setState({ selected_sourcelink_id: sourcelink.id })}
          >
            {sourcelink.title}
          </li>
        ))}

        {clear_selection}

      </ul>
    );
  }

  render(props: SourceLinkPanelProps, state: SourceLinkPanelState) {

    let validToSubmit = true;

    let error_title = <span></span>
    if (this.state.error !== null) {
      error_title = <span class="error">{this.state.error}</span>
    }

    let title_length_error = <span></span>

    if (this.state.title.length < SOURCELINK_TITLE_MINIMUM_LENGTH) {
      validToSubmit = false;

      if (this.state.title.length !== 0) {
        // title_length_error = <span>Minimum title length is {SOURCELINK_TITLE_MINIMUM_LENGTH} characters.</span>

        title_length_error = <span>Title needs {SOURCELINK_TITLE_MINIMUM_LENGTH - this.state.title.length} more characters.</span>
      }
    }



    let add_button = <span></span>
    if (validToSubmit === true) {
      add_button = <div>
        <button type="submit" onClick={() => this.addSourceLink()}>Add source link</button>
      </div>
    }

    let sourcelinks_block = this.renderSourcelinks();

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


    return <div class='source_link_panel_react'>
      <h3>Create Source Link</h3>

      {text_selected_box}

      <h4>Current sourcelinks</h4>
      {sourcelinks_block}
    </div>;
  }
}