import {h, Component} from "preact";
import {RoomAnnotationView} from "./generated/types";
import {api, GetRoomsAnnotationsResponse} from "./generated/api_routes";
import {sendMessage} from "./message/message";
import {PdfSelectionType} from "./constants";
import {get_logged_in, subscribe_logged_in} from "./store";

export interface RoomAnnotationPanelProps {
    room_id: string
}

interface RoomAnnotationPanelState {
    annotations: RoomAnnotationView[],
    error: string|null,
    logged_in: boolean,
}

function getDefaultState(): RoomAnnotationPanelState {
    return {
        annotations: [],
        error: null,
        logged_in: get_logged_in(),
    };
}



export class RoomAnnotationsPanel extends Component<RoomAnnotationPanelProps, RoomAnnotationPanelState> {

    unsubscribe_logged_in: (() => void)|null = null;

    constructor(props: RoomAnnotationPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshRoomAnnotations();
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({logged_in: logged_in});
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
    }

    refreshRoomAnnotations() {
        api.rooms.annotations(this.props.room_id).
        then((data:GetRoomsAnnotationsResponse) => this.processData(data)).
        catch((data:any) => this.processError(data));
    }

    processData(data:GetRoomsAnnotationsResponse) {
        if (data.data.annotations === undefined) {
            this.setState({error: "Server response did not contains 'annotations'."})
            return;
        }

        this.setState({annotations: data.data.annotations})
    }

    processError (data:any) {
        console.log("something went wrong.");
        console.log(data)
    }

    shareAnnotation(annotation: RoomAnnotationView, annotationUrl: string) {
        const full_url = window.location.origin + annotationUrl;
        const title = annotation.title || "Unnamed Link";
        const markdown_link = `[${title}](${full_url})`;
        sendMessage(PdfSelectionType.APPEND_TO_MESSAGE_INPUT, {text: markdown_link});
    }

    restoreState(state_to_restore: object) {
    }

    renderRoomAnnotation(annotation: RoomAnnotationView, logged_in: boolean) {
        const annotationUrl = `/rooms/${this.props.room_id}/file/${annotation.file_id}/annotations/${annotation.room_annotation_id}/view`;

        return (
          <tr key={annotation.id}>
              <td>
                  <a href={annotationUrl} target="_blank">
                      {annotation.title || "Unnamed Link"}
                  </a>
              </td>
              <td>
                  <a href={annotationUrl}>View</a>
              </td>
              {logged_in && (
                  <td>
                      <button className="button_standard button_chat" onClick={() => this.shareAnnotation(annotation, annotationUrl)} title="Share annotation to chat">
                          Post&nbsp;to&nbsp;chat
                      </button>
                  </td>
              )}
          </tr>
        );
    }

    renderAnnotations() {
        if (this.state.annotations.length === 0) {
            return <div>
                <h2>Annotations</h2>
                <span>No annotations.</span>
            </div>
        }

        const logged_in = this.state.logged_in;
        return <div>
            <h2>Annotations</h2>
            <table>
              <tbody>
                <tr>
                    <td>Name</td>
                    <td>Size</td>
                    {logged_in && <td></td>}
                </tr>
                {Object.values(this.state.annotations).map((annotation: RoomAnnotationView) => this.renderRoomAnnotation(annotation, logged_in))}
              </tbody>
            </table>
        </div>
    }

    render(props: RoomAnnotationPanelProps, state: RoomAnnotationPanelState) {
        let error_block = <span>&nbsp;</span>;
        if (this.state.error != null) {
            error_block = <div class="error">Last error: {this.state.error}</div>
        }

        let length = this.state.annotations.length;
        let files_block = this.renderAnnotations();

        return  <div class='room_annotations_panel_react'>
            {error_block}
            {files_block}
            <button className="button_standard" onClick={() => this.refreshRoomAnnotations()}>Refresh</button>
        </div>;
    }
}
