<?php
require 'vendor/autoload.php';

use Google\Cloud\Vision\VisionClient;

$vision = new VisionClient([
    'keyFile' => json_decode(file_get_contents('/home/rolandgolla/develoment/googlekeys/Never-Code-Alone-Page-b3030ac27acc.json'), true)
]);

// Annotate an image, detecting faces.
$image = $vision->image(
    fopen('Existenzgruendung-Developer-Startup.jpg', 'r'),
    ['text']
);

$annotation = $vision->annotate($image);

// Determine if the detected faces have headwear.
foreach ($annotation->text() as $key => $text) {
    if ($text->description()) {
        echo "Text $key has description: " . $text->description() . "\n";
    }
}
