<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DemoteUserCommand extends AbstractUserCommand
{
    /**
     * @var UserManager
     */
    private $manager;

    protected static $defaultName = 'nines:user:demote';

    public function __construct(UserManager $manager, ValidatorInterface $validator, EntityManagerInterface $em) {
        parent::__construct($validator, $em);
        $this->manager = $manager;
    }

    protected function getArgs() : array {
        return [
            ['name' => 'email', 'desc' => 'Email address to promote', 'question' => 'Email address: ', 'valid' => [new NotBlank(), new Email()]],
            ['name' => 'role', 'desc' => 'The role to remove', 'question' => 'Role: ', 'valid' => [new NotBlank()]],
        ];
    }

    protected function configure() : void {
        $this->setDescription('Remove a role to from user');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');
        $user = $this->manager->find($email);

        if ( ! $user) {
            $output->writeln("Cannot find user {$email}.");

            return 1;
        }

        $this->manager->demote($user, $role);
        $this->em->flush();

        $output->writeln("Role {$role} removed from {$user}.");

        return 0;
    }
}
