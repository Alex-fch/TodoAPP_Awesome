<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{

    /**
     * @var TaskRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * Constructeur du TaskController, Type Hinté le repository et le manager
     *
     * @param TaskRepository $repository
     * @param EntityManagerInterface $manager
     */
    public function __construct(TaskRepository $repository, EntityManagerInterface $manager)
    {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing()
    {

        // Récupérer les informations de l'utilisateur connecté
        // $user = $this->getUser();
        // dd($user);

        // dans ce repository nous récupérons toutes les données
        $tasks = $this->repository->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @Route("/tasks/update/{id}", name="task_update", requirements={"id"="\d+"})
     * @param Request $request
     * @return Response
     */
    public function task(Task $task = null, Request $request): Response
    {

        if (!$task) {
            $task = new Task();
            $flag = true;
        } else {
            $flag = false;
        }

        // On crée notre formulaire
        $form = $this->createForm(TaskType::class, $task, array());

        if ($flag) {
            $task->setCreatedAt(new \DateTime);
        }

        // On récupère notre formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            // On fait persister notre task
            $this->manager->persist($task);
            // On flush le tout en BDD
            $this->manager->flush();

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', ['task' => $task, 'form' => $form->createView()]);
    }

    /**
     * @Route("tasks/delete/{id}", name="task_delete", requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return Response
     */
    public function deleteTask(Task $task): Response
    {
        $this->manager->remove($task);
        $this->manager->flush();

        return $this->redirectToRoute('tasks_listing');
    }
}
