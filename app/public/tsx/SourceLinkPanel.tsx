
import {h, Component} from "preact";
import {registerMessageListener, sendMessage} from "./message/message";
import {RoomSourceLink} from "./generated/types";
import {countWords} from "./functions";
import {SOURCELINK_TITLE_MINIMUM_LENGTH} from "./generated/constants";

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

export function receiveSelectionMessage(event: MessageEvent) {
  if (event.data && event.data.type === "selectionData") {
    const message: SelectionMessage = event.data;
    // console.log("Received selection data:", message.selection_data);
    sendMessage("text_selected", message.selection_data);
  }
}

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

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
  // checkbox_handler: (event: any, id: string) => void
  error: string|null,
}

function getDefaultState(props: SourceLinkPanelProps): SourceLinkPanelState {
  return {
    selection_data: null,
    title: "",
    text: "",
    sourcelinks: [],
    selected_sourcelink_id: props.selected_sourcelink_id || null,
    error: null,
  };
}

export class SourceLinkPanel extends Component<SourceLinkPanelProps, SourceLinkPanelState> {

  message_listener:null|number = null;

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

    this.refreshRoomFileSourcelinks();
  }

  componentWillUnmount() {
  }

  receiveTextSelected(selection_data: SelectionData) {
    this.setState({selection_data:selection_data});
  }

  handleCheckboxChange(event: any, id: string) {
  }

  restoreState(state_to_restore: object) {
  }

  refreshRoomFileSourcelinks() {
    const endpoint = `/api/rooms/${this.props.room_id}/file/${this.props.file_id}/sourcelinks`;

    fetch(endpoint).
    then((response:Response) => { if (response.status !== 200) {throw new Error("Server failed to return an OK response.") } return response;}).
    then((response:Response) => response.json()).
    then((data:any) =>this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  processData(data:any) {
    if (data.data.sourcelinks === undefined) {
      this.setState({error: "Server response did not contains 'sourcelinks'."})
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

    let iframe = document.getElementById('pdf_iframe');
    const selectedSourcelink = this.state.sourcelinks.find(
      (sourcelink) => sourcelink.id === this.state.selected_sourcelink_id
    );

    if (!selectedSourcelink) {
      return;
    }

    const selectionDataJson = selectedSourcelink ? selectedSourcelink.highlights_json : null;
    let highlights = JSON.parse(selectionDataJson)

    // console.log("sendHighlightsToDraw");

    // @ts-ignore:content window does exist
    if (iframe) {
      // @ts-ignore:content window does exist
      if (iframe.contentWindow) {
        // @ts-ignore:content window does exist
        iframe.contentWindow.postMessage({
          type: 'draw_highlights',
          highlights: highlights
        }, '*',);

        console.log("highlights sent");
      }
      else {
        console.log("iframe.contentWindow is null");
      }
    }
    else {
      console.log("no iframe");
    }
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
  }

  renderSourcelinks() {
    if (this.state.sourcelinks.length === 0) {
      return <span>No sourcelinks</span>;
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

        <li onClick={() => this.setState({ selected_sourcelink_id: null })}>Clear selection</li>
      </ul>
    );
  }

  render(props: SourceLinkPanelProps, state: SourceLinkPanelState) {

    let validToSubmit = true;

    let error_title = <span></span>
    if (this.state.error !== null) {
      error_title = <span class="error">{this.state.error}</span>
    }


    if (this.state.title.length < SOURCELINK_TITLE_MINIMUM_LENGTH) {
      validToSubmit = false;
    }

    let add_button = <span></span>
    if (validToSubmit === true) {
      add_button = <div>
        <button type="submit" onClick={() => this.addSourceLink()}>Add source link</button>
      </div>
    }

    let sourcelinks_block = this.renderSourcelinks();

    let text_selected_box = <div>
      <span>Select some text to create a sourcelink.</span>
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
          <div>
          <label>You have selected {countWords(this.state.selection_data.text)} words</label>
          </div>

          <label>
            Title <input name="title" size={100} onInput={
            // @ts-ignore
            e => this.setState({title: e.target.value})
          }/>
            <br/>
            {error_title}
          </label>
          {add_button}
        </div>
      </div>
    }


    return <div class='source_link_panel_react'>
      <h3>Source Link panel</h3>

      {text_selected_box}

      <h4>Current sourcelinks</h4>
      {sourcelinks_block}
    </div>;
  }
}