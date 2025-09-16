import {h, Component} from "preact";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {BristolStairInfo} from "./generated/types";

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

    restoreState(state_to_restore: object) {
    }

    render(props: BristolStairsPanelProps, state: BristolStairsPanelState) {
        let error_block = <span>&nbsp;</span>;
        let stair_info = <span>Stair selected is null</span>

        if (this.state.selectedStairInfo !== null) {
            stair_info = <span class='image-wrapper'>
                Description:{this.state.selectedStairInfo.description} <br/>
                Steps: {this.state.selectedStairInfo.steps} <br/>
                <img src='/api/bristol_stairs/image/{this.state.selectedStairInfo.id}' alt='some stairs'/><br/>
            </span>
        }

        return <div class='bristol_stairs_panel_react'>
            {stair_info}
        </div>;
    }
}