import {h, Component} from "preact";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface MemeManagementPanelProps {
    // no properties currently
}


interface Meme {
    id: string;
    user_id: string;
    filename: string;
    filetype: string;
    filestate: string;
}

interface MemeTag {
    id: string;
    user_id: string;
    meme_id: string;
    type: string;
    text: string;
}

interface Tag {

}

interface MemeInfo {
    tags: Array<Tag>
}




interface MemeManagementPanelState {
    memes: Array<Meme>;
    memeBeingEdited: Meme|null;
    memeInfo: Array<MemeInfo>;
    memeBeingEdited_memetags: Array<MemeTag>|null;
    meme_edit_type: string;
    meme_edit_text: string;
    confirmMemeTagDelete: null|MemeTag;
}

function getDefaultState(/*initialControlParams: object*/): MemeManagementPanelState {
    return {
        memes: [],
        memeBeingEdited: null,
        memeInfo: null,
        memeBeingEdited_memetags: null,
        meme_edit_type: 'text',
        meme_edit_text: '',
        confirmMemeTagDelete: null
    };
}

export class MemeManagementPanel extends Component<MemeManagementPanelProps, MemeManagementPanelState> {

    constructor(props: MemeManagementPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshMemes();
    }

    componentWillUnmount() {
    }

    restoreState(state_to_restore: object) {
    }

    processData(data:any) {
        this.setState({memes: data.memes})
    }

    refreshMemes() {
        fetch('/api/memes').
          then((response:Response) => response.json()).
          then((data:any) =>this.processData(data));
    }

    processMemeTagData(memeTags: Array<MemeTag>) {
        this.setState({
            memeBeingEdited_memetags: memeTags
        });
    }

    startEditing(meme: Meme) {
        this.setState({
            memeBeingEdited: meme,
            memeInfo: null,
            memeBeingEdited_memetags: null
        });

        let url = "/api/memes/" + meme.id + "/tags";
        fetch(url).
          then((response:Response) => response.json()).
          then((memeTags: Array<MemeTag>) =>this.processMemeTagData(memeTags));
    }

    cancelMemeEditing() {
        this.setState({memeBeingEdited: null})
    }

    renderMeme(meme: Meme) {
        return <tr key={meme.id}>
            <td>
                {meme.id}
            </td>
            <td>
                {meme.id}
                <span
                  class="button"
                  onClick={() => this.startEditing(meme)}
                >Edit tags</span>

            </td>
        </tr>
    }

    renderMemeBlock() {
        if (this.state.memes !== null && this.state.memes.length === 0) {
            return <span>You don't have any memes uploaded. If you did, you could manage them here.</span>
        }

        return <table>
            <tr key={"header"}>
                {Object.keys(this.state.memes[0]).map((key) => (
                  <th>{key}</th>
                ))}
            </tr>
            {Object.values(this.state.memes).map((meme: Meme) => this.renderMeme(meme))}
        </table>
    }

    changeMemeType(something: Event) {
        this.setState({
            // @ts-ignore: It does exist.
            meme_edit_type: something.currentTarget.value
        });
    }

    handleTextChange(something: Event) {
        // @ts-ignore: It does exist.
        console.log("new text = " + something.currentTarget.value);
        this.setState({
            // @ts-ignore: It does exist.
            meme_edit_text: something.currentTarget.value
        });
    }

    processTagAddData(memeTags: Array<MemeTag>) {
        this.setState({
            memeBeingEdited_memetags: memeTags
        });
    }

    handleAddTag() {
        console.log("Add tag of Type ", this.state.meme_edit_type, " text of ", this.state.meme_edit_text);
        const formData = new FormData();

        formData.append("meme_id", this.state.memeBeingEdited.id);
        formData.append("type", this.state.meme_edit_type);
        formData.append("text", this.state.meme_edit_text);

        let params = {
            method: 'POST',
            body: formData
        }

        fetch('/api/meme-tag-add/', params).
          then((response:Response) => response.json()).
          then((data:any) => this.processTagAddData(data));
    }

    handleEditMemeTag(memeTag: MemeTag) {

    }

