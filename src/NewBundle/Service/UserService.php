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
 * Class UserService
 * @package NewBundle\Service
 */
class UserService extends Controller {

        /**
         * @var EntityManager $entityManager
         */
        protected $em;

        /**
         * @var TranslatorInterface
         */
        protected $translator;

        /**
         * UserService constructor.
         * @param EntityManager $em
         * @param TranslatorInterface $translator
         */
        public function __construct(EntityManager $em, TranslatorInterface $translator){
            $this->em = $em;
            $this->translator = $translator;
        }

        /**
         * Adds or updates a user to the database. Validates that the parameters are correct.
         *
         * @param json $params
         * @param json $user
         * @return json_data
         */
        public function updateUser($params, $user){
            if(!$user){
                throw $this->createNotFoundException('The user does not exist.');
            }

            if(!isset($params['username'])){
                throw $this->createNotFoundException('Username error.');
            }
            $user->setUsername($params['username']);

            if(!isset($params['firstName'])){
                throw $this->createNotFoundException('First name error.');
            }
            $user->setFirstName($params['firstName']);

            if(!isset($params['lastName'])){
                throw $this->createNotFoundException('Last name error.');
            }
            $user->setLastName($params['lastName']);

            if(!isset($params['email'])){
                throw $this->createNotFoundException('Email error.');
            }
            
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                $message = $this->translator->trans("Invalid email format.");
                return array(
                    "status" => false,
                    "message" => $message
                );
                
            } 
            $user->setEmail($params['email']);

            if(!isset($params['isActive'])){
                throw $this->createNotFoundException('Is active error.');
            }
            if($params['isActive'] == 'true'){
                $user->setIsActive(1);
            } else{
                $user->setIsActive(0);
            }
            
            if(!isset($params['role'])){
                throw $this->createNotFoundException('Role error.');
            }
            if($params['role'] == 'admin'){
                $user->setRole('ROLE_ADMIN');
            }

            if($params['role'] == 'user'){
                $user->setRole('ROLE_USER');
            }

            $user->setIsDeleted(false);

            if(!isset($params['action'])){
                throw $this->createNotFoundException('Action error.');
            }
            
            try {
                $this->em->persist($user);
                $this->em->flush();

                if($params['action'] == 'add'){
                    $message = $this->translator->trans('Added User into the database');
                } elseif ($params['action'] == 'edit'){
                    $message = $this->translator->trans('The user has been modified');
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
        public function deleteUser($params){
            $user = $this->em->getRepository('NewBundle:User')->find($params['id']);

            if(!$user){
                $message =  $this->translator->trans('The user does not exist.');
                
                $json_data = array(
                    "status" => false,
                    "message" => $message
                );

                return $json_data;
            }

            if($user->getRole() == 'ROLE_USER'){
                $user->setIsDeleted(true);
                $this->em->flush();

                $message =  $this->translator->trans('The user has been deleted.');

                $json_data = array(
                    "status" => true,
                    "message" => $message
                );

            } else{
                $message =  $this->translator->trans('The user could not be deleted.');

                $json_data = array(
                    "status" => false,
                    "message" => $message
                );
            }

            return $json_data;
        }
}