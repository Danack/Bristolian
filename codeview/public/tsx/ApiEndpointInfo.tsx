






enum ApiCallMethod {
  GET = 'GET',
  POST = 'POST'
}

interface WidgetApiCall {
  method: ApiCallMethod,
  path: string
}


interface WidgetInfo {
  name: string,
  component: string,
  source: string,
  api_calls: WidgetApiCall[],
}




// "widgets": [
//   {
//     "name": "bristol_stairs_panel",
//     "component": "BristolStairsPanel",
//     "source": "app/public/tsx/BristolStairsPanel.tsx",
//     "api_calls": [
//       {
//         "method": "GET",
//         "path": "/api/bristol_stairs_openmap_nearby"
//       },
//       {
//         "method": "POST",
//         "path": "/api/bristol_stairs_update/{bristol_stair_info_id:.*}"
//       },
//       {
//         "method": "POST",
//         "path": "/api/bristol_stairs_update_position/{bristol_stair_info_id:.*}"
//       },
//       {
//         "method": "POST",
//         "path": "/api/bristol_stairs_image"
//       }
//     ]
//   },