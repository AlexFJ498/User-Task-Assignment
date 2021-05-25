<?php

namespace NewBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use NewBundle\Entity\Task;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class of the Task controller.
 */
class NewTaskController extends Controller{

    /**
     * Index of tasks list
     *
     * @return render
     */
    public function indexAction(){
        return $this->render('NewBundle:Task:taskIndex.html.twig');
    }

    /**
     * Gets the tasks from the repository to generate the Datatable.
     *
     * @param Request $request
     * @return JsonResponse 
     */
    public function getTasksListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $params = $request->request->all();

        // Obtenemos la información de las tareas
        $tasks_list = $em->getRepository('NewBundle:Task')->getTasksList($params);

        return new JsonResponse($tasks_list);
    }

    /**
     * Gets the available users for the creation of a task, and pass them to the add task view.
     *
     * @return render
     */
    public function addAction(){
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('NewBundle:User')->findBy(array(
            'isDeleted' => 0
        ));

        return $this->render('NewBundle:Task:taskAdd.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * Calls the task service to add or update a task. If the action is add,
     * it passes a new task object. If the action is edit, it passes the task 
     * that will be modified.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(Request $request){
        $params = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if ($params['action'] == 'add'){
            $task = new Task();

        } else if ($params['action'] == 'edit'){
            $id = $params['id'];
            $task = $em->getRepository('NewBundle:Task')->findOneById($id);

            if(!$task){
                throw $this->createNotFoundException('The task does not exist.');
            }
        }
        
        $return = $this->get('TaskService')->updateTask($params, $task);

        return new JsonResponse($return);
    }

    /**
     * Creates de view of task view using the task id. It passes the task.
     *
     * @param int $id
     * @return render
     */
    public function viewAction($id){
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('NewBundle:Task')->find($id);

        if(!$task){
            throw $this->createNotFoundException('The task does not exist.');
        }

        return $this->render('NewBundle:Task:taskView.html.twig', array(
            'task' => $task
        ));
    
    }
    
    /**
     * Creates the view of task edit using the task id. It passes the task and the
     * available users.
     *
     * @param int $id
     * @return render
     */
    public function editAction($id){
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NewBundle:Task')->find($id);
        
        if(!$task){
            throw $this->createNotFoundException('The task does not exist.');
        } 

        $users = $em->getRepository('NewBundle:User')->findBy(array(
            'isDeleted' => 0
        ));
        
        return $this->render('NewBundle:Task:taskEdit.html.twig', array(
            'task' => $task,
            'users' => $users
        ));
    }

    /**
     * Gets the delete request and calls the delete service function.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request){
        $params = $request->request->all();

        $return = $this->get('TaskService')->deleteTask($params);

        return new JsonResponse($return);
    }

    /**
     * Gets the current active user and shows them their active tasks.
     *
     * @return render
     */
    public function customAction(){
        $idUser = $this->get('security.token_storage')->getToken()->getUser()->getId();

        return $this->render('NewBundle:Task:taskCustom.html.twig', array(
            'idUser' => $idUser
        ));
    }

    /**
     * Gets the end task request and calls the respective service function.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function endTaskAction(Request $request){
        $id = $request->request->get('id');

        if(!$id){
            throw $this->createNotFoundException('The task does not exist.');
        }

        $return = $this->get('TaskService')->endTask($id);
        return new JsonResponse($return);
    }
}