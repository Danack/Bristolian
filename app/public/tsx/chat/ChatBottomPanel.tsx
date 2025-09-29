

import {h, Component} from "preact";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface ChatBottomPanelProps {
  // initial_json_data: string;
}

interface ChatBottomPanelState {
  // current_page: number;
}

function getDefaultState(props: ChatBottomPanelProps): ChatBottomPanelState
{
  return {
    // current_page: 0,
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

  render(props: ChatBottomPanelProps, state: ChatBottomPanelState) {

    return (
      <div class="bottom-bar">
        <div class="left-section">
          <div class="avatar"></div>
          <textarea class="message-input" placeholder="Write a message..."></textarea>
          <button class="send-btn">Send</button>
          <button class="upload-btn">Upload</button>
        </div>
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










