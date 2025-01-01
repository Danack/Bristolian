
import {h, Component} from "preact";
import {registerMessageListener, sendMessage} from "./message/message";

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
  selected_sourcelink_ids: null|string[],
}

interface SourceLinkPanelState {
  selection_data: SelectionData|null,
  title: string,
  text: string,
  selected_sourcelink_ids: string[]
  error: string|null,
}

function getDefaultState(props: SourceLinkPanelProps): SourceLinkPanelState {
  return {
    selection_data: null,
    title: "",
    text: "",
    selected_sourcelink_ids: props.selected_sourcelink_ids || [],
    error: null,
  };
}

export class TextNotePanel extends Component<SourceLinkPanelProps, SourceLinkPanelState> {

  message_listener:null|number = null;

  constructor(props: SourceLinkPanelProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  componentDidMount() {
    this.message_listener = registerMessageListener(
      "text_selected",
      (selection_data: SelectionData) => this.receiveTextSelected(selection_data)
    );
  }

  componentWillUnmount() {
  }

  receiveTextSelected(selection_data: SelectionData) {
    this.setState({selection_data:selection_data});
  }

  processResponse(response:Response) {
    // if (response.status !== 200) {
    //   this.setState({error: "Server failed to return an OK response."})
    //   return;
    // }
    // let json = response.json();
    // this.processData(json);
  }

  processError (data:any) {
    console.log("something went wrong.");
    console.log(data)
  }

  restoreState(state_to_restore: object) {
  }

  sendRectsToDraw() {
    let iframe = document.getElementById('pdf_iframe');

    // @ts-ignore:content window does exist
    if (iframe && iframe.contentWindow) {
      // @ts-ignore:content window does exist
      iframe.contentWindow.postMessage({
        type: 'draw_rects',
        rects: this.state.selection_data.highlights
      }, '*',);
    }
  }

  // renderRectangle(rect:Highlight, i:number) {
  //   return <tr key={i}>
  //     <td>
  //       {rect.page}
  //     </td>
  //     <td>
  //       {rect.left}
  //     </td>
  //     <td>
  //       {rect.top}
  //     </td>
  //     <td>
  //       {rect.right}
  //     </td>
  //     <td>
  //       {rect.bottom}
  //     </td>
  //   </tr>
  // }
  //
  // renderRectanglesBlock() {
  //   return <table>
  //     <tr key={"header"}>
  //       <th>Page</th>
  //       <th>Left</th>
  //       <th>Top</th>
  //       <th>Right</th>
  //       <th>Bottom</th>
  //     </tr>
  //     {Object.values(this.state.selection_data.rectangles).map((rect, i) => this.renderRectangle(rect, i))}
  //   </table>
  // }

  // SpotlightText

  addSourceLink() {

    let rectangles_json = JSON.stringify(this.state.selection_data.highlights);

    const formData = new FormData();

    // formData.append("room_id", this.props.room_id);
    // formData.append("file_id", this.props.file_id);
    formData.append("title", this.state.title);
    formData.append("highlights_json", rectangles_json);
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
  }


  render(props: SourceLinkPanelProps, state: SourceLinkPanelState) {


    let validToSubmit = true;

    //   let rectangles = this.renderRectanglesBlock();
    // {rectangles}



    let error_title = <span></span>

    let add_button = <span></span>

    if (validToSubmit === true) {
      add_button = <button type="submit" onClick={() => this.addSourceLink()}>Add source link</button>
    }

    let text_selected_box = <div>
      <span>No text selected</span>
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
          <button onClick={() => this.sendRectsToDraw()}>Send back to draw</button>
        </div>

        <div>
          <label>

            Title <input name="title" size={100} onChange={
            // @ts-ignore
            e => this.setState({title: e.target.value})
          }/>
            <br/>
            {error_title}
          </label>
        </div>
      </div>

      {add_button}

    }


    return <div class='text_note_panel_react'>
      <h3>Source Link panel.</h3>

      selected_sourcelink_ids = {this.state.selected_sourcelink_ids}


      {text_selected_box}



    </div>;
  }
}