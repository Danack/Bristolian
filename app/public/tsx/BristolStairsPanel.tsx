import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {BristolStairInfo} from "./generated/types";
import {global} from "./globals";

import {BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME} from "./generated/constants";
import {PdfSelectionType} from "./constants";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface BristolStairsPanelProps {
    selected_stair_info: BristolStairInfo|null;
}

interface BristolStairsPanelState {
    error: string|null,
    selected_stair_info: BristolStairInfo|null,
    original_stair_info: BristolStairInfo|null,
    changes_made: boolean,
    editing_position: boolean,
    uploading_image: boolean,
    position_latitude: number|null;
    position_longitude: number|null;

    uploadProgress: number | null,
    selectedFile: File|null,
}

function getDefaultState(props: BristolStairsPanelProps): BristolStairsPanelState {

    return {
        error: null,
        selected_stair_info: props.selected_stair_info,
        original_stair_info: props.selected_stair_info,
        changes_made: false,
        editing_position: false,
        uploading_image: false,
        position_latitude: null,
        position_longitude: null,

        uploadProgress: null,
        selectedFile: null,
    };
}

export class BristolStairsPanel extends Component<BristolStairsPanelProps, BristolStairsPanelState> {

    message_listener_marker_clicked: number|null;
    message_listener_map_moved: number|null;

    constructor(props: BristolStairsPanelProps) {
        super(props);
        this.state = getDefaultState(props);
    }

    componentDidMount() {
        this.message_listener_map_moved = registerMessageListener(
          "STAIRS_MAP_POSITION_CHANGED",
          // @ts-ignore: not helping...
          (mapPositionInfo) => {
              this.setState({
                  // @ts-ignore: not helping...
                  position_latitude: mapPositionInfo.latitude,
                  // @ts-ignore: not helping...
                  position_longitude: mapPositionInfo.longitude,
              })
          }
        )

        this.message_listener_marker_clicked = registerMessageListener(
          "MAP_MARKER_CLICKED",
          (selected_stair_info: BristolStairInfo) => this.handleMarkerClicked(selected_stair_info)
        )

        if (this.state.selected_stair_info !== null) {
            console.log("STAIR_SELECTED_ON_LOAD should have been sent");
            sendMessage("STAIR_SELECTED_ON_LOAD", {stair_info: this.state.selected_stair_info});
        }

        console.log("stairs panel loaded.");
    }

    handleMarkerClicked(selected_stair_info: BristolStairInfo) {
        // @ts-ignore: not helping...
        this.setState({
            // @ts-ignore: not helping...
            selected_stair_info: selected_stair_info,
            // @ts-ignore: not helping...
            original_stair_info: selected_stair_info,
        })

        // Update the URL without reloading the page
        const newUrl = `/tools/bristol_stairs/${selected_stair_info.id}`;
        window.history.pushState(
          { stairId: selected_stair_info.id }, // state object
          "",                                // title (ignored by most browsers)
          newUrl                             // new URL
        );
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener_marker_clicked);
        this.message_listener_marker_clicked = null;
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    handleDescriptionChange = (value: string) => {
        this.setState((prevState) => ({
            selected_stair_info: {
                ...prevState.selected_stair_info,
                description: value,
            },
            changes_made: true
        }));
    };

    handleStepsChange = (value: number) => {
        this.setState((prevState) => ({
            selected_stair_info: {
                ...prevState.selected_stair_info,
                steps: value,
            },
            changes_made: true
        }));
    };

    handleSave() {
        // Your save logic goes here (e.g. API call)
        console.log("Saved:", this.state.selected_stair_info);

        let stair_info = this.state.selected_stair_info;

        const endpoint = `/api/bristol_stairs_update/${this.state.selected_stair_info.id}`;
        const formData = new FormData();

        formData.append("bristol_stair_info_id", stair_info.id);
        formData.append("description", stair_info.description);
        formData.append("steps", "" + stair_info.steps);

        let params = {
            method: 'POST',
            body: formData
        }

        fetch(endpoint, params).
        then((response:Response) => {
            if (response.status !== 200 && response.status !== 400) {
                throw new Error("Server failed to return an expected response.")
            }
            return response;
        }).
        then((response:Response) => response.json()).
        then((data:any) =>this.processData(data, stair_info)).
        catch((data:any) => this.processError(data));
    };

