<?php

namespace App\Controller;

use App\Entity\Robot;
use App\Form\RobotType;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/robots')]
class RobotController extends AbstractController
{
    #[Route('', name: 'app_robot_index', methods: ['GET'])]
    public function index(RobotRepository $robotRepository): Response
    {
        return $this->render('robot/index.html.twig', [
            'robots' => $robotRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/new', name: 'app_robot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $robot = new Robot();
        $form = $this->createForm(RobotType::class, $robot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($robot);
            $em->flush();

            $this->addFlash('success', 'Robot created successfully!');
            return $this->redirectToRoute('app_robot_show', ['id' => $robot->getId()]);
        }

        return $this->render('robot/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_robot_show', methods: ['GET'])]
    public function show(Robot $robot, RobotRepository $robotRepository): Response
    {
        return $this->render('robot/show.html.twig', [
            'robot' => $robot,
            'stats' => $robotRepository->getStatistics($robot),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_robot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Robot $robot, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RobotType::class, $robot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Robot updated successfully!');
            return $this->redirectToRoute('app_robot_show', ['id' => $robot->getId()]);
        }

        return $this->render('robot/edit.html.twig', [
            'robot' => $robot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_robot_delete', methods: ['POST'])]
    public function delete(Request $request, Robot $robot, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $robot->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($robot);
            $em->flush();
            $this->addFlash('success', 'Robot deleted successfully!');
        }

        return $this->redirectToRoute('app_robot_index');
    }
}
