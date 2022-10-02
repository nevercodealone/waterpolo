<?php

namespace App\Handler;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageHandler
{
    private readonly string $tmpFolder;

    public function __construct(
        private readonly KernelInterface $appKernel,
        private readonly Filesystem $filesystem
    ) {
        $this->tmpFolder = $this->appKernel->getProjectDir().'/public/tmp/photos/';

        if (!$this->filesystem->exists($this->tmpFolder)) {
            $this->filesystem->mkdir($this->tmpFolder, 0777);
        }
    }

    public function saveFileFromUrl(string $urlToFile): string
    {
        try {
            $filename = basename($urlToFile);
            $this->filesystem->copy($urlToFile, $this->tmpFolder.$filename);
        } catch (Exception) {
            return '';
        }

        return $filename;
    }

    public function getDateFromImage(string $urlToFile): string|false
    {
        $headers = get_headers($urlToFile, true);
        if ($headers) {
            return $headers['Last-Modified'];
        }

        return false;
    }
}