    processData(data:any, stair_info: BristolStairInfo) {
        console.log("success, presumably");
        sendMessage("STAIR_INFO_UPDATED", {stair_info: stair_info});
        this.setState({
            changes_made: false,
            original_stair_info: this.state.selected_stair_info
        })
    }

    handleCancel() {
        this.setState({
            changes_made: false,
            selected_stair_info: this.state.original_stair_info
        })
    }

    startEditingPosition = () => {
        this.setState(prevState => ({ editing_position: true }));
        sendMessage("STAIR_START_EDITING_POSITION", {stair_info: this.state.selected_stair_info});
    }

    startUploadingImage = () => {
        this.setState({ uploading_image: true });
    }

    processUpdatePosition() {

        const stair_info: BristolStairInfo = {
            ...this.state.selected_stair_info,
            latitude: this.state.position_latitude.toString(),
            longitude: this.state.position_longitude.toString(),
        };

        const endpoint = `/api/bristol_stairs_update_position/${this.state.selected_stair_info.id}`;
        const formData = new FormData();

        formData.append("bristol_stair_info_id", stair_info.id);
        formData.append("latitude", stair_info.latitude);
        formData.append("longitude", "" + stair_info.longitude);

        let params = {
            method: 'POST',
            body: formData
        }

        fetch(endpoint, params).
        then((response:Response) => {
            if (response.status !== 200) {
                throw new Error("Server failed to return an expected response.")
            }
            return response;
        }).
        then((response:Response) => response.json()).
        then((data:any) => this.handlePositionUpdated(data, stair_info)).
        catch((data:any) => this.processError(data));
    }

    handlePositionUpdated(data:any, stair_info: BristolStairInfo) {
        console.log("Position updated:", stair_info);
        sendMessage("STAIR_POSITION_UPDATED", { stair_info });
        this.setState({
            selected_stair_info: stair_info,
            original_stair_info: stair_info,
            editing_position: false
        });
    }

    renderEditingPosition() {
        const { stored_stair_image_file_id } = this.state.selected_stair_info;

        return (
          <span className="contents-wrapper">
            <img
              src={"/bristol_stairs/image/" + stored_stair_image_file_id}
              alt="some stairs"
              style={{marginBottom: "1rem"}}
            />

            <button onClick={() => this.processUpdatePosition()} >
                Update Position
              </button>
            <button onClick={() => {
                  this.setState({editing_position: false});
                  sendMessage("STAIR_CANCEL_EDITING_POSITION", {});
              }} >
              Cancel Changing Position
            </button>
            <br/>
            Position latitude {this.state.position_latitude} <br/>
            Position longitude {this.state.position_longitude} <br/>
          </span>
        );
    }

    editingDescriptionAndSteps() {
        const {changes_made, editing_position} = this.state;

        const {description, steps, stored_stair_image_file_id} =
          this.state.selected_stair_info;

        // Default (not editing position): full editing UI
        return (
          <span className="contents-wrapper">
            <img
              src={"/bristol_stairs/image/" + stored_stair_image_file_id}
              alt="some stairs"
              style={{marginBottom: "1rem"}}
            />

            <span className="form-row">
              <label htmlFor="desc">Description</label>
              <input
                id="desc"
                type="text"
                value={description}
                onInput={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                  this.handleDescriptionChange(e.currentTarget.value)
                }
              />
            </span>
            <span className="form-row">
              <label htmlFor="steps">Steps</label>
              <input
                id="steps"
                type="number"
                value={steps}
                onInput={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                  this.handleStepsChange(parseInt(e.currentTarget.value))
                }
              />
            </span>

              <button onClick={() => this.handleSave()} disabled={!changes_made}>
                Save Changes
              </button>

              <button
                onClick={() => this.handleCancel()}
                disabled={!changes_made}
              >
                Cancel Changes
              </button>

              <br/>
              <button onClick={this.startEditingPosition}>
                Edit Position
              </button>
          </span>
        );
    }


    // TODO - move to a component
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
          BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME,
          this.state.selectedFile,
          this.state.selectedFile.name
        );

        // Request made to the backend api
        console.log("Should upload ", formData);

        let params = {
            method: 'POST',
            body: formData
        }

        let endpoint = `/api/bristol_stairs_image`
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

                // If you expect JSON back from the server:
                let data;
                try {
                    const responseText = xhr.responseText; // plain string
                    data = JSON.parse(responseText);

                    console.log("Parsed JSON:", data.stair_info);
                    // Need to trigger reloading data in map, and then select the just uploaded stair_info

                    // Could just refresh page with appropriate URL?
                    let new_url = `/tools/bristol_stairs/${data.stair_info.id}`;
                    console.log("lets change the page to " + new_url);
                    window.location.href = new_url;


                } catch (e) {
                    console.error("Failed to parse JSON:", e);
                }

                this.setState({
                    uploadProgress: null,
                    selectedFile: null,
                    uploading_image: false
                });

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
    // TODO - move to a component





    renderUploadPanel() {

        //
        // let error_block = <span>&nbsp;</span>;
        // if (this.state.error != null) {
        //     error_block = <div class="error">Error: {this.state.error}</div>;
        // }

        // let accept_string = this.props.accepted_file_extensions.join(", ");

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

                  <p>{this.state.selectedFile ? `Selected file: ${this.state.selectedFile.name}` : "Drop files here or click to select files."}</p>
                  <input
                    type="file"
                    // accept={accept_string}
                    onChange={this.onFileChange}
                    style={{display: "block", marginTop: "10px"}}
                  />


                  <button onClick={this.onFileUpload}>Upload</button>
              </div>

              {/*{state.uploadProgress !== null && (*/}
              {/*  <div class="progress-bar" style={{ marginTop: "10px" }}>*/}
              {/*      <div*/}
              {/*        style={{*/}
              {/*            width: `${state.uploadProgress}%`,*/}
              {/*            backgroundColor: "#4caf50",*/}
              {/*            height: "10px",*/}
              {/*        }}*/}
              {/*      ></div>*/}
              {/*      <p>{state.uploadProgress}%</p>*/}
              {/*  </div>*/}
              {/*)}*/}
              {/*{error_block}*/}
          </div>
        );

    }

    renderLoggedInStairInfo() {
        // If editing position, show only position UI
        if (this.state.editing_position) {
            return this.renderEditingPosition();
        }

        return this.editingDescriptionAndSteps();
    }

    renderViewOnlyStairInfo() {
        const {description, steps, stored_stair_image_file_id} = this.state.selected_stair_info;

        return (
          <span className="contents-wrapper">
            <img
              src={"/bristol_stairs/image/" + stored_stair_image_file_id}
              alt="some stairs"
              style={{marginBottom: "1rem"}}
            />

            <span className="form-row">
              <label htmlFor="desc">Description</label>
              <span  id="desc">{description}</span>
            </span>

            <span className="form-row">
              <label htmlFor="steps">Steps</label>
              <span id="steps">{steps}</span>
            </span>
          </span>
        );
    }


    render(props: BristolStairsPanelProps, state: BristolStairsPanelState) {
        let stair_info = <span>Click a marker on the map to view the stairs.</span>
        let upload_button = <span></span>

        if (global.logged_in === true) {
            upload_button = <button onClick = {this.startUploadingImage}>Upload image </button>
        }

        if (this.state.uploading_image === true) {
            upload_button = this.renderUploadPanel();
        }

        else if (this.state.selected_stair_info !== null) {
            if (global.logged_in === true) {
                stair_info = this.renderLoggedInStairInfo();
            } else {
                stair_info = this.renderViewOnlyStairInfo()
            }
        }

        return <div class='bristol_stairs_panel_react'>
            {stair_info}
            {upload_button}
        </div>;
    }
}