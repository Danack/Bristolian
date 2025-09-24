import { h, Component } from "preact";
import { formatDateTimeForDB, humanFileSize } from "./functions";
import { ProcessorRunRecord, ProcessType } from "./generated/types";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface ProcessorRunRecordPanelProps {
    room_id: string;
}

interface ProcessorRunRecordPanelState {
    run_records: ProcessorRunRecord[];
    error: string | null;
    selected_task_type: ProcessType | "ALL";
    loading: boolean;
}

function getDefaultState(): ProcessorRunRecordPanelState {
    return {
        run_records: [],
        error: "Initial state",
        selected_task_type: "ALL",
        loading: false,
    };
}

export class ProcessorRunRecordPanel extends Component<
  ProcessorRunRecordPanelProps,
  ProcessorRunRecordPanelState
> {
    refresh_run_record: ReturnType<typeof setInterval>;

    constructor(props: ProcessorRunRecordPanelProps) {
        super(props);
        this.state = getDefaultState();
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
        const { selected_task_type } = this.state;
        let endpoint = `/api/log/processor_run_records`;

        if (selected_task_type !== "ALL") {
            endpoint += `?task_type=${encodeURIComponent(selected_task_type)}`;
        }

        this.setState({ loading: true });

        fetch(endpoint)
          .then((response: Response) => {
              if (response.status !== 200) {
                  throw new Error("Server failed to return an OK response.");
              }
              return response;
          })
          .then((response: Response) => response.json())
          .then((data: any) => this.processData(data))
          .catch((data: any) => this.processError(data));
    }

    processResponse(response: Response) {
        if (response.status !== 200) {
            this.setState({ error: "Server failed to return an OK response." });
            return;
        }
        let json = response.json();
        this.processData(json);
    }

    processData(data: any) {
        if (data.data.run_records === undefined) {
            this.setState({
                error: "Server response did not contain 'run_records'.",
                loading: false,
            });
            return;
        }

        let run_records_received = data.data.run_records;
        let run_records: ProcessorRunRecord[] = [];

        for (let i = 0; i < run_records_received.length; i++) {
            const entry = run_records_received[i];
            const run_record: ProcessorRunRecord = {
                id: entry.id,
                task: entry.task,
                debug_info: entry.debug_info,
                start_time: new Date(entry.start_time),
                end_time: new Date(entry.end_time),
                status: entry.status,
            };

            run_records.push(run_record);
        }

        this.setState({
            error: null,
            run_records: run_records,
            loading: false,
        });
    }

    processError(data: any) {
        console.log("something went wrong.");
        console.log(data);
        this.setState({
            error: "something went wrong: " + data.message,
            loading: false,
        });
    }

    handleTaskTypeChange = (event: Event) => {
        const value = (event.target as HTMLSelectElement).value as
          | ProcessType
          | "ALL";
        this.setState({ selected_task_type: value }, () =>
          this.refreshRunRecords()
        );
    };

    restoreState(state_to_restore: object) {}

    renderRoomLink(run_record: ProcessorRunRecord) {
        const start_time_formatted = formatDateTimeForDB(run_record.start_time);
        const end_time_formatted = formatDateTimeForDB(run_record.end_time);

        return (
          <tr key={run_record.id}>
              <td>{run_record.id}</td>
              <td>{run_record.task}</td>
              <td>{run_record.status}</td>
              <td>{start_time_formatted}</td>
              <td>{end_time_formatted}</td>
          </tr>
        );
    }

    renderRunRecords() {
        const { error, loading, run_records } = this.state;

        const allOptions = ["ALL", ...Object.values(ProcessType)];

        return (
          <div>
              <h2>Processor Run Records</h2>

              <label>
                  Filter by Task Type:{" "}
                  <select
                    value={this.state.selected_task_type}
                    onChange={this.handleTaskTypeChange}
                  >
                      {allOptions.map((taskType) => (
                        <option value={taskType} key={taskType}>
                            {taskType}
                        </option>
                      ))}
                  </select>
              </label>

              {loading ? (
                <div style="margin-top: 1em;">Refreshing…</div>
              ) : error ? (
                <div style="margin-top: 1em;">Error: {error}</div>
              ) : run_records.length === 0 ? (
                <div style="margin-top: 1em;">
                    No Processor Run Records found. This is normal on local
                    dev box.
                </div>
              ) : (
                <table style="margin-top: 1em;">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task Type</th>
                        <th>Status</th>
                        <th>Start at</th>
                        <th>Ended at</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {run_records.map((r) => this.renderRoomLink(r))}
                    </tbody>
                </table>
              )}
          </div>
        );
    }

    render(props: ProcessorRunRecordPanelProps, state: ProcessorRunRecordPanelState) {
        return (
          <div class="room_links_panel_react">{this.renderRunRecords()}</div>
        );
    }
}
