import {h, Component} from "preact";
import {global} from "./globals";

export interface LoginStatusPanelProps {
  // no properties currently
}

interface LoggedInInfo {
  logged_in: boolean,
}

interface LoginStatusPanelState {
  logged_in: boolean;
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
    global.logged_in = false;
    // this.user_search_timeout = null;
  }

  componentDidMount() {
    // console.log('componentDidMount');
    this.triggerDataFetch()
  }

  componentWillUnmount() {
  }

  triggerDataFetch() {
    // console.log('triggerDataFetch');
    let callback = () => this.setTimeout();

    // TODO - we shouldn't need to renew the session so often.
    // at some point, renewing could be done every 20 requests.

    fetch(
      '/api/login-status', {
      headers: {
        'x-session-renew': 'true'
      }
    }).
      then((response:Response) => response.json()).
      then((data:any) => this.processLoggedInResponse(data)).
      finally(callback);
  }

  setTimeout() {
    setTimeout(
      () => this.triggerDataFetch(),
      20 * 1000 // every 20 seconds
    );
  }

  processLoggedInResponse(data: LoggedInInfo) {
    global.logged_in = data.logged_in;

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
      <br/>
      <a href="/user/memes">Memes</a>
      <br/>
      <a href="/admin">Admin</a>
    </div>;
  }
}
