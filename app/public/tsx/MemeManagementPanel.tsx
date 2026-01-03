import {h, Component} from "preact";
import {api, GetMemesResponse} from "./generated/api_routes";
import {Meme} from "./generated/types";

export interface MemeManagementPanelProps {
    // no properties currently
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
    memeTagBeingEdited: MemeTag|null;
    editTag_type: string;
    editTag_text: string;
    searchQuery: string;
    searchTagType: string;
    isSearching: boolean;
}

function getDefaultState(/*initialControlParams: object*/): MemeManagementPanelState {
    return {
        memes: [],
        memeBeingEdited: null,
        memeInfo: null,
        memeBeingEdited_memetags: null,
        meme_edit_type: 'text',
        meme_edit_text: '',
        confirmMemeTagDelete: null,
        memeTagBeingEdited: null,
        editTag_type: '',
        editTag_text: '',
        searchQuery: '',
        searchTagType: '',
        isSearching: false
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

    processData(data:GetMemesResponse) {
        if (data.data.memes === undefined) {
            console.error("Server response did not contain 'memes'.");
            return;
        }
        this.setState({memes: data.data.memes})
    }

    refreshMemes() {
        api.memes().
        then((data:GetMemesResponse) => this.processData(data)).
        catch((err:any) => {
            console.error("Failed to fetch memes:", err);
        });
    }

    handleSearchQueryChange(event: Event) {
        // @ts-ignore: It does exist.
        this.setState({ searchQuery: event.currentTarget.value });
    }

    handleSearchTagTypeChange(event: Event) {
        // @ts-ignore: It does exist.
        this.setState({ searchTagType: event.currentTarget.value });
    }

    searchMemes() {
        const params = new URLSearchParams();
        if (this.state.searchQuery) {
            params.append('query', this.state.searchQuery);
        }
        if (this.state.searchTagType) {
            params.append('tag_type', this.state.searchTagType);
        }

        const url = '/api/memes/search' + (params.toString() ? '?' + params.toString() : '');
        
        this.setState({ isSearching: true });

        fetch(url)
            .then((response: Response) => response.json())
            .then((data: GetMemesResponse) => {
                this.setState({ 
                    memes: data.data.memes,
                    isSearching: false 
                });
            })
            .catch((err: any) => {
                console.error("Failed to search memes:", err);
                this.setState({ isSearching: false });
            });
    }

    clearSearch() {
        this.setState({
            searchQuery: '',
            searchTagType: ''
        });
        this.refreshMemes();
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
          then((data: any) => {
              const tags = data.data.meme_tags;
              this.processMemeTagData(tags);
          });
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
                <button
                    type="button"
                    class="button"
                    onClick={() => this.startEditing(meme)}
                >Edit tags</button>
            </td>
        </tr>
    }

    renderMemeBlock() {
        if (this.state.memes && this.state.memes.length === 0) {
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
        this.setState({
            memeTagBeingEdited: memeTag,
            editTag_type: memeTag.type,
            editTag_text: memeTag.text
        });
    }

    handleEditTagTypeChange(event: Event) {
        // @ts-ignore: It does exist.
        this.setState({ editTag_type: event.currentTarget.value });
    }

    handleEditTagTextChange(event: Event) {
        // @ts-ignore: It does exist.
        this.setState({ editTag_text: event.currentTarget.value });
    }

    handleSaveEditTag() {
        if (!this.state.memeTagBeingEdited) return;

        const formData = new FormData();
        formData.append("meme_tag_id", this.state.memeTagBeingEdited.id);
        formData.append("type", this.state.editTag_type);
        formData.append("text", this.state.editTag_text);

        fetch('/api/meme-tag-update/', {
            method: 'PUT',
            body: formData
        })
        .then((response: Response) => response.json())
        .then((data: any) => {
            // Update the tag in the local state
            if (this.state.memeBeingEdited_memetags) {
                const updatedTags = this.state.memeBeingEdited_memetags.map((tag: MemeTag) => {
                    if (tag.id === this.state.memeTagBeingEdited?.id) {
                        return {
                            ...tag,
                            type: this.state.editTag_type,
                            text: this.state.editTag_text
                        };
                    }
                    return tag;
                });
                this.setState({
                    memeBeingEdited_memetags: updatedTags,
                    memeTagBeingEdited: null,
                    editTag_type: '',
                    editTag_text: ''
                });
            }
        })
        .catch((err: any) => {
            console.error("Failed to update tag:", err);
        });
    }

    handleCancelEditTag() {
        this.setState({
            memeTagBeingEdited: null,
            editTag_type: '',
            editTag_text: ''
        });
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

    renderMemeTagEditModal() {
        if (!this.state.memeTagBeingEdited) return null;

        return <div class="modal">
            <div class="modal-content">
                <h3>Edit Tag</h3>
                <div class="edit-tag-form">
                    <div>
                        <label>Type:</label>
                        <select
                            value={this.state.editTag_type}
                            onChange={(e) => this.handleEditTagTypeChange(e)}>
                            <option value="text">Text</option>
                            <option value="type">Type</option>
                            <option value="source">Source</option>
                        </select>
                    </div>
                    <div>
                        <label>Text:</label>
                        <textarea
                            rows={4}
                            cols={60}
                            value={this.state.editTag_text}
                            onChange={(e) => this.handleEditTagTextChange(e)} />
                    </div>
                    <div class="modal-buttons">
                        <span
                            className="button"
                            onClick={() => this.handleSaveEditTag()}>Save</span>
                        <span
                            className="button"
                            onClick={() => this.handleCancelEditTag()}>Cancel</span>
                    </div>
                </div>
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
            return <span class="loading_message">Loading tags...</span>
        }

        if (this.state.memeBeingEdited_memetags.length === 0) {
            return <span class="no_tags_message">No tags yet. Add one below.</span>
        }

        return <table class="meme_tags_table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Text</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {Object.values(this.state.memeBeingEdited_memetags).
                    map((memeTag: MemeTag) => this.renderMemeTag(memeTag))}
            </tbody>
        </table>
    }

    renderMemeEdit() {
        let meme_url = "/images/memes/" + this.state.memeBeingEdited.id + ".jpg";
        let current_tags = this.renderCurrentMemeTags();

        return <div class="meme_edit_container">
            <div class="meme_edit_header">
                <h3>Editing meme</h3>
                <span class="meme_id">{this.state.memeBeingEdited.id}</span>
                <span className="button" onClick={() => this.cancelMemeEditing()}>Done</span>
            </div>

            <div class="meme_edit_content">
                <div class="meme_image_container">
                    <img src={meme_url} alt="meme being edited" class="meme_preview_image" />
                </div>

                <div class="meme_edit_sidebar">
                    <div class="meme_tags_section">
                        <h4>Current Tags</h4>
                        {current_tags}
                    </div>

                    <div class="meme_add_tag_section">
                        <h4>Add New Tag</h4>
                        <div class="add_tag_form">
                            <div class="form_row">
                                <label>Type:</label>
                                <select
                                    value={this.state.meme_edit_type}
                                    onChange={(something) => this.changeMemeType(something)}>
                                    <option value="text">Text</option>
                                    <option value="type">Type</option>
                                    <option value="source">Source</option>
                                </select>
                            </div>
                            <div class="form_row">
                                <label>Tag text:</label>
                                <textarea
                                    rows={4}
                                    value={this.state.meme_edit_text}
                                    onChange={(event) => this.handleTextChange(event)}
                                    placeholder="Enter tag text..." />
                            </div>
                            <span class="button" onClick={() => this.handleAddTag()}>Add tag</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    }

    render(props: MemeManagementPanelProps, state: MemeManagementPanelState) {
        let deleteMemeTagModal = null;
        if (this.state.confirmMemeTagDelete !== null) {
            deleteMemeTagModal = this.renderMemeTagDeleteModal();
        }

        let editMemeTagModal = null;
        if (this.state.memeTagBeingEdited !== null) {
            editMemeTagModal = this.renderMemeTagEditModal();
        }

        if (this.state.memeBeingEdited !== null) {
            let this_bit = this.renderMemeEdit();
            return <div class='meme_management_panel_react'>
                {deleteMemeTagModal}
                {editMemeTagModal}
                {this_bit}
            </div>;
        }

        let meme_block = this.renderMemeBlock();

        const searchSection = <div class="meme_search_section">
            <h4>Search Memes</h4>
            <div class="search_controls">
                <div class="search_input_group">
                    <label>Tag text:</label>
                    <input
                        type="text"
                        value={this.state.searchQuery}
                        onChange={(e) => this.handleSearchQueryChange(e)}
                        placeholder="Search in tag text..."
                    />
                </div>
                <div class="search_input_group">
                    <label>Tag type:</label>
                    <select
                        value={this.state.searchTagType}
                        onChange={(e) => this.handleSearchTagTypeChange(e)}>
                        <option value="">All types</option>
                        <option value="text">Text</option>
                        <option value="type">Type</option>
                        <option value="source">Source</option>
                    </select>
                </div>
                <div class="search_buttons">
                    <span 
                        class="button" 
                        onClick={() => this.searchMemes()}>
                        {this.state.isSearching ? 'Searching...' : 'Search'}
                    </span>
                    <span 
                        class="button" 
                        onClick={() => this.clearSearch()}>
                        Clear
                    </span>
                </div>
            </div>
        </div>;

        return <div class='meme_management_panel_react'>
            {deleteMemeTagModal}
            {editMemeTagModal}
            <h3>Meme Management Panel</h3>
            {searchSection}
            <div class="meme_list_controls">
                <span class="button" onClick={() => this.refreshMemes()}>Refresh All</span>
            </div>
            {meme_block}
        </div>;
    }
}
