import {h, Component} from "preact";
import {humanFileSize} from "./functions";
import {RoomLinkAddPanel} from "./RoomLinkAddPanel";
import {registerMessageListener} from "./message/message";
import {PdfSelectionType} from "./constants";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface RoomLinksPanelProps {
    room_id: string
}

interface RoomLink {
    id: string,
    link_id: string,
    url: string,
    title: string,
    description: string,
}

interface RoomLinksPanelState {
    roomLinks: RoomLink[],
    linkBeingEdited: RoomLink|null,
    error: string|null,
}

function getDefaultState(): RoomLinksPanelState {
    return {
        roomLinks: [],
        linkBeingEdited: null,
        error: null,
    };
}


export class RoomLinksPanel extends Component<RoomLinksPanelProps, RoomLinksPanelState> {

    message_listener: number|null;

    constructor(props: RoomLinksPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshLinks();
        this.message_listener = registerMessageListener(
          PdfSelectionType.ROOM_LINKS_CHANGED,
          () => this.refreshLinks()
        );
    }

    componentWillUnmount() {
    }

    refreshLinks() {
        const endpoint = `/api/rooms/${this.props.room_id}/links`;
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
        if (data.data.links === undefined) {
            this.setState({error: "Server response did not contains 'links'."})
        }

        let links = data.data.links;

        let roomLinks:RoomLink[] = [];

        for(let i=0; i<links.length; i++) {
            const entry = links[i]

            // @ts-ignore: any ...
            const roomLink:RoomLink = {
                id: entry.id,
                url: entry.url,
                title: entry.title,
                description: entry.description,
            };

            roomLinks.push(roomLink);
        }

        this.setState({roomLinks: roomLinks})
    }
    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    restoreState(state_to_restore: object) {
    }

    startEditingRoomLink(roomLink: RoomLink) {
        this.setState({linkBeingEdited: roomLink})
    }

    cancelEditingRoomLink() {
        this.setState({linkBeingEdited: null})
    }

    renderRoomLink(link: RoomLink) {
        let resolved_title = link.url;

        if (link.title) {
            resolved_title = link.title;
        }

        // if (this.state.linkBeingEdited !== null &&
        //     this.state.linkBeingEdited.id == link.id) {
        //     return <tr key={link.id}>
        //       <td>
        //           URL: {link.url}<br/>
        //           Title:
        //       </td>
        //       <td>edit me daddy.</td>
        //
        //
        //       <td>
        //         <button onClick={() => this.cancelEditingRoomLink()}>Cancel</button>
        //         <button onClick={() => this.cancelEditingRoomLink()}>Save</button>
        //       </td>
        //     </tr>
        // }



        return <tr key={link.id}>
            <td>
                <a href={link.url} target="_blank">
                    {resolved_title}
                </a>
            </td>
            {/*<td>*/}
            {/*    <button onClick={() => this.startEditingRoomLink(link)}>Edit</button>*/}
            {/*</td>*/}
        </tr>
    }

    renderLinks() {
        if (this.state.roomLinks.length === 0) {
            return <span>No links.</span>
        }

        return <div>
            <h2>Links</h2>
            <table>
                <thead>
                <tr>
                    <td>Links</td>
                </tr>
                </thead>
                <tbody>
                {Object.values(this.state.roomLinks).
                map((roomLink: RoomLink) => this.renderRoomLink(roomLink))}
                </tbody>
            </table>
        </div>
    }



    render(props: RoomLinksPanelProps, state: RoomLinksPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        let length = this.state.roomLinks.length;
        // let number_block = <div>There are {length} links</div>;
        let links_block = this.renderLinks();

        return  <div class='room_links_panel_react'>
            <div>
                {error_block}
                {links_block}

                <button onClick={() => this.refreshLinks()}>Refresh</button>

                <RoomLinkAddPanel room_id={this.props.room_id}/>
            </div>

        </div>;
    }
}