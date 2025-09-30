import {h, Component} from "preact";
import {FileUpload} from "./components/FileUpload";
import {PdfSelectionType} from "./constants";
import {sendMessage} from "./message/message";
import {use_logged_in} from "./store";
import {ROOM_FILE_UPLOAD_FORM_NAME} from "./generated/constants";

interface RoomFileUploadPanelProps {
    room_id: string;
}

export class RoomFileUploadPanel extends Component<RoomFileUploadPanelProps> {
    onUploadSuccess = (data: any) => {
        console.log("Room file uploaded", data);
        sendMessage(PdfSelectionType.ROOM_FILES_CHANGED, {});
    };

    onUploadError = (err: string) => {
        console.error("Room file upload error:", err);
        // you can set component state here if you want to show UI feedback
    };

    render() {
      const logged_in = use_logged_in();

      if (logged_in !== true) {
        return <span></span>
      }

    const uploadUrl = `/api/rooms/${this.props.room_id}/file-upload`;

      return (
        <div class="room-file-upload-panel">
          <FileUpload
            uploadUrl={uploadUrl}
            formFieldName={ROOM_FILE_UPLOAD_FORM_NAME} // change to whatever your backend expects
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
