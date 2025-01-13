import {h, Component} from "preact";
import {humanFileSize, formatDateTime} from "./functions";
import {DateTime} from "luxon";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;



export interface RoomFilesPanelProps {
    room_id: string
}

interface RoomFile {
    id: string, //"01939c6d-42ef-706a-87d1-83313443d34b",
    normalized_name: string, // "01939c6d-42ee-71c3-a90b-e9c943ea704c.pdf",
    original_filename: string, //"sample.pdf",
    state: string, // "uploaded",
    size: number, // "18810",
    created_at: DateTime
}



interface RoomFilesPanelState {
    files: RoomFile[],
    error: string|null,
}

function getDefaultState(): RoomFilesPanelState {
    return {
        files: [],
        error: null,
    };
}



export class RoomFilesPanel extends Component<RoomFilesPanelProps, RoomFilesPanelState> {

    constructor(props: RoomFilesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshFiles();
    }

    componentWillUnmount() {
    }

    refreshFiles() {
        const endpoint = `/api/rooms/${this.props.room_id}/files`;
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
        if (data.data.files === undefined) {
            this.setState({error: "Server response did not contains 'files'."})
        }

        let files:RoomFile[] = [];
        for(let i=0; i<data.data.files.length; i++) {
            const entry = data.data.files[i]

            // @ts-ignore: any ...
            const file:RoomFile = {
                id: entry.id,
                normalized_name: entry.normalized_name,
                original_filename: entry.original_filename,
                size: entry.size,
                created_at: DateTime.fromISO(entry.created_at)
            };

            files.push(file);
        }

        this.setState({files: files})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomFile(file: RoomFile) {
        let file_url = `/rooms/${this.props.room_id}/file/${file.id}/${file.original_filename}`
        let annotate_url = `/rooms/${this.props.room_id}/file_annotate/${file.id}`

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
                <a href={annotate_url}>
                  Annotate
                </a>
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
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    <td>Date</td>
                </tr>
                {Object.values(this.state.files).
                map((roomFile: RoomFile) => this.renderRoomFile(roomFile))}
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