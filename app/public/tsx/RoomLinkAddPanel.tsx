import {h, Component} from "preact";


let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface RoomLinkAddPanelProps {
  room_id: string
}

interface RoomLinkAddPanelState {
  // links: RoomLink[],
  url: string,
}

function getDefaultState(): RoomLinkAddPanelState {
  return {
    // links: [],
    url: '',
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

    let params = {
      method: 'POST',
      body: formData
    }

    fetch(endpoint, params).
    then((response:Response) => { if (response.status !== 200) {throw new Error("Server failed to return an OK response.") } return response;}).
    then((response:Response) => response.json()).
    then((data:any) =>this.processData(data)).
    catch((data:any) => this.processError(data));
  }

  // processResponse(response:Response) {
  //   if (response.status !== 200) {
  //     this.setState({error: "Server failed to return an OK response."})
  //     return;
  //   }
  //   let json = response.json();
  //   this.processData(json);
  // }

  processData(data:any) {
    // if (data.links === undefined) {
    //   this.setState({error: "Server response did not contains 'links'."})
    // }
    //
    // let links:RoomLink[] = [];
    //
    // for(let i=0; i<data.links.length; i++) {
    //   const entry = data.links[i]
    //
    //   // @ts-ignore: any ...
    //   const file:RoomLink = {
    //     id: entry.id,
    //     url: entry.url,
    //     description: entry.description,
    //   };
    //
    //   links.push(file);
    // }
    //
    // this.setState({links: links})
  }

  processError (data:any) {
    console.log("something went wrong.");
    console.log(data)
  }


  render(props: RoomLinkAddPanelProps, state: RoomLinkAddPanelState) {
    // @ts-ignore
    return  <div class='room_links_add_panel_react'>
      <div>
        <label>
          Url: <input name="url" onChange={
          // @ts-ignore
            e => this.setState({url: e.target.value})
          } />
        </label>
        <button type="submit" onClick={() => this.addLink()}>Add link</button>
      </div>
    </div>;
  }
}