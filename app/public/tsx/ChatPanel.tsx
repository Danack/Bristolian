import { h, Component } from "preact";
import { registerMessageListener, sendMessage, unregisterListener } from "./message/message";
import { EventType } from "./events";
import {ChatBottomPanel} from "./chat/ChatBottomPanel";
import {UserChatMessage, ChatType, createUserChatMessage, SystemChatMessage} from "./generated/types";
import {localTimeSimple} from "./functions";


export interface ConnectionPanelProps {
    username: string;
    room_id: string;
    replyingToMessage?: UserChatMessage | null;
    onCancelReply?: () => void;
}

interface UserProfile {
    user_id: string;
    display_name: string | null;
    avatar_image_id: string | null;
}

export interface MessageEncapsulated {
    type: ChatType;
    message: SystemChatMessage|UserChatMessage;
}


interface ConnectionPanelState {
    connection_state: string;
    messageToSend: string;  // text box input
    lastMessageReceived: string;  // last message from websocket

    // TODO - Messages needs to be changed to an array of MessageEncapsulated
    messages: UserChatMessage[];
    userProfiles: Map<string, UserProfile>;
    messageHeights: Map<number, number>;
    replyingToMessage: UserChatMessage | null;  // message being replied to
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
            messageHeights: new Map(),
            replyingToMessage: null
        };
    }

    componentDidMount() {
        this.connect();
        this.loadExistingMessages();

        // this.message_listener = registerMessageListener(
        //   EventType.trigger_sound,
        //   (data: any) => this.sendSound(data)
        // );
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
    }

    loadExistingMessages() {
        const endpoint = `/api/chat/room_messages/${this.props.room_id}/`;

        fetch(endpoint)
            .then((response: Response) => response.json())
            .then((data: any) => {
                if (data.messages) {
                    // Convert API response format to ChatMessage objects
                    const existingMessages: UserChatMessage[] = data.messages.map((msgData: any) => {
                        return createUserChatMessage({
                            id: msgData.id,
                            user_id: msgData.user_id,
                            room_id: msgData.room_id,
                            text: msgData.text,
                            reply_message_id: msgData.reply_message_id,
                            created_at: msgData.created_at
                        });
                    });

                    // Sort messages by ID (ascending order - oldest first)
                    existingMessages.sort((a, b) => a.id - b.id);

                    // Set messages in chronological order (oldest first)
                    this.setState({
                        messages: existingMessages
                    });

                    // Fetch user profiles for all existing messages
                    const uniqueUserIds = [...new Set(existingMessages.map(msg => msg.user_id))];
                    uniqueUserIds.forEach(userId => this.fetchUserProfile(userId));
                }
            })
            .catch((err: any) => {
                console.error('Failed to load existing messages:', err);
            });
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

        if (data.type === ChatType.USER_MESSAGE) {
            let current_messages = [...this.state.messages]; // Create a copy to avoid mutating state
            let message = createUserChatMessage(data.chat_message);
            current_messages.push(message);

            // Sort messages by ID (ascending order - oldest first)
            current_messages.sort((a, b) => a.id - b.id);

            this.setState({
                messages: current_messages
            });

            // Fetch user profile for this message
            this.fetchUserProfile(message.user_id);
        }
        else if (data.type === ChatType.SYSTEM_MESSAGE) {
            // TODO - System messages need to be parse
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

    startReply = (message: UserChatMessage) => {
        this.setState({ replyingToMessage: message });
    }

    cancelReply = () => {
        this.setState({ replyingToMessage: null });
    }

    // Group messages from the same user within 600 seconds
    groupMessages(): UserChatMessage[][] {
        const groups: UserChatMessage[][] = [];
        let currentGroup: UserChatMessage[] = [];

        for (let i = 0; i < this.state.messages.length; i++) {
            const message = this.state.messages[i];
            
            if (currentGroup.length === 0) {
                // Start a new group
                currentGroup.push(message);
            } else {
                const lastMessage = currentGroup[currentGroup.length - 1];
                const timeDiff = Math.abs(new Date(message.created_at).getTime() - new Date(lastMessage.created_at).getTime()) / 1000;
                
                // Check if same user and within 600 seconds
                if (message.user_id === lastMessage.user_id && timeDiff <= 600) {
                    currentGroup.push(message);
                } else {
                    // Different user or time gap too large, start new group
                    groups.push(currentGroup);
                    currentGroup = [message];
                }
            }
        }
        
        // Add the last group if it has messages
        if (currentGroup.length > 0) {
            groups.push(currentGroup);
        }
        
        return groups;
    }

    renderChatMessage(message: UserChatMessage, index: number, isFirstInGroup: boolean) {
        const userProfile = this.state.userProfiles.get(message.user_id);
        const shortUserId = this.getShortUserId(message.user_id);
        const displayName = userProfile?.display_name || shortUserId;
        const messageHeight = this.state.messageHeights.get(index);
        const isCompact = messageHeight !== undefined && messageHeight < 40;
        
        const profileLinkClass = isCompact ? "user-profile-link compact" : "user-profile-link";
        const avatarClass = isCompact ? "compact" : "";
        
        return <div className="message_row" key={index} ref={this.setMessageRef(index)}>
            {isFirstInGroup ? (
                <div className="user_signature">
                    {isCompact ? (
                        <a href={`/users/${message.user_id}/profile`} className={profileLinkClass}>
                            <span className="user-display-name">{displayName}</span>
                            {userProfile?.avatar_image_id && (
                                <img
                                    src={`/users/${message.user_id}/avatar`}
                                    alt="User avatar"
                                    className={avatarClass}
                                />
                            )}
                        </a>
                    ) : (
                        <a href={`/users/${message.user_id}/profile`} className={profileLinkClass}>
                            {userProfile?.avatar_image_id && (
                                <img
                                    src={`/users/${message.user_id}/avatar`}
                                    alt="User avatar"
                                />
                            )}
                            <span className="user-display-name">{displayName}</span>
                        </a>
                    )}
                </div>
            ) : (
                <div className="user_signature"></div>
            )}
            <div className="message_content">
                <div className="messages">{message.text}</div>
                <span className="timestamp">{localTimeSimple(message.created_at)}</span>
            </div>
            <div className="message_reply_area">
                <button
                    className="unstyled reply_button"
                    onClick={() => this.startReply(message)}
                    title="Reply to this message"
                >
                    ⤶
                </button>
            </div>
        </div>;
    }

    renderCommentsBlock() {
        const messageGroups = this.groupMessages();
        let messageIndex = 0;
        
        return <div>
            {messageGroups.map((group, groupIndex) => (
                <div className="message_group" key={groupIndex}>
                    {group.map((message, indexInGroup) => {
                        const msgElement = this.renderChatMessage(message, messageIndex, indexInGroup === 0);
                        messageIndex++;
                        return msgElement;
                    })}
                </div>
            ))}
        </div>;
    }

    render() {
        let comments_block = this.renderCommentsBlock();
        return (
          <div>
              <div>
                  {comments_block}
              </div>

              <ChatBottomPanel
                room_id={this.props.room_id}
                replyingToMessage={null}
                onCancelReply={this.cancelReply}
              />
          </div>
        );
    }
}
