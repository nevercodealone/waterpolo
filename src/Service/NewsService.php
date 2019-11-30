<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class NewsService
{
    public function getNews()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://newsapi.org/v2/everything?q=wasserball&from=2019-10-30&sortBy=publishedAt&language=de&apiKey=' . $_ENV['NEWSAPI']);

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }
}
