import {h, Component} from "preact";
import {api, GetMemesResponse, GetMemesUntaggedResponse, PostMemetagaddResponse, PostMemetagdeleteResponse} from "./generated/api_routes";
import {StoredMeme, createStoredMeme} from "./generated/types";
import {MemeTagType} from "./MemeTagType";
import {MEME_FILE_UPLOAD_FORM_NAME, DUPLICATE_FILENAME, MEMES_DISPLAY_LIMIT} from "./generated/constants";

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

enum UploadStatus {
    Idle = 'idle',
    Uploading = 'uploading',
    Success = 'success',
    Error = 'error'
}

interface FileUploadItem {
    file: File;
    id: string;
    status: UploadStatus;
    message: string|null;
    /** Object URL for image preview; must be revoked when item is removed or panel closed */
    previewUrl: string;
}

interface MemeManagementPanelState {
    memes: Array<StoredMeme>;
    memesTruncated: boolean; // true when API limited results to 50
    memeBeingEdited: StoredMeme|null;
    memeInfo: Array<MemeInfo>;
    memeBeingEdited_memetags: Array<MemeTag>|null;
    meme_edit_text: string;
    meme_text: string|null; // OCR text for the meme being edited
    meme_text_error: string|null;
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
    // Upload state
    showUploadPanel: boolean;
    fileUploads: Array<FileUploadItem>;
    isDragging: boolean;
    uploadSelectedTags: Array<string>; // Tags to apply to all uploaded memes
    uploadTagSearchQuery: string; // Search query for filtering tags
    uploadNewTagText: string; // Text for creating a new tag
    uploadAddTagError: string|null; // Error message for adding new tag
    uploadTagSearchTimeoutId?: number; // Timeout for tag search
    // Multi-select and bulk tag add
    selectedMemeIds: Array<string>;
    bulkSelectedTags: Array<string>;
    bulkTagSearchQuery: string;
    bulkNewTagText: string;
    bulkAddError: string|null;
    bulkAddInProgress: boolean;
}

const MINIMUM_TAG_LENGTH = 4;

