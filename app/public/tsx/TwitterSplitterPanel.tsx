import {h, Component} from "preact";

export interface TwitterSplitterPanelProps {
  // no properties currently
}

interface Tweet {
  text: string;
  copied: boolean
}

interface TwitterSplitterPanelState {
  tweets: Array<Tweet>;
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
    "\n\n"
  ];

  let best_break_point = 0;

  for (const breakpoint of breakpoints) {
    let index_of_break = text_to_analyze.lastIndexOf(breakpoint);
    if (index_of_break != -1) {
      if (best_break_point < index_of_break) {
        best_break_point = index_of_break;
      }
    }
  }

  if (best_break_point !== 0) {
    let tweet_text = remaining_text.substring(0, best_break_point + 1);
    remaining_text = remaining_text.substring(best_break_point + 1);
    return [remaining_text, tweet_text];
  }

  // No nice places found, just hard split at 280 characters
  let tweet_text = remaining_text.substring(0, 280);
  remaining_text = remaining_text.substring(280);

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
    let tweet_strings = split_tweets(event.target.value);
    let tweets = [];
    for (let i in tweet_strings) {
      tweets.push({
        text: tweet_strings[i],
        copied: false
      })
    }

    this.setState({tweets: tweets});
  };

  copyTweet(index: number) {

    let text = this.state.tweets[index].text;

    try {
      navigator.clipboard.writeText(text);
    }
    catch (e) {
      alert("Error writing to clipboard");
      return;
    }

    let current_tweets = this.state.tweets;
    current_tweets[index].copied = true;
    this.setState({tweets: current_tweets});
  }

  debugFunc() {
    debugger;
  }

  renderTweet(tweet: Tweet, index: number) {
    let copy_button = <img
      onClick={() => this.debugFunc}
      src="/svg/copy-icon.svg"
      alt="copy"
      width="16"
      height="16"/>;

    if (tweet.copied) {
      copy_button = <span>copied</span>;
    }

    return <tr key={index}>
      <td>
        {tweet.text}
      </td>
      <td onClick={() => this.copyTweet(index)}>
        {copy_button}
        <br/>
        <div>
        {tweet.text.length}&nbsp;/&nbsp;280
        </div>
      </td>
    </tr>
  }

  renderTweets() {
    let tweets = [<tr>
        <td>No tweets yet.</td>
        <td></td>
    </tr>];

    if (this.state.tweets.length != 0) {
      tweets = this.state.tweets.map(this.renderTweet, this);
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
  }
}










