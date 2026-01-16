import {h, Component} from "preact";
import {humanFileSize, formatDateTime} from "./functions";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {PdfSelectionType} from "./constants";
import {api, GetRoomsFilesResponse} from "./generated/api_routes";
import {RoomFileObjectInfo, createRoomFileObjectInfo} from "./generated/types";
import {get_logged_in, subscribe_logged_in} from "./store";

export interface RoomFilesPanelProps {
    room_id: string
}

interface RoomFilesPanelState {
    files: RoomFileObjectInfo[],
    error: string|null,
    logged_in: boolean,
}

function getDefaultState(): RoomFilesPanelState {
    return {
        files: [],
        error: null,
        logged_in: get_logged_in(),
    };
}

export class RoomFilesPanel extends Component<RoomFilesPanelProps, RoomFilesPanelState> {

    message_listener: number|null;
    unsubscribe_logged_in: (() => void)|null = null;

    constructor(props: RoomFilesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshFiles();
        this.message_listener = registerMessageListener(PdfSelectionType.ROOM_FILES_CHANGED, () => this.refreshFiles());
        
        // Subscribe to login state changes to re-render when login status changes
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
        this.message_listener = null;
        
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
    }

    refreshFiles() {
        api.rooms.files(this.props.room_id).
        then((data:GetRoomsFilesResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data:GetRoomsFilesResponse) {
        if (data.data.files === undefined) {
            this.setState({error: "Server response did not contains 'files'."})
            return;
        }

        // GetRoomsFilesResponse structure: { result: 'success', data: { files: DateToString<RoomFileObjectInfo>[] } }
        // API returns dates as strings, so we convert them to Date objects using the generated conversion function
        const files:RoomFileObjectInfo[] = data.data.files.map((file) => 
            createRoomFileObjectInfo(file)
        );

        this.setState({files: files})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    shareFile(file: RoomFileObjectInfo, file_url: string) {
        // Build the full URL including the origin
        const full_url = window.location.origin + file_url;
        const markdown_link = `[${file.original_filename}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, {text: markdown_link});
    }

    renderRoomFile(file: RoomFileObjectInfo, logged_in: boolean) {
        let file_url = `/rooms/${this.props.room_id}/file/${file.id}/${file.original_filename}`
        let annotate_url = `/rooms/${this.props.room_id}/file_annotate/${file.id}`

        let annotate_block = <a href={annotate_url}>
            Annotate
        </a>

        let file_url_lower_case = file_url.toLowerCase();
        if (file_url_lower_case.endsWith(".pdf") !== true) {
            annotate_block = <span></span>
        }

        return <tr key={file.id}>
            <td>
                <a href={file_url} target="_blank">
                    {file.original_filename}
                </a>
            </td>
            <td>
                {humanFileSize(file.size)}
            </td>
            <td>
                {formatDateTime(file.created_at)}
            </td>

            <td>
                {annotate_block}
            </td>
            {logged_in && (
                <td>
                    <button onClick={() => this.shareFile(file, file_url)} title="Share file link to chat">
                        Share
                    </button>
                </td>
            )}
        </tr>
    }

    renderFiles() {
        if (this.state.files.length === 0) {
            return <span>No files.</span>
        }

        const logged_in = this.state.logged_in;

        return <div>
            <h2>Files</h2>
            <table>
              <tbody>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    <td>Date</td>
                    <td></td>
                    {logged_in && <td></td>}
                </tr>
                {Object.values(this.state.files).
                map((roomFile: RoomFileObjectInfo) => this.renderRoomFile(roomFile, logged_in))}
              </tbody>
            </table>
        </div>
    }

    render(props: RoomFilesPanelProps, state: RoomFilesPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        let length = this.state.files.length;
        let number_block = <div>There are {length} files</div>;
        let files_block = this.renderFiles();

        return  <div class='room_files_panel_react'>
            {error_block}
            {files_block}
            {number_block}
            <button onClick={() => this.refreshFiles()}>Refresh</button>
        </div>;
    }
}