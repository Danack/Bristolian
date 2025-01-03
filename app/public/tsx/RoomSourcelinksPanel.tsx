import {h, Component} from "preact";
import {humanFileSize} from "./functions";
import {RoomSourceLink} from "./generated/types";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;


export interface RoomSourcelinkPanelProps {
    room_id: string
}

interface RoomSourcelinkPanelState {
    sourcelinks: RoomSourceLink[],
    error: string|null,
}

function getDefaultState(): RoomSourcelinkPanelState {
    return {
        sourcelinks: [],
        error: null,
    };
}



export class RoomSourcelinksPanel extends Component<RoomSourcelinkPanelProps, RoomSourcelinkPanelState> {

    constructor(props: RoomSourcelinkPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshRoomSourcelinks();
    }

    componentWillUnmount() {
    }

    refreshRoomSourcelinks() {
        const endpoint = `/api/rooms/${this.props.room_id}/sourcelinks`;
        fetch(endpoint).

        then((response:Response) => { if (response.status !== 200) {throw new Error("Server failed to return an OK response.") } return response;}).
        then((response:Response) => response.json()).
        then((data:any) =>this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processResponse(response:Response) {
        if (response.status !== 200) {
            this.setState({error: "Server failed to return an OK response."})
            return;
        }
        let json = response.json();
        this.processData(json);
    }

    processData(data:any) {
        if (data.data.sourcelinks === undefined) {
            this.setState({error: "Server response did not contains 'sourcelinks'."})
        }

        this.setState({sourcelinks: data.data.sourcelinks})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomSourcelink(sourceLink: RoomSourceLink) {
        const sourcelinkUrl = `/rooms/${this.props.room_id}/file/${sourceLink.file_id}/sourcelinks/${sourceLink.id}`;

        return (
          <tr key={sourceLink.id}>
              <td>
                  <a href={sourcelinkUrl} target="_blank">
                      {sourceLink.title || "Unnamed Link"}
                  </a>
              </td>
              {/*<td>*/}
              {/*    {sourceLink.text ? (*/}
              {/*      <span>{sourceLink.text.substring(0, 50)}...</span> // Display the first 50 characters*/}
              {/*    ) : (*/}
              {/*      <i>No description</i>*/}
              {/*    )}*/}
              {/*</td>*/}
              <td>
                  <a href={sourcelinkUrl + "/view"}>View</a>
              </td>
          </tr>
        );
    }

    renderSourcelinks() {
        if (this.state.sourcelinks.length === 0) {
            return <span>No sourcelinks.</span>
        }

        return <div>
            <h2>Sourcelinks</h2>
            <table>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    <td></td>
                </tr>
                {Object.values(this.state.sourcelinks).
                map((sourceLink: RoomSourceLink) => this.renderRoomSourcelink(sourceLink))}
            </table>
        </div>
    }

    render(props: RoomSourcelinkPanelProps, state: RoomSourcelinkPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        let length = this.state.sourcelinks.length;

        let number_block = <div>There are {length} sourcelinks</div>;

        let files_block = this.renderSourcelinks();

        return  <div class='room_sourcelinks_panel_react'>
            {error_block}
            {files_block}
            {number_block}
            <button onClick={() => this.refreshRoomSourcelinks()}>Refresh</button>
        </div>;
    }
}