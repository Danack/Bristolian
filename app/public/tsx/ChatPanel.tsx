import { h, Component } from "preact";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { EventType } from "./events";
import {ChatBottomPanel} from "./chat/ChatBottomPanel";
import {ChatMessage, ChatType, createChatMessage} from "./generated/types";
import {localTimeSimple} from "./functions";


export interface ConnectionPanelProps {
    username: string;
    room_id: string;
}

interface ConnectionPanelState {
    connection_state: string;
    messageToSend: string;  // text box input
    lastMessageReceived: string;  // last message from websocket
    messages: ChatMessage[]
}

export class ChatPanel extends Component<ConnectionPanelProps, ConnectionPanelState> {

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
            messages: [
                // {
                //     id: 54321,
                //     user_id: "example_user_1",
                //     room_id: props.room_id,
                //     text: "Hello world!",
                //     message_reply_id: 0,
                //     created_at: new Date()
                // },
                // {
                //     id: 54322,
                //     user_id: "example_user_2",
                //     room_id: props.room_id,
                //     text: "Second message.",
                //     message_reply_id: 54321,
                //     created_at: new Date()
                // },
            ]
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

        let data = JSON.parse(messageEvent.data);

        if (data.type === ChatType.MESSAGE) {
            let current_messages = this.state.messages;

            let message = createChatMessage(data.chat_message);

            current_messages.push(message);

            this.setState({
                messages: current_messages
            });
        }
        else if (data.type === undefined) {
            console.error("type not set in message. Something is wrong with the server.");
        }
        else {
            console.log(`Unsupported message of type ${data.type}`)
        }
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

    renderChatMessage(message: ChatMessage, index: number) {
        return <div className="message_row" key={index}>
            <div className="user_signature">
                {message.user_id}
            </div>
            <div className="message_content">
                <div className="messages">Text is {message.text}</div>
                <span className="timestamp">{localTimeSimple(message.created_at)}</span>
            </div>
        </div>;
    }

    renderCommentsBlock() {
        return <div>{this.state.messages.map(this.renderChatMessage)} </div>;
    }

    render() {
        let comments_block = this.renderCommentsBlock();
        return (
          <div>
              <div>
                  {comments_block}
              </div>

              <ChatBottomPanel room_id={this.props.room_id}/>
          </div>
        );
    }
}
