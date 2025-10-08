

import {h, Component} from "preact";
import {use_logged_in} from "../store";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

type CallbackFunction = () => void;

export interface ChatBottomPanelProps {
  // initial_json_data: string;
  // onInput:

  room_id: string;

}

interface ChatBottomPanelState {

  messageToSend: string;  // text box input
}

function getDefaultState(props: ChatBottomPanelProps): ChatBottomPanelState
{
  return {
    // current_page: 0,
    messageToSend: ""
  };
}

export class ChatBottomPanel extends Component<ChatBottomPanelProps, ChatBottomPanelState> {

  constructor(props: ChatBottomPanelProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  componentDidMount() {
    // this.restoreStateFn = (event:any) => this.restoreState(event.state);
    // @ts-ignore: I don't understand that error message.
    // window.addEventListener('popstate', this.restoreStateFn);
  }

  componentWillUnmount() {
    // unbind the listener
    // @ts-ignore: I don't understand that error message.
    // window.removeEventListener('popstate', this.restoreStateFn, false);
    // this.restoreStateFn = null;
  }

  handleInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    this.setState({ messageToSend: target.value });
  }

  foo (data:any) {

  }


  handleMessageSend = () => {

    const formData = new FormData();
    formData.append("room_id", this.props.room_id);
    formData.append("text", this.state.messageToSend);
    // message_reply_id

    let params = {
      method: 'POST',
      body: formData
    }

    fetch('/api/chat/message', params).
    then((response:Response) => response.json()).
    then((data:any) => this.foo(data));
  }






  render(props: ChatBottomPanelProps, state: ChatBottomPanelState) {

    const logged_in = use_logged_in();

    let left_section = <div className="left-section">
      <span>You must be <a href="/login">logged in</a> to talk.</span>
    </div>

      if (logged_in === true) {
      left_section = <div className="left-section">
        <div className="avatar"></div>
        <textarea
          className="message-input"
          placeholder="Write a message..."
          onInput={this.handleInputChange}>
          </textarea>
        <button className="send-btn" onClick={() => this.handleMessageSend()}>Send</button>
        <button className="upload-btn">Upload</button>
      </div>
    }


      return (
      <div class="bottom-bar">
        {left_section}
        <div class="right-section">
          <img src="logo.png" alt="Logo" class="logo"/>
          <div class="links">
            <a href="#">Help</a> |
            <a href="#">FAQ</a> |
            <a href="#">Legal</a> |
            <a href="#">Privacy Policy</a> |
            <a href="#">Mobile</a>
          </div>
        </div>
      </div>);
  }
}










