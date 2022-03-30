<?php

namespace App\Service;

use Google\Cloud\Vision\VisionClient;

class ImageService
{
    /**
     * @var VisionClient
     */
    private VisionClient $vision;

    public function __construct()
    {
        $contents = file_get_contents($_ENV['GOOGLE_JSON']);
        if ($contents === false) {
            throw new \LogicException('Google JSON file not found');
        }

        $this->vision = new VisionClient([
            'keyFile' => json_decode($contents, true, flags:JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @return array<string>
     * @throws \Exception
     */
    public function getWebEntities(string $path, string $entity = 'description'): array
    {
        try {
            $image = $this->vision->image(
                fopen($path, 'r') ?: throw new \Exception('File not found'),
                ['WEB_DETECTION']
            );
        } catch (\Exception) {
            throw new \Exception('Wrong argument');
        }

        $annotation = $this->vision->annotate($image);

        $info = $annotation->info();
        if (! $info) {
            return [];
        }

        $webEntities = $info['webDetection']['webEntities'];

        if ('description' === $entity) {
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
