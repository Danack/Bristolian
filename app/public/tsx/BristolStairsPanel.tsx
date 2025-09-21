import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {BristolStairInfo} from "./generated/types";
import {global} from "./globals";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface BristolStairsPanelProps {
}

interface BristolStairsPanelState {
    error: string|null,
    marker_id: number|null;
    selectedStairInfo: BristolStairInfo|null,
    original_stair_info: BristolStairInfo|null,
    changes_made: boolean,
    editing_position: boolean,
    position_latitude: number|null;
    position_longitude: number|null;
}

function getDefaultState(): BristolStairsPanelState {
    return {
        marker_id: null,
        error: null,
        selectedStairInfo: null,
        original_stair_info: null,
        changes_made: false,
        editing_position: false,
        position_latitude: null,
        position_longitude: null,
    };
}

export class BristolStairsPanel extends Component<BristolStairsPanelProps, BristolStairsPanelState> {

    message_listener_marker_clicked: number|null;

    message_listener_map_moved: number|null;

    constructor(props: BristolStairsPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
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
          // @ts-ignore: not helping...
          (selectedStairInfo) => {
              // @ts-ignore: not helping...
              this.setState({
                  // @ts-ignore: not helping...
                  selectedStairInfo: selectedStairInfo,
                  // @ts-ignore: not helping...
                  original_stair_info: selectedStairInfo,
              })
          }
        )

        console.log("stairs panel loaded.");
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
            selectedStairInfo: {
                ...prevState.selectedStairInfo,
                description: value,
            },
            changes_made: true
        }));
    };

    handleStepsChange = (value: number) => {
        this.setState((prevState) => ({
            selectedStairInfo: {
                ...prevState.selectedStairInfo,
                steps: value,
            },
            changes_made: true
        }));
    };

    handleSave() {
        // Your save logic goes here (e.g. API call)
        console.log("Saved:", this.state.selectedStairInfo);

        let stairInfo = this.state.selectedStairInfo;

        const endpoint = `/api/bristol_stairs_update/${this.state.selectedStairInfo.id}`;
        const formData = new FormData();

        formData.append("bristol_stair_info_id", stairInfo.id);
        formData.append("description", stairInfo.description);
        formData.append("steps", "" + stairInfo.steps);

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
        then((data:any) =>this.processData(data, stairInfo)).
        catch((data:any) => this.processError(data));
    };

    processData(data:any, stairInfo: BristolStairInfo) {
        console.log("success, presumably");
        sendMessage("STAIR_INFO_UPDATED", {stairInfo: stairInfo});
        this.setState({
            changes_made: false,
            original_stair_info: this.state.selectedStairInfo
        })
    }

    handleCancel() {
        this.setState({
            changes_made: false,
            selectedStairInfo: this.state.original_stair_info
        })
    }

    startEditingPosition = () => {
        this.setState(prevState => ({ editing_position: true }));
        sendMessage("STAIR_START_EDITING_POSITION", {stairInfo: this.state.selectedStairInfo});
    }

    processUpdatePosition() {

        const stairInfo: BristolStairInfo = {
            ...this.state.selectedStairInfo,
            latitude: this.state.position_latitude.toString(),
            longitude: this.state.position_longitude.toString(),
        };

        const endpoint = `/api/bristol_stairs_update_position/${this.state.selectedStairInfo.id}`;
        const formData = new FormData();

        formData.append("bristol_stair_info_id", stairInfo.id);
        formData.append("latitude", stairInfo.latitude);
        formData.append("longitude", "" + stairInfo.longitude);

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
        then((data:any) => this.handlePositionUpdated(data, stairInfo)).
        catch((data:any) => this.processError(data));
    }

    handlePositionUpdated(data:any, stairInfo: BristolStairInfo) {
        console.log("Position updated:", stairInfo);
        sendMessage("STAIR_POSITION_UPDATED", { stairInfo });
        this.setState({
            selectedStairInfo: stairInfo,
            original_stair_info: stairInfo,
            editing_position: false
        });
    }

    renderEditingPosition() {

        const { stored_stair_image_file_id } = this.state.selectedStairInfo;

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
          this.state.selectedStairInfo;

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

    renderLoggedInStairInfo() {
        // If editing position, show only position UI
        if (this.state.editing_position) {
            return this.renderEditingPosition();
        }

        return this.editingDescriptionAndSteps();
    }

    renderViewOnlyStairInfo() {
        const {description, steps, stored_stair_image_file_id} = this.state.selectedStairInfo;

        return (
          <span className="contents-wrapper">
            <img
              src={"/bristol_stairs/image/" + stored_stair_image_file_id}
              alt="some stairs"
              style={{marginBottom: "1rem"}}
            />

            {/*<label>Description</label>*/}
            {/*<span>{description}</span>*/}

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

        if (this.state.selectedStairInfo !== null) {
            if (global.logged_in === true) {
                stair_info = this.renderLoggedInStairInfo();
            } else {
                stair_info = this.renderViewOnlyStairInfo()
            }
        }

        return <div class='bristol_stairs_panel_react'>
            {stair_info}
        </div>;
    }
}