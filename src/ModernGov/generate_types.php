<?php

// @codeCoverageIgnoreStart

$url = "mgWebService.wsdl";

$xml_string = file_get_contents($url);

if ($xml_string === false) {
    echo "Failed to open wsdl file.";
    exit(-1);
}

$xmlreader = new XMLReader();

$xmlreader->xml($xml_string);


$count = 0;
$indent = 0;

$current_path = [];

$name_path = [
    'wsdl:definitions',
    'wsdl:types',
    's:schema',
    's:element',
];


$type_path = [
    'wsdl:definitions',
    'wsdl:types',
    's:schema',
    's:element',
    's:complexType',
    's:sequence',
    's:element',
];
$pushes = 0;
$pops = 0;
$indent = 0;

while ($xmlreader->read()) {
    $count += 1;
    switch ($xmlreader->nodeType) {
        case XMLReader::ELEMENT:
            if ($xmlreader->isEmptyElement === false) {
                $indent += 1;
                array_push($current_path, $xmlreader->name);
                $pushes += 1;
            }
//            echo str_repeat("\t", $indent), '[element]: ', $xmlreader->name, "\n";
            if ($current_path === $name_path) {
                echo "name path " . $xmlreader->getAttribute('name') . "\n";
            }

            if ($current_path === $type_path) {
//                echo "type path " . $xmlreader->getAttribute('name') . "\n";

                //minOccurs="1" maxOccurs="1" name="lCommitteeId" type=
            }


            break;
        case XMLReader::TEXT:
//            echo str_repeat("\t", $indent), $xmlreader->value, "\n";
            break;
        case XMLReader::END_ELEMENT:
//            echo str_repeat("\t", --$indent), '[end element]: ', $xmlreader->name, "\n";
            array_pop($current_path);
            $pops += 1;
            break;
    }


//    if ($count > 100 ) {
////        var_dump($current_path);
//        break;
//    }
}

echo "Pushes = $pushes \n";
echo "pops = $pops \n";


//[element]: wsdl:definitions - no attrib data
//[element]: wsdl:types - no attrib data
//[element]: s:schema - no attrib data
//[element]: s:element - name="GetMeeting"
//[element]: s:complexType - no attrib data
//[element]: s:sequence
//[element]: s:element - minOccurs="1" maxOccurs="1" name="lMeetingId" type="s:int"
//[end element]: s:sequence
//[end element]: s:complexType
//[end element]: s:element





/*


<wsdl:definitions xmlns:tm="http://microsoft.com/wsdl/mime/textMatching/" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/" xmlns:tns="http://moderngov.co.uk/namespaces" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:s="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:http="http://schemas.xmlsoap.org/wsdl/http/" targetNamespace="http://moderngov.co.uk/namespaces" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
    <wsdl:types>
        <s:schema elementFormDefault="qualified" targetNamespace="http://moderngov.co.uk/namespaces">
            <s:element name="GetMeeting">
                <s:complexType>
                    <s:sequence>
                        <s:element minOccurs="1" maxOccurs="1" name="lMeetingId" type="s:int" />
                    </s:sequence>
                </s:complexType>
            </s:element>


*/




echo "count = $count \n";


// @codeCoverageIgnoreEnd
