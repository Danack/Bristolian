<?php

$colors = [
    // Five darker pastel colors (for better visibility)
    '#D05060', // Dark pastel pink
    '#4080C0', // Dark pastel blue
    '#40A060', // Dark pastel green
    '#C0B040', // Dark pastel yellow
    '#9060A0', // Dark pastel lavender
    
    // Three shades of grey
    '#D3D3D3', // Light grey
    '#808080', // Medium grey
    '#4D4D4D', // Dark grey
    
    // Colors of the rainbow
    '#FF0000', // Red
    '#FF7F00', // Orange
    '#FFFF00', // Yellow
    '#00FF00', // Green
    '#0000FF', // Blue
    '#4B0082', // Indigo
    '#9400D3', // Violet

];


// Define 10 different shape patterns using sets of points
// Each shape is an array of [x, y] coordinates that will be connected with lines

$shapes = [
    // 1. Complex star burst (12 rays from center)
    [
        [256, 256], [256, 80],   // Top ray
        [256, 256], [362, 106],  // Top-right ray
        [256, 256], [432, 256],  // Right ray
        [256, 256], [362, 406],  // Bottom-right ray
        [256, 256], [256, 432],  // Bottom ray
        [256, 256], [150, 406],  // Bottom-left ray
        [256, 256], [80, 256],   // Left ray
        [256, 256], [150, 106],  // Top-left ray
        [256, 256], [330, 130],  // Additional rays for complexity
        [256, 256], [382, 330],
        [256, 256], [182, 330],
        [256, 256], [130, 182],
    ],
    
    // 2. Interlocking triangles/Star of David with details
    [
        [256, 90], [380, 320], [132, 320], [256, 90],  // Upward triangle
        [256, 422], [132, 192], [380, 192], [256, 422], // Downward triangle
        [256, 130], [256, 382],  // Vertical line through center
        [160, 192], [352, 320],  // Diagonal cross lines
        [352, 192], [160, 320],
    ],
    
    // 3. Spirograph/Flower pattern
    [
        // Outer petals (8 petals radiating from center)
        [256, 256], [256, 80], [290, 140], [256, 256],  // Petal 1
        [256, 256], [382, 130], [340, 200], [256, 256], // Petal 2
        [256, 256], [432, 256], [360, 256], [256, 256], // Petal 3
        [256, 256], [382, 382], [340, 312], [256, 256], // Petal 4
        [256, 256], [256, 432], [256, 372], [256, 256], // Petal 5
        [256, 256], [130, 382], [172, 312], [256, 256], // Petal 6
        [256, 256], [80, 256], [152, 256], [256, 256],  // Petal 7
        [256, 256], [130, 130], [172, 200], [256, 256], // Petal 8
    ],
    
    // 4. Celtic knot inspired pattern
    [
        // Outer frame with curves
        [156, 100], [256, 80], [356, 100],  // Top curve
        [432, 156], [412, 256], [432, 356], // Right curve
        [356, 412], [256, 432], [156, 412], // Bottom curve
        [80, 356], [100, 256], [80, 156],   // Left curve
        [156, 100],  // Close outer frame
        // Inner interlaced pattern
        [180, 180], [256, 160], [332, 180], [352, 256], [332, 332], 
        [256, 352], [180, 332], [160, 256], [180, 180],
        // Cross connections
        [256, 160], [256, 352],  // Vertical
        [160, 256], [352, 256],  // Horizontal
        [180, 180], [332, 332],  // Diagonal
        [332, 180], [180, 332],  // Other diagonal
    ],
    
    // 5. Star (5-pointed)
    [
        [256, 70],   // Top point
        [280, 180],  // Inner right of top
        [400, 200],  // Right point
        [300, 280],  // Inner top right
        [330, 400],  // Bottom right point
        [256, 320],  // Inner bottom
        [182, 400],  // Bottom left point
        [212, 280],  // Inner top left
        [112, 200],  // Left point
        [232, 180],  // Inner left of top
        [256, 70],   // Back to top
    ],
    
    // 6. Star (8-pointed)
    [
        [256, 60],   // Top
        [276, 176],  // Inner
        [392, 120],  // Top right
        [336, 236],  // Inner
        [452, 256],  // Right
        [336, 276],  // Inner
        [392, 392],  // Bottom right
        [276, 336],  // Inner
        [256, 452],  // Bottom
        [236, 336],  // Inner
        [120, 392],  // Bottom left
        [176, 276],  // Inner
        [60, 256],   // Left
        [176, 236],  // Inner
        [120, 120],  // Top left
        [236, 176],  // Inner
        [256, 60],   // Back to top
    ],
    
    // 7. Grid pattern (horizontal and vertical lines)
    [
        [100, 100], [412, 100],  // Top horizontal
        [100, 206], [412, 206],  // Middle horizontal 1
        [100, 312], [412, 312],  // Middle horizontal 2
        [100, 418], [412, 418],  // Bottom horizontal
        [100, 100], [100, 418],  // Left vertical
        [206, 100], [206, 418],  // Middle vertical 1
        [312, 100], [312, 418],  // Middle vertical 2
        [412, 100], [412, 418],  // Right vertical
    ],
    
    // 8. Ladder pattern
    [
        [150, 80],  [150, 432],  // Left rail
        [362, 80],  [362, 432],  // Right rail
        [150, 120], [362, 120],  // Rung 1
        [150, 200], [362, 200],  // Rung 2
        [150, 280], [362, 280],  // Rung 3
        [150, 360], [362, 360],  // Rung 4
    ],
    
    // 9. Zigzag pattern
    [
        [80, 150],
        [180, 80],
        [280, 150],
        [380, 80],
        [432, 150],
        [380, 220],
        [280, 150],
        [180, 220],
        [80, 150],
        [132, 280],
        [232, 350],
        [332, 280],
        [432, 350],
    ],
    
    // 10. Spiral/Concentric pattern
    [
        [100, 100], [412, 100], [412, 412], [100, 412], [100, 100],  // Outer square
        [140, 140], [372, 140], [372, 372], [140, 372], [140, 140],  // Second square
        [180, 180], [332, 180], [332, 332], [180, 332], [180, 180],  // Third square
        [220, 220], [292, 220], [292, 292], [220, 292], [220, 220],  // Inner square
    ],
];

$image_count = 0;

for ($j=0; $j < count($shapes); $j++) {

    for ($i = 0; $i < count($colors); $i++) {

        $image = new Imagick();
        $image->newPseudoImage(512, 512, "canvas:rgb(238, 238, 238)");

        $draw = new ImagickDraw();
        $draw->setStrokeColor($colors[$i]);
        $draw->setStrokeWidth(3);
        $draw->setFillOpacity(0); // No fill, just outlines

        // Select shape based on index (cycle through shapes if more colors than shapes)
        $points = $shapes[$j];

        // Draw lines connecting all points in the shape
        for ($line = 0; $line < count($points) - 1; $line++) {
            $draw->line(
                $points[$line][0], $points[$line][1],
                $points[$line + 1][0], $points[$line + 1][1]
            );
        }

        $image->drawImage($draw);

        $image->setImageFormat("png");
        $image->writeImage(__DIR__ . "/avatar/avatar_" . $image_count . ".png");

        $image_count += 1;
    }
}