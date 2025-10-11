import {h, Component} from "preact";
import {set_logged_in, set_user_info} from "./store";

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
  private previousLoginState: boolean | null = null;

  constructor(props: LoginStatusPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
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
    const loginStateChanged = this.previousLoginState !== data.logged_in;
    
    set_logged_in(data.logged_in);

    this.setState({
      logged_in: data.logged_in
    });

    // Update the previous login state
    this.previousLoginState = data.logged_in;

    // Early return if login status hasn't changed
    if (!loginStateChanged) {
      return;
    }

    // Fetch user info since login status has changed
    if (data.logged_in) {
      fetch('/users/whoami')
        .then((response: Response) => response.json())
        .then((userInfo: any) => {
          set_user_info({
            user_id: userInfo.user_id || null,
            avatar_image_id: userInfo.avatar_image_id || null
          });
        })
        .catch((err: any) => {
          console.error('Failed to fetch user info:', err);
          set_user_info({
            user_id: null,
            avatar_image_id: null
          });
        });
      return;
    }
    
    // User logged out, clear user info
    set_user_info({
      user_id: null,
      avatar_image_id: null
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
      <a href="/user/profile">Profile</a>
      <br/>
      <a href="/admin">Admin</a>
    </div>;
  }
}
