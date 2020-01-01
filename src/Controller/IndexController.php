<?php

namespace App\Controller;

use App\Service\NewsService;
use App\Service\YouTubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @var NewsService
     */
    private $newsService;

    /**
     * @var YouTubeService
     */
    private $youTubeService;

    public function __construct(NewsService $newsService, YouTubeService $youTubeService)
    {
        $this->newsService = $newsService;
        $this->youTubeService = $youTubeService;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('news');
    }

    /**
     * @Route("/news/", name="news")
     */
    public function newsAction(): Response
    {
        $news = $this->newsService->getNews();

        return $this->render('index/news.html.twig', [
            'news' => $news
        ]);
    }

    /**
     * @Route("/videos/", name="videos")
     */
    public function videoAction(): Response
    {
        $youTubeVideos = $this->youTubeService->getVideos();
        return $this->render('index/video.html.twig', [
            'youTubeVideos' => $youTubeVideos
        ]);
    }
}
