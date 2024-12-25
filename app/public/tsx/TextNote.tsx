
import {h, Component} from "preact";
import {registerMessageListener, sendMessage} from "./message/message";

export interface SelectionPosition {
  top: number;
  left: number;
  bottom: number;
  right: number;
}

export interface SelectionData {
  position: SelectionPosition;
  text: string
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

export interface TextNotePanelProps {
  room_id: string
  file_id: string
}

interface TextNotePanelState {
  selection_data: SelectionData|null,
  error: string|null,
}

function getDefaultState(): TextNotePanelState {
  return {
    selection_data: null,
    error: null,
  };
}

export class TextNotePanel extends Component<TextNotePanelProps, TextNotePanelState> {

  message_listener:null|number = null;

  constructor(props: TextNotePanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {
    // this.refreshFiles();
    this.message_listener = registerMessageListener(
      "text_selected",
      (selection_data: SelectionData) => this.receiveTextSelected(selection_data)
    );
  }

  componentWillUnmount() {
  }

  receiveTextSelected(selection_data: SelectionData) {
    // console.log("text selected asdad");
    // console.log(message)
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

  render(props: TextNotePanelProps, state: TextNotePanelState) {

    let select_text_block = <span>No text selected</span>
    if (this.state.selection_data !== null) {
        let position = this.state.selection_data.position;

      select_text_block = <div>
        Position <br/>
         top: {position.top} <br/>
         left: {position.left}<br/>
         bottom: {position.bottom}<br/>
         right: {position.right}<br/>

        Text <br/>
        <pre>
        {this.state.selection_data.text}
        </pre>
      </div>
    }

    return  <div class='text_note_panel_react'>
      <h3>This is the text note panel.</h3>
      {select_text_block}
    </div>;
  }
}