    handleConfirmDeleteMemeTag() {
        console.log("Need to delete");
        const formData = new FormData();

        formData.append("meme_id", this.state.memeBeingEdited.id);
        formData.append("meme_tag_id", this.state.confirmMemeTagDelete.id);

        let params = {
            method: 'DELETE',
            body: formData
        }
        fetch('/api/meme-tag-delete/', params).
          then((response:Response) => response.json()).
          then((memeTags: Array<MemeTag>) => this.processMemeTagData(memeTags));
        }

    handleCancelDeleteMemeTag() {
        console.log('handleCancelDeleteMemeTag');
        this.setState({confirmMemeTagDelete: null});
    }

    handleDeleteMemeTag(memeTag: MemeTag) {

        console.log('handleConfirmDeleteMemeTag');

        this.setState({confirmMemeTagDelete: null });

    }

    renderMemeTagDeleteModal() {
        return <div class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <p>Some text in the Modal..</p>

                <span
                  className="button"
                  onClick={() => this.handleConfirmDeleteMemeTag()}>Delete</span>

                <span
                  className="button"
                  onClick={() => this.handleCancelDeleteMemeTag()}>Cancel</span>
            </div>
        </div>
    }

    renderMemeTag(memeTag: MemeTag) {
        return <tr key={memeTag.id}>
            <td>
                {memeTag.type}
            </td>
            <td>
                {memeTag.text}
            </td>
            <td>
                <span
                  className="button"
                  onClick={() => this.handleEditMemeTag(memeTag)}>Edit</span>
            </td>
            <td>
                <span
                  className="button"
                  onClick={() => this.handleDeleteMemeTag(memeTag)}>Delete</span>
            </td>
        </tr>

    }

    renderCurrentMemeTags() {
        if (this.state.memeBeingEdited_memetags === null) {
            return <span>memetags being loaded</span>
        }

        return <div>Current Meme tags go here.
            <table>
                <tr>
                    <td>Type</td>
                    <td>Text</td>
                    <td></td>
                    <td></td>
                </tr>
            {Object.values(this.state.memeBeingEdited_memetags).
              map((memeTag: MemeTag) => this.renderMemeTag(memeTag))}
            </table>
        </div>
    }

    renderMemeEdit() {
        let meme_url = "/images/memes/" + this.state.memeBeingEdited.id + ".jpg";
        let current_tags = this.renderCurrentMemeTags();

        return <span>
          <span>Need to edit meme {this.state.memeBeingEdited.id}</span>
            <span><img src={meme_url} alt="some meme"/></span>
          <span>Type would be: {this.state.meme_edit_type}</span>
            {current_tags}
          <span>
              <span>
                  <select
                    value={this.state.meme_edit_type}
                    onChange={(something) => this.changeMemeType(something)}>
                      <option value="text">Text</option>
                      <option value="type">Type</option>
                      <option value="source">Source</option>
                  </select>
              </span>
              <span>
                  <textarea
                    type='text'
                    rows={4}
                    cols={80}
                    value={this.state.meme_edit_text}
                    onChange={(event) => this.handleTextChange(event)} />
              </span>
              <span class="button" onClick={() => this.handleAddTag()}>Add tag</span>
          </span>

          <span className="button" onClick={() => this.cancelMemeEditing()}>Done</span>
        </span>
    }

    render(props: MemeManagementPanelProps, state: MemeManagementPanelState) {
        let error_block = <span>&nbsp;</span>;

        let deleteMemeTagModal = <span>{this.state.confirmMemeTagDelete}</span>;

        if (this.state.confirmMemeTagDelete !== null) {
            console.log("this.state.confirmMemeTagDelete !== null ?");
            deleteMemeTagModal = this.renderMemeTagDeleteModal();
        }

        if (this.state.memeBeingEdited !== null) {
            let this_bit = this.renderMemeEdit();
            return <div class='meme_management_panel_react'>
                {deleteMemeTagModal}
                {this_bit}
            </div>;
        }

        let meme_block = this.renderMemeBlock();

        return  <div class='meme_management_panel_react'>
            {deleteMemeTagModal}
            I am the happy fun time meme management panel.

            <span class="button" onClick={() => this.refreshMemes()}>Refresh</span>
            {meme_block}
        </div>;
    }
}
