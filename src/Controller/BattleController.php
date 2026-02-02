<?php

namespace App\Controller;

use App\Dto\Input\BattleAddRobotInput;
use App\Dto\Input\BattleScoreInput;
use App\Entity\Battle;
use App\Entity\BattleParticipant;
use App\Enum\BattleStatus;
use App\Form\BattleAddRobotType;
use App\Form\BattleScoreType;
use App\Form\BattleType;
use App\Repository\BattleRepository;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/battles')]
class BattleController extends AbstractController
{
    #[Route('', name: 'app_battle_index', methods: ['GET'])]
    public function index(BattleRepository $battleRepository): Response
    {
        return $this->render('battle/index.html.twig', [
            'battles' => $battleRepository->findAllWithParticipants(),
        ]);
    }

    #[Route('/new', name: 'app_battle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $battle = new Battle();
        $battle->setDateTime(new \DateTime());
        $form = $this->createForm(BattleType::class, $battle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($battle);
            $em->flush();

            $this->addFlash('success', 'Battle scheduled successfully!');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        return $this->render('battle/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_battle_show', methods: ['GET'])]
    public function show(int $id, BattleRepository $battleRepository, RobotRepository $robotRepository): Response
    {
        $battle = $battleRepository->findOneWithParticipants($id);
        if (!$battle) {
            throw $this->createNotFoundException('Battle not found.');
        }

        $participantRobotIds = [];
        foreach ($battle->getParticipants() as $participant) {
            $participantRobotIds[] = $participant->getRobot()->getId();
        }

        $allRobots = $robotRepository->findAllOrderedByName();
        $availableRobots = array_filter($allRobots, function ($robot) use ($participantRobotIds) {
            return !in_array($robot->getId(), $participantRobotIds);
        });

        $addRobotForm = $this->createForm(BattleAddRobotType::class, null, [
            'available_robots' => $availableRobots,
            'action' => $this->generateUrl('app_battle_add_robot', ['id' => $battle->getId()]),
        ]);

        $scoreForm = null;
        if (!$battle->isCompleted() && $battle->getParticipants()->count() >= 2) {
            $scoreForm = $this->createForm(BattleScoreType::class, null, [
                'participants' => $battle->getParticipants()->toArray(),
                'action' => $this->generateUrl('app_battle_score', ['id' => $battle->getId()]),
            ]);
        }

        return $this->render('battle/show.html.twig', [
            'battle' => $battle,
            'add_robot_form' => $addRobotForm,
            'score_form' => $scoreForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_battle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Battle $battle, EntityManagerInterface $em): Response
    {
        if ($battle->isCompleted()) {
            $this->addFlash('warning', 'Cannot edit a completed battle.');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        $form = $this->createForm(BattleType::class, $battle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Battle updated successfully!');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        return $this->render('battle/edit.html.twig', [
            'battle' => $battle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/add-robot', name: 'app_battle_add_robot', methods: ['POST'])]
    public function addRobot(Request $request, Battle $battle, RobotRepository $robotRepository, EntityManagerInterface $em): Response
    {
        if ($battle->isCompleted()) {
            $this->addFlash('warning', 'Cannot add robots to a completed battle.');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        $participantRobotIds = [];
        foreach ($battle->getParticipants() as $participant) {
            $participantRobotIds[] = $participant->getRobot()->getId();
        }

        $allRobots = $robotRepository->findAllOrderedByName();
        $availableRobots = array_filter($allRobots, function ($robot) use ($participantRobotIds) {
            return !in_array($robot->getId(), $participantRobotIds);
        });

        $input = new BattleAddRobotInput();
        $form = $this->createForm(BattleAddRobotType::class, $input, [
            'available_robots' => $availableRobots,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant = new BattleParticipant();
            $participant->setBattle($battle);
            $participant->setRobot($input->robot);

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', sprintf('Robot "%s" added to the battle!', $input->robot->getName()));
        }

        return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
    }

    #[Route('/{id}/remove-robot/{pid}', name: 'app_battle_remove_robot', methods: ['POST'])]
    public function removeRobot(Request $request, Battle $battle, int $pid, EntityManagerInterface $em): Response
    {
        if ($battle->isCompleted()) {
            $this->addFlash('warning', 'Cannot remove robots from a completed battle.');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        if ($this->isCsrfTokenValid('remove_robot' . $pid, $request->getPayload()->getString('_token'))) {
            $participant = $em->getRepository(BattleParticipant::class)->find($pid);
            if ($participant && $participant->getBattle()->getId() === $battle->getId()) {
                $em->remove($participant);
                $em->flush();
                $this->addFlash('success', 'Robot removed from the battle.');
            }
        }

        return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
    }

    #[Route('/{id}/score', name: 'app_battle_score', methods: ['POST'])]
    public function score(Request $request, int $id, BattleRepository $battleRepository, EntityManagerInterface $em): Response
    {
        $battle = $battleRepository->findOneWithParticipants($id);
        if (!$battle) {
            throw $this->createNotFoundException('Battle not found.');
        }

        if ($battle->isCompleted()) {
            $this->addFlash('warning', 'This battle has already been scored.');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        if ($battle->getParticipants()->count() < 2) {
            $this->addFlash('warning', 'Need at least 2 robots to score a battle.');
            return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
        }

        $input = new BattleScoreInput();
        $form = $this->createForm(BattleScoreType::class, $input, [
            'participants' => $battle->getParticipants()->toArray(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($battle->getParticipants() as $participant) {
                if ($participant->getId() == $input->winner) {
                    $participant->setScore(3);
                    $participant->setIsKnockout($input->isKnockout);
                } else {
                    $participant->setScore(0);
                }
            }

            $battle->setStatus(BattleStatus::Completed);
            $em->flush();

            $this->addFlash('success', 'Battle result recorded!');
        }

        return $this->redirectToRoute('app_battle_show', ['id' => $battle->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_battle_delete', methods: ['POST'])]
    public function delete(Request $request, Battle $battle, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $battle->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($battle);
            $em->flush();
            $this->addFlash('success', 'Battle deleted successfully!');
        }

        return $this->redirectToRoute('app_battle_index');
    }
}
