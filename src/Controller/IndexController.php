<?php

namespace App\Controller;

use App\Service\NewsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @var NewsService
     */
    private $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $news = $this->newsService->getNews();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'news' => $news
        ]);
    }
}
