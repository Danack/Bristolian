import {h, Component} from "preact";
import {humanFileSize, formatDateTime} from "./functions";
import {registerMessageListener, sendMessage, unregisterListener} from "./message/message";
import {PdfSelectionType} from "./constants";
import {api, GetRoomsFilesResponse} from "./generated/api_routes";
import {StoredFile, createStoredFile} from "./generated/types";

export interface RoomFilesPanelProps {
    room_id: string
}

interface RoomFilesPanelState {
    files: StoredFile[],
    error: string|null,
}

function getDefaultState(): RoomFilesPanelState {
    return {
        files: [],
        error: null,
    };
}

export class RoomFilesPanel extends Component<RoomFilesPanelProps, RoomFilesPanelState> {

    message_listener: number|null;

    constructor(props: RoomFilesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshFiles();
        this.message_listener = registerMessageListener(PdfSelectionType.ROOM_FILES_CHANGED, () => this.refreshFiles())
    }

    componentWillUnmount() {
        unregisterListener(this.message_listener);
        this.message_listener = null;
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

        // GetRoomsFilesResponse structure: { result: 'success', data: { files: DateToString<StoredFile>[] } }
        // API returns dates as strings, so we convert them to Date objects using the generated conversion function
        const files:StoredFile[] = data.data.files.map((file) => 
            createStoredFile(file)
        );

        this.setState({files: files})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    renderRoomFile(file: StoredFile) {
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
        </tr>
    }

    renderFiles() {
        if (this.state.files.length === 0) {
            return <span>No files.</span>
        }

        return <div>
            <h2>Files</h2>
            <table>
              <tbody>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    <td>Date</td>
                </tr>
                {Object.values(this.state.files).
                map((roomFile: StoredFile) => this.renderRoomFile(roomFile))}
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