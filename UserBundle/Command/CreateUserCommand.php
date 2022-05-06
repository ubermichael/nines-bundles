<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserCommand extends AbstractUserCommand {
    private ?UserManager $manager = null;

    protected static $defaultName = 'nines:user:create';

    public function __construct(UserManager $manager, ValidatorInterface $validator, EntityManagerInterface $em) {
        parent::__construct($validator, $em);
        $this->manager = $manager;
    }

    /**
     * @return array<int,mixed>
     */
    protected function getArgs() : array {
        return [
            ['name' => 'email', 'desc' => 'Email address for the new user account', 'question' => 'Email address: ', 'valid' => [new NotBlank(), new Email()]],
            ['name' => 'fullname', 'desc' => 'User\'s full name', 'question' => 'Full name: ', 'valid' => [new NotBlank()]],
            ['name' => 'affiliation', 'desc' => 'User\'s Institutional affiliation', 'question' => 'Affiliation: ', 'valid' => [new NotBlank()]],
        ];
    }

    protected function configure() : void {
        $this->setDescription('Create a new user in the database');
        parent::configure();
    }

    /**
     * @throws Exception
     */
    protected function generatePassword() : string {
        $bytes = random_bytes(UserManager::PASSWORD_BYTES);

        return base64_encode($bytes);
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setFullname($input->getArgument('fullname'));
        $user->setAffiliation($input->getArgument('affiliation'));
        $password = $this->manager->encodePassword($user, $this->manager->generatePassword());
        $user->setPassword($password);
        $this->manager->requestReset($user);
        $this->manager->sendReset($user, []);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("Account {$user->getEmail()} created, but not active.");

        return 0;
    }
}
