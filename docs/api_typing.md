

I want to be able to make it easier to program interactions with the server for the API.

I want to generate a TypeScript file (similar to how other TypeScript files are generated), that contains info from the routes listed in Bristolian/api/src/api_routes.php 


The generated TypeScript file should contain:

1. an end point string (aka uri) where the API can be called, which is exported, so that it can be used as a type, in other typescript files.

2. definitions of the types that the PHP controllers will return. This is information that will need to be added to the file Bristolian/api/src/api_routes.php

I also want to generate PHP files to represent the return type.

For example, for the route '/api/rooms/{room_id:.*}/files' the type information is:

```
[
    ['files', \Bristolian\Model\StoredFile::class, true]
],
```

Index 0 is the name of the parameter.
Index 1 is the type.
Index 2 is true, which means that multiple files will be returned.

there is an example of a generated Response type at src/Bristolian/Response/Typed/example/RoomFilesResponse.php