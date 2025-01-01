import {h, Component} from "preact";

import {ChangeEvent} from "preact/compat";

export interface TeleprompterPanelProps {
  // no properties currently
}

// TODO
// Scrolling ✓
// Speed control ✓
// border ✓
// font size ✓
// font choice?
// color

interface TeleprompterPanelState {
  // The speed of scrolling forwards
  speed: number;

  // The current position of the text
  offset: number;

  // The text as set in the textarea
  text: string;

  // Are we in fullscreen mode
  showing: boolean;

  // Does the user want mirroring?
  mirror: boolean;

  // Is the marquee currently moving.
  moving: boolean;

  // Size of the border
  border_size: number;
}

function getDefaultState(/*initialControlParams: object*/): TeleprompterPanelState {

  let text = "What is Lorem Ipsum?\n" +
    "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\n" +
    "\n" +
    "Why do we use it?\n" +
    "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).\n" +
    "\n" +
    "\n" +
    "Where does it come from?\n" +
    "Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\n" +
    "\n" +
    "The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.\n" +
    "\n" +
    "Where can I get some?\n" +
    "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

  return {
    speed: 3,
    offset: 200,
    text: text,
    showing: false,
    mirror: false,
    moving: true,
    border_size: 150,
  };
}

export class TeleprompterPanel extends Component<TeleprompterPanelProps, TeleprompterPanelState> {

  // Create a reference to the key press function to avoid creating
  // references and to make unbinding it work unambiguously
  keyKeyDownFn = (e: any) => this.handleKeyDown(e);

  constructor(props: TeleprompterPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
    console.log("reconstructed?");

    setInterval(() => this.handleUpdateTick(), 5);
  }

  componentDidMount() {
    document.addEventListener("keydown", this.keyKeyDownFn, false);
  }

  componentWillUnmount() {
    document.removeEventListener("keydown", this.keyKeyDownFn, false);
    console.log("component will unmounted");
  }

  handleSpeedSettingChange(e:ChangeEvent){
    // @ts-ignore:why does ChangeEvent not have a value element...
    this.setState({speed: e.target.value});
  }

  handleBorderSizeChange(e:ChangeEvent){
    // @ts-ignore:why does ChangeEvent not have a value element...
    this.setState({border_size: e.target.value});
  }

  handleMirrorClick() {
    this.setState({mirror: !this.state.mirror});
  }

  toggleShowing(e: any) {

    // blur the focus, so that key-presses don't hide
    // the teleprompter
    try {
      e.target.blur();
    }
    catch (error) {
      console.log("Error blurring focus: " + error);
    }

    let new_showing_state = !this.state.showing;

    if (new_showing_state === false) {
      this.setState({
        moving: false,
        showing: new_showing_state,
      });
    }
    else {
      this.setState({
        moving: true,
        showing: new_showing_state,
      });
    }
  }

  handleUpdateTick() {
    if (this.state.showing !== true) {
      return;
    }

    if (this.state.moving !== true) {
      return;
    }

    let new_offset = this.state.offset - (this.state.speed / 10);
    this.setState({offset: new_offset});
  }

  handleKeyDown(e:any) {

    let stateToSet = {};
    if (e.key == "Escape") {
      if (this.state.showing) {
        // @ts-ignore:this is a normal piece of code.
        stateToSet.showing = false;
      }
    }
    else if (e.key == "ArrowUp") {
      // @ts-ignore:this is a normal piece of code.
      stateToSet.speed = 0.5 + this.state.speed;
    }
    else if (e.key == "ArrowDown") {
      // @ts-ignore:this is a normal piece of code.
      stateToSet.speed = -0.5 + this.state.speed;
    }
    else if (e.code === 'Space') {
      // @ts-ignore:this is a normal piece of code.
      stateToSet.moving = !this.state.moving;
    }

    else if (e.code === 'PageUp') {
      // @ts-ignore:this is a normal piece of code.
      stateToSet.offset = this.state.offset + 200;
      // TODO - make this accurate
    }
    else if (e.code === 'PageDown') {
      // @ts-ignore:this is a normal piece of code.
      stateToSet.offset = this.state.offset + 200;
      // TODO - make this accurate
    }
    else {
      console.log("key is " + e.key);
    }

    if (Object.keys(stateToSet).length != 0) {
      this.setState(stateToSet);
    }
  }

  renderControls() {
    return <span>
      <div>
      Speed&nbsp;&nbsp;
      <input
        type='number'
        size={4}
        step="0.5"
        style="width: 64px;"
        onChange={(e) => this.handleSpeedSettingChange(e)}
        value={this.state.speed}></input>
      </div>

      <div>
      Border&nbsp;&nbsp;
      <input
        type='number'
        size={4}
        step="5"
        style="width: 64px;"
        onChange={(e) => this.handleBorderSizeChange(e)}
        value={this.state.border_size}></input>
      </div>
      <div>
        Mirroring {this.state.mirror ? "on" : "off"}&nbsp;&nbsp;
        <button onClick={() => this.handleMirrorClick()}>Toggle</button>
      </div>
      <div>
        <button onClick={(e) => this.toggleShowing(e)}>Start</button>
      </div>
    </span>
  }

  render(props: TeleprompterPanelProps, state: TeleprompterPanelState) {
    let controls = this.renderControls();

    let prompt = <span></span>;
    if (this.state.showing) {

      let teleprompter_class = this.state.mirror ? "teleprompter_overlay teleprompter_mirror" : "teleprompter_overlay";

      const style = {
        "margin-top": "" + this.state.offset + "px",
        "padding-left": "" + this.state.border_size + "px",
        "padding-right": "" + this.state.border_size + "px"
      };

      prompt = <div
        class={teleprompter_class}>
        <div class="teleprompter_words" style={style}>
          {this.state.text}
        </div>
      </div>
    }

    return  <div class='teleprompter_panel_react'>
      <table>
        <tr>
          <th>Text</th>
          <th>Controls</th>
        </tr>
        <tr>
          <td>
            <textarea cols={80} rows={40}>
              {this.state.text}
            </textarea>

          </td>
          <td>
            {controls}

            <h3>Instructions</h3>

            <p>Put some text in the box.</p>

            <div>Arrow up - scroll faster </div>
            <div>Arrow down - scroll slower </div>
            <div>Page up - page up </div>
            <div>Page down - page down</div>
            <div>Escape - exit fullscreen</div>
            <div>Space - pause</div>
          </td>
        </tr>
      </table>

      {prompt}

    </div>;
  }
}
