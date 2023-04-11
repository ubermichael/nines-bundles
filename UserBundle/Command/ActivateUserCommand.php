<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Nines\UserBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActivateUserCommand extends AbstractUserCommand {
    protected static $defaultName = 'nines:user:activate';

    /**
     * @return array<int,mixed>
     */
    protected function getArgs() : array {
        return [
            ['name' => 'email', 'desc' => 'Email address for the account', 'question' => 'Email address: ', 'valid' => [new NotBlank(), new Email()]],
        ];
    }

    protected function configure() : void {
        $this->setDescription('Enable a user account');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $email = $input->getArgument('email');
        /** @var ?User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ( ! $user) {
            $output->writeln("Cannot find user {$email}.");

            return 1;
        }

        $user->setActive(true);
        $this->em->flush();

        $output->writeln("Account {$user->getEmail()} is active.");

        return 0;
    }
}
