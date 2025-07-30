import {h, Component} from "preact";
import {humanFileSize} from "./functions";

import {ProcessorRunRecord} from "./generated/types";


let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface ProcessorRunRecordPanelProps {
    room_id: string
}

interface ProcessorRunRecordPanelState {
    runRecords: ProcessorRunRecord[],
    error: string|null,
}

function getDefaultState(): ProcessorRunRecordPanelState {
    return {
        runRecords: [],
        error: null,
    };
}


export class ProcessorRunRecordPanel extends Component<ProcessorRunRecordPanelProps, ProcessorRunRecordPanelState> {

    constructor(props: ProcessorRunRecordPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshRunRecords();
    }

    componentWillUnmount() {
    }

    refreshRunRecords() {
        const endpoint = `/api/log/processor_run_records`;
        debugger;
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
        if (data.data.run_records === undefined) {
            this.setState({error: "Server response did not contains 'links'."})
        }

        let run_records = data.data.run_records;

        // let roomLinks:RoomLink[] = [];
        //
        // for(let i=0; i<links.length; i++) {
        //     const entry = links[i]
        //
        //     // @ts-ignore: any ...
        //     const roomLink:RoomLink = {
        //         id: entry.id,
        //         url: entry.url,
        //         title: entry.title,
        //         description: entry.description,
        //     };
        //
        //     roomLinks.push(roomLink);
        // }

        // @ts-ignore: yolo
        this.setState({run_records: run_records})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
        this.setState({error: "something went wrong"})
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomLink(runRecord: ProcessorRunRecord) {

        const options: Intl.DateTimeFormatOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        };

        const formatted = new Date().toLocaleString('en-GB', options).replace(',', '');
        <tr key={runRecord.id}>
            <td>{runRecord.id}</td>
            <td>{runRecord.processor_type}</td>
            <td>{formatted}</td>
            <td>
                <pre>{runRecord.debug_info}</pre>
            </td>
        </tr>
    }



    renderRunRecords() {

        if (this.state.runRecords.length === 0) {
            return <div>No Processor Run Records found. This is normal on local dev box.</div>;
        }

        return (<div>
          <h2>Processor Run Records</h2>
        <table>
        <thead>
              <tr>
                  <th>ID</th>
                  <th>Processor Type</th>
                  <th>Created At</th>
                  <th>Debug Info</th>
              </tr>
              </thead>
              <tbody>
              {Object.values(this.state.runRecords).
              map((roomLink: ProcessorRunRecord) => this.renderRoomLink(roomLink))}
              </tbody>
          </table>
        </div>);
    }

    render(props: ProcessorRunRecordPanelProps, state: ProcessorRunRecordPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        // let length = this.state.roomLinks.length;
        // // let number_block = <div>There are {length} links</div>;
        let run_records_block = this.renderRunRecords();

        return  <div class='room_links_panel_react'>
            {run_records_block}

            {/*    <button onClick={() => this.refreshLinks()}>Refresh</button>*/}
        </div>;
    }
}