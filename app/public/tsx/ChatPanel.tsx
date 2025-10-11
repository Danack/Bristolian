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

interface UserProfile {
    user_id: string;
    display_name: string | null;
    avatar_image_id: string | null;
}

interface ConnectionPanelState {
    connection_state: string;
    messageToSend: string;  // text box input
    lastMessageReceived: string;  // last message from websocket
    messages: ChatMessage[];
    userProfiles: Map<string, UserProfile>;
    messageHeights: Map<number, number>;
}

export class ChatPanel extends Component<ConnectionPanelProps, ConnectionPanelState> {

    connectInterval: number = null;
    timeout: number = 250; // Initial timeout duration
    ws: WebSocket | null = null;
    message_listener: number = 0;
    messageRefs: Map<number, HTMLDivElement> = new Map();

    constructor(props: ConnectionPanelProps) {
        super(props);

        this.state = {
            connection_state: "Init",
            messageToSend: "",
            lastMessageReceived: "",
            messages: [],
            userProfiles: new Map(),
            messageHeights: new Map()
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

    fetchUserProfile(userId: string) {
        // Don't fetch if we already have it
        if (this.state.userProfiles.has(userId)) {
            return;
        }

        fetch(`/api/users/${userId}`)
            .then((response: Response) => response.json())
            .then((userInfo: UserProfile) => {
                const newProfiles = new Map(this.state.userProfiles);
                newProfiles.set(userId, userInfo);
                this.setState({ userProfiles: newProfiles });
            })
            .catch((err: any) => {
                console.error('Failed to fetch user profile:', err);
            });
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
            
            // Fetch user profile for this message
            this.fetchUserProfile(message.user_id);
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

    setMessageRef = (index: number) => (el: HTMLDivElement | null) => {
        if (el) {
            this.messageRefs.set(index, el);
            // Use requestAnimationFrame to ensure layout has settled
            requestAnimationFrame(() => {
                const height = el.offsetHeight;
                if (this.state.messageHeights.get(index) !== height) {
                    const newHeights = new Map(this.state.messageHeights);
                    newHeights.set(index, height);
                    this.setState({ messageHeights: newHeights });
                }
            });
        }
    };

    getShortUserId(userId: string): string {
        const parts = userId.split('-');
        return parts[parts.length - 1];
    }

    renderChatMessage(message: ChatMessage, index: number) {
        const userProfile = this.state.userProfiles.get(message.user_id);
        const shortUserId = this.getShortUserId(message.user_id);
        const displayName = userProfile?.display_name || shortUserId;
        const messageHeight = this.state.messageHeights.get(index);
        const isCompact = messageHeight !== undefined && messageHeight < 40;
        
        // Compact layout for short messages (< 40px)
        const compactLayout = (
            <a 
                href={`/users/${message.user_id}/profile`}
                style="display: flex; flex-direction: row; align-items: center; justify-content: flex-end; text-decoration: none; gap: 4px; color: inherit;"
            >
                <span style="font-size: 0.75rem; line-height: 1;">{displayName}</span>
                {userProfile?.avatar_image_id && (
                    <img 
                        src={`/users/${message.user_id}/avatar`} 
                        alt="User avatar"
                        style="height: 20px; width: auto; display: block;"
                    />
                )}
            </a>
        );
        
        // Normal layout for taller messages
        const normalLayout = (
            <a 
                href={`/users/${message.user_id}/profile`}
                style="display: flex; flex-direction: column; align-items: flex-end; text-decoration: none; gap: 4px; color: inherit;"
            >
                {userProfile?.avatar_image_id && (
                    <img 
                        src={`/users/${message.user_id}/avatar`} 
                        alt="User avatar"
                        style="height: 32px; width: auto; display: block;"
                    />
                )}
                <span style="font-size: 0.75rem; line-height: 1;">{displayName}</span>
            </a>
        );
        
        return <div className="message_row" key={index} ref={this.setMessageRef(index)}>
            <div className="user_signature">
                {isCompact ? compactLayout : normalLayout}
            </div>
            <div className="message_content">
                <div className="messages">{message.text}</div>
                <span className="timestamp">{localTimeSimple(message.created_at)}</span>
            </div>
        </div>;
    }

    renderCommentsBlock() {
        return <div>{this.state.messages.map((msg, idx) => this.renderChatMessage(msg, idx))} </div>;
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
