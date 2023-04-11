<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractUserCommand extends Command {
    protected ?ValidatorInterface $validator = null;

    protected ?UserPasswordEncoderInterface $encoder = null;

    protected ?EntityManagerInterface $em = null;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $em) {
        $this->validator = $validator;
        $this->em = $em;
        parent::__construct();
    }

    /**
     * @return array<int,mixed>
     */
    abstract protected function getArgs() : array;

    protected function configure() : void {
        foreach ($this->getArgs() as $arg) {
            $mode = InputArgument::REQUIRED;
            if (isset($arg['required']) && false === $arg['required']) {
                $mode = InputArgument::OPTIONAL;
            }
            $this->addArgument($arg['name'], $mode, $arg['desc']);
        }
    }

    /**
     * @param ?array<Constraint> $constraints
     */
    protected function question(string $question, ?array $constraints = [], ?bool $hidden = false) : Question {
        $question = new Question($question);
        if ($hidden) {
            $question->setHidden(true);
            $question->setHiddenFallback(false);
        }
        if (count($constraints) > 0) {
            $question->setValidator(function($answer) use ($constraints) {
                $errors = $this->validator->validate($answer, $constraints);
                if ($errors->count()) {
                    throw new RuntimeException($errors[0]->getMessage());
                }

                return $answer;
            });
        }

        return $question;
    }

    protected function interact(InputInterface $input, OutputInterface $output) : void {
        /** @var SymfonyQuestionHelper $helper */
        $helper = $this->getHelper('question');

        foreach ($this->getArgs() as $arg) {
            if ( ! $input->getArgument($arg['name'])) {
                $question = $this->question($arg['question'], $arg['valid']);
                $input->setArgument($arg['name'], $helper->ask($input, $output, $question));
            }
        }
    }
}
