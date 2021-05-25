<?php

namespace NewBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use NewBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class of the User controller.
 */
class NewUserController extends Controller{

    /**
     * Calls the welcome view.
     *
     * @return render
     */
    public function homeAction(){
        return $this->render('NewBundle:User:home.html.twig');
    }

    /**
     * Index of users list
     *
     * @return render
     */
    public function indexAction(){
        return $this->render('NewBundle:User:userIndex.html.twig');
    }

    /**
     * Gets the users from the repository to generate the Datatable.
     *
     * @param Request $request
     * @return JsonResponse 
     */
    public function getUsersListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $params = $request->request->all();

        // Obtenemos la información de los usuarios
        $users_list = $em->getRepository('NewBundle:User')->getUsersList($params);

        return new JsonResponse($users_list);
    }

    /**
     * View of the user add.
     *
     * @return render
     */
    public function addAction(){
        return $this->render('NewBundle:User:userAdd.html.twig');
    }

    /**
     * Calls the user service to add or update a user. If the action is add,
     * it passes a new user object. If the action is edit, it passes the user 
     * that will be modified.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(Request $request){
        $params = $request->request->all();

        if($params['action'] == 'add'){
            $user = new User();
            
            
        } elseif($params['action'] == 'edit'){
            $em = $this->getDoctrine()->getManager();
            $id = $params['id'];
            
            $user = $em->getRepository('NewBundle:User')->findOneById($id);

            if(!$user){
                throw $this->createNotFoundException('The user does not exist.');
            }
        }
        
        $password = $params['password'];
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

        $return = $this->get('UserService')->updateUser($params, $user);
        
        return new JsonResponse($return);
    }
    
    /**
     * Creates de view of user view using the user id. It passes the user and their tasks.
     *
     * @param int $id
     * @return render
     */
    public function viewAction($id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('NewBundle:User')->findOneById($id);
        
        if(!$user){
            throw $this->createNotFoundException('The user does not exist.');
        }
        
        $tasks = $em->getRepository('NewBundle:Task')->findBy(array(
            'user' => $id
        ));
        
        return $this->render('NewBundle:User:userView.html.twig', array(
            'user' => $user,
            'tasks' => $tasks
        ));
    }
    
    /**
     * Creates the view of user edit using the user id. It passes the task and the
     * available users.
     *
     * @param int $id
     * @return render
     */
    public function editAction($id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('NewBundle:User')->findOneById($id);

        if(!$user){
            throw $this->createNotFoundException('The user does not exist.');
        }

        return $this->render('NewBundle:User:userEdit.html.twig', array(
            'user' => $user
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

        $return = $this->get('UserService')->deleteUser($params);

        return new JsonResponse($return);
    }
}
