import {h, Component} from "preact";
import {RoomSourceLinkView} from "./generated/types";
import {api, GetRoomsSourcelinksResponse} from "./generated/api_routes";


export interface RoomSourcelinkPanelProps {
    room_id: string
}

interface RoomSourcelinkPanelState {
    sourcelinks: RoomSourceLinkView[],
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
        api.rooms.sourcelinks(this.props.room_id).
        then((data:GetRoomsSourcelinksResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data:GetRoomsSourcelinksResponse) {
        if (data.data.sourcelinks === undefined) {
            this.setState({error: "Server response did not contains 'sourcelinks'."})
            return;
        }

        this.setState({sourcelinks: data.data.sourcelinks})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomSourcelink(sourceLink: RoomSourceLinkView) {
        const sourcelinkUrl = `/rooms/${this.props.room_id}/file/${sourceLink.file_id}/sourcelinks/${sourceLink.room_sourcelink_id}/view`;

        return (
          <tr key={sourceLink.id}>
              <td>
                  <a href={sourcelinkUrl} target="_blank">
                      {sourceLink.title || "Unnamed Link"}
                  </a>
              </td>
              <td>
                  <a href={sourcelinkUrl}>View</a>
              </td>
          </tr>
        );
    }

    renderSourcelinks() {
        if (this.state.sourcelinks.length === 0) {
            return <div>
                <h2>Sourcelinks</h2>
                <span>No sourcelinks.</span>
            </div>
        }

        return <div>
            <h2>Sourcelinks</h2>
            <table>
              <tbody>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    <td></td>
                </tr>
                {Object.values(this.state.sourcelinks).map((sourceLink: RoomSourceLinkView) => this.renderRoomSourcelink(sourceLink))}
              </tbody>
            </table>
        </div>
    }

    render(props: RoomSourcelinkPanelProps, state: RoomSourcelinkPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        let length = this.state.sourcelinks.length;
        // let number_block = <div>There are {length} sourcelinks</div>;
        let files_block = this.renderSourcelinks();

        return  <div class='room_sourcelinks_panel_react'>
            {error_block}
            {files_block}
            <button onClick={() => this.refreshRoomSourcelinks()}>Refresh</button>
        </div>;
    }
}