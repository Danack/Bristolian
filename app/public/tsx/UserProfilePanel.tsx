import {h, Component} from "preact";
import {call_api} from "./functions";
import {
    MINIMUM_DISPLAY_NAME_LENGTH,
    MAXIMUM_DISPLAY_NAME_LENGTH,
    MINIMUM_ABOUT_ME_LENGTH,
    MAXIMUM_ABOUT_ME_LENGTH
} from "./generated/constants";

export interface UserProfilePanelProps {
    user_id: string;
    display_name: string;
    about_me: string | null;
    avatar_image_id: string | null;
    is_own_profile: boolean;
}

interface UserProfilePanelState {
    display_name: string;
    about_me: string;
    original_display_name: string;
    original_about_me: string;
    is_editing: boolean;
    changes_made: boolean;
    error: string | null;
    saving: boolean;
    uploading_avatar: boolean;
}

function getDefaultState(props: UserProfilePanelProps): UserProfilePanelState {
    return {
        display_name: props.display_name,
        about_me: props.about_me || '',
        original_display_name: props.display_name,
        original_about_me: props.about_me || '',
        is_editing: false,
        changes_made: false,
        error: null,
        saving: false,
        uploading_avatar: false
    };
}

export class UserProfilePanel extends Component<UserProfilePanelProps, UserProfilePanelState> {
    constructor(props: UserProfilePanelProps) {
        super(props);
        this.state = getDefaultState(props);
    }

    handleDisplayNameChange = (value: string) => {
        this.setState({
            display_name: value,
            changes_made: value !== this.state.original_display_name || 
                          this.state.about_me !== this.state.original_about_me
        });
    };

    handleAboutMeChange = (value: string) => {
        this.setState({
            about_me: value,
            changes_made: value !== this.state.original_about_me || 
                          this.state.display_name !== this.state.original_display_name
        });
    };

    startEditing = () => {
        this.setState({ is_editing: true });
    };

    cancelEditing = () => {
        this.setState({
            display_name: this.state.original_display_name,
            about_me: this.state.original_about_me,
            is_editing: false,
            changes_made: false,
            error: null
        });
    };

    handleSave = () => {
        this.setState({ saving: true, error: null });

        const endpoint = '/api/user/profile';
        const form_data = new FormData();
        form_data.append("display_name", this.state.display_name);
        form_data.append("about_me", this.state.about_me);

        call_api(endpoint, form_data)
            .then((data: any) => this.handleSaveSuccess(data))
            .catch((err: any) => this.handleSaveError(err));
    };

    handleSaveSuccess = (data: any) => {
        console.log("Profile updated successfully", data);
        this.setState({
            original_display_name: this.state.display_name,
            original_about_me: this.state.about_me,
            is_editing: false,
            changes_made: false,
            saving: false,
            error: null
        });
    };

    handleSaveError = (err: any) => {
        console.error("Failed to update display name", err);
        this.setState({
            error: "Failed to update display name. Please try again.",
            saving: false
        });
    };

    renderViewMode() {
        const { display_name, about_me } = this.state;
        const { is_own_profile, avatar_image_id } = this.props;

        return (
            <div class="profile-view">
                {avatar_image_id && (
                    <div class="profile-avatar">
                        <img 
                            src={`/avatar/image/${avatar_image_id}`} 
                            alt="User avatar"
                            class="avatar-image"
                        />
                    </div>
                )}
                
                <div class="profile-field">
                    <label>Display Name:</label>
                    <span class="profile-value">{display_name || 'Not set'}</span>
                </div>
                
                <div class="profile-field">
                    <label>About Me:</label>
                    <span class="profile-value">{about_me || 'Not set'}</span>
                </div>
                
                {is_own_profile && (
                    <button onClick={this.startEditing} class="btn-edit">
                        Edit Profile
                    </button>
                )}
            </div>
        );
    }

    handleAvatarUpload = (e: h.JSX.TargetedEvent<HTMLInputElement, Event>) => {
        const file = e.currentTarget.files?.[0];
        if (!file) return;

        this.setState({ uploading_avatar: true, error: null });

        const formData = new FormData();
        formData.append('avatar_file', file);

        call_api('/api/user/avatar', formData)
            .then((data: any) => {
                console.log("Avatar uploaded successfully", data);
                this.setState({ 
                    uploading_avatar: false 
                });
                // Reload page to show new avatar
                window.location.reload();
            })
            .catch((err: any) => {
                console.error("Avatar upload failed", err);
                this.setState({
                    uploading_avatar: false,
                    error: "Failed to upload avatar. Please try again."
                });
            });
    };

    renderEditMode() {
        const { display_name, about_me, changes_made, error, saving, uploading_avatar } = this.state;
        const { avatar_image_id } = this.props;

        return (
            <div class="profile-edit">
                {error && <div class="error-message">{error}</div>}
                
                {avatar_image_id && (
                    <div class="profile-avatar">
                        <img 
                            src={`/avatar/image/${avatar_image_id}`} 
                            alt="User avatar"
                            class="avatar-image"
                        />
                    </div>
                )}
                
                <div class="profile-field">
                    <label htmlFor="avatar_upload">Avatar Image:</label>
                    <input
                        id="avatar_upload"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg"
                        onChange={this.handleAvatarUpload}
                        disabled={uploading_avatar}
                    />
                    {uploading_avatar && <span class="upload-status">Uploading...</span>}
                </div>
                
                <div class="profile-field">
                    <label htmlFor="display_name">Display Name:</label>
                    <input
                        id="display_name"
                        type="text"
                        value={display_name}
                        onInput={(e: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                            this.handleDisplayNameChange(e.currentTarget.value)
                        }
                        disabled={saving}
                        placeholder="Enter your display name"
                        minLength={MINIMUM_DISPLAY_NAME_LENGTH}
                        maxLength={MAXIMUM_DISPLAY_NAME_LENGTH}
                    />
                    <small class="field-hint">
                        {MINIMUM_DISPLAY_NAME_LENGTH}-{MAXIMUM_DISPLAY_NAME_LENGTH} characters
                    </small>
                </div>

                <div class="profile-field">
                    <label htmlFor="about_me">About Me:</label>
                    <textarea
                        id="about_me"
                        value={about_me}
                        onInput={(e: h.JSX.TargetedEvent<HTMLTextAreaElement, Event>) =>
                            this.handleAboutMeChange(e.currentTarget.value)
                        }
                        disabled={saving}
                        placeholder="Tell us about yourself..."
                        rows={6}
                        minLength={MINIMUM_ABOUT_ME_LENGTH}
                        maxLength={MAXIMUM_ABOUT_ME_LENGTH}
                    />
                    <small class="field-hint">
                        Maximum {MAXIMUM_ABOUT_ME_LENGTH} characters
                    </small>
                </div>

                <div class="button-group">
                    <button 
                        onClick={this.handleSave} 
                        disabled={!changes_made || saving}
                        class="btn-save"
                    >
                        {saving ? 'Saving...' : 'Save Changes'}
                    </button>
                    
                    <button 
                        onClick={this.cancelEditing}
                        disabled={saving}
                        class="btn-cancel"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        );
    }

    render(props: UserProfilePanelProps, state: UserProfilePanelState) {
        return (
            <div class="user_profile_panel_react">
                <h2>Profile Information</h2>
                {state.is_editing ? this.renderEditMode() : this.renderViewMode()}
            </div>
        );
    }
}

