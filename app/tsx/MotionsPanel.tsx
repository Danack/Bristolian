import {h, Component} from "preact";

// import {sendMessage, EventType, registerEvent, unregisterEvent} from "./events";
// import { startMessageProcessing } from "danack-message";

interface Choice {
    text: string;
}

interface Question {
    text: string;
    voting_system: string;
    choices: Array<Choice>;
}

interface Motion {
    type: string;
    name: string;
    start_datetime: Date;
    close_datetime: Date;
    questions: Array<Question>;
}

// interface Motions {
//     motions: Array<Motion>;
// }

export interface MotionsPanelProps {
    // api_url: string;
    // initialControlParams: object;
}

interface MotionsPanelState {
    motions: Array<Motion>;
}


// function isMotions(obj: any): obj is Motions {
//
//     // TODO - actually do the type checking.
//
//     return true;
//     // return typeof obj.description === "string" && typeof obj.status === "boolean";
// }


function getDefaultState(/*initialControlParams: object*/): MotionsPanelState {
    return {
         motions: [],
    };
}

export class MotionsPanel extends Component<MotionsPanelProps, MotionsPanelState> {

    restoreStateFn: Function;

    constructor(props: MotionsPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
        // this.triggerSetImageParams();
        this.fetchData();
    }

    processData(data: any) {

        // if (!isMotions(data)) {
        //     console.log("data is invalid");
        //     return;
        // }

        // const motions = data as Motions;

        this.setState({motions: data})
    }


    fetchData() {
        fetch('http://local.api.voting.phpimagick.com/motions')
            .then(response => response.json())
            .then(data => this.processData(data));
    }

    componentDidMount() {
        this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // @ts-ignore: I don't understand that error message.
        window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // unbind the listener
        // @ts-ignore: I don't understand that error message.
        window.removeEventListener('popstate', this.restoreStateFn, false);
        this.restoreStateFn = null;
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

    // triggerSetImageParams() {
    //     this.setState({
    //         isProcessing: true
    //     });
    //
    //     let params = {
    //         colorspace:this.state.colorspace,
    //         channel_1_sample: this.state.channel_1_sample,
    //         channel_2_sample: this.state.channel_2_sample,
    //         channel_3_sample: this.state.channel_3_sample,
    //         imagepath: this.state.imagepath
    //     };
    //
    //     sendMessage(EventType.set_image_params, params);
    // }

    renderMotion(motion: Motion) {
        return <div>
            <h2>{motion.name}</h2>
            <div>
                Opens: {motion.start_datetime}
            </div>
            <div>
                Closes: {motion.close_datetime}
            </div>
        </div>
    }


    render(props: MotionsPanelProps, state: MotionsPanelState) {

        let motionsBlocks = this.state.motions.map(
            motion => this.renderMotion(motion)
        );

        return  <div class='motions_panel_react'>
            I am a react block. woot.
            <div>{motionsBlocks}</div>

        </div>;
    }
}










