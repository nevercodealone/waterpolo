<?php declare(strict_types=1);

namespace App\Grabber;

interface WebsiteGrabberInterface
{
    /**
     * @param string $url
     * @param array<string, string> $properties
     *
     * @throws \JsonException
     * @return array<array>|false
     */
    public function getNewsItemsFromUrl(string $url, array $properties): array|false;
}
