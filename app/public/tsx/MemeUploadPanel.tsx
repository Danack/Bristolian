import {h, Component} from "preact";

import {MEME_FILE_UPLOAD_FORM_NAME} from "./generated/constants";

export interface MemeUploadPanelProps {
    // no properties currently
}

enum UploadStatus {
    Idle = 'idle',
    Uploading = 'uploading',
    Success = 'success',
    Error = 'error'
}

interface MemeUploadPanelState {
    selectedFile: File|null,
    isDragging: boolean,
    uploadStatus: UploadStatus,
    uploadMessage: string|null,
}

function getDefaultState(): MemeUploadPanelState {
    return {
        selectedFile: null,
        isDragging: false,
        uploadStatus: UploadStatus.Idle,
        uploadMessage: null,
    };
}



export class MemeUploadPanel extends Component<MemeUploadPanelProps, MemeUploadPanelState> {

    constructor(props: MemeUploadPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);

    }

    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
        // unbind the listener
        // @ts-ignore: I don't understand that error message.
        // window.removeEventListener('popstate', this.restoreStateFn, false);
        // this.restoreStateFn = null;
    }

    restoreState(state_to_restore: object) {
        // if (state_to_restore === null) {
        //     this.setState(getDefaultState(this.props.initialControlParams));
        //     return;
        // }
        //
        // this.setState(state_to_restore);
        // this.triggerSetImageParams();
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
        if (files && files.length > 0) {
            const file = files[0];
            // Check if it's an image file
            if (file.type.startsWith('image/')) {
                this.setState({ 
                    selectedFile: file,
                    uploadStatus: UploadStatus.Idle,
                    uploadMessage: null
                });
            } else {
                this.setState({
                    uploadStatus: UploadStatus.Error,
                    uploadMessage: 'Please drop an image file.'
                });
            }
        }
    }

    // On file select (from the pop up)
    onFileChange = (event: any) => {

        // preview file here?
        // name "0HjGvBW.jpeg"
        // size 210806
        // type "image/jpeg"
        // webkitRelativePath ""


        // Update the state
        this.setState({
            selectedFile: event.target.files[0],
        });
    };


    // On file upload (click the upload button)
    onFileUpload = () => {
        if (!this.state.selectedFile) {
            this.setState({
                uploadStatus: UploadStatus.Error,
                uploadMessage: 'No file selected.'
            });
            return;
        }

        // Create an object of formData
        const formData = new FormData();

        // Update the formData object
        formData.append(
            MEME_FILE_UPLOAD_FORM_NAME,
            this.state.selectedFile,
            this.state.selectedFile.name
        );

        this.setState({ uploadStatus: UploadStatus.Uploading, uploadMessage: null });

        let params = {
            method: 'POST',
            body: formData
        }
        
        fetch('/api/meme-upload/', params)
            .then((response: Response) => response.json())
            .then((data: any) => {
                if (data.result === 'success') {
                    this.setState({
                        uploadStatus: UploadStatus.Success,
                        uploadMessage: 'Meme uploaded successfully!',
                        selectedFile: null
                    });
                } else {
                    this.setState({
                        uploadStatus: UploadStatus.Error,
                        uploadMessage: data.error || 'Upload failed.'
                    });
                }
            })
            .catch((err: any) => {
                this.setState({
                    uploadStatus: UploadStatus.Error,
                    uploadMessage: 'Upload failed: ' + err.message
                });
            });
    };



//     const handleChange = (event) => {
//         const file = event.target.files[0]
// //console.log(file)
//         setFile({
//             picturePreview: URL.createObjectURL(event.target.files[0]),
//             pictureAsFile: event.target.files[0]
//         })}

    // const upload = () => {
    //     setProgress(0);
    //     if (!currentFile) return;
    //
    //     UploadService.upload(currentFile, (event: any) => {
    //         setProgress(Math.round((100 * event.loaded) / event.total));
    //     })
    //       .then((response) => {
    //           setMessage(response.data.message);
    //           return UploadService.getFiles();
    //       })
    //       .then((files) => {
    //           setFileInfos(files.data);
    //       })
    //       .catch((err) => {
    //           setProgress(0);
    //
    //           if (err.response && err.response.data && err.response.data.message) {
    //               setMessage(err.response.data.message);
    //           } else {
    //               setMessage("Could not upload the File!");
    //           }
    //
    //           setCurrentFile(undefined);
    //       });
    // };

    render(props: MemeUploadPanelProps, state: MemeUploadPanelState) {
        const dropZoneClass = state.isDragging 
            ? 'meme_drop_zone meme_drop_zone_dragging' 
            : 'meme_drop_zone';

        let statusBlock = null;
        if (state.uploadStatus === UploadStatus.Uploading) {
            statusBlock = <div class="upload_status uploading">Uploading...</div>;
        } else if (state.uploadStatus === UploadStatus.Success) {
            statusBlock = <div class="upload_status success">{state.uploadMessage}</div>;
        } else if (state.uploadStatus === UploadStatus.Error) {
            statusBlock = <div class="upload_status error">{state.uploadMessage}</div>;
        }

        let selectedFileInfo = null;
        if (state.selectedFile) {
            selectedFileInfo = <div class="selected_file_info">
                Selected: {state.selectedFile.name} ({Math.round(state.selectedFile.size / 1024)} KB)
            </div>;
        }

        const isUploading = state.uploadStatus === UploadStatus.Uploading;

        return <div class='meme_upload_panel_react'>
            <div
                class={dropZoneClass}
                onDragEnter={(e: DragEvent) => this.handleDragEnter(e)}
                onDragOver={(e: DragEvent) => this.handleDragOver(e)}
                onDragLeave={(e: DragEvent) => this.handleDragLeave(e)}
                onDrop={(e: DragEvent) => this.handleDrop(e)}>
                <p class="drop_zone_text">
                    {state.isDragging 
                        ? 'Drop your image here!' 
                        : 'Drag and drop an image here, or use the button below'}
                </p>
                <input
                    type="file"
                    accept="image/*"
                    onChange={this.onFileChange}
                    disabled={isUploading}
                />
                {selectedFileInfo}
                <button 
                    onClick={this.onFileUpload} 
                    disabled={isUploading || !state.selectedFile}
                    class="upload_button">
                    {isUploading ? 'Uploading...' : 'Upload!'}
                </button>
            </div>
            {statusBlock}
        </div>;
    }
}



// // On file upload (click the upload button)
// onFileUpload = () => {
//     // Create an object of formData
//     const formData = new FormData();
//
//     // Update the formData object
//     formData.append(
//       "myFile",
//       this.state.selectedFile,
//       this.state.selectedFile.name
//     );
//
//     // Details of the uploaded file
//     console.log(this.state.selectedFile);
//
//     // Request made to the backend api
//     // Send formData object
//     axios.post("api/uploadfile", formData);
// };