function getDefaultState(/*initialControlParams: object*/): MemeManagementPanelState {
    return {
        memes: [],
        memesTruncated: false,
        memeBeingEdited: null,
        memeInfo: null,
        memeBeingEdited_memetags: null,
        meme_edit_text: '',
        meme_text: null,
        meme_text_error: null,
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
        timeoutId: undefined,
        showUploadPanel: false,
        fileUploads: [],
        isDragging: false,
        uploadSelectedTags: [],
        uploadTagSearchQuery: '',
        uploadNewTagText: '',
        uploadAddTagError: null,
        uploadTagSearchTimeoutId: undefined,
        selectedMemeIds: [],
        bulkSelectedTags: [],
        bulkTagSearchQuery: '',
        bulkNewTagText: '',
        bulkAddError: null,
        bulkAddInProgress: false
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
        // GetMemesResponse structure: { result: 'success', data: { memes, truncated? } }
        const memes: StoredMeme[] = data.data.memes.map((meme) => 
            createStoredMeme(meme)
        );
        this.setState({
            memes,
            memesTruncated: data.data.truncated === true
        });
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

    /**
     * Fetches a memes API response (memes + truncated), maps to StoredMeme[], updates state and suggested tags.
     */
    fetchMemesAndApply(url: string, errorContext: string) {
        fetch(url)
            .then((response: Response) => response.json())
            .then((data: GetMemesResponse | GetMemesUntaggedResponse) => {
                const memes: StoredMeme[] = data.data.memes.map((meme) =>
                    createStoredMeme(meme)
                );
                this.setState({
                    memes,
                    memesTruncated: data.data.truncated === true,
                    isSearching: false
                });
                this.loadSuggestedTagsForMemes(memes);
            })
            .catch((err: unknown) => {
                console.error(errorContext, err);
                this.setState({ isSearching: false });
            });
    }

    performSearch(showUntagged: boolean = false) {
        this.setState({ isSearching: true });

        if (showUntagged) {
            this.fetchMemesAndApply('/api/memes/untagged', 'Failed to fetch untagged memes:');
            return;
        }

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
        this.fetchMemesAndApply(url, 'Failed to search memes:');
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

    loadSuggestedTagsForMemes(memes: Array<StoredMeme>) {
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

    startEditing(meme: StoredMeme) {
        this.setState({
            memeBeingEdited: meme,
            memeInfo: null,
            memeBeingEdited_memetags: null,
            meme_text: null,
            meme_text_error: null
        });

        let url = "/api/memes/" + meme.id + "/tags";
        fetch(url).
          then((response:Response) => response.json()).
          then((data: any) => {
              const tags = data.data.meme_tags;
              this.processMemeTagData(tags);
          });

        // Fetch meme text
        let textUrl = "/api/memes/" + meme.id + "/text";
        fetch(textUrl).
          then((response:Response) => response.json()).
          then((data: any) => {
              const memeTextData = data.data.meme_text;
              this.setState({
                  meme_text: memeTextData === null ? '' : memeTextData.text
              });
          }).
          catch((err: any) => {
              console.error("Failed to fetch meme text:", err);
              this.setState({
                  meme_text_error: "Failed to load meme text"
              });
          });
    }

    cancelMemeEditing() {
        this.setState({memeBeingEdited: null})
    }

    toggleMemeSelection(memeId: string) {
        this.setState(prev => {
            const idx = prev.selectedMemeIds.indexOf(memeId);
            const selectedMemeIds = idx === -1
                ? [...prev.selectedMemeIds, memeId]
                : prev.selectedMemeIds.filter(id => id !== memeId);
            return { selectedMemeIds };
        });
    }

    clearMemeSelection() {
        this.setState({
            selectedMemeIds: [],
            bulkSelectedTags: [],
            bulkAddError: null
        });
    }

    handleMemeCardClick(e: MouseEvent, meme: StoredMeme) {
        if ((e as MouseEvent).shiftKey) {
            e.preventDefault();
            this.toggleMemeSelection(meme.id);
        } else {
            this.clearMemeSelection();
            this.startEditing(meme);
        }
    }

    renderMeme(meme: StoredMeme) {
        const meme_url = `/images/memes/${meme.id}.jpg`;
        
        const isSelected = this.state.selectedMemeIds.indexOf(meme.id) !== -1;
        return <div 
            key={meme.id} 
            className={`meme_card${isSelected ? ' meme_card_selected' : ''}`}
            onClick={(e: MouseEvent) => this.handleMemeCardClick(e, meme)}
            style={{cursor: 'pointer'}}
            title={isSelected ? 'Shift+click to deselect' : 'Click to edit, Shift+click to select for bulk tagging'}
        >
            <div className="meme_card_image_container">
                <img src={meme_url} alt={meme.normalized_name} className="meme_thumbnail" />
            </div>
        </div>
    }

    renderMemeBlock() {
        if (this.state.memes && this.state.memes.length === 0) {
            return <span>You don't have any memes uploaded. If you did, you could manage them here.</span>
        }

        return <div>
            {this.state.memesTruncated && (
                <p class="memes_truncated_message">Too many memes, displayed {MEMES_DISPLAY_LIMIT}</p>
            )}
            <div className="meme_grid">
                {Object.values(this.state.memes).map((meme: StoredMeme) => this.renderMeme(meme))}
            </div>
        </div>
    }

    renderBulkAddTagsPanel() {
        const n = this.state.selectedMemeIds.length;
        const bulkSelectedTagsBox = this.state.bulkSelectedTags.length > 0 ? (
            <div class="selected_tags_box">
                <h5>Tags to add to all {n} memes:</h5>
                <div class="tag_list">
                    {this.state.bulkSelectedTags.map((tagText: string) => (
                        <span
                            key={tagText}
                            class="tag selected_tag"
                            onClick={() => this.handleRemoveBulkTag(tagText)}
                            title="Click to remove"
                        >
                            {tagText} ×
                        </span>
                    ))}
                </div>
            </div>
        ) : null;

        const filteredBulkSuggested = this.state.bulkTagSearchQuery.trim() === ''
            ? this.state.suggestedTags
            : this.state.suggestedTags.filter((tag: TagSuggestion) =>
                tag.text.toLowerCase().includes(this.state.bulkTagSearchQuery.toLowerCase())
            );
        const bulkSuggestedTagsBox = filteredBulkSuggested.length > 0 ? (
            <div class="suggested_tags_box">
                <h5>Suggested tags:</h5>
                <div class="tag_list">
                    {filteredBulkSuggested.map((tag: TagSuggestion) => {
                        const isSelected = this.state.bulkSelectedTags.indexOf(tag.text) !== -1;
                        return (
                            <span
                                key={tag.text}
                                class={`tag suggested_tag ${isSelected ? 'tag_selected' : ''}`}
                                onClick={() => this.handleBulkTagClick(tag.text)}
                                title={`${tag.count} memes (Click to ${isSelected ? 'remove' : 'add'})`}
                            >
                                {tag.text} ({tag.count})
                            </span>
                        );
                    })}
                </div>
            </div>
        ) : this.state.bulkTagSearchQuery.trim() !== '' ? (
            <div class="suggested_tags_box">
                <p style="font-size: 0.9rem; color: #666; margin: 0;">No matching tags. Create a new tag below.</p>
            </div>
        ) : null;

        const canAdd = this.state.bulkSelectedTags.length > 0 && !this.state.bulkAddInProgress;

        return (
            <div class="bulk_add_tags_panel">
                <div class="bulk_add_tags_header">
                    <span class="bulk_add_tags_title">{n} meme{n !== 1 ? 's' : ''} selected — add tags to all</span>
                    <span class="button" onClick={() => this.clearMemeSelection()}>Clear selection</span>
                </div>
                <div class="bulk_add_tags_content">
                    {bulkSelectedTagsBox}
                    <div class="tag_search_section">
                        <label>Search tags:</label>
                        <input
                            type="text"
                            value={this.state.bulkTagSearchQuery}
                            onInput={(e) => this.handleBulkTagSearchChange(e)}
                            placeholder="Type to search tags..."
                        />
                    </div>
                    {bulkSuggestedTagsBox}
                    <div class="add_tag_section">
                        <label>Create new tag:</label>
                        <div class="add_tag_input_group">
                            <input
                                type="text"
                                value={this.state.bulkNewTagText}
                                onChange={(e) => this.handleBulkNewTagTextChange(e)}
                                placeholder="Enter new tag text..."
                                onKeyDown={(e: KeyboardEvent) => {
                                    if (e.key === 'Enter') {
                                        this.handleAddBulkTag();
                                    }
                                }}
                            />
                            <span class="button" onClick={() => this.handleAddBulkTag()}>Add tag</span>
                        </div>
                        {this.state.bulkAddError !== null && (
                            <span class="error" style="font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                                {this.state.bulkAddError}
                            </span>
                        )}
                    </div>
                    <div class="bulk_add_actions">
                        <span
                            class="button"
                            onClick={() => canAdd && this.handleAddTagsToSelectedMemes()}
                            style={{ opacity: canAdd ? 1 : 0.6, cursor: canAdd ? 'pointer' : 'not-allowed' }}
                        >
                            {this.state.bulkAddInProgress ? 'Adding…' : `Add tags to ${n} meme${n !== 1 ? 's' : ''}`}
                        </span>
                    </div>
                </div>
            </div>
        );
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

    handleMemeTextChange(event: Event) {
        // @ts-ignore: It does exist.
        const text = event.currentTarget.value;
        this.setState({
            meme_text: text,
            meme_text_error: null
        });
    }

    handleSaveMemeText() {
        if (this.state.memeBeingEdited === null) {
            return;
        }

        const text = this.state.meme_text || '';

        // Validate text length (matches backend limit)
        if (text.length > 4096) {
            this.setState({
                meme_text_error: "Meme text cannot exceed 4096 characters"
            });
            return;
        }

        const formData = new FormData();
        formData.append("text", text);

        fetch('/api/memes/' + this.state.memeBeingEdited.id + '/text', {
            method: 'PUT',
            body: formData
        })
        .then((response: Response) => response.json())
        .then((data: any) => {
            if (data.result === 'success') {
                this.setState({
                    meme_text_error: null
                });
            } else {
                this.setState({
                    meme_text_error: "Failed to save meme text"
                });
            }
        })
        .catch((err: any) => {
            console.error("Failed to save meme text:", err);
            this.setState({
                meme_text_error: "Failed to save meme text. Please try again."
            });
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
          then((data: PostMemetagaddResponse) => {
              this.processTagAddData(data.data.meme_tags);
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
          then((data: PostMemetagdeleteResponse) => {
              this.processMemeTagData(data.data.meme_tags);
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

    handleShowUpload() {
        this.setState({ showUploadPanel: true });
        // Load suggested tags when opening upload panel
        if (this.state.suggestedTags.length === 0) {
            this.loadSuggestedTags();
        }
    }

    handleHideUpload() {
        this.state.fileUploads.forEach(item => {
            if (item.previewUrl) {
                URL.revokeObjectURL(item.previewUrl);
            }
        });
        this.setState({ 
            showUploadPanel: false,
            fileUploads: [],
            isDragging: false,
            uploadSelectedTags: [],
            uploadTagSearchQuery: '',
            uploadNewTagText: '',
            uploadAddTagError: null,
            uploadTagSearchTimeoutId: undefined
        });
        if (this.state.uploadTagSearchTimeoutId !== undefined) {
            clearTimeout(this.state.uploadTagSearchTimeoutId);
        }
    }

    handleUploadTagSearchChange(event: Event) {
        // @ts-ignore: It does exist.
        const newValue = event.currentTarget.value;
        
        // Clear any existing timeout
        if (this.state.uploadTagSearchTimeoutId !== undefined) {
            clearTimeout(this.state.uploadTagSearchTimeoutId);
        }

        // Set a new timeout to search after user stops typing
        const timeoutId = window.setTimeout(() => {
            this.setState({ uploadTagSearchTimeoutId: undefined });
            // Could fetch tag suggestions here if we had a search endpoint
            // For now, we'll filter client-side
        }, 300);

        this.setState({ 
            uploadTagSearchQuery: newValue,
            uploadTagSearchTimeoutId: timeoutId
        });
    }

    handleUploadNewTagTextChange(event: Event) {
        // @ts-ignore: It does exist.
        const text = event.currentTarget.value;
        this.setState({
            uploadNewTagText: text,
            uploadAddTagError: null
        });
    }

    handleAddUploadTag() {
        const tagText = this.state.uploadNewTagText.trim();
        
        // Validate minimum length
        if (tagText.length < MINIMUM_TAG_LENGTH) {
            this.setState({
                uploadAddTagError: `Tag text must be at least ${MINIMUM_TAG_LENGTH} characters long.`
            });
            return;
        }

        // Check if tag already exists
        if (this.state.uploadSelectedTags.indexOf(tagText) !== -1) {
            this.setState({
                uploadAddTagError: 'This tag is already selected.'
            });
            return;
        }

        // Add tag to selected tags
        this.setState({
            uploadSelectedTags: [...this.state.uploadSelectedTags, tagText],
            uploadNewTagText: '',
            uploadAddTagError: null
        });
    }

    handleUploadTagClick(tagText: string) {
        const uploadSelectedTags = [...this.state.uploadSelectedTags];
        const index = uploadSelectedTags.indexOf(tagText);
        
        if (index === -1) {
            uploadSelectedTags.push(tagText);
        } else {
            uploadSelectedTags.splice(index, 1);
        }
        
        this.setState({ uploadSelectedTags });
    }

    handleRemoveUploadTag(tagText: string) {
        const uploadSelectedTags = this.state.uploadSelectedTags.filter(t => t !== tagText);
        this.setState({ uploadSelectedTags });
    }

    handleBulkTagClick(tagText: string) {
        const bulkSelectedTags = [...this.state.bulkSelectedTags];
        const index = bulkSelectedTags.indexOf(tagText);
        if (index === -1) {
            bulkSelectedTags.push(tagText);
        } else {
            bulkSelectedTags.splice(index, 1);
        }
        this.setState({ bulkSelectedTags, bulkAddError: null });
    }

    handleRemoveBulkTag(tagText: string) {
        this.setState({
            bulkSelectedTags: this.state.bulkSelectedTags.filter(t => t !== tagText),
            bulkAddError: null
        });
    }

    handleBulkTagSearchChange(event: Event) {
        const target = event.currentTarget as HTMLInputElement;
        this.setState({ bulkTagSearchQuery: target.value });
    }

    handleBulkNewTagTextChange(event: Event) {
        const target = event.currentTarget as HTMLInputElement;
        this.setState({ bulkNewTagText: target.value, bulkAddError: null });
    }

    handleAddBulkTag() {
        const tagText = this.state.bulkNewTagText.trim();
        if (tagText.length < MINIMUM_TAG_LENGTH) {
            this.setState({
                bulkAddError: `Tag text must be at least ${MINIMUM_TAG_LENGTH} characters long.`
            });
            return;
        }
        if (this.state.bulkSelectedTags.indexOf(tagText) !== -1) {
            return;
        }
        this.setState({
            bulkSelectedTags: [...this.state.bulkSelectedTags, tagText],
            bulkNewTagText: '',
            bulkAddError: null
        });
    }

    handleAddTagsToSelectedMemes() {
        const { selectedMemeIds, bulkSelectedTags, bulkAddInProgress } = this.state;
        if (bulkAddInProgress || selectedMemeIds.length === 0 || bulkSelectedTags.length === 0) {
            return;
        }
        this.setState({ bulkAddInProgress: true, bulkAddError: null });
        const addOne = (memeId: string, index: number): Promise<void> => {
            return this.addTagsToMeme(memeId, bulkSelectedTags).then(() => {
                if (index < selectedMemeIds.length - 1) {
                    return new Promise<void>(r => setTimeout(r, 80)).then(() => addOne(selectedMemeIds[index + 1], index + 1));
                }
            });
        };
        addOne(selectedMemeIds[0], 0)
            .then(() => {
                this.setState({
                    bulkAddInProgress: false,
                    bulkSelectedTags: [],
                    bulkNewTagText: '',
                    bulkAddError: null
                });
                this.refreshMemes();
            })
            .catch((err: unknown) => {
                console.error("Bulk add tags failed:", err);
                this.setState({
                    bulkAddInProgress: false,
                    bulkAddError: "Failed to add tags to some memes. Please try again."
                });
            });
    }

    addTagsToMeme(memeId: string, tagTexts: Array<string>): Promise<void> {
        if (tagTexts.length === 0) {
            return Promise.resolve();
        }

        // Add tags sequentially to avoid overwhelming the server
        const addTagPromises = tagTexts.map(tagText => {
            const formData = new FormData();
            formData.append("meme_id", memeId);
            formData.append("type", MemeTagType.USER_TAG);
            formData.append("text", tagText);

            return fetch('/api/meme-tag-add/', {
                method: 'POST',
                body: formData
            }).then((response: Response) => {
                if (!response.ok) {
                    console.error(`Failed to add tag "${tagText}" to meme ${memeId}`);
                }
                return response.json();
            });
        });

        return Promise.all(addTagPromises).then(() => {});
    }

    addFilesToUpload(files: FileList | null) {
        if (!files || files.length === 0) return;

        const imageFiles: File[] = [];
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                imageFiles.push(file);
            }
        }

        if (imageFiles.length === 0) {
            return;
        }

        const newUploads: FileUploadItem[] = imageFiles.map(file => ({
            file: file,
            id: `${file.name}-${Date.now()}-${Math.random()}`,
            status: UploadStatus.Idle,
            message: null as string|null,
            previewUrl: URL.createObjectURL(file)
        }));

        this.setState(prevState => ({
            fileUploads: [...prevState.fileUploads, ...newUploads]
        }));
    }

    handleDragEnter(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        this.setState({ isDragging: true });
    }

    handleDragOver(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        this.setState({ isDragging: true });
    }

    handleDragLeave(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        this.setState({ isDragging: false });
    }

    handleDrop(event: DragEvent) {
        event.preventDefault();
        event.stopPropagation();
        this.setState({ isDragging: false });

        const files = event.dataTransfer?.files;
        this.addFilesToUpload(files || null);
    }

    onFileChange = (event: any) => {
        const files = event.target.files;
        this.addFilesToUpload(files);
        // Reset the input so the same file can be selected again
        event.target.value = '';
    };

    uploadFile(uploadItem: FileUploadItem) {
        // Update status to uploading
        this.setState(prevState => ({
            fileUploads: prevState.fileUploads.map(item => 
                item.id === uploadItem.id 
                    ? { ...item, status: UploadStatus.Uploading, message: 'Uploading...' }
                    : item
            )
        }));

        const formData = new FormData();
        formData.append(
            MEME_FILE_UPLOAD_FORM_NAME,
            uploadItem.file,
            uploadItem.file.name
        );

        let params = {
            method: 'POST',
            body: formData
        }
        
        fetch('/api/meme-upload/', params)
            .then((response: Response) => response.json())
            .then((data: any) => {
                if (data.result === 'success') {
                    const memeId = data.meme_id;
                    
                    // Add selected tags to the uploaded meme
                    if (this.state.uploadSelectedTags.length > 0) {
                        this.addTagsToMeme(memeId, this.state.uploadSelectedTags)
                            .then(() => {
                                // Tags added successfully
                            })
                            .catch((err: any) => {
                                console.error("Failed to add tags to meme:", err);
                            });
                    }

                    this.setState(prevState => ({
                        fileUploads: prevState.fileUploads.map(item => 
                            item.id === uploadItem.id 
                                ? { ...item, status: UploadStatus.Success, message: 'Uploaded successfully!' }
                                : item
                        )
                    }));
                    // Refresh the memes list after successful upload
                    this.refreshMemes();
                } else {
                    // Handle duplicate filename error with user-friendly message
                    let errorMessage = data.error || 'Upload failed.';
                    if (data.error_code === DUPLICATE_FILENAME && data.error_data?.filename) {
                        errorMessage = `A file named "${data.error_data.filename}" has already been uploaded. Please rename the file and try again.`;
                    }
                    
                    this.setState(prevState => ({
                        fileUploads: prevState.fileUploads.map(item => 
                            item.id === uploadItem.id 
                                ? { ...item, status: UploadStatus.Error, message: errorMessage }
                                : item
                        )
                    }));
                }
            })
            .catch((err: any) => {
                this.setState(prevState => ({
                    fileUploads: prevState.fileUploads.map(item => 
                        item.id === uploadItem.id 
                            ? { ...item, status: UploadStatus.Error, message: 'Upload failed: ' + err.message }
                            : item
                    )
                }));
            });
    }

    onFileUpload = () => {
        const filesToUpload = this.state.fileUploads.filter(item => item.status === UploadStatus.Idle);
        
        if (filesToUpload.length === 0) {
            return;
        }

        // Upload files sequentially to avoid overwhelming the server
        filesToUpload.forEach((uploadItem, index) => {
            setTimeout(() => {
                this.uploadFile(uploadItem);
            }, index * 100); // Small delay between uploads
        });
    };

    removeUploadItem(uploadId: string) {
        this.setState(prevState => {
            const item = prevState.fileUploads.find(i => i.id === uploadId);
            if (item?.previewUrl) {
                URL.revokeObjectURL(item.previewUrl);
            }
            return {
                fileUploads: prevState.fileUploads.filter(item => item.id !== uploadId)
            };
        });
    }

    clearSuccessfulUploads = () => {
        this.setState(prevState => {
            const toRemove = prevState.fileUploads.filter(item => item.status === UploadStatus.Success);
            toRemove.forEach(item => {
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
            });
            const removeIds = new Set(toRemove.map(i => i.id));
            return {
                fileUploads: prevState.fileUploads.filter(item => !removeIds.has(item.id))
            };
        });
    };

    clearErrorUploads = () => {
        this.setState(prevState => {
            const toRemove = prevState.fileUploads.filter(item => item.status === UploadStatus.Error);
            toRemove.forEach(item => {
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
            });
            const removeIds = new Set(toRemove.map(i => i.id));
            return {
                fileUploads: prevState.fileUploads.filter(item => !removeIds.has(item.id))
            };
        });
    };

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
                    <div className="meme_text_section">
                        <h4>Meme Text (OCR)</h4>
                        <textarea
                            rows={8}
                            cols={60}
                            value={this.state.meme_text === null ? '' : this.state.meme_text}
                            onChange={(e) => this.handleMemeTextChange(e)}
                            onBlur={() => this.handleSaveMemeText()}
                            placeholder="Meme text from OCR (if available)..."
                            style={{width: '100%', minHeight: '120px'}}
                        />
                        {this.state.meme_text_error !== null && (
                            <span class="error">{this.state.meme_text_error}</span>
                        )}
                        {this.state.meme_text !== null && this.state.meme_text.length > 0 && (
                            <div style={{marginTop: '8px', fontSize: '0.9em', color: '#666'}}>
                                Text auto-saves when you click outside the textarea. ({this.state.meme_text.length}/4096 characters)
                            </div>
                        )}
                    </div>

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

        const bulkAddPanel = this.state.selectedMemeIds.length > 0 ? this.renderBulkAddTagsPanel() : null;

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

        // Upload panel (shown when showUploadPanel is true)
        if (this.state.showUploadPanel) {
            const dropZoneClass = this.state.isDragging 
                ? 'meme_drop_zone meme_drop_zone_dragging' 
                : 'meme_drop_zone';

            const hasFilesToUpload = this.state.fileUploads.length > 0;
            const isUploading = this.state.fileUploads.some(item => item.status === UploadStatus.Uploading);
            const hasIdleFiles = this.state.fileUploads.some(item => item.status === UploadStatus.Idle);
            const hasSuccessfulUploads = this.state.fileUploads.some(item => item.status === UploadStatus.Success);
            const hasErrorUploads = this.state.fileUploads.some(item => item.status === UploadStatus.Error);

            // Upload tag selection panel
            const uploadSelectedTagsBox = this.state.uploadSelectedTags.length > 0 ? (
                <div class="selected_tags_box">
                    <h5>Selected Tags (will be applied to all uploads):</h5>
                    <div class="tag_list">
                        {this.state.uploadSelectedTags.map((tagText: string) => (
                            <span 
                                key={tagText}
                                class="tag selected_tag"
                                onClick={() => this.handleRemoveUploadTag(tagText)}
                                title="Click to remove"
                            >
                                {tagText} ×
                            </span>
                        ))}
                    </div>
                </div>
            ) : null;

            // Filter suggested tags based on search query
            const filteredSuggestedTags = this.state.uploadTagSearchQuery.trim() === ''
                ? this.state.suggestedTags
                : this.state.suggestedTags.filter((tag: TagSuggestion) =>
                    tag.text.toLowerCase().includes(this.state.uploadTagSearchQuery.toLowerCase())
                );

            const uploadSuggestedTagsBox = filteredSuggestedTags.length > 0 ? (
                <div class="suggested_tags_box">
                    <h5>Suggested Tags:</h5>
                    <div class="tag_list">
                        {filteredSuggestedTags.map((tag: TagSuggestion) => {
                            const isSelected = this.state.uploadSelectedTags.indexOf(tag.text) !== -1;
                            return (
                                <span
                                    key={tag.text}
                                    class={`tag suggested_tag ${isSelected ? 'tag_selected' : ''}`}
                                    onClick={() => this.handleUploadTagClick(tag.text)}
                                    title={`${tag.count} memes (Click to ${isSelected ? 'remove' : 'add'})`}
                                >
                                    {tag.text} ({tag.count})
                                </span>
                            );
                        })}
                    </div>
                </div>
            ) : this.state.uploadTagSearchQuery.trim() !== '' ? (
                <div class="suggested_tags_box">
                    <p style="font-size: 0.9rem; color: #666; margin: 0;">No matching tags found. Create a new tag below.</p>
                </div>
            ) : null;

            return <div class='meme_management_panel_react'>
                {deleteMemeTagModal}
                {editMemeTagModal}
                <div class="meme_upload_fullwidth">
                    <div class="meme_upload_header">
                        <h3>Upload Memes</h3>
                        <span class="button" onClick={() => this.handleHideUpload()}>Close</span>
                    </div>
                    <div class="meme_upload_content">
                        <div class="meme_upload_left">
                            <div
                                class={dropZoneClass}
                                onDragEnter={(e: DragEvent) => this.handleDragEnter(e)}
                                onDragOver={(e: DragEvent) => this.handleDragOver(e)}
                                onDragLeave={(e: DragEvent) => this.handleDragLeave(e)}
                                onDrop={(e: DragEvent) => this.handleDrop(e)}>
                                <p class="drop_zone_text">
                                    {this.state.isDragging 
                                        ? 'Drop your images here!' 
                                        : 'Drag and drop images here, or use the button below'}
                                </p>
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    onChange={this.onFileChange}
                                    disabled={isUploading}
                                />
                            </div>
                        </div>
                        <div class="meme_upload_right">
                            <div class="meme_upload_tag_panel">
                                <h4>Tags to Apply</h4>
                                <p class="tag_panel_description">Select tags to apply to all uploaded images:</p>
                                {uploadSelectedTagsBox}
                                
                                <div class="tag_search_section">
                                    <label>Search tags:</label>
                                    <input
                                        type="text"
                                        value={this.state.uploadTagSearchQuery}
                                        onInput={(e) => this.handleUploadTagSearchChange(e)}
                                        placeholder="Type to search tags..."
                                    />
                                </div>

                                {uploadSuggestedTagsBox}

                                <div class="add_tag_section">
                                    <label>Create new tag:</label>
                                    <div class="add_tag_input_group">
                                        <input
                                            type="text"
                                            value={this.state.uploadNewTagText}
                                            onChange={(e) => this.handleUploadNewTagTextChange(e)}
                                            placeholder="Enter new tag text..."
                                            onKeyDown={(e: KeyboardEvent) => {
                                                if (e.key === 'Enter') {
                                                    this.handleAddUploadTag();
                                                }
                                            }}
                                        />
                                        <span 
                                            class="button" 
                                            onClick={() => this.handleAddUploadTag()}>
                                            Add Tag
                                        </span>
                                    </div>
                                    {this.state.uploadAddTagError !== null && (
                                        <span class="error" style="font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                                            {this.state.uploadAddTagError}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {hasIdleFiles && (
                        <div class="upload_actions">
                            <button 
                                onClick={this.onFileUpload} 
                                disabled={isUploading}
                                class="upload_button">
                                {isUploading ? 'Uploading...' : `Upload ${this.state.fileUploads.filter(item => item.status === UploadStatus.Idle).length} file(s)`}
                            </button>
                        </div>
                    )}

                    {(hasSuccessfulUploads || hasErrorUploads) && (
                        <div class="upload_actions">
                            {hasSuccessfulUploads && (
                                <button
                                    type="button"
                                    class="button"
                                    onClick={this.clearSuccessfulUploads}>
                                    Clear uploads
                                </button>
                            )}
                            {hasErrorUploads && (
                                <button
                                    type="button"
                                    class="button"
                                    onClick={this.clearErrorUploads}>
                                    Clear errors
                                </button>
                            )}
                        </div>
                    )}

                    {hasFilesToUpload && (
                        <div class="file_upload_list">
                            {this.state.fileUploads.map((uploadItem) => {
                                let statusClass = 'upload_item_idle';
                                if (uploadItem.status === UploadStatus.Uploading) {
                                    statusClass = 'upload_item_uploading';
                                } else if (uploadItem.status === UploadStatus.Success) {
                                    statusClass = 'upload_item_success';
                                } else if (uploadItem.status === UploadStatus.Error) {
                                    statusClass = 'upload_item_error';
                                }

                                return <div key={uploadItem.id} class={`file_upload_item ${statusClass}`}>
                                    <img
                                        src={uploadItem.previewUrl}
                                        alt=""
                                        class="file_upload_preview"
                                    />
                                    <div class="file_upload_info">
                                        <span class="file_name">{uploadItem.file.name}</span>
                                        <span class="file_size">({Math.round(uploadItem.file.size / 1024)} KB)</span>
                                    </div>
                                    <div class="file_upload_status">
                                        {uploadItem.status === UploadStatus.Idle && <span>Ready to upload</span>}
                                        {uploadItem.status === UploadStatus.Uploading && <span>Uploading...</span>}
                                        {uploadItem.status === UploadStatus.Success && <span class="status_success">✓ {uploadItem.message}</span>}
                                        {uploadItem.status === UploadStatus.Error && <span class="status_error">✗ {uploadItem.message}</span>}
                                    </div>
                                    {(uploadItem.status === UploadStatus.Idle || uploadItem.status === UploadStatus.Error) && (
                                        <button 
                                            class="button remove_file_button"
                                            onClick={() => this.removeUploadItem(uploadItem.id)}>
                                            Remove
                                        </button>
                                    )}
                                </div>;
                            })}
                        </div>
                    )}
                </div>
            </div>;
        }

        // Normal view with horizontal layout
        const searchByTextSection = <div class="meme_search_box">
            <label>Search by text:</label>
            <input
                type="text"
                value={this.state.searchTextQuery}
                onInput={(e) => this.handleSearchTextQueryChange(e)}
                placeholder="Search in meme text (OCR)..."
            />
        </div>;

        const searchByTagsSection = <div class="meme_search_box">
            {selectedTagsBox}
            {suggestedTagsBox}
            <label>Search by tags:</label>
            <input
                type="text"
                value={this.state.searchQuery}
                onChange={(e) => this.handleSearchQueryChange(e)}
                placeholder="Search in tag text..."
            />
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
                <span 
                    class="button untagged_button"
                    onClick={() => this.performSearch(true)}
                    title="Show only memes with no tags (so you can add some)">
                    Show untagged
                </span>
            </div>
        </div>;

        const uploadButtonSection = <div class="meme_upload_button_section">
            <span 
                class="button meme_upload_button"
                onClick={() => this.handleShowUpload()}>
                Upload Meme
            </span>
        </div>;

        return <div class='meme_management_panel_react'>
            {deleteMemeTagModal}
            {editMemeTagModal}
            <div class="meme_search_bar">
                {searchByTextSection}
                {searchByTagsSection}
                {uploadButtonSection}
            </div>
            <div class="meme_list_controls">
                <span class="button" onClick={() => this.refreshMemes()}>Refresh All</span>
            </div>
            {bulkAddPanel}
            {meme_block}
        </div>;
    }
}
