import {h, Component} from "preact";

export interface TwitterSplitterPanelProps {
  // no properties currently
}

interface Tweet {
  text: string;
  copied: boolean
}

enum Numbering {
  None = "None",
  Prefix = "1/ Prefix",
}


interface TwitterSplitterPanelState {
  tweets: Array<Tweet>;
  numbering: Numbering;
  current_text: string;
}

function getDefaultState(): TwitterSplitterPanelState {
  return {
    tweets: [],
    numbering: Numbering.None,
    current_text: ""
  };
}

function getNumberPrefixString(numbering: Numbering, tweet_index: number) {
  if (numbering !== Numbering.Prefix) {
    return "";
  }

  return "" + tweet_index + "/ ";
}

function getNumberPostfixString(numbering: Numbering, tweet_index: number) {
  return "";
}


function remove_text(remaining_text: string, numbering: Numbering, tweet_index: number) {

  let number_prefix = getNumberPrefixString(numbering, tweet_index);
  let number_postfix = getNumberPostfixString(numbering, tweet_index);

  let size_available = 280 - number_prefix.length;

  if (remaining_text.length < size_available) {
    return ["", number_prefix + remaining_text + number_postfix];
  }

  let text_to_analyze = remaining_text.slice(0, size_available);

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
    return [remaining_text, number_prefix + tweet_text + number_postfix];
  }

  // No nice places found, just hard split at size_available characters
  let tweet_text = remaining_text.substring(0, size_available);
  remaining_text = remaining_text.substring(size_available);

  return [remaining_text, number_prefix + tweet_text + number_postfix];
}

export function split_tweets(input_text: string, numbering: Numbering): Array<string> {

  let tweets = [];
  let tweet_text: string;
  let remaining_text: string = input_text;

  while (remaining_text.length > 0) {
    [remaining_text, tweet_text] = remove_text(remaining_text, numbering, tweets.length + 1);
    tweets.push(tweet_text);
  }

  return tweets;
}

function calculate_tweets(new_text: string, numbering: Numbering): Array<Tweet> {
  let tweet_strings = split_tweets(new_text, numbering);
  let tweets = [];
  for (let i in tweet_strings) {
    tweets.push({
      text: tweet_strings[i],
      copied: false
    })
  }

  return tweets;
}

export class TwitterSplitterPanel extends Component<TwitterSplitterPanelProps, TwitterSplitterPanelState> {

  constructor(props: TwitterSplitterPanelProps) {
    super(props);
    this.state = getDefaultState();
  }

  handleTextChange(event: any) {

    let new_text = event.target.value;
    let tweets = calculate_tweets(new_text, this.state.numbering);

    this.setState({
      tweets: tweets,
      current_text: new_text
    });
  };

  handleNumberingChange(e: any) {
    let new_numbering = e.target.value;
    let tweets = calculate_tweets(this.state.current_text, new_numbering);
    this.setState({
      numbering: new_numbering,
      tweets: tweets
    });
  }

  copyTweet(index: number) {
    let text = this.state.tweets[index].text;

    try {
      navigator.clipboard.writeText(text);
    }
    catch (e) {
      alert("Error writing to clipboard");
    }

    let current_tweets = this.state.tweets;
    current_tweets[index].copied = true;
    this.setState({tweets: current_tweets});
    return false;
  }

  renderTweet(tweet: Tweet, index: number) {
    let copy_button = <img
      src="/svg/copy-icon.svg"
      alt="copy"
      width="16"
      height="16"/>;

    if (tweet.copied) {
      copy_button = <span>copied</span>;
    }

    let shown_text = tweet.text;//.replace(/(?:\r\n|\r|\n)/g, '<br>');

    return <tr key={index}>
      <td style="white-space: pre-line">
        {shown_text}
      </td>
      <td onClick={() => { return this.copyTweet(index)}}>
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

  renderNumbering() {
    let none_selected = false;
    let prefix_selected = false;

    if (this.state.numbering == Numbering.None) {
      none_selected = true;
    }

    if (this.state.numbering == Numbering.Prefix) {
      prefix_selected = true;
    }

    return <select onChange={(e) => this.handleNumberingChange(e)}>
      <option selected={none_selected}>{Numbering.None}</option>)
      <option selected={prefix_selected}>{Numbering.Prefix}</option>)
    </select>;
  }

  render(props: TwitterSplitterPanelProps, state: TwitterSplitterPanelState) {

    let tweets = this.renderTweets();
    let numbering = this.renderNumbering();

    let chars_info = <div>Chars: {this.state.current_text.length}</div>;

    return <div class='twitter_splitter_panel_react'>
      <p>Write some text in the box, it will be split into tweets on the right. Copy the tweets when you're done.</p>
      Tweet numbering: {numbering}
      <table>
        <tr>
          <td>
            <textarea
              cols={100}
              rows={40}
              placeholder="Type here..."
              value={this.state.current_text}
              onChange={(event) => this.handleTextChange(event)}
              onInput={(event) => this.handleTextChange(event)}>
            </textarea>
          </td>
          <td>
            {chars_info}
            {tweets}
          </td>
        </tr>
      </table>
      <p>Emojis might not be handled correctly. Or links.</p>
    </div>;
  }
}
