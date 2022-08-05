import {h, Component} from "preact";


let api_url: string = process.env.PHP_WEB_BUGS_BASE_URL;

export interface CSPViolationReportsPanelProps {
    // no properties currently
    initial_json_data: string;
}

interface CSPViolationReport {
    document_uri: string;
    referrer: string;
    blocked_uri: string;
    violated_directive: string;
    original_policy: string;
    disposition: string;
    effective_directive: string;
    line_number: string;
    script_sample: string;
    source_file: string;
    status_code: string;
}

interface CSPViolationReportsPanelState {
    current_page: number;
    csp_reports: Array<CSPViolationReport>;
}

function getDefaultState(): CSPViolationReportsPanelState {
    return {
        current_page: 0,
        csp_reports: []
    };
}

// Example data
// http://127.0.0.1/api.php?type=comment_details&comment_id=1
// {"comment_id":1,"error":"bug report is private", "bug_id": 3}
// {"comment_id":1,"email":"asda.. at bar dot com", "bug_id": 3}
// http://127.0.0.1/api.php?type=max_comment_id
// {"max_comment_id":1}

export class CommentsPanel extends Component<CSPViolationReportsPanelProps, CSPViolationReportsPanelState> {

    // maxCommentId: number|null = null;
    // maxLoadedCommentId: number|null = null;

    constructor(props: CSPViolationReportsPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);

        debugger;

        // TODO - copy the data which is passed in as props,
        // to real data.

        // this.fetchMaxCommentData();
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
    //
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
        return <div key={index}>

           document_uri : {csp_report.document_uri}
           referrer : {csp_report.referrer}
           blocked_uri : {csp_report.blocked_uri}
           violated_directive : {csp_report.violated_directive}
           original_policy : {csp_report.original_policy}
           disposition : {csp_report.disposition}
           effective_directive : {csp_report.effective_directive}
           line_number : {csp_report.line_number}
           script_sample : {csp_report.script_sample}
           source_file : {csp_report.source_file}
           status_code : {csp_report.status_code}
        </div>;
    }

    renderCSPReports() {
        if (this.state.csp_reports.length == 0) {
            return <span>No csp reports</span>
        }

        return <div>
            {this.state.csp_reports.map(this.renderCSPReport)}
        </div>;
    }

    render(props: CSPViolationReportsPanelProps, state: CSPViolationReportsPanelState) {
        let comments_block = this.renderCSPReports();
        // let error_block = <span>&nbsp;</span>;
        // {error_block}

        return  <div>
            <div>
                {comments_block}
            </div>



        </div>;
    }
}










