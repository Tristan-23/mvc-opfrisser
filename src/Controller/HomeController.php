<?php

namespace App\Controller;

use App\Repository\BattleRepository;
use App\Repository\RobotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RobotRepository $robotRepository, BattleRepository $battleRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'leaderboard' => $robotRepository->getLeaderboard(),
            'upcoming_battles' => $battleRepository->findScheduled(5),
            'recent_battles' => $battleRepository->findCompleted(5),
        ]);
    }
}
