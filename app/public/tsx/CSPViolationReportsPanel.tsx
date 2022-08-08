import {h, Component} from "preact";


let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;
let REPORTS_SHOWN_PER_PAGE: number = 10;

export interface CSPViolationReportsPanelProps {
    // no properties currently
    initial_json_data: string;
}

interface CSPViolationReport {
    document_uri: string|null;
    referrer: string|null;
    blocked_uri: string|null;
    violated_directive: string|null;
    original_policy: string|null;
    disposition: string|null;
    effective_directive: string|null;
    line_number: string|null;
    script_sample: string|null;
    source_file: string|null;
    status_code: string|null;
}

interface CSPViolationReportsPanelState {
    current_page: number;
    csp_reports: Array<CSPViolationReport>;
    csp_report_count: number;
}

function getDefaultState(props: CSPViolationReportsPanelProps): CSPViolationReportsPanelState
{
    return {
        current_page: 0,
        csp_reports: convertJsonToCSPViolationReport(props.initial_json_data),
        csp_report_count: getCountFromInfo(props.initial_json_data)
    };
}

function convertDataToCspReport(data:any): CSPViolationReport
{
    let csp_violation_report: CSPViolationReport = {
        document_uri: data['document-uri'] ?? null,
        referrer: data['referrer'] ?? null,
        blocked_uri: data['blocked-uri'] ?? null,
        violated_directive: data['violated-directive'] ?? null,
        original_policy: data['original-policy'] ?? null,
        disposition: data['disposition'] ?? null,
        effective_directive: data['effective-directive'] ?? null,
        line_number: data['line-number'] ?? null,
        script_sample: data['script-sample'] ?? null,
        source_file: data['source-file'] ?? null,
        status_code: data['status-code'] ?? null,
    };

    return csp_violation_report;
}

function getCountFromInfo(array_of_data: any): number
{
    let count = array_of_data['count'];
    return count;
}

function convertJsonToCSPViolationReport(array_of_data: any):Array<CSPViolationReport>
{
    let csp_reports: Array<CSPViolationReport> = [];
    let reports = array_of_data['reports'];

    for (let datum in reports) {
        let csp_report = convertDataToCspReport(reports[datum])
        csp_reports.push(csp_report);
    }

    return csp_reports;
}

export class CSPViolationReportsPanel extends
  Component<CSPViolationReportsPanelProps, CSPViolationReportsPanelState> {

    constructor(props: CSPViolationReportsPanelProps) {
        super(props);
        this.state = getDefaultState(props);
    }


    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // unbind the listener
        // @ts-ignore: I don't understand that error message.
        // window.removeEventListener('popstate', this.restoreStateFn, false);
        // this.restoreStateFn = null;
    }

    renderCSPReport(csp_report: CSPViolationReport, index: number) {
        return <tr key={index}>
          <td>{csp_report.document_uri}</td>
          <td>{csp_report.referrer}</td>
          <td>{csp_report.blocked_uri}</td>
          <td>{csp_report.violated_directive}</td>
          <td>{csp_report.original_policy}</td>
          <td>{csp_report.disposition}</td>
          <td>{csp_report.effective_directive}</td>
          <td>{csp_report.line_number}</td>
          <td>{csp_report.script_sample}</td>
          <td>{csp_report.source_file}</td>
          <td>{csp_report.status_code}</td>
        </tr>;
    }

    renderCSPReportsTableBody() {
        return <tbody>{this.state.csp_reports.map(this.renderCSPReport)} </tbody>;
    }

    renderSelectorOptions(csp_report_count: number) {
        let option_array = [];
        let max_page_for_selector = csp_report_count / REPORTS_SHOWN_PER_PAGE;

        if (max_page_for_selector > REPORTS_SHOWN_PER_PAGE) {
            max_page_for_selector = REPORTS_SHOWN_PER_PAGE;
        }

        for (let i = 0; i < max_page_for_selector; i += 1) {
            option_array.push(<option value="{i}">{i + 1}</option>)
        }

        return option_array;
    }

    processCspPageData(selected_index: number, data: any) {
        let new_state = {
            current_page: selected_index,
            csp_reports: convertJsonToCSPViolationReport(data),
            csp_report_count: getCountFromInfo(data)
        };

        this.setState(new_state);
    }

    updatePageSelector(event: Event) {
        // @ts-ignore:selectedIndex will too exist.
        let selected_index = event.target.selectedIndex;

        console.log("Need to fetch page " + selected_index);
        let url = api_url + '/system/csp/reports_for_page?page=' + selected_index;
        fetch(url)
            .then(response => response.json())
            .then(data => this.processCspPageData(selected_index, data))
            .catch((error) => {
                // this.setState({last_error: "Failed to page " + selected_index});
                console.log(error)
        });
    }


    renderCSPReportsPageSelector() {

        let textblock = <span>Number of CSP reports: {this.state.csp_report_count}</span>;

        if (this.state.csp_report_count <= REPORTS_SHOWN_PER_PAGE) {
            return <div>{textblock}</div>
        }

        let selector_options = this.renderSelectorOptions(this.state.csp_report_count);

        let selector = <div>
            Page select:
            <select name="page" onChange={(e) => this.updatePageSelector(e)}>
              {selector_options}
            </select>
        </div>;

        return <div>
            {textblock}
            {selector}
        </div>;
    }

    render(props: CSPViolationReportsPanelProps, state: CSPViolationReportsPanelState) {

        if (this.state.csp_reports.length == 0) {
            return <span>No csp reports</span>
        }

        let csp_reports_table_body = this.renderCSPReportsTableBody();
        let csp_reports_selector = this.renderCSPReportsPageSelector();

        return  <div>
            <div>{csp_reports_selector}</div>

          <table>
            <thead>
              <th>document_uri</th>
              <th>referrer</th>
              <th>blocked_uri</th>
              <th>violated_directive</th>
              <th>original_policy</th>
              <th>disposition</th>
              <th>effective_directive</th>
              <th>line_number</th>
              <th>script_sample</th>
              <th>source_file</th>
              <th>status_code</th>
            </thead>

            {csp_reports_table_body}

          </table>
        </div>;
    }
}










