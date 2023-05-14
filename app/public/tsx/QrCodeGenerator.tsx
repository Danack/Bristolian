import {h, Component} from "preact";

import {ChangeEvent} from "preact/compat";

export interface QrCodeGeneratorPanelProps {
  // no properties currently
}

interface QrCodeGeneratorPanelState {
  // The email address to send to
  address: string;
  is_valid: boolean

  // // The text for the link. Can be blank to be same as email address
  // link_text: string;
}

function getDefaultState(/*initialControlParams: object*/): QrCodeGeneratorPanelState {
  return {
    address: "",
    is_valid: false
    // link_text: "",
  };
}

export class QrCodeGeneratorPanel extends Component<QrCodeGeneratorPanelProps, QrCodeGeneratorPanelState> {

  constructor(props: QrCodeGeneratorPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {
  }

  componentWillUnmount() {
  }

  handleAddressChange(e:Event /*HTMLInputElement*/ /*: React.ChangeEvent<HTMLInputElement>*/) {
    let trimmed = "";
    // @ts-ignore:why is HTMLInputElement not recognised?
    let value = e.target.value;

    if (typeof value === 'string' || value instanceof String) {
      trimmed = value.trim();
    }

    let is_valid = false;
    try {
      let url_parsed = new URL(trimmed);
      is_valid = true;
    } catch (_) {
      // nothing to do.
    }

    this.setState({address: trimmed, is_valid: is_valid});
  }


  render(props: QrCodeGeneratorPanelProps, state: QrCodeGeneratorPanelState) {

    let qr_code_link = <span></span>
    let trimmed_address = this.state.address.trim();

    if (trimmed_address.length > 0) {
      let qr_code_url = `/qr/code?url=${trimmed_address}`;
      qr_code_link = <img src={qr_code_url} alt='a qr code'/>
    }

    let status = <span></span>;

    if (this.state.is_valid === false && trimmed_address.length > 0) {
      if (trimmed_address.startsWith("https://") === false &&
        trimmed_address.startsWith("http://") === false) {
        status = <span class='status_error'>URL is not valid - needs to start with http:// or https://</span>;
      }
      else {
        status = <span class='status_error'>URL is not valid</span>;
      }
    }

    return  <div class='qr_code_generator_panel_react'>
      Enter a URL <input type="text" size={80}
        // @ts-ignore:event types are annoying
        onInput={(e) => this.handleAddressChange(e)}
        onChange={(e) => this.handleAddressChange(e)}
        value={this.state.address}></input>
      <p>{status}</p>
      {qr_code_link}
    </div>;
  }
}
