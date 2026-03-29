import {h, Component} from "preact";
import {FileUpload} from "./components/FileUpload";
import {PdfSelectionType} from "./constants";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {get_logged_in, subscribe_logged_in} from "./store";
import {ROOM_FILE_UPLOAD_FORM_NAME} from "./generated/constants";
import {getRoomFileEditingActive} from "./room_file_editing";

interface RoomFileUploadPanelProps {
    room_id: string;
}

interface RoomFileUploadPanelState {
    logged_in: boolean;
    roomFileEditingActive: boolean;
}

export class RoomFileUploadPanel extends Component<RoomFileUploadPanelProps, RoomFileUploadPanelState> {

    private unsubscribe_logged_in: (() => void) | null = null;
    private editing_message_listener: number | null = null;

    constructor(props: RoomFileUploadPanelProps) {
        super(props);
        this.state = {
            logged_in: get_logged_in(),
            roomFileEditingActive: getRoomFileEditingActive(),
        };
    }

    componentDidMount() {
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({ logged_in });
        });
        this.editing_message_listener = registerMessageListener(
            PdfSelectionType.ROOM_FILE_EDITING_ACTIVE,
            (params: object) => {
                const active = (params as { active?: boolean }).active === true;
                this.setState({ roomFileEditingActive: active });
            }
        );
    }

    componentWillUnmount() {
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
        if (this.editing_message_listener !== null) {
            unregisterListener(this.editing_message_listener);
            this.editing_message_listener = null;
        }
    }

    onUploadSuccess = (data: any) => {
        console.log("Room file uploaded", data);
        sendMessage(PdfSelectionType.ROOM_FILES_CHANGED, {});
    };

    onUploadError = (err: string) => {
        console.error("Room file upload error:", err);
    };

    render() {
        if (this.state.logged_in !== true) {
            return <span></span>;
        }
        // Hidden while a room file is being edited (ROOM_FILE_EDITING_ACTIVE + room_file_editing).
        if (this.state.roomFileEditingActive) {
            return <span></span>;
        }

        const uploadUrl = `/api/rooms/${this.props.room_id}/file-upload`;

        return (
            <div class="room-file-upload-panel">
                <FileUpload
                    uploadUrl={uploadUrl}
                    formFieldName={ROOM_FILE_UPLOAD_FORM_NAME}
                    allowedTypes={[
                        "image/jpeg",
                        "image/heic",
                        "image/png",
                        "application/pdf"
                    ]}
                    allowedExtensions={["jpg", "jpeg", "heic", "png", "pdf"]}
                    onUploadSuccess={this.onUploadSuccess}
                    onUploadError={this.onUploadError}
                    fetchGPS={false}
                />
            </div>
        );
    }
}
