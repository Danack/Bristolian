import {h, Component} from "preact";

export interface NotificationTestPanelProps {
  // no properties currently
}

interface MessageSentInfo {
  message_sent_time: null|Date,
  message_sent_recipient: string|null,
}

interface NotificationTestPanelState {

  user_search: string;
  users_found: Array<string>|null;
  message_sent_info: MessageSentInfo

}

function getDefaultState(/*initialControlParams: object*/): NotificationTestPanelState {
  return {
    user_search: '',
    users_found: null,
    message_sent_info: null
  };
}

export class NotificationTestPanel extends Component<NotificationTestPanelProps, NotificationTestPanelState> {

  user_search_timeout:null|number;

  constructor(props: NotificationTestPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
    this.user_search_timeout = null;
  }

  componentDidMount() {
  }

  componentWillUnmount() {
  }

  handleEmailSearchChange(event: Event) {
    // @ts-ignore: Yes, it does exist.
    let new_user_search = event.currentTarget.value;

    if (this.user_search_timeout !== null) {
      // Clear the previous timer, to prevent incorrect search
      clearTimeout(this.user_search_timeout);
    }

    this.setState({user_search: new_user_search});

    // @ts-ignore: Yes, it does exist.
    this.user_search_timeout = setTimeout(
      () => this.startSearch(),
      200
    );
  }

  handleUserSearchResults(data: Array<string>) {
    this.setState({users_found: data})
  }

  startSearch() {
    let url = '/api/search_users?'+  new URLSearchParams({
      user_search: this.state.user_search
    });

    fetch(url).
      then((response:Response) => response.json()).
      then((data) => this.handleUserSearchResults(data));
  }

  handleUserPingResult(user:string, data: any) {
    let info:MessageSentInfo = {
      message_sent_time: new Date(),
      message_sent_recipient: user
    }

    this.setState({
      message_sent_info: info
    })
  }

  pingUser(user: string) {
    let url = '/api/ping_user?'+  new URLSearchParams({
      user: user
    });

    fetch(url).
      then((response:Response) => response.json()).
      then((data) => this.handleUserPingResult(user, data));
  }

  renderUserRow(user: string, index: number) {
    return <tr key={index}>
      <td>{user}</td>
      <td>
        <span className="button"
          onClick={() => this.pingUser(user)}>
          Ping 'em.</span>
      </td>
    </tr>
  }

  renderUsersFound() {
    let callback = (user: string, index: number) => { return this.renderUserRow(user, index);}

    return <tbody>{this.state.users_found.map(callback)}</tbody>;
  }

  renderMessageSentBlock() {
    if (this.state.message_sent_info === null) {
      return <span></span>
    }

    // TODO - add seconds ago...
    return <p>Message sent to {this.state.message_sent_info.message_sent_recipient}.</p>
  }

  render(props: NotificationTestPanelProps, state: NotificationTestPanelState) {

    let users_found_block = <span></span>
    if (this.state.users_found !== null) {
      users_found_block = this.renderUsersFound();
    }

    let message_sent_block = this.renderMessageSentBlock();

    return  <div class='notification_test_panel_react'>
      <h3>Notification test</h3>
      <p>I am the happy fun time Notification test.</p>
      {message_sent_block}
      <p>
        Search by email:
        <input
          type="text"
          onInput={(event:Event) => this.handleEmailSearchChange(event)}
          onChange={(event:Event) => this.handleEmailSearchChange(event)} />
      </p>
      {users_found_block}

    </div>;
  }
}
