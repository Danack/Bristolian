import {h, Component} from "preact";
import {isUrl} from "./functions";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface RoomLinkAddPanelProps {
  room_id: string
}

interface RoomLinkAddPanelState {

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

  constructor(props: RoomLinkAddPanelProps) {
    super(props);
    this.state = getDefaultState(/*props.initialControlParams*/);
  }

  componentDidMount() {
    // this.refreshLinks();
  }

  componentWillUnmount() {
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
    // success
    if (data.status === 'success') {

      let new_state = getDefaultState();
      new_state.result = "Link added";
      this.setState(new_state)
    }

    // failure
    if (data.status === 'fail') {
      if (data.data['/url'] !== undefined) {
        // @ts-ignore
        this.setState({error_url: data.data['/url']})
      }
      if (data.data['/title'] !== undefined) {
        // @ts-ignore
        this.setState({error_title: data.data['/title']})
      }
      if (data.data['/description'] !== undefined) {
        // @ts-ignore
        this.setState({error_description: data.data['/description']})
      }
    }
  }

  processError (data:any) {
    console.log("something went wrong.");
    console.log(data)
  }


  render(props: RoomLinkAddPanelProps, state: RoomLinkAddPanelState) {

    let isValidUrl = isUrl(this.state.url);

    let add_button = <div>not valid</div>

    if (isValidUrl) {
      add_button = <button type="submit" onClick={() => this.addLink()}>Add link</button>
    }

    let result = <span></span>;

    if (this.state.result !== null) {
      result = <span>result is {this.state.result}</span>
    }

    let error_url = <span></span>
    let error_title = <span></span>
    let error_description = <span></span>
    {
      if (this.state.error_url !== null) {
        error_url = <span class="error">{this.state.error_url}</span>
      }

      if (this.state.error_title !== null) {
        error_title = <span class="error">{this.state.error_title}</span>
      }


        if (this.state.error_description !== null) {
          error_description = <span class="error">{this.state.error_description}</span>
        }

          // @ts-ignore
        return <div class='room_links_add_panel_react'>
          {result}
          <div>
            <label>

              Url <input name="url" size={100} onChange={
              // @ts-ignore
              e => this.setState({url: e.target.value})
            }/>
              <br/>
              {error_url}
            </label>

            <label>

              Title <input name="title" size={100} onChange={
              // @ts-ignore
              e => this.setState({title: e.target.value})
            }/>
              {error_title}
              <br/>
            </label>

            <label>

              Description <input name="description" onChange={
              // @ts-ignore
              e => this.setState({description: e.target.value})
            }/>
              {error_description}
              <br/>
            </label>

            {add_button}

          </div>
        </div>;
      }
    }
  }