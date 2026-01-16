import {h, Component} from "preact";
import {humanFileSize, isUrl} from "./functions";
import {RoomLinkAddPanel} from "./RoomLinkAddPanel";
import {registerMessageListener} from "./message/message";
import {PdfSelectionType} from "./constants";
import {api, GetRoomsLinksResponse} from "./generated/api_routes";
import {RoomLink, createRoomLink} from "./generated/types";

export interface RoomLinksPanelProps {
    room_id: string
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
        api.rooms.links(this.props.room_id).
        then((data:GetRoomsLinksResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data:GetRoomsLinksResponse) {
        if (data.data.links === undefined) {
            this.setState({error: "Server response did not contains 'links'."})
            return;
        }

        // GetRoomsLinksResponse structure: { result: 'success', data: { links: DateToString<RoomLink>[] } }
        // Convert date strings to Date objects using the generated helper
        const roomLinks:RoomLink[] = data.data.links.map((link) => 
            createRoomLink(link)
        );

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
        let resolved_title = link.title || link.link_id;

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
              <span>
                {resolved_title}
              </span>
            </td>
            <td>
              <button onClick={() => this.startEditingRoomLink(link)}>Edit</button>
            </td>
        </tr>
    }

    renderLinks() {
        if (this.state.roomLinks.length === 0) {
            return <span>No links.</span>
        }

        return <table>
                {/*<thead>*/}
                {/*<tr>*/}
                {/*    <td>Links</td>*/}
                {/*</tr>*/}
                {/*</thead>*/}
                <tbody>
                {Object.values(this.state.roomLinks).
                map((roomLink: RoomLink) => this.renderRoomLink(roomLink))}
                </tbody>
            </table>

    }

    renderLinkBeingEdited() {



        let error_url = <span></span>
        let error_title = <span></span>
        let error_description = <span></span>

        // if (this.state.error_url !== null) {
        //     error_url = <span class="error">{this.state.error_url}</span>
        // }
        //
        // if (this.state.error_title !== null) {
        //     error_title = <span class="error">{this.state.error_title}</span>
        // }
        //
        // if (this.state.error_description !== null) {
        //     error_description = <span class="error">{this.state.error_description}</span>
        // }

        // Note: RoomLink doesn't have a url property, only link_id
        // The URL editing functionality is not currently implemented
        let add_button = <span><button disabled={true}>Save</button>Editing not fully implemented.</span>

        // @ts-ignore
        return <div class='room_links_add_panel_react'>
            <table>
                <tbody>
                <tr>
                    <td>
                        <label>
                            Link ID
                        </label>
                    </td>
                    <td>
                        <span>{this.state.linkBeingEdited.link_id}</span>
                        {error_url}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            Title
                        </label>
                    </td>
                    <td>
                        <input name="title"
                               size={100}
                               value={this.state.linkBeingEdited.title}
                               onChange={
                                   // @ts-ignore
                                   e => this.setState({title: e.target.value})
                               }/>
                        {error_title}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for={"description"}>Description</label>
                    </td>

                    <td>
              <textarea
                name="description"
                rows={4}
                cols={80}
                value={this.state.linkBeingEdited.description}
                onChange={
                    // @ts-ignore
                    e => this.setState({description: e.target.value})
                }/>

                        {error_description}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        {add_button}
                        <button type="submit" onClick={() => this.cancelEditingRoomLink()}>Cancel</button>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>

                </tbody>
            </table>
        </div>;



    }

    renderTableOfLinks() {

        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }
        let length = this.state.roomLinks.length;
        // let number_block = <div>There are {length} links</div>;
        let links_block = this.renderLinks();

        return <div>
            {error_block}
            {links_block}
            <button onClick={() => this.refreshLinks()}>Refresh</button>
            <RoomLinkAddPanel room_id={this.props.room_id}/>
        </div>
    }

    render(props: RoomLinksPanelProps, state: RoomLinksPanelState) {

        let content = this.renderTableOfLinks();

        if (this.state.linkBeingEdited !== null) {
            content = this.renderLinkBeingEdited();
        }

        return <div className='room_links_panel_react'>
            <h2>Links</h2>
            <div>
                {content}
            </div>
        </div>;
    }
}