import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {BristolStairInfo} from "./generated/types";
import {FileUpload} from "./components/FileUpload";
import {call_api, open_lightbox_if_not_mobile} from "./functions";
import {use_logged_in} from "./store";
import {BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME} from "./generated/constants";


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
        console.log("Saved:", this.state.selected_stair_info);

        const stair_info = this.state.selected_stair_info;
        const endpoint = `/api/bristol_stairs_update/${stair_info.id}`;
        const form_data = new FormData();

        form_data.append("bristol_stair_info_id", String(stair_info.id));
        form_data.append("description", stair_info.description);
        form_data.append("steps", "" + stair_info.steps);

        call_api(endpoint, form_data)
          .then((data: any) => this.processData(data, stair_info))
          .catch((err: any) => this.processError(err));
    }

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
            latitude: this.state.position_latitude,
            longitude: this.state.position_longitude,
        };

        const endpoint = `/api/bristol_stairs_update_position/${stair_info.id}`;
        const form_data = new FormData();
        form_data.append("bristol_stair_info_id", String(stair_info.id));
        form_data.append("latitude", String(stair_info.latitude ?? ''));
        form_data.append("longitude", String(stair_info.longitude ?? ''));

        call_api(endpoint, form_data)
          .then((data: any) => this.handlePositionUpdated(data, stair_info))
          .catch((err: any) => this.processError(err));
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

        let img_src = "/bristol_stairs/image/" + stored_stair_image_file_id;

        // Default (not editing position): full editing UI
        return (
          <span className="contents-wrapper">
            <img
              src={img_src}
              alt="some stairs"
              style={{marginBottom: "1rem"}}
              onClick={() => open_lightbox_if_not_mobile(img_src)}
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

    // On file select (from the pop up)
    onFileChange = (event: any) => {

        // Update the state
        this.setState({
            selectedFile: event.target.files[0],
        });
    };

    renderUploadPanel() {
        return (
          <FileUpload
            uploadUrl="/api/bristol_stairs_image"
            formFieldName={BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME}
            allowedTypes={["image/jpeg", "image/heic"]}
            allowedExtensions={["jpg", "jpeg", "heic"]}
        onUploadSuccess={(data) => {
            console.log("Upload success", data);
            const stairInfo = data?.data?.stair_info ?? data?.stair_info;
            if (stairInfo?.id) {
                const newUrl = `/tools/bristol_stairs/${stairInfo.id}`;
                window.location.href = newUrl;
            } else {
                console.error("Upload succeeded but stair_info.id missing", data);
            }
        }}
            onUploadError={(error) => {
                console.error("Upload error:", error);
            }}
            fetchGPS={true}
          />
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

        const logged_in = use_logged_in();

        if (logged_in === true) {
            upload_button = <button onClick = {this.startUploadingImage}>Upload image </button>
        }

        if (this.state.uploading_image === true) {
            upload_button = this.renderUploadPanel();
        }
        else if (this.state.selected_stair_info !== null) {
            if (logged_in === true) {
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