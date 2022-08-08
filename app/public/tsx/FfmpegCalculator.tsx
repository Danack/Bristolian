
import {h, Component} from "preact";

// let api_url: string = process.env.PHP_WEB_BUGS_BASE_URL;

export interface FfmpegCalculatorPanelProps {
    // no properties currently
}

// interface Comment {
//     comment_id: number;
//     bug_id: number;
//     error: string|null;
//     email: string|null;
// }



interface FfmpegCalculatorPanelState {
    // max_comment_id: number|null;
    // comments: Array<Comment>;
    // last_error: any;
}

function getDefaultState(/*initialControlParams: object*/): FfmpegCalculatorPanelState {
    return {
        max_comment_id: null,
        comments: [],
        last_error: null
    };
}



export class FfmpegCalculatorPanel extends
    Component<FfmpegCalculatorPanelProps, FfmpegCalculatorPanelState> {

    // How often to check for new comments in seconds
    // refresh_rate:number = 20;

    // Store the callback so it can be cancelled on manual refresh
    // fetchMaxCommentCallback:NodeJS.Timeout = null;
    // TODO - clearTimeout(this.connectInterval);

    // restoreStateFn: Function;

    // maxCommentId: number|null = null;
    // maxLoadedCommentId: number|null = null;

    constructor(props: FfmpegCalculatorPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }


    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // unbind the listener
        // @ts-ignore: I don't understand that error message.
        // window.removeEventListener('popstate', this.restoreStateFn, false);
        // this.restoreStateFn = null;
    }



    FfmpegCalculatorPanelState

    render(props: FfmpegCalculatorPanelProps, state: FfmpegCalculatorPanelState) {
        // let comments_block = this.renderComments();

        let command_text = "ffmpeg -i output_clip.mp4 -vf \"unsharp=luma_msize_x=5:luma_msize_y=5:luma_amount=1.0:chroma_amount=0.5,cas,eq=gamma=0.8:saturation=1.1:brightness=0.1:contrast=1.1\" -vcodec h264_nvenc -c:v libx265 -crf 20 -an output_clip_adjusted_1.mp4\n"

        return  <div class='ffmpeg_calculator_panel_react'>
            <div>
                {command_text}
            </div>
        </div>;
    }
}

// ffmpeg -i output_clip.mp4 -vf "unsharp=luma_msize_x=5:luma_msize_y=5:luma_amount=1.0:chroma_amount=0.5,cas,eq=gamma=0.8:saturation=1.1:brightness=0.1:contrast=1.1" -vcodec h264_nvenc -c:v libx265 -crf 20 -an output_clip_adjusted_1.mp4
