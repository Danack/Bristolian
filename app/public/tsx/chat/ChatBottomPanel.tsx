

import {h} from "preact";
import {useState, useEffect, useRef} from "preact/hooks";
import {use_logged_in, use_user_info} from "../store";
import {MessageEncapsulated} from "../ChatPanel";
import {registerMessageListener, unregisterListener} from "../message/message";
import {PdfSelectionType} from "../constants";

export interface ChatBottomPanelProps {
  room_id: string;
  replyingToMessage?: MessageEncapsulated | null;
  onCancelReply?: () => void;
}

export function ChatBottomPanel(props: ChatBottomPanelProps) {
  const [messageToSend, setMessageToSend] = useState("");
  const [cursorPosition, setCursorPosition] = useState(0);
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const logged_in = use_logged_in();
  const user_info = use_user_info();

  // Insert text at the current cursor position
  const insertTextAtCursor = (textToInsert: string) => {
    setMessageToSend((prev) => {
      const before = prev.substring(0, cursorPosition);
      const after = prev.substring(cursorPosition);
      const newText = before + textToInsert + after;
      setCursorPosition(cursorPosition + textToInsert.length);
      return newText;
    });
  };

  // Listen for messages to insert text at cursor position
  useEffect(() => {
    const listenerId = registerMessageListener(
      PdfSelectionType.APPEND_TO_MESSAGE_INPUT,
      (params: {text: string}) => {
        insertTextAtCursor(params.text);
      }
    );
    return () => {
      unregisterListener(listenerId);
    };
  }, [cursorPosition]);

  // Update cursor position when textarea selection changes
  const updateCursorPosition = () => {
    if (textareaRef.current) {
      setCursorPosition(textareaRef.current.selectionStart);
    }
  };

  const handleInputChange = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    setMessageToSend(target.value);
    setCursorPosition(target.selectionStart);
  };

  const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      if (messageToSend.trim()) {
        handleMessageSend();
      }
    }
  };

  const foo = (data: any) => {
    // Handle response
  };

  const handleMessageSend = () => {
    // Don't send if there's no text or only whitespace
    if (!messageToSend.trim()) {
      return;
    }

    const formData = new FormData();
    formData.append("room_id", props.room_id);
    formData.append("text", messageToSend);

    // Add reply ID if replying to a message
    if (props.replyingToMessage) {
      formData.append("message_reply_id", props.replyingToMessage.message.id.toString());
    }

    let params = {
      method: 'POST',
      body: formData
    };

    fetch('/api/chat/message', params)
      .then((response: Response) => response.json())
      .then((data: any) => {
        foo(data);
        // Clear the text input after successful send
        setMessageToSend("");
        // Cancel reply after sending
        if (props.onCancelReply) {
          props.onCancelReply();
        }
      });
  };

  let avatar_section = <div className="avatar-section">
  </div>;


  let interactive_section = <div>
    <span>You must be <a href="/login">logged in</a> to talk.</span>
  </div>

  let replying_section = <div className="reply-indicator-top"></div>;

  if (logged_in === true) {
    interactive_section = <div>
      <div className="input-row">
        <textarea
          ref={textareaRef}
          className="message-input"
          placeholder={props.replyingToMessage ? "Reply... (Enter to send)" : "Write a message... (Enter to send)"}
          onInput={handleInputChange}
          onKeyDown={handleKeyDown}
          onKeyUp={updateCursorPosition}
          onClick={updateCursorPosition}
          onSelect={updateCursorPosition}
          value={messageToSend}>
        </textarea>
        <button className="send-btn" onClick={handleMessageSend}>Send</button>
        <button className="upload-btn">Upload</button>
      </div>
    </div>;

    if (user_info.user_id && user_info.avatar_image_id) {
      avatar_section =
        <div className="avatar-section">
          <img
            className="avatar"
            src={`/users/${user_info.user_id}/avatar`}
            alt="User avatar"
          />;
        </div>
    }
  }

  // Always show replying_section with fixed height
  if (props.replyingToMessage) {
    replying_section = <div className="reply-indicator-top">
      <span>Replying to message {props.replyingToMessage.message.id}</span>
      <button className="cancel-reply-btn" onClick={props.onCancelReply}>×</button>
    </div>;
  } else {
    replying_section = <div className="reply-indicator-top"></div>;
  }

  return (
    <div className="bottom-bar">
      {avatar_section}
      <div className="interactive_section">
        {replying_section}
        {interactive_section}
      </div>
      <div className="right-section">
        Please report bugs to Danack
      </div>
    </div>
  ) ;
}
