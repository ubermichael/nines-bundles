<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangePasswordCommand extends AbstractUserCommand {
    private ?UserManager $manager = null;

    protected static $defaultName = 'nines:user:password';

    public function __construct(UserManager $manager, ValidatorInterface $validator, EntityManagerInterface $em) {
        parent::__construct($validator, $em);
        $this->manager = $manager;
    }

    /**
     * @return array<int,mixed>
     */
    protected function getArgs() : array {
        return [
            ['name' => 'email', 'desc' => 'Email address for the account', 'question' => 'Email address: ', 'valid' => [new NotBlank(), new Email()]],
            ['name' => 'password', 'desc' => 'New password for the account', 'question' => 'New password: ', 'valid' => [new NotBlank()], 'required' => false],
        ];
    }

    protected function configure() : void {
        $this->setDescription('Change the password for a user');
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

        if ( ! $input->hasArgument('password')) {
            /** @var SymfonyQuestionHelper $helper */
            $helper = $this->getHelper('question');
            $password = $helper->ask($input, $output, $this->question('New password: ', [new Length(['min' => 8])], true));
            $confirm = $helper->ask($input, $output, $this->question('Confirm password: ', [new Length(['min' => 8])], true));

            if ($password !== $confirm) {
                $output->writeln('The passwords do not match. The password had not been changed.');
            }
        } else {
            $password = $input->getArgument('password');
        }

        $encoded = $this->manager->encodePassword($user, $password);
        $user->setPassword($encoded);

        $this->em->flush();
        $output->writeln("Password for {$user->getEmail()} changed.");

        return 0;
    }
}
