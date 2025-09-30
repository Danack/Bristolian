import { h, Component } from "preact";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { EventType } from "./events";
import { SoundType } from "./sounds";
import {ChatBottomPanel} from "./chat/ChatBottomPanel";

export interface ConnectionPanelProps {
    username: string;
}


interface ChatMessage {
    text: string;
}

interface ConnectionPanelState {
    connection_state: string;
    messageToSend: string;  // text box input
    lastMessageReceived: string;  // last message from websocket

    messages: ChatMessage[]
}

export class CommsPanel extends Component<ConnectionPanelProps, ConnectionPanelState> {

    connectInterval: number = null;
    timeout: number = 250; // Initial timeout duration
    ws: WebSocket | null = null;
    message_listener: number = 0;

    constructor(props: ConnectionPanelProps) {
        super(props);

        this.state = {
            connection_state: "Init",
            messageToSend: "",
            lastMessageReceived: "",
            messages: []
        };
    }

    componentDidMount() {
        this.connect();

        // this.message_listener = registerMessageListener(
        //   EventType.trigger_sound,
        //   (data: any) => this.sendSound(data)
        // );
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
    }

    onMessage(messageEvent: MessageEvent) {
        console.log("Received data", messageEvent.data);

        let parsedData;
        try {
            parsedData = JSON.parse(messageEvent.data);
        } catch {
            parsedData = messageEvent.data;
        }

        // {"type":"message","message":"{\n \"message\": \"Hello there tiger.\"\n}"}
        debugger;

        let data = JSON.parse(messageEvent.data);

        if (data.type === "message") {
            let new_chat_message:ChatMessage = {text: data.text}
            let current_messages = this.state.messages;
            current_messages.push(new_chat_message)

            this.setState({
                messages: current_messages
            });
        }

        // Update last message received in state
        this.setState({ lastMessageReceived: messageEvent.data });

        sendMessage(EventType.received_sound, parsedData);
    }

    onClose(e: any) {
        console.log(`Socket closed. Reconnect in ${Math.min(10000 / 1000, (this.timeout + this.timeout) / 1000)} seconds.`, e.reason);
        this.timeout = this.timeout + this.timeout;

        // @ts-ignore
        this.connectInterval = setTimeout(this.check, Math.min(10000, this.timeout));
    }

    onError(err: any) {
        console.log("Socket encountered error: ", err, "Closing socket");
        this.setState({ connection_state: "Errored" });
        this.ws?.close();
    }

    onOpen() {
        console.log("connected websocket main component");
        this.setState({ connection_state: "Open" });
        this.timeout = 250;
        clearTimeout(this.connectInterval);
    }

    connect = () => {
        this.ws = new WebSocket("ws://local.chat.bristolian.org:8015/chat");
        this.ws.onopen = () => this.onOpen();
        this.ws.onmessage = (messageEvent: MessageEvent) => this.onMessage(messageEvent);
        this.ws.onclose = e => this.onClose(e);
        this.ws.onerror = err => this.onError(err);
    }

    check = () => {
        if (!this.ws || this.ws.readyState === WebSocket.CLOSED) {
            this.connect();
        }
    }

    handleInputChange = (e: Event) => {
        const target = e.target as HTMLInputElement;
        this.setState({ messageToSend: target.value });
    }

    handleSendMessage = () => {
        if (this.ws && this.state.messageToSend.trim() !== "") {
            const msg = {
                username: this.props.username,
                message: this.state.messageToSend
            };
            this.ws.send(JSON.stringify(msg));
            this.setState({ messageToSend: "" });
        }
    }



    renderChatMessage(message: ChatMessage, index: number) {
        return <tr key={index}>
            <td>Text is {message.text}</td>
        </tr>;
    }

    renderCommentsBlock() {
        return <table><tbody>{this.state.messages.map(this.renderChatMessage)} </tbody></table>;
    }


    render() {
        let comments_block = this.renderCommentsBlock();
        return (
          <div>
              <div>
                  {comments_block}
              </div>

              <div style={{marginTop: "10px"}}>
                  <input
                    type="text"
                    value={this.state.messageToSend}
                    onInput={this.handleInputChange}
                    placeholder="Type a message"
                  />
                  <button onClick={this.handleSendMessage}>Send</button>
              </div>

              <div style={{marginTop: "10px"}}>
                  Last message received: {this.state.lastMessageReceived}
              </div>

              <ChatBottomPanel/>
          </div>
        );
    }
}
