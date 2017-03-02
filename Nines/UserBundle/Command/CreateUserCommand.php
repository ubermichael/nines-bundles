<?php

namespace Nines\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Replaces the CreateUserCommand from FOSUserBundle to add support for
 * fullname and institution.
 * 
 * Requires Symfony > 3.0
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fos:user:create')
            ->setDescription('Create a user.')
            ->setDefinition(array(
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('fullname', InputArgument::REQUIRED, 'The full name'),
                new InputArgument('institution', InputArgument::REQUIRED, 'The institution'),
                new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
                new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
            ))
            ->setHelp(<<<EOT
The <info>fos:user:create</info> command creates a user:

  <info>php app/console fos:user:create user@example.com</info>

This interactive shell will ask you for a password.

You can alternatively specify the email and password as the first and second arguments:

  <info>php app/console fos:user:create matthieu@example.com mypassword</info>

You can create a super admin via the super-admin flag:

  <info>php app/console fos:user:create admin@example.com --super-admin</info>

You can create an inactive user (will not be able to log in):

  <info>php app/console fos:user:create user@example.com --inactive</info>

EOT
            );
    }

    /**
     * {@inheritDocs}
     * 
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $fullname = $input->getArgument('fullname');
        $institution = $input->getArgument('institution');
        $inactive = $input->getOption('inactive');
        $superadmin = $input->getOption('super-admin');

        $manipulator = $this->getContainer()->get('appuserbundle.user_manipulator');
        $manipulator->create($email, $password, $fullname, $institution, !$inactive, $superadmin);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $email));
    }

    /**
     * {@inheritDocs}
     * 
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose an email:');
            $question->setValidator(function($email) {
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }

                return $email;
            });
            $questions['email'] = $question;
        }

        if (!$input->getArgument('fullname')) {
            $question = new Question('Please choose an fullname:');
            $question->setValidator(function($fullname) {
                if (empty($fullname)) {
                    throw new \Exception('Email can not be empty');
                }

                return $fullname;
            });
            $questions['fullname'] = $question;
        }

        if (!$input->getArgument('institution')) {
            $question = new Question('Please choose an institution:');
            $question->setValidator(function($institution) {
                if (empty($institution)) {
                    throw new \Exception('Email can not be empty');
                }

                return $institution;
            });
            $questions['institution'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose an password:');
            $question->setValidator(function($password) {
                if (empty($password)) {
                    throw new \Exception('Email can not be empty');
                }

                return $password;
            });
            $questions['password'] = $question;
        }
		
        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
		
    }
}
