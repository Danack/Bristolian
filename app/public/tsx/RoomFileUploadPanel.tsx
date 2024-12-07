import {h, Component} from "preact";
import {global} from "./globals";

import {ROOM_FILE_UPLOAD_FORM_NAME} from "./generated/constants";

let api_url: string = process.env.BRISTOLIAN_API_BASE_URL;

export interface RoomFileUploadPanelProps {
    room_id: string
}

interface RoomFileUploadPanelState {
    error: string|null,
    selectedFile: File|null,
}

function getDefaultState(): RoomFileUploadPanelState {
    return {
        error: null,
        selectedFile: null,
    };
}



export class RoomFileUploadPanel extends Component<RoomFileUploadPanelProps, RoomFileUploadPanelState> {

    constructor(props: RoomFileUploadPanelProps) {
        super(props);
        this.state = getDefaultState(/*props.initialControlParams*/);

    }

    componentDidMount() {
        // this.restoreStateFn = (event:any) => this.restoreState(event.state);
        // @ts-ignore: I don't understand that error message.
        // window.addEventListener('popstate', this.restoreStateFn);
    }

    componentWillUnmount() {
    }

    restoreState(state_to_restore: object) {
    }

    handleDragEnter(event: any) { //DragEvent<HTMLDivElement>) {
        event.preventDefault();
        console.log("handleDragEnter");
    }

    handleDragOver(event: any) { //DragEvent<HTMLDivElement>) {
        event.preventDefault();
        console.log("handleDragOver");
    }

    handleDragLeave(event: any) { //DragEvent<HTMLDivElement>) {
        event.preventDefault();
        console.log("handleDragLeave");
    }

    handleDrop  (event: any) { //DragEvent<HTMLDivElement>){
        event.preventDefault();
        console.log("Here we'll handle the dropped files");
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
        // Create an object of formData
        const formData = new FormData();

        if (this.state.selectedFile === null) {
            this.setState({error: "select a file to upload"})
            return;
        }

        this.setState({error: null})

        // Details of the uploaded file
        console.log("selectedFile ", this.state.selectedFile);

        // Update the formData object
        formData.append(
          ROOM_FILE_UPLOAD_FORM_NAME,
          this.state.selectedFile,
          this.state.selectedFile.name
        );

        // Request made to the backend api
        console.log("Should upload ", formData);

        let params = {
            method: 'POST',
            body: formData
        }

        let endpoint = `/api/rooms/${this.props.room_id}/file-upload`

        fetch(endpoint, params);
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

    render(props: RoomFileUploadPanelProps, state: RoomFileUploadPanelState) {
         let error_block = <span>&nbsp;</span>;
         if (this.state.error != null) {
             error_block = <div class="error">Error: {this.state.error}</div>
         }

        // {/*onDragEnd={onDragEnd}*/}
        // {/*onDragStart={() => setDraggedIndex(index)}*/}

        if (global.logged_in === false) {
            return <span></span>
        }

        return  <div class='room_file_upload_panel_react'>
            <h3>Drag a file here to upload</h3>
            <div
                 onDragEnter={(something:any) => this.handleDragEnter(something)}
                 onDragOver={(something:any) => this.handleDragOver(something)}
                 onDrop={(something:any) => this.handleDrop(something)}>
                <input
                  type="file"
                  onChange={this.onFileChange}
                />
                <button onClick={this.onFileUpload}>
                    Upload!
                </button>
            </div>

            {error_block}

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






