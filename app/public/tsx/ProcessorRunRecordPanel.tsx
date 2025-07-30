import {h, Component} from "preact";
import {humanFileSize} from "./functions";

import {ProcessorRunRecord} from "./generated/types";
import {DateTime} from "luxon";


let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface ProcessorRunRecordPanelProps {
    room_id: string
}

interface ProcessorRunRecordPanelState {
    run_records: ProcessorRunRecord[],
    error: string|null,
}

function getDefaultState(): ProcessorRunRecordPanelState {
    return {
        run_records: [],
        error: null,
    };
}


export class ProcessorRunRecordPanel extends Component<ProcessorRunRecordPanelProps, ProcessorRunRecordPanelState> {

    refresh_run_record: ReturnType<typeof setInterval>;

    constructor(props: ProcessorRunRecordPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshRunRecords();
        this.refresh_run_record = setInterval(
          () => this.refreshRunRecords(),
          5 * 1000
        );
    }

    componentWillUnmount() {
        clearInterval(this.refresh_run_record);
    }

    refreshRunRecords() {
        const endpoint = `/api/log/processor_run_records`;
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

        let run_records_received = data.data.run_records;

        let run_records:ProcessorRunRecord[] = [];

        for(let i=0; i<run_records_received.length; i++) {
            const entry = run_records_received[i]

            DateTime.fromFormat(entry.start_time, "yyyy-MM-d H:i:s");


              // @ts-ignore: any ...
            const run_record:ProcessorRunRecord = {
                id: entry.id,
                task: entry.task,
                start_time: DateTime.fromFormat(entry.start_time,'yyyy-MM-dd HH:mm:ss'),
                end_time: DateTime.fromFormat(entry.end_time,'yyyy-MM-dd HH:mm:ss'),
                status: entry.status
            };

            console.log(run_record.start_time);


            run_records.push(run_record);
        }

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

    renderRoomLink(run_record: ProcessorRunRecord) {
        const start_time_formatted = run_record.start_time.toFormat('yyyy-MM-dd HH:mm:ss');
        const end_time_formatted = run_record.end_time.toFormat('yyyy-MM-dd HH:mm:ss');

        return <tr key={run_record.id}>
            <td>{run_record.id}</td>
            <td>{run_record.task}</td>
            <td>{run_record.status}</td>
            <td>{start_time_formatted}</td>
            <td>{end_time_formatted}</td>
        </tr>
    }

    renderRunRecords() {

        if (this.state.run_records.length === 0) {
            return <div>
                <h2>Processor Run Records</h2>
                <div>No Processor Run Records found. This is normal on local dev box.</div>
            </div>;
        }

        return (<div>
          <h2>Processor Run Records</h2>
        <table>
        <thead>
              <tr>
                  <th>ID</th>
                  <th>Processor Type</th>
                  <th>Status</th>
                  <th>Start at</th>
                  <th>Ended at</th>
              </tr>
              </thead>
              <tbody>
              {Object.values(this.state.run_records).
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