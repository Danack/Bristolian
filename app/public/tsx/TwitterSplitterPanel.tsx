import {h, Component} from "preact";

export interface TwitterSplitterPanelProps {
  // no properties currently
}

interface TwitterSplitterPanelState {
  tweets: Array<string>;
}

function getDefaultState(/*initialControlParams: object*/): TwitterSplitterPanelState {
  return {
    tweets: []
  };
}

function remove_text(remaining_text: string) {
  if (remaining_text.length < 280) {
    return ["", remaining_text];
  }

  let text_to_analyze = remaining_text.slice(0, 280);

  let breakpoints = [
    ". ",
    ", ",
    ",",
  ];

  for (const breakpoint of breakpoints) {
    let index_of_break = text_to_analyze.lastIndexOf(breakpoint);
    if (index_of_break != -1) {
      let tweet_text = remaining_text.substr(0, index_of_break + breakpoint.length);
      remaining_text = remaining_text.substr(index_of_break + breakpoint.length);
      return [remaining_text, tweet_text];
    }
  }

  // No nice places found, just hard split at 280 characters
  let tweet_text = remaining_text.substr(0, 280);
  remaining_text = remaining_text.substr(280);

  return [remaining_text, tweet_text];
}

export function split_tweets(input_text: string): Array<string> {

  let tweets = [];
  let tweet_text: string;
  let remaining_text: string = input_text;

  while (remaining_text.length > 0) {
    [remaining_text, tweet_text] = remove_text(remaining_text);
    tweets.push(tweet_text);
  }

  return tweets;
}


export class TwitterSplitterPanel extends Component<TwitterSplitterPanelProps, TwitterSplitterPanelState> {

  constructor(props: TwitterSplitterPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
    // this.fetchMaxCommentData();
  }

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

    handleMessageChange(event: any) {

        // setMessage(event.target.value);

        // // @ts-ignore: Why are events so hard to type
        // console.log(event.target.value);
      let tweets = split_tweets(event.target.value);

      this.setState({tweets: tweets});
    };



  renderTweet(tweet: string, index: number) {
    return <tr key={index}>
      <td>{tweet}</td>
    </tr>
  }

  renderTweets() {
    let tweets = [<tr>
        <td>No tweets yet.</td>
    </tr>];

    if (this.state.tweets.length != 0) {
      tweets = this.state.tweets.map(this.renderTweet);
    }

    return <table>
        {tweets}
    </table>
  }

  render(props: TwitterSplitterPanelProps, state: TwitterSplitterPanelState) {

    let tweets = this.renderTweets();

    return <div class='twitter_splitter_panel_react'>
      <table>
        <tr>
          <td>
            <textarea
              cols={100}
              rows={40}
              placeholder="Type here..."
              onChange={(event) => this.handleMessageChange(event)}
              onInput={(event) => this.handleMessageChange(event)}>
            </textarea>
          </td>
          <td>{tweets}</td>
        </tr>
      </table>
    </div>;

    return <div>These are TwitterSplitter.</div>
  }
}










