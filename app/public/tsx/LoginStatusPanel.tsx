import {h, Component} from "preact";

export interface LoginStatusPanelProps {
  // no properties currently
}

interface LoggedInInfo {
  logged_in: boolean,
}

interface LoginStatusPanelState {
  logged_in: boolean;
  // user_search: string;
  // users_found: Array<string>|null;
  // message_sent_info: MessageSentInfo
}

function getDefaultState(/*initialControlParams: object*/): LoginStatusPanelState {
  return {
    logged_in: false
  };
}

export class LoginStatusPanel extends Component<LoginStatusPanelProps, LoginStatusPanelState> {

  // user_search_timeout:null|number;

  constructor(props: LoginStatusPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
    // this.user_search_timeout = null;
  }

  componentDidMount() {
    console.log('componentDidMount');
    this.triggerDataFetch()
  }

  componentWillUnmount() {
  }

  triggerDataFetch() {
    // console.log('triggerDataFetch');
    let callback = () => this.setTimeout();

    fetch('/api/login-status').
      then((response:Response) => response.json()).
      then((data:any) => this.processLoggedInResponse(data)).
      finally(callback);
  }

  setTimeout() {

    console.log('setTimeout');
    setTimeout(
      () => this.triggerDataFetch(),
      // 10 *  // every ten minutes
      20 * 1000 // every 20 seconds
    );
  }

  processLoggedInResponse(data: LoggedInInfo) {
    this.setState({
      logged_in: data.logged_in
    });
  }

  render(props: LoginStatusPanelProps, state: LoginStatusPanelState) {
    // TODO - use https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API
    if (this.state.logged_in === false) {
      return <div class='login_status_panel_react'>
        <a href="/login">Click to Login</a>
      </div>;
    }

    return <div class='login_status_panel_react'>
      <a href="/logout">Logout</a>
    </div>;
  }
}
