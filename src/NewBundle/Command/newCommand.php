<?php
namespace NewBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Gets admins information.
 */
class newCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('new')
            ->setDescription('Comando genérico de testing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $userRepo = $em->getRepository('NewBundle:User');

        $admins = $userRepo->findByRole('ROLE_ADMIN');
        $i = 1;

        //* INICIO
        $output->writeLn("<info> ---- INICIO ---- </info>\n");

        foreach($admins as $admin){
            $output->writeLn("Admin " . $i . ":");
            $output->writeLn("Username:" . $admin->getUsername());
            $output->writeLn("FirstName:" . $admin->getFirstName());
            $output->writeLn("LastName:" . $admin->getLastName());
            $output->writeLn("Email:" . $admin->getEmail() . "\n");

            $i ++;
        }

        //* FIN
        $output->writeLn("\n<info> ----- FIN ------ </info>");
    }

}
?> 