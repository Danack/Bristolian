import {h, Component} from "preact";
import {isUrl} from "./functions";
import {PdfSelectionType} from "./constants";
import {sendMessage} from "./message/message";
import {get_logged_in, subscribe_logged_in} from "./store";

export interface RoomLinkAddPanelProps {
  room_id: string
}

interface RoomLinkAddPanelState {

  logged_in: boolean,
  url: string,
  title: string,
  description: string,
  result: null|string,

  error_url: string|null,
  error_title: string|null,
  error_description: string|null,
}

function getDefaultState(): RoomLinkAddPanelState {
  return {
    logged_in: get_logged_in(),
    url: '',
    title: '',
    description: '',
    result: null,
    error_url: null,
    error_title: null,
    error_description: null,
  };
}


export class RoomLinkAddPanel extends Component<RoomLinkAddPanelProps, RoomLinkAddPanelState> {
  private unsubscribe_logged_in: (() => void) | null = null;

  constructor(props: RoomLinkAddPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {
    this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
      this.setState({logged_in});
    });
    // this.refreshLinks();
  }

  componentWillUnmount() {
    if (this.unsubscribe_logged_in) {
      this.unsubscribe_logged_in();
      this.unsubscribe_logged_in = null;
    }
  }

  addLink() {
    const endpoint = `/api/rooms/${this.props.room_id}/links`;
    const formData = new FormData();
    formData.append("url", this.state.url);
    formData.append("title", this.state.title);
    formData.append("description", this.state.description);

    let params = {
      method: 'POST',
      body: formData
    }

    fetch(endpoint, params).
    then((response:Response) => {
      if (response.status !== 200 && response.status !== 400) {
        throw new Error("Server failed to return an expected response.")
      }
      return response;
    }).
    then((response:Response) => response.json()).
    then((data:any) =>this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  processData(data:any) {
    // New shape: SuccessResponse -> { result: "success" }
    if (data.result === 'success') {

      let new_state = getDefaultState();
      new_state.result = "Link added";
      this.setState(new_state)
      sendMessage(PdfSelectionType.ROOM_LINKS_CHANGED, {});
    }

    // For any non-success, just log for now; validation responses will be handled
    // when the backend emits a new, non-legacy shape.
    if (data.result !== 'success') {
      console.log("Add link failed", data);
    }
  }

  processError (data:any) {
    console.log("something went wrong.");
    console.log(data)
  }


  render(props: RoomLinkAddPanelProps, state: RoomLinkAddPanelState) {

    if (state.logged_in !== true) {
      return <span></span>
    }

    let isValidUrl = isUrl(this.state.url);

    let add_button = <span><button className="button_standard" disabled={true}>Add link</button>Url is not valid.</span>

    if (isValidUrl) {
      add_button = <button type="submit" className="button_standard" onClick={() => this.addLink()}>Add link</button>
    }

    let result = <span></span>;

    if (this.state.result !== null) {
      result = <span>{this.state.result}</span>
    }


    let error_url = <span></span>
    let error_title = <span></span>
    let error_description = <span></span>

    if (this.state.error_url !== null) {
      error_url = <span class="error">{this.state.error_url}</span>
    }

    if (this.state.error_title !== null) {
      error_title = <span class="error">{this.state.error_title}</span>
    }

    if (this.state.error_description !== null) {
      error_description = <span class="error">{this.state.error_description}</span>
    }


    const fieldIdPrefix = `room_link_add_${props.room_id}_`;

    // @ts-ignore
    return <div class='room_links_add_panel_react'>
      <table>
        <tbody>
        <tr>
          <td>
            <label htmlFor={`${fieldIdPrefix}url`}>
              Url
            </label>
          </td>
          <td>
            <input id={`${fieldIdPrefix}url`}
                   name="url"
                   size={100}
                   value={this.state.url}
                   // @ts-ignore
                   onInput={ e => {
                     const value = (e.currentTarget as HTMLInputElement).value;
                     this.setState({url: value})
                   }}
                   />
            {error_url}
          </td>
        </tr>
        <tr>
          <td>
            <label htmlFor={`${fieldIdPrefix}title`}>
              Title
            </label>
          </td>
          <td>
            <input id={`${fieldIdPrefix}title`}
                   name="title"
                   size={100}
                   value={this.state.title}
                   // @ts-ignore
                   onInput={e => {
                     const value = (e.currentTarget as HTMLInputElement).value;
                     this.setState({title: value})
                   }}
                   />
            {error_title}
          </td>
        </tr>
        <tr>
          <td>
            <label htmlFor={`${fieldIdPrefix}description`}>Description</label>
          </td>

          <td>
            <textarea
              id={`${fieldIdPrefix}description`}
              name="description"
              rows={4}
              cols={80}
              value={this.state.description}
              onInput={
                // @ts-ignore
                e => this.setState({description: e.target.value})
              }/>

            {error_description}
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            {add_button}
          </td>
        </tr>
        <tr>
          <td></td>
          <td>{result}</td>
        </tr>

        </tbody>
      </table>
    </div>;

  }
}