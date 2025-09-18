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
    selectedStairInfo: BristolStairInfo|null
}

function getDefaultState(): BristolStairsPanelState {
    return {
        marker_id: null,
        error: null,
        selectedStairInfo: null,
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
              this.setState({selectedStairInfo: selectedStairInfo})
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
        }));
    };

    handleStepsChange = (value: number) => {
        this.setState((prevState) => ({
            selectedStairInfo: {
                ...prevState.selectedStairInfo,
                steps: value,
            },
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
        then((data:any) =>this.processData(data)).
        catch((data:any) => this.processError(data));
    };

    processData(data:any) {
        console.log("success, presumably");
    }

    renderLoggedInStairInfo() {

        const { description, steps, stored_stair_image_file_id } =
          this.state.selectedStairInfo;

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
            onChange={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
              this.handleDescriptionChange(e.currentTarget.value)
            }
          />

          <label htmlFor="steps">Steps</label>
          <input
            id="steps"
            type="number"
            value={steps}
            onChange={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
              this.handleStepsChange(parseInt(e.currentTarget.value))
            }
          />

          <button style={{gridColumn: "2"}} onClick={() => this.handleSave()}>
            Save Changes
          </button>
        </span>
        );
    }

    renderViewOnlyStairInfo() {
        const { description, steps, stored_stair_image_file_id } = this.state.selectedStairInfo;
  
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
        let error_block = <span>&nbsp;</span>;
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