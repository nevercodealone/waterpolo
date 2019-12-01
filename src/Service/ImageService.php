<?php


namespace App\Service;


use Google\Cloud\Vision\VisionClient;
use http\Exception;

class ImageService
{
    /**
     * @var VisionClient
     */
    private $vision;

    public function __construct()
    {
        $this->vision = new VisionClient([
            'keyFile' => json_decode(file_get_contents($_ENV['GOOGLE_JSON']), true)
        ]);
    }

    public function getWebEntities(string $path, string $entity = 'description'): array
    {
        try {
            $image = $this->vision->image(
                fopen($path, 'r'),
                ['WEB_DETECTION']
            );

        } catch (\Exception $exception) {
            throw new \Exception('Wrong argument');
        }

        $annotation = $this->vision->annotate($image);

        $info = $annotation->info();
        $webEntities = $info['webDetection']['webEntities'];

        if ($entity === 'description') {
            $descriptions = [];

            foreach ($webEntities as $webEntity) {
                if ($webEntity) {
                    if (isset($webEntity['description'])) {
                        $descriptions[] = $webEntity['description'];
                    }
                }
            }

            $webEntities = $descriptions;
        }

        return $webEntities;
    }
}
