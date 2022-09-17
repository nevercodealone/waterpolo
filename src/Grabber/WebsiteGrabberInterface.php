<?php

declare(strict_types=1);

namespace App\Grabber;

use JsonException;

interface WebsiteGrabberInterface
{
    /**
     * @param array<string, string> $properties
     *
     * @throws JsonException
     *
     * @return array<array>|false
     */
    public function getNewsItemsFromUrl(string $url, array $properties): array|false;
}
