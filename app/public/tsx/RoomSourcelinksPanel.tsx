import {h, Component} from "preact";
import {RoomSourceLinkView} from "./generated/types";
import {api, GetRoomsSourcelinksResponse} from "./generated/api_routes";
import {sendMessage} from "./message/message";
import {PdfSelectionType} from "./constants";
import {get_logged_in, subscribe_logged_in} from "./store";

export interface RoomSourcelinkPanelProps {
    room_id: string
}

interface RoomSourcelinkPanelState {
    sourcelinks: RoomSourceLinkView[],
    error: string|null,
    logged_in: boolean,
}

function getDefaultState(): RoomSourcelinkPanelState {
    return {
        sourcelinks: [],
        error: null,
        logged_in: get_logged_in(),
    };
}



export class RoomSourcelinksPanel extends Component<RoomSourcelinkPanelProps, RoomSourcelinkPanelState> {

    unsubscribe_logged_in: (() => void)|null = null;

    constructor(props: RoomSourcelinkPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshRoomSourcelinks();
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
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

    shareSourcelink(sourceLink: RoomSourceLinkView, sourcelinkUrl: string) {
        const full_url = window.location.origin + sourcelinkUrl;
        const title = sourceLink.title || "Unnamed Link";
        const markdown_link = `[${title}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, {text: markdown_link});
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomSourcelink(sourceLink: RoomSourceLinkView, logged_in: boolean) {
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
              {logged_in && (
                  <td>
                      <button className="button_standard button_chat" onClick={() => this.shareSourcelink(sourceLink, sourcelinkUrl)} title="Share sourcelink to chat">
                          Post&nbsp;to&nbsp;chat
                      </button>
                  </td>
              )}
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

        const logged_in = this.state.logged_in;
        return <div>
            <h2>Sourcelinks</h2>
            <table>
              <tbody>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    {logged_in && <td></td>}
                </tr>
                {Object.values(this.state.sourcelinks).map((sourceLink: RoomSourceLinkView) => this.renderRoomSourcelink(sourceLink, logged_in))}
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
            <button className="button_standard" onClick={() => this.refreshRoomSourcelinks()}>Refresh</button>
        </div>;
    }
}