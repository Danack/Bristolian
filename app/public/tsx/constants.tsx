import {sendMessage} from "./message/message";


export enum PdfSelectionType {
  TEXT_SELECTED = "selectionData",
  TEXT_DESELECTED = "textDeselected",

  PDF_READY = "pdf_ready",


  PDF_RENDERING = "pdf_rendering",



  ROOM_FILES_CHANGED = "room_files_changed",

  ROOM_LINKS_CHANGED = "room_links_changed",

  APPEND_TO_MESSAGE_INPUT = "append_to_message_input",

}