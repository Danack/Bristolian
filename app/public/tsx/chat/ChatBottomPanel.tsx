

import {h} from "preact";
import {useState} from "preact/hooks";
import {use_logged_in, use_user_info} from "../store";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface ChatBottomPanelProps {
  room_id: string;
}

export function ChatBottomPanel(props: ChatBottomPanelProps) {
  const [messageToSend, setMessageToSend] = useState("");
  const logged_in = use_logged_in();
  const user_info = use_user_info();

  const handleInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    setMessageToSend(target.value);
  };

  const foo = (data: any) => {
    // Handle response
  };

  const handleMessageSend = () => {
    const formData = new FormData();
    formData.append("room_id", props.room_id);
    formData.append("text", messageToSend);
    // message_reply_id

    let params = {
      method: 'POST',
      body: formData
    };

    fetch('/api/chat/message', params)
      .then((response: Response) => response.json())
      .then((data: any) => foo(data));
  };

  let left_section = <div className="left-section">
    <span>You must be <a href="/login">logged in</a> to talk.</span>
  </div>;

  if (logged_in === true) {
    const avatar_element = user_info.user_id && user_info.avatar_image_id ? (
      <img
        className="avatar"
        src={`/users/${user_info.user_id}/avatar`}
        alt="User avatar"
      />
    ) : (
      <div className="avatar"></div>
    );

    left_section = <div className="left-section">
      {avatar_element}
      <textarea
        className="message-input"
        placeholder="Write a message..."
        onInput={handleInputChange}
        value={messageToSend}>
      </textarea>
      <button className="send-btn" onClick={handleMessageSend}>Send</button>
      <button className="upload-btn">Upload</button>
    </div>;
  }

  // <img src="logo.png" alt="Logo" className="logo"/>
  // <div className="links">
  //   <a href="#">Help</a> |
  //   <a href="#">FAQ</a> |
  //   <a href="#">Legal</a> |
  //   <a href="#">Privacy Policy</a> |
  //   <a href="#">Mobile</a>
  // </div>

  return (
    <div className="bottom-bar">
      {left_section}
      <div className="right-section">
        Please report bugs to Danack
      </div>
    </div>
  );
}
