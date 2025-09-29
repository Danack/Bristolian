import {h, Component} from "preact";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;
// let REPORTS_SHOWN_PER_PAGE: number = 10;

export interface BlankPanelProps {
    // initial_json_data: string;
}

interface BlankPanelState {
    // current_page: number;
}

function getDefaultState(props: BlankPanelProps): BlankPanelState
{
    return {
        // current_page: 0,
    };
}

export class BlankPanel extends Component<BlankPanelProps, BlankPanelState> {

    constructor(props: BlankPanelProps) {
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

    render(props: BlankPanelProps, state: BlankPanelState) {

        return  <div>
            Henlo
        </div>;
    }
}










