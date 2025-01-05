import {h, Component} from "preact";
import {global} from "./globals";

import {ROOM_FILE_UPLOAD_FORM_NAME} from "./generated/constants";
import {registerMessageListener} from "./message/message";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface RoomFileUploadPanelProps {
    room_id: string
    accepted_file_extensions: string[]
}

interface RoomFileUploadPanelState {
    error: string|null,
    selectedFile: File|null,
    uploadProgress: number | null,
}

function getDefaultState(): RoomFileUploadPanelState {
    return {
        error: null,
        selectedFile: null,
        uploadProgress: null,
    };
}


export class RoomFileUploadPanel extends Component<RoomFileUploadPanelProps, RoomFileUploadPanelState> {

    constructor(props: RoomFileUploadPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);

    }

    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
    }

    restoreState(state_to_restore: object) {
    }

    handleDragEnter(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        console.log("File dragged into the area");
    }

    handleDragOver(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        console.log("File being dragged over the area");
    }

    handleDragLeave(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        console.log("File left the drop area");
    }

    handleDrop(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();

        if (event.dataTransfer && event.dataTransfer.files.length > 0) {
            const file = event.dataTransfer.files[0];
            console.log("Dropped file: ", file);
            this.setState({ selectedFile: file });

            // Clear drag data to avoid duplicate events
            event.dataTransfer.clearData();
        }
    }

    // On file select (from the pop up)
    onFileChange = (event: any) => {

        // Update the state
        this.setState({
            selectedFile: event.target.files[0],
        });
    };


    // On file upload (click the upload button)
    onFileUpload = () => {
        // Create an object of formData
        const formData = new FormData();

        if (this.state.selectedFile === null) {
            this.setState({error: "select a file to upload"})
            return;
        }

        this.setState({
            error: null,
            uploadProgress: 0
        });

        // Details of the uploaded file
        console.log("selectedFile ", this.state.selectedFile);

        // Update the formData object
        formData.append(
          ROOM_FILE_UPLOAD_FORM_NAME,
          this.state.selectedFile,
          this.state.selectedFile.name
        );

        // Request made to the backend api
        console.log("Should upload ", formData);

        let params = {
            method: 'POST',
            body: formData
        }

        let endpoint = `/api/rooms/${this.props.room_id}/file-upload`
        const xhr = new XMLHttpRequest();

        xhr.open("POST", endpoint, true);

        xhr.upload.onprogress = (event) => {
            if (event.lengthComputable) {
                const progress = Math.round((event.loaded / event.total) * 100);
                this.setState({ uploadProgress: progress });
                console.log(`Upload progress: ${progress}%`);
            }
        };

        xhr.onload = () => {
            if (xhr.status === 200) {
                console.log("File uploaded successfully");
                this.setState({ uploadProgress: null, selectedFile: null });
            } else {
                this.setState({
                    uploadProgress: null,
                    error: "Upload failed"
                });
            }
        };

        xhr.onerror = () => {
            this.setState({
                uploadProgress: null,
                error: "An error occurred during the upload"
            });
        };

        xhr.send(formData);
    };




    render(props: RoomFileUploadPanelProps, state: RoomFileUploadPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Error: {this.state.error}</div>;
        }

        let accept_string = this.props.accepted_file_extensions.join(", ");

        return (
          <div class="room_file_upload_panel_react">
              <h3>Drag a file here to upload</h3>
              <div
                class="drop-area"
                onDragEnter={(e) => this.handleDragEnter(e as DragEvent)}
                onDragOver={(e) => this.handleDragOver(e as DragEvent)}
                onDragLeave={(e) => this.handleDragLeave(e as DragEvent)}
                onDrop={(e) => this.handleDrop(e as DragEvent)}
                style={{border: "2px dashed #ccc", padding: "20px", borderRadius: "5px"}}
              >

                  <p>{state.selectedFile ? `Selected file: ${state.selectedFile.name}` : "Drop files here or click to select files."}</p>
                  <input
                    type="file"
                    accept={accept_string}
                    onChange={this.onFileChange}
                    style={{display: "block", marginTop: "10px"}}
                  />


                  <button onClick={this.onFileUpload}>Upload</button>
              </div>

              {state.uploadProgress !== null && (
                <div class="progress-bar" style={{ marginTop: "10px" }}>
                    <div
                      style={{
                          width: `${state.uploadProgress}%`,
                          backgroundColor: "#4caf50",
                          height: "10px",
                      }}
                    ></div>
                    <p>{state.uploadProgress}%</p>
                </div>
              )}


              {error_block}
          </div>
        );
    }
}







