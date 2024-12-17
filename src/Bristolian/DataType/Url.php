<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;

#[\Attribute]
class Url implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new MinLength(1),
            new MaxLength(2048), // TODO - needs better validation
        );
    }


    /*

    TODO - validate URLS
    // https://stackoverflow.com/a/44029246/778719
$regularExpression  = "((https?|ftp)\:\/\/)?"; // SCHEME Check
$regularExpression .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass Check
$regularExpression .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP Check
$regularExpression .= "(\:[0-9]{2,5})?"; // Port Check
$regularExpression .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path Check
$regularExpression .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query String Check
$regularExpression .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor Check

if(preg_match("/^$regularExpression$/i", $posted_url)) {
if(preg_match("@^http|https://@i",$posted_url)) {
$final_url = preg_replace("@(http://)+@i",'http://',$posted_url);
    // return "*** - ***Match : ".$final_url;
}
else {
    $final_url = 'http://'.$posted_url;
    // return "*** / ***Match : ".$final_url;
}
}
else {
    if (substr($posted_url, 0, 1) === '/') {
        // return "*** / ***Not Match :".$final_url."<br>".$baseUrl.$posted_url;
        $final_url = $baseUrl.$posted_url;
    }
    else {
        // return "*** - ***Not Match :".$posted_url."<br>".$baseUrl."/".$posted_url;
        $final_url = $baseUrl."/".$final_url; }
}

    */
}
