<?php

namespace Bristolian\AppController;

use Bristolian\Parameters\Params;
use Bristolian\Response\SVGResponse;
use chillerlan\QRCode\QRCode as QRCodeGenerator;
use chillerlan\QRCode\QROptions;
use VarMap\VarMap;

class QRCode
{
    public function get(VarMap $varMap): SVGResponse
    {
        $qrData = Params::createFromVarMap($varMap);

//        https://www.qrcode.com/en/about/version.html#versionPage1_10

        $options = new QROptions([
//            'version'             => 7,
            'outputType'          => QRCodeGenerator::OUTPUT_MARKUP_SVG,
//            'svgViewBoxSize'    => '0 0 100 100',
            'imageBase64'         => false,
            'eccLevel'            => QRCodeGenerator::ECC_Q,
            'addQuietzone'        => true,
            // if set to false, the light modules won't be rendered
            'drawLightModules'    => true,
            // empty the default value to remove the fill* and opacity* attributes from the <path> elements
//            'markupDark'          => '',
//            'markupLight'         => '',
            // draw the modules as circles isntead of squares
//            'drawCircularModules' => true,
            'circleRadius'        => 0.4,
            'connectPaths'        => true,
            // keep modules of these types as square
//            'keepAsSquare'        => [
//                QRMatrix::M_FINDER_DARK,
//                QRMatrix::M_FINDER_DOT,
//                QRMatrix::M_ALIGNMENT_DARK,
//            ],
            // https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
            'svgDefs'             => '
	<linearGradient id="rainbow" x1="100%" y2="100%">
		<stop stop-color="#e2453c" offset="2.5%"/>
		<stop stop-color="#e07e39" offset="21.5%"/>
		<stop stop-color="#e5d667" offset="40.5%"/>
		<stop stop-color="#51b95b" offset="59.5%"/>
		<stop stop-color="#1e72b7" offset="78.5%"/>
		<stop stop-color="#6f5ba7" offset="97.5%"/>
	</linearGradient>
	<style><![CDATA[
		.dark{fill: url(#rainbow);}
		.light{fill: #eee;}
	]]></style>',
        ]);

        $qrGenerator = new QRCodeGenerator($options);

        return new SVGResponse($qrGenerator->render($qrData->url));
    }
}
