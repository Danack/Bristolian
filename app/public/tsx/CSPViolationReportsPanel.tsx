import {h, Component} from "preact";


// let api_url: string = process.env.PHP_WEB_BUGS_BASE_URL;

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
}

function getDefaultState(props: CSPViolationReportsPanelProps): CSPViolationReportsPanelState
{
    return {
        current_page: 0,
        csp_reports: convertJsonToCSPViolationReport(props.initial_json_data)
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

function convertJsonToCSPViolationReport(array_of_data: any):Array<CSPViolationReport>
{
    let csp_reports: Array<CSPViolationReport> = [];
    for (let datum in array_of_data) {
        let csp_report = convertDataToCspReport(array_of_data[datum])
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

    // processMaxCommentData(data: any) {
    //     if (data.max_comment_id == undefined) {
    //         console.log("Data did not return max_comment_id");
    //         return;
    //     }
    //     // @ts-ignore:int blah blah
    //     this.setState({max_comment_id: data.max_comment_id});
    //     this.maxCommentId = data.max_comment_id;
    //     this.fetchComments();
    // }

    // fetchComments() {
    //     // this is the first comment loaded, so just load it
    //     if (this.maxLoadedCommentId == null) {
    //         this.fetchComment(this.maxCommentId);
    //         this.maxLoadedCommentId = this.maxCommentId;
    //         return;
    //     }
    //
    //     for (let i=this.maxLoadedCommentId; i<this.maxCommentId; i+=1) {
    //         this.fetchComment(i);
    //     }
    //
    //     this.maxLoadedCommentId = this.maxCommentId;
    // }

    // processFetchCommentError(error: any) {
    //     console.log('processFetchCommentError:', error);
    //     this.setState({last_error: error})
    // }
    //
    // fetchComment(commentId: number) {
    //     console.log("Need to fetch comment " + commentId);
    //     let url = api_url + '/api.php?type=comment_details&comment_id=' + commentId;
    //     fetch(url)
    //         .then(response => response.json())
    //         .then(data => this.processCommentData(commentId, data))
    //         .catch((error) => {
    //             this.setState({last_error: "Failed to fetchComment " + commentId});
    //         });
    // }

    // processCommentData(commentId: number, data: any) {
    //     console.log(commentId);
    //     console.log(data);
    //
    //     let comment:Comment = {
    //         comment_id: data.comment_id,
    //         bug_id: data.bug_id,
    //         error: data.error ?? null,
    //         email: data.email ?? null,
    //     };
    //
    //     if (comment.email !== null) {
    //         comment.email = comment.email.replace(' &#x64;&#111;&#x74; ', '.');
    //         comment.email = comment.email.replace(' &#x61;&#116; ', '@');
    //     }
    //
    //     let newComments: Array<Comment> = this.state.comments;
    //     newComments.unshift(comment);
    //     newComments = newComments.slice(0, 10);
    //
    //     this.setState({comments: newComments});
    // }

    // fetchMaxCommentData() {
    //     let url = api_url + '/api.php?type=max_comment_id';
    //     fetch(url)
    //         .then(response => response.json())
    //         .then(data => this.processMaxCommentData(data))
    //         .catch((error) => {
    //             this.setState({last_error: "Failed to fetchMaxCommentData"});
    //         });
    //
    //     //call check function after timeout
    //     // @ts-ignore: Timeout blah blah
    //     this.fetchMaxCommentCallback = setTimeout(
    //         () => this.fetchMaxCommentData(),
    //         this.refresh_rate * 1000
    //     );
    //     // console.log("Should refresh");
    // }

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

    restoreState(state_to_restore: object) {
        // if (state_to_restore === null) {
        //     this.setState(getDefaultState(this.props.initialControlParams));
        //     return;
        // }
        //
        // this.setState(state_to_restore);
        // this.triggerSetImageParams();
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

    render(props: CSPViolationReportsPanelProps, state: CSPViolationReportsPanelState) {

        if (this.state.csp_reports.length == 0) {
            return <span>No csp reports</span>
        }

        let csp_reports_table_body = this.renderCSPReportsTableBody();

        return  <div>
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










