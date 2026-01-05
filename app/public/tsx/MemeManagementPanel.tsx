import {h, Component} from "preact";
import {api, GetMemesResponse} from "./generated/api_routes";
import {Meme} from "./generated/types";
import {MemeTagType} from "./MemeTagType";

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




interface TagSuggestion {
    text: string;
    count: number;
}

interface MemeManagementPanelState {
    memes: Array<Meme>;
    memeBeingEdited: Meme|null;
    memeInfo: Array<MemeInfo>;
    memeBeingEdited_memetags: Array<MemeTag>|null;
    meme_edit_text: string;
    addTagError: string|null;
    confirmMemeTagDelete: null|MemeTag;
    memeTagBeingEdited: MemeTag|null;
    editTag_text: string;
    editTagError: string|null;
    searchQuery: string;
    searchTextQuery: string;
    selectedTags: Array<string>; // Array of tag texts that are selected for search
    suggestedTags: Array<TagSuggestion>; // Suggested tags to show
    isSearching: boolean;
    timeoutId?: number;
}

const MINIMUM_TAG_LENGTH = 4;

function getDefaultState(/*initialControlParams: object*/): MemeManagementPanelState {
    return {
        memes: [],
        memeBeingEdited: null,
        memeInfo: null,
        memeBeingEdited_memetags: null,
        meme_edit_text: '',
        addTagError: null,
        confirmMemeTagDelete: null,
        memeTagBeingEdited: null,
        editTag_text: '',
        editTagError: null,
        searchQuery: '',
        searchTextQuery: '',
        selectedTags: [],
        suggestedTags: [],
        isSearching: false,
        timeoutId: undefined
    };
}

export class MemeManagementPanel extends Component<MemeManagementPanelProps, MemeManagementPanelState> {

