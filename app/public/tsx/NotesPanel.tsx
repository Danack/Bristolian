import {h, Component} from "preact";


let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface NotesPanelProps {
    // no properties currently
}

interface Comment {
    // comment_id: number;
    // bug_id: number;
    // error: string|null;
    // email: string|null;
}

interface NotesPanelState {
    // max_comment_id: number|null;
    // Notes: Array<Comment>;
    // last_error: any;
}

function getDefaultState(/*initialControlParams: object*/): NotesPanelState {
    return {
        // max_comment_id: null,
        // Notes: [],
        // last_error: null
    };
}


export class NotesPanel extends Component<NotesPanelProps, NotesPanelState> {

    // // How often to check for new Notes in seconds
    // refresh_rate:number = 20;
    //
    // // Store the callback so it can be cancelled on manual refresh
    // fetchMaxCommentCallback:NodeJS.Timeout = null;
    // // TODO - clearTimeout(this.connectInterval);
    //
    // restoreStateFn: Function;
    //
    // maxCommentId: number|null = null;
    // maxLoadedCommentId: number|null = null;

    constructor(props: NotesPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
        // this.fetchMaxCommentData();
    }

    processMaxCommentData(data: any) {
        // if (data.max_comment_id == undefined) {
        //     console.log("Data did not return max_comment_id");
        //     return;
        // }
        // // @ts-ignore:int blah blah
        // this.setState({max_comment_id: data.max_comment_id});
        // this.maxCommentId = data.max_comment_id;
        // this.fetchNotes();
    }

    fetchNotes() {
        // // this is the first comment loaded, so just load it
        // if (this.maxLoadedCommentId == null) {
        //     this.fetchComment(this.maxCommentId);
        //     this.maxLoadedCommentId = this.maxCommentId;
        //     return;
        // }
        //
        // for (let i=this.maxLoadedCommentId; i<this.maxCommentId; i+=1) {
        //     this.fetchComment(i);
        // }
        //
        // this.maxLoadedCommentId = this.maxCommentId;
    }

    processFetchCommentError(error: any) {
        // console.log('processFetchCommentError:', error);
        // this.setState({last_error: error})
    }

    fetchComment(commentId: number) {
        // console.log("Need to fetch comment " + commentId);
        // let url = api_url + '/api.php?type=comment_details&comment_id=' + commentId;
        // fetch(url)
        //     .then(response => response.json())
        //     .then(data => this.processCommentData(commentId, data))
        //     .catch((error) => {
        //         this.setState({last_error: "Failed to fetchComment " + commentId});
        //     });
    }

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
    //     let newNotes: Array<Comment> = this.state.Notes;
    //     newNotes.unshift(comment);
    //     newNotes = newNotes.slice(0, 10);
    //
    //     this.setState({Notes: newNotes});
    // }
    //
    // fetchMaxCommentData() {
    //     let url = api_url + '/api.php?type=max_comment_id';
    //     fetch(url)
    //       .then(response => response.json())
    //       .then(data => this.processMaxCommentData(data))
    //       .catch((error) => {
    //         this.setState({last_error: "Failed to fetchMaxCommentData"});
    //       });
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
        // // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // // unbind the listener
        // // @ts-ignore: I don't understand that error message.
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

     renderComment(comment: Comment, index: number) {
         // let url = 'http://127.0.0.1:8080/bug.php?id=' + comment.bug_id
         // if (comment.email != null) {
         //     return <div key={index}>
         //     Comment {comment.comment_id} from {comment.email} for <a href={url} target="_blank">bug {comment.bug_id}</a>
         //
         //    </div>;
         // }
         //
         // return <div key={index}>
         //     {comment.comment_id} hidden as {comment.error} <a href={url} target="_blank">bug {comment.bug_id}</a>
         // </div>;
    }

    renderNotes() {
        // if (this.state.Notes.length == 0) {
        //     return <span>No Notes</span>
        // }
        //
        // return <div>
        //     {this.state.Notes.map(this.renderComment)}
        // </div>;
    }

    render(props: NotesPanelProps, state: NotesPanelState) {
        // let Notes_block = this.renderNotes();
        // let error_block = <span>&nbsp;</span>;
        // if (this.state.last_error != null) {
        //     error_block = <div class="error">Last error: {this.state.last_error}</div>
        // }
        //
        // return  <div class='Notes_panel_react'>
        //     <div>
        //         {Notes_block}
        //     </div>
        //
        //     {error_block}
        //
        // </div>;

        return <div>These are notes.</div>
    }
}










