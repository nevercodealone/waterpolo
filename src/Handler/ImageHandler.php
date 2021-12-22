<?php

namespace App\Handler;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageHandler
{
    public function __construct(
        private KernelInterface $appKernel,
        private Filesystem $filesystem
    )
    {
        $this->tmpFolder = $this->appKernel->getProjectDir().'/public/tmp/photos/';

        if (!$this->filesystem->exists($this->tmpFolder)) {
            $this->filesystem->mkdir($this->tmpFolder, 0777);
        }
    }

    public function saveFileFromUrl(string $urlToFile): string
    {
        try {
            $filename = basename($urlToFile);
            $this->filesystem->copy($urlToFile, $this->tmpFolder . $filename);
        } catch (\Exception $exception) {
            return '';
        }
        return $filename;
    }

    public function getdateFromImage($urlToFile) {

        $date = get_headers($urlToFile, 1)["Last-Modified"];

        return $date;
    }
}