    constructor(props: MemeManagementPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);
    }

    componentDidMount() {
        this.refreshMemes();
        this.loadSuggestedTags();
        this.keydownHandler = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && this.state.confirmMemeTagDelete !== null) {
                this.handleCancelDeleteMemeTag();
            }
        };
        document.addEventListener('keydown', this.keydownHandler);
    }

    componentWillUnmount() {
        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
        }
        if (this.state.timeoutId) {
            clearTimeout(this.state.timeoutId);
        }
    }

    private keydownHandler?: (e: KeyboardEvent) => void;

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
        then((data:GetMemesResponse) => {
            this.processData(data);
            // Load suggested tags when refreshing (showing all memes)
            this.loadSuggestedTags();
        }).
        catch((err:any) => {
            console.error("Failed to fetch memes:", err);
        });
    }

    handleSearchQueryChange(event: Event) {
        // @ts-ignore: It does exist.
        this.setState({ searchQuery: event.currentTarget.value });
    }

    handleSearchTextQueryChange(event: Event) {
        // @ts-ignore: It does exist.
        const newValue = event.currentTarget.value;
        
        // Clear any existing timeout
        if (this.state.timeoutId !== undefined) {
            clearTimeout(this.state.timeoutId);
        }

        // Set a new timeout to search after 50ms
        const timeoutId = window.setTimeout(() => {
            this.setState({ timeoutId: undefined });
            this.performSearch();
        }, 50);

        this.setState({ 
            searchTextQuery: newValue,
            timeoutId: timeoutId
        });
    }

    performSearch() {
        const params = new URLSearchParams();
        if (this.state.searchQuery) {
            params.append('query', this.state.searchQuery);
        }
        if (this.state.searchTextQuery) {
            params.append('text_search', this.state.searchTextQuery);
        }
        if (this.state.selectedTags.length > 0) {
            params.append('tags', this.state.selectedTags.join(','));
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
                // Update suggested tags based on search results
                this.loadSuggestedTagsForMemes(data.data.memes);
            })
            .catch((err: any) => {
                console.error("Failed to search memes:", err);
                this.setState({ isSearching: false });
            });
    }

    searchMemes() {
        // Clear any pending timeout
        if (this.state.timeoutId !== undefined) {
            clearTimeout(this.state.timeoutId);
        }
        this.setState({ timeoutId: undefined });
        this.performSearch();
    }

    clearSearch() {
        this.setState({
            searchQuery: '',
            searchTextQuery: '',
            selectedTags: []
        });
        this.refreshMemes();
        this.loadSuggestedTags();
    }

    loadSuggestedTags() {
        fetch('/api/memes/tag-suggestions?limit=10')
            .then((response: Response) => response.json())
            .then((data: any) => {
                this.setState({ 
                    suggestedTags: data.data.tags || []
                });
            })
            .catch((err: any) => {
                console.error("Failed to load suggested tags:", err);
            });
    }

    loadSuggestedTagsForMemes(memes: Array<Meme>) {
        if (memes.length === 0) {
            this.loadSuggestedTags();
            return;
        }

        const memeIds = memes.map(m => m.id).join(',');
        fetch(`/api/memes/tag-suggestions?meme_ids=${memeIds}&limit=10`)
            .then((response: Response) => response.json())
            .then((data: any) => {
                this.setState({ 
                    suggestedTags: data.data.tags || []
                });
            })
            .catch((err: any) => {
                console.error("Failed to load suggested tags for memes:", err);
            });
    }

    handleTagClick(tagText: string) {
        const selectedTags = [...this.state.selectedTags];
        const index = selectedTags.indexOf(tagText);
        
        if (index === -1) {
            // Add tag
            selectedTags.push(tagText);
        } else {
            // Remove tag
            selectedTags.splice(index, 1);
        }
        
        this.setState({ selectedTags }, () => {
            // Perform search after updating selected tags
            this.performSearch();
        });
    }

    handleRemoveSelectedTag(tagText: string) {
        const selectedTags = this.state.selectedTags.filter(t => t !== tagText);
        this.setState({ selectedTags }, () => {
            // Perform search after removing tag
            this.performSearch();
        });
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
        const meme_url = `/images/memes/${meme.id}.jpg`;
        
        return <tr key={meme.id}>
            <td>
                <img src={meme_url} alt={meme.normalized_name} className="meme_thumbnail" />
            </td>
            {/*<td>*/}
            {/*    {meme.original_filename}*/}
            {/*</td>*/}
            <td>
                <button
                    type="button"
                    className="button"
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
                <th>Image</th>
                {/*<th>Name</th>*/}
                <th>Edit</th>
            </tr>
            {Object.values(this.state.memes).map((meme: Meme) => this.renderMeme(meme))}
        </table>
    }

    handleTextChange(something: Event) {
        // @ts-ignore: It does exist.
        const text = something.currentTarget.value;
        console.log("new text = " + text);
        // Clear error when user starts typing
        this.setState({
            // @ts-ignore: It does exist.
            meme_edit_text: text,
            addTagError: null
        });
    }

    processTagAddData(memeTags: Array<MemeTag>) {
        this.setState({
            memeBeingEdited_memetags: memeTags
        });
    }

    handleAddTag() {
        const tagText = this.state.meme_edit_text.trim();
        
        // Validate minimum length
        if (tagText.length < MINIMUM_TAG_LENGTH) {
            this.setState({
                addTagError: `Tag text must be at least ${MINIMUM_TAG_LENGTH} characters long.`
            });
            return;
        }

        console.log("Add tag with text: ", tagText);
        const formData = new FormData();

        formData.append("meme_id", this.state.memeBeingEdited.id);
        formData.append("type", MemeTagType.USER_TAG);
        formData.append("text", tagText);

        let params = {
            method: 'POST',
            body: formData
        }

        fetch('/api/meme-tag-add/', params).
          then((response:Response) => response.json()).
          then((data:any) => {
              this.processTagAddData(data);
              // Clear the text input and error after successful add
              this.setState({ 
                  meme_edit_text: '',
                  addTagError: null
              });
          })
          .catch((err: any) => {
              console.error("Failed to add tag:", err);
              this.setState({
                  addTagError: "Failed to add tag. Please try again."
              });
          });
    }

    handleEditMemeTag(memeTag: MemeTag) {
        // Only allow editing user_tag tags
        if (memeTag.type !== MemeTagType.USER_TAG) {
            return;
        }
        this.setState({
            memeTagBeingEdited: memeTag,
            editTag_text: memeTag.text
        });
    }

    handleEditTagTextChange(event: Event) {
        // @ts-ignore: It does exist.
        const text = event.currentTarget.value;
        // Clear error when user starts typing
        this.setState({ 
            editTag_text: text,
            editTagError: null
        });
    }

    handleSaveEditTag() {
        if (!this.state.memeTagBeingEdited) return;

        const tagText = this.state.editTag_text.trim();

        // Validate minimum length
        if (tagText.length < MINIMUM_TAG_LENGTH) {
            this.setState({
                editTagError: `Tag text must be at least ${MINIMUM_TAG_LENGTH} characters long.`
            });
            return;
        }

        const formData = new FormData();
        formData.append("meme_tag_id", this.state.memeTagBeingEdited.id);
        formData.append("type", MemeTagType.USER_TAG);
        formData.append("text", tagText);

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
                            type: MemeTagType.USER_TAG,
                            text: tagText
                        };
                    }
                    return tag;
                });
                this.setState({
                    memeBeingEdited_memetags: updatedTags,
                    memeTagBeingEdited: null,
                    editTag_text: '',
                    editTagError: null
                });
            }
        })
        .catch((err: any) => {
            console.error("Failed to update tag:", err);
            this.setState({
                editTagError: "Failed to update tag. Please try again."
            });
        });
    }

    handleCancelEditTag() {
        this.setState({
            memeTagBeingEdited: null,
            editTag_text: '',
            editTagError: null
        });
    }

    handleConfirmDeleteMemeTag() {
        if (!this.state.confirmMemeTagDelete || !this.state.memeBeingEdited) {
            return;
        }

        console.log("Need to delete");
        const formData = new FormData();

        formData.append("meme_id", this.state.memeBeingEdited.id);
        formData.append("meme_tag_id", this.state.confirmMemeTagDelete.id);

        let params = {
            method: 'POST',
            body: formData
        }
        fetch('/api/meme-tag-delete/', params).
          then((response:Response) => {
              if (!response.ok) {
                  return response.json().then((data: any) => {
                      throw new Error(data.message || 'Delete failed');
                  });
              }
              return response.json();
          }).
          then((memeTags: Array<MemeTag>) => {
              this.processMemeTagData(memeTags);
              this.setState({ confirmMemeTagDelete: null });
          })
          .catch((err: any) => {
              console.error("Failed to delete tag:", err);
              // Could show an error to the user here if needed
          });
        }

    handleCancelDeleteMemeTag() {
        console.log('handleCancelDeleteMemeTag');
        this.setState({confirmMemeTagDelete: null});
    }

    handleDeleteMemeTag(memeTag: MemeTag) {
        // Only allow deleting user_tag tags
        if (memeTag.type !== MemeTagType.USER_TAG) {
            return;
        }
        this.setState({confirmMemeTagDelete: memeTag });
    }

    renderMemeTagDeleteModal() {
        if (!this.state.confirmMemeTagDelete) return null;

        return <div class="modal">
            <div class="modal-content delete-modal-content">
                <span class="close" onClick={() => this.handleCancelDeleteMemeTag()}>&times;</span>
                <p class="delete-modal-text">Are you sure you want to delete this tag?</p>

                <div class="modal-buttons">
                    <span
                      className="button"
                      onClick={() => this.handleCancelDeleteMemeTag()}>Cancel</span>

                    <span
                      className="button"
                      onClick={() => this.handleConfirmDeleteMemeTag()}>Delete</span>
                </div>
            </div>
        </div>
    }

    renderMemeTagEditModal() {
        if (!this.state.memeTagBeingEdited) return null;

        let editTagError = null;
        if (this.state.editTagError !== null) {
            editTagError = <span class="error">{this.state.editTagError}</span>;
        }

        return <div class="modal">
            <div class="modal-content">
                <h3>Edit Tag</h3>
                <div class="edit-tag-form">
                    <div>
                        <label>Text:</label>
                        <textarea
                            rows={4}
                            cols={60}
                            value={this.state.editTag_text}
                            onChange={(e) => this.handleEditTagTextChange(e)} />
                        {editTagError}
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
                    <th>Tags</th>
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

        return <div className="meme_edit_container">
            <div className="meme_edit_header">
                <h3>Editing meme</h3>
                <span className="button" onClick={() => this.cancelMemeEditing()}>Done</span>
            </div>

            <div className="meme_edit_content">
                <div className="meme_image_container">
                    <img src={meme_url} alt="meme being edited" class="meme_preview_image"/>
                </div>

                <div className="meme_edit_sidebar">
                    <div className="meme_tags_section">
                        {current_tags}
                    </div>

                    <div className="meme_add_tag_section">

                        <div className="add_tag_form">
                            <div className="form_row">
                                <div className="input_row">
                                    <input
                                      type="text"
                                      value={this.state.meme_edit_text}
                                      onChange={(event) => this.handleTextChange(event)}
                                      placeholder="Enter tag text..."/>
                                    <span className="button" onClick={() => this.handleAddTag()}>Add tag</span>
                                </div>
                                {this.state.addTagError !== null && (
                                  <span class="error">{this.state.addTagError}</span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="meme_done_container">
                <span className="button" onClick={() => this.cancelMemeEditing()}>Done</span>
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

        const selectedTagsBox = this.state.selectedTags.length > 0 ? (
            <div class="selected_tags_box">
                <h5>Selected Tags:</h5>
                <div class="tag_list">
                    {this.state.selectedTags.map((tagText: string) => (
                        <span 
                            key={tagText}
                            class="tag selected_tag"
                            onClick={() => this.handleRemoveSelectedTag(tagText)}
                            title="Click to remove"
                        >
                            {tagText} ×
                        </span>
                    ))}
                </div>
            </div>
        ) : null;

        const suggestedTagsBox = this.state.suggestedTags.length > 0 ? (
            <div class="suggested_tags_box">
                <h5>Suggested Tags:</h5>
                <div class="tag_list">
                    {this.state.suggestedTags.map((tag: TagSuggestion) => {
                        const isSelected = this.state.selectedTags.indexOf(tag.text) !== -1;
                        return (
                            <span
                                key={tag.text}
                                class={`tag suggested_tag ${isSelected ? 'tag_selected' : ''}`}
                                onClick={() => this.handleTagClick(tag.text)}
                                title={`${tag.count} memes (Click to ${isSelected ? 'remove' : 'add'})`}
                            >
                                {tag.text} ({tag.count})
                            </span>
                        );
                    })}
                </div>
            </div>
        ) : null;

        const searchSection = <div class="meme_search_section">
            <h4>Search Memes</h4>
            {selectedTagsBox}
            {suggestedTagsBox}
            <div class="search_controls">
                <div class="search_input_group">
                    <label>Search by tags:</label>
                    <input
                        type="text"
                        value={this.state.searchQuery}
                        onChange={(e) => this.handleSearchQueryChange(e)}
                        placeholder="Search in tag text..."
                    />
                </div>
                <div class="search_input_group">
                    <label>Search by meme text:</label>
                    <input
                        type="text"
                        value={this.state.searchTextQuery}
                        onInput={(e) => this.handleSearchTextQueryChange(e)}
                        placeholder="Search in meme text (OCR)..."
                    />
                    {this.state.timeoutId !== undefined && (
                        <span style="font-size: 0.8em; color: #666;">(Timeout active: {this.state.timeoutId})</span>
                    )}
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
            {searchSection}
            <div class="meme_list_controls">
                <span class="button" onClick={() => this.refreshMemes()}>Refresh All</span>
            </div>
            {meme_block}
        </div>;
    }
}
