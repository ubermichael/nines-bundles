<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordCommand extends AbstractUserCommand {
    /**
     * @var UserManager
     */
    private $manager;

    protected static $defaultName = 'nines:user:reset';

    public function __construct(UserManager $manager, ValidatorInterface $validator, EntityManagerInterface $em) {
        parent::__construct($validator, $em);
        $this->manager = $manager;
    }

    protected function getArgs() : array {
        return [
            [
                'name' => 'email',
                'desc' => 'Email address for the account to reset',
                'question' => 'Email address: ',
                'valid' => [new NotBlank(), new Email()],
            ],
        ];
    }

    protected function configure() : void {
        $this->setDescription('Email a passowrd reset to a user');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $email = $input->getArgument('email');
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ( ! $user) {
            $output->writeln("Cannot find user {$email}.");

            return 1;
        }

        $this->manager->requestReset($user);
        $this->manager->sendReset($user, []);
        $this->em->flush();

        $output->writeln("Password reset email sent to {$user->getEmail()}.");

        return 0;
    }
}
