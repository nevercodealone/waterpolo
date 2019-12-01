<?php
require 'vendor/autoload.php';

use Google\Cloud\Vision\VisionClient;

$vision = new VisionClient([
    'keyFile' => json_decode(file_get_contents('/home/rolandgolla/develoment/googlekeys/Never-Code-Alone-Page-b3030ac27acc.json'), true)
]);

// Annotate an image, detecting faces.
$image = $vision->image(
    fopen('marco-stamm-mannschaft.jpg', 'r'),
    ['WEB_DETECTION']
);

$annotation = $vision->annotate($image);

$info = $annotation->info();
$webEntities = $info['webDetection']['webEntities'];

foreach ($webEntities as $webEntity) {
    if(isset($webEntity['description'])) {
        echo "has description: " . $webEntity['description'] . "\n";
    }
}
