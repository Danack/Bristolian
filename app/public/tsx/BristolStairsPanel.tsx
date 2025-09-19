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
    changes_made: boolean

}

function getDefaultState(): BristolStairsPanelState {
    return {
        marker_id: null,
        error: null,
        selectedStairInfo: null,
        original_stair_info: null,
        changes_made: false,
    };
}

export class BristolStairsPanel extends Component<BristolStairsPanelProps, BristolStairsPanelState> {

    message_listener: number|null;

    constructor(props: BristolStairsPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        // this.refreshFiles();
        this.message_listener = registerMessageListener(
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
        unregisterListener(this.message_listener);
        this.message_listener = null;
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    // restoreState(state_to_restore: object) {
    // }

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

    renderLoggedInStairInfo() {
        const { description, steps, stored_stair_image_file_id } =
          this.state.selectedStairInfo;
        const { changes_made } = this.state;

        // Editable version
        return (
          <span className="image-wrapper">
      <img
        src={"/bristol_stairs/image/" + stored_stair_image_file_id}
        alt="some stairs"
        style={{gridColumn: "1 / span 2", marginBottom: "1rem"}}
      />

      <label htmlFor="desc">Description</label>
      <input
        id="desc"
        type="text"
        value={description}
        onInput={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
          this.handleDescriptionChange(e.currentTarget.value)
        }
      />

      <label htmlFor="steps">Steps</label>
      <input
        id="steps"
        type="number"
        value={steps}
        onInput={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
          this.handleStepsChange(parseInt(e.currentTarget.value))
        }
      />

      <div className="button-row">
        <button onClick={() => this.handleSave()} disabled={!changes_made}>
          Save Changes
        </button>

          {changes_made && (
            <button onClick={() => this.handleCancel()}>
                Cancel Changes
            </button>
          )}
      </div>
      </span>

        );
    }

    renderViewOnlyStairInfo() {
        const {description, steps, stored_stair_image_file_id} = this.state.selectedStairInfo;

        return (
          <span className="image-wrapper">
            <img
              src={"/bristol_stairs/image/" + stored_stair_image_file_id}
              alt="some stairs"
              style={{ gridColumn: "1 / span 2", marginBottom: "1rem" }}
            />

            <label>Description</label>
            <span>{description}</span>

            <label>Steps</label>
            <span>{steps}</span>
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