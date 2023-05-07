import {h, Component} from "preact";

import {ChangeEvent} from "preact/compat";

export interface EmailLinkGeneratorPanelProps {
  // no properties currently
}

interface EmailLinkGeneratorPanelState {
  // The email address to send to
  address: string;

  // The text for the link. Can be blank to be same as email address
  link_text: string;

  // The placeholder subject line
  subject: string;

  //
  cc: string;

  //
  bcc: string;

  body: string;
  clipboard: boolean;
}

function getDefaultState(/*initialControlParams: object*/): EmailLinkGeneratorPanelState {
  return {
    address: "",
    link_text: "",
    subject: "",
    cc: "",
    bcc: "",
    body: "",
    clipboard: true
  };
}

export class EmailLinkGeneratorPanel extends Component<EmailLinkGeneratorPanelProps, EmailLinkGeneratorPanelState> {

  constructor(props: EmailLinkGeneratorPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {

    if (navigator.clipboard === undefined) {
      this.setState({clipboard: false});
    }
  }

  componentWillUnmount() {
  }

  copyToClipBoard() {
    let text = this.generate_link_string();
    const copyContent = async () => {
      try {
        await navigator.clipboard.writeText(text);
        console.log('Content copied to clipboard');
      } catch (err) {
        alert('Failed to copy: ' + err);
      }
    }

    copyContent();
  }

  handleAddressChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    let trimmed = "";
    // @ts-ignore:why is HTMLInputElement not recognised?
    let value = e.target.value;

    if (typeof value === 'string' || value instanceof String) {
      trimmed = value.trim();
    }
    this.setState({address: trimmed});
  }


  handleLinkTextChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    // @ts-ignore:why is HTMLInputElement not recognised?
    this.setState({link_text: e.target.value});
  }

  handleSubjectChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    // @ts-ignore:why is HTMLInputElement not recognised?
    this.setState({subject: e.target.value});
  }

  handleCCChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    // @ts-ignore:why is HTMLInputElement not recognised?
    this.setState({cc: e.target.value});
  }

  handleBCCChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    // @ts-ignore:why is HTMLInputElement not recognised?
    this.setState({bcc: e.target.value});
  }

  handleBodyChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    // @ts-ignore:why is HTMLInputElement not recognised?
    this.setState({body: e.target.value});
  }

  generate_link() {
    let link_text = this.state.link_text
    if (link_text.length == 0) {
      link_text = this.state.address;
    }
    return link_text;
  }


  generate_link_string() {
    if (this.state.address.length == 0) {
      return "";
    }

    let separator = "";
    let subject_text = "";
    if (this.state.subject.length != 0) {
      subject_text="Subject=" + encodeURIComponent(this.state.subject);
      separator = "&";
    }

    let cc_text = "";
    if (this.state.cc.length != 0) {
      cc_text = separator + "cc=" + encodeURIComponent(this.state.cc);
      separator = "&";
    }

    let bcc_text = "";
    if (this.state.bcc.length != 0) {
      bcc_text = separator + "bcc=" + encodeURIComponent(this.state.bcc);
      separator = "&";
    }

    let body_text = "";
    if (this.state.body.length != 0) {
      body_text = separator + "body=" + encodeURIComponent(this.state.body);
    }

    let href = "mailto:" + this.state.address + "?" + subject_text + cc_text + bcc_text + body_text;
    let link_text = this.generate_link();

    let result = `<a href=${href} target='_blank'>${link_text}</a>`;

    return result;
  }

  // This is duplication, but it's quicker than figuring out
  // how to get the raw html from a DOM object.
  generate_link_html() {

    if (this.state.address.length == 0) {
      return "";
    }

    let separator = "";
    let subject_text = "";
    if (this.state.subject.length != 0) {
      subject_text="Subject=" + encodeURIComponent(this.state.subject);
      separator = "&";
    }

    let cc_text = "";
    if (this.state.cc.length != 0) {
      cc_text = separator + "cc=" + encodeURIComponent(this.state.cc);
      separator = "&";
    }

    let bcc_text = "";
    if (this.state.bcc.length != 0) {
      bcc_text = separator + "bcc=" + encodeURIComponent(this.state.bcc);
      separator = "&";
    }

    let body_text = "";
    if (this.state.body.length != 0) {
      body_text = separator + "body=" + encodeURIComponent(this.state.body);
    }

    let href = "mailto:" + this.state.address + "?" + subject_text + cc_text + bcc_text + body_text;
    let link_text = this.generate_link();

    let result = <a href={href} target="_blank">{link_text}</a>

    return result;
  }

  render(props: EmailLinkGeneratorPanelProps, state: EmailLinkGeneratorPanelState) {
    let link_html = this.generate_link_html();
    let link_string = this.generate_link_string();

    let copy_button = <span></span>;

    if (this.state.clipboard === true) {
      copy_button = <input type="button" value="Copy" onClick={() => this.copyToClipBoard()}>copy</input>
    }

    return  <div class='email_link_generator_panel_react'>
      <h3>Input</h3>
      <table class='large_table'>
        <tbody>
        <tr>
          <td>Address</td>
          <td><input type="email" size={80}
            // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleAddressChange(e)}
                     onChange={(e) => this.handleAddressChange(e)}
                     value={this.state.address}></input>

          </td>
        </tr>

        <tr>
          <td>Link text (leave blank for email)</td>
          <td><input type="email" size={80}
            // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleLinkTextChange(e)}
                     onChange={(e) => this.handleLinkTextChange(e)}
                     value={this.state.link_text}/></td>
        </tr>


        <tr>
          <td>Subject</td>
          <td><input type="text" size={80}
            // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleSubjectChange(e)}
                     onChange={(e) => this.handleSubjectChange(e)}
                     value={this.state.subject}/></td>
        </tr>
        <tr>
          <td>CC</td>
          <td><input type="text" size={80}
                     // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleCCChange(e)}
                     onChange={(e) => this.handleCCChange(e)}
                     value={this.state.cc}/></td>
        </tr>
        <tr>
          <td>BCC</td>
          <td><input type="text" size={80}
                     // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleBCCChange(e)}
                     onChange={(e) => this.handleBCCChange(e)}
                     value={this.state.bcc}/></td>
        </tr>

        <tr>
          <td>Body</td>
          <td><input type="text" size={80}
                     // @ts-ignore:event types are annoying
                     onInput={(e) => this.handleBodyChange(e)}
                     onChange={(e) => this.handleBodyChange(e)}
                     value={this.state.body}/></td>
        </tr>
        </tbody>
      </table>

      <h3>Output</h3>
      <table>
         <tr>
           <td>HTML</td>
           <td>{link_string}</td>
         </tr>

        <tr>
          <td>Test</td>
          <td>{link_html}</td>
        </tr>
      </table>

      {copy_button}
    </div>;
  }
}
