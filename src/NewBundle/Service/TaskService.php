<?php

namespace NewBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use NewBundle\Entity\User;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;



/**
 * Class TaskService
 * @package NewBundle\Service
 */
class TaskService extends Controller {

        /**
         * @var EntityManager $entityManager
         */
        protected $em;

        /**
         * @var TranslatorInterface
         */
        protected $translator;

        /**
         * TaskService constructor.
         * @param EntityManager $em
         * @param TranslatorInterface $translator
         */
        public function __construct(EntityManager $em, TranslatorInterface $translator){
            $this->em = $em;
            $this->translator = $translator;
        }

        /**
         * Adds or updates a task to the database. Validates that the parameters are correct.
         *
         * @param json $params
         * @param json $task
         * @return json_data
         */
        public function updateTask($params, $task){
            if(!$task){
                throw $this->createNotFoundException('The task does not exist.');
            }

            if(!isset($params['title'])){
                throw $this->createNotFoundException('Title error.');
            }
            $task->setTitle($params['title']);

            if(!isset($params['userId'])){
                throw $this->createNotFoundException('User id error.');
            }
            $user = $this->em->getRepository('NewBundle:User')->findOneById($params['userId']);

            if(!$user){
                throw $this->createNotFoundException('The user does not exist.');
            }
            $task->setUser($user);

            if(!isset($params['description'])){
                throw $this->createNotFoundException('Description error.');
            }
            $task->setDescription($params['description']);
            $task->setStatus(0);

            if(!isset($params['action'])){
                throw $this->createNotFoundException('Action error.');
            }
            
            try {
                $this->em->persist($task);
                $this->em->flush();

                if($params['action'] == 'add'){
                    $message = $this->translator->trans('Added Task into the database');
                } elseif ($params['action'] == 'edit'){
                    $message = $this->translator->trans('The task has been modified');
                }
                
                $json_data = array(
                    "status" => true,
                    "message" => $message
                );
            } catch (DBALException $e) {
                $json_data = array(
                    "status" => false,
                    "message" => $e->getMessage()
                );
            }

            return $json_data;

        }

        /**
         * Deletes the receiv task using their id.
         *
         * @param json $params
         * @return json_data
         */
        public function deleteTask($params){
            if(!isset($params['id'])){
                throw $this->createNotFoundException('Id error.');
            }
            $task = $this->em->getRepository('NewBundle:Task')->find($params['id']);

            if(!$task){
                $message =  $this->translator->trans('The task does not exist.');
                
                $json_data = array(
                    "status" => false,
                    "message" => $message
                );
                
                return $json_data;
            } 

            try {
                $this->em->remove($task);
                $this->em->flush();

                $message =  $this->translator->trans('The task has been deleted.');

                $json_data = array(
                    "status" => true,
                    "message" => $message
                );

            } catch (DBALException $e) {
                $json_data = array(
                    "status" => false,
                    "message" => $e
                );
            }

            return $json_data;
        }

        /**
         * Changes the task status, using their id.
         *
         * @param int $id
         * @return json_data
         */
        public function endTask($id){
            $task = $this->em->getRepository('NewBundle:Task')->find($id);

            if(!$task){
                throw $this->createNotFoundException('The task does not exist.');
            }

            $status = $task->getStatus();

            if($status == 1){
                $task->setStatus(0);
                $message = $this->translator->trans('The task is not completed.');
            } elseif ($status == 0){
                $message = $this->translator->trans('The task has been ended.');
                $task->setStatus(1);
            }

            try {
                $this->em->persist($task);
                $this->em->flush();
                
                $json_data = array(
                    "status" => true,
                    "message" => $message,
                    "taskStatus" => $task->getStatus()
                );

            } catch (DBALException $e) {
                $json_data = array(
                    "status" => false,
                    "message" => $e
                );
            }

            return $json_data;
        }
}