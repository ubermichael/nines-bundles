<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Services;

use DateTimeImmutable;
use Exception;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserManager.
 *
 * Convient user management functions.
 */
class UserManager
{
    public const PASSWORD_BYTES = 24;

    public const TOKEN_BYTES = 24;

    public const TOKEN_EXPIRY = ' +1 day';

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var string
     */
    private $afterLogin;

    /**
     * @var string
     */
    private $afterRequest;

    /**
     * @var string
     */
    private $afterReset;

    /**
     * @var string
     */
    private $afterLogout;

    /**
     * @var string
     */
    private $sender;

    /**
     * UserManager constructor.
     *
     * @param $afterLogin
     * @param $afterRequest
     * @param $afterReset
     * @param $afterLogout
     * @param array $roles
     */
    public function __construct($afterLogin, $afterRequest, $afterReset, $afterLogout, $roles = []) {
        $this->roles = $roles;
        $this->afterLogin = $afterLogin;
        $this->afterRequest = $afterRequest;
        $this->afterReset = $afterReset;
        $this->afterLogout = $afterLogout;
    }

    public function setSender($sender) : void {
        $this->sender = $sender;
    }

    /**
     * @required
     */
    public function setEncoder(UserPasswordEncoderInterface $encoder) : void {
        $this->encoder = $encoder;
    }

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     */
    public function setRepository(UserRepository $repository) : void {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setMailer(MailerInterface $mailer) : void {
        $this->mailer = $mailer;
    }

    public function getRoles() : array {
        return $this->roles;
    }

    public function getAfterLogin() : string {
        return $this->afterLogin;
    }

    public function getAfterRequest() : string {
        return $this->afterRequest;
    }

    public function getAfterReset() : string {
        return $this->afterReset;
    }

    public function getAfterLogout() : string {
        return $this->afterLogout;
    }

    /**
     * @param $email
     */
    public function find($email) : ?UserInterface {
        return $this->repository->findOneByEmail($email);
    }

    /**
     * @param $token
     *
     * @throws Exception
     */
    public function findByToken($token) : ?UserInterface {
        /** @var User $user */
        $user = $this->repository->findOneByResetToken($token);
        if ($user && $user->getResetExpiry() < new DateTimeImmutable()) {
            $this->logger->warning("{$user->getEmail()} attempted to use expired token.");

            return null;
        }

        return $user;
    }

    /**
     * @throws Exception
     */
    public function generatePassword() : string {
        $bytes = random_bytes(self::PASSWORD_BYTES);

        return base64_encode($bytes);
    }

    /**
     * @throws Exception
     */
    public function generateToken() : string {
        return rtrim(strtr(base64_encode(random_bytes(self::TOKEN_BYTES)), '+/', '-_'), '=');
    }

    /**
     * @throws Exception
     */
    public function requestReset(User $user) : void {
        $token = $this->generateToken();
        $expiry = new DateTimeImmutable(self::TOKEN_EXPIRY);

        $user->setResetToken($token);
        $user->setResetExpiry($expiry);
    }

    /**
     * @param $password
     *
     * @throws Exception
     */
    public function encodePassword(UserInterface $user, $password) : string {
        return $this->encoder->encodePassword($user, $password);
    }

    /**
     * @param $password
     */
    public function changePassword(UserInterface $user, $password) : void {
        $user->setPassword($this->encoder->encodePassword($user, $password));
    }

    /**
     * @param $password
     */
    public function validatePassword(UserInterface $user, $password) : bool {
        return $this->encoder->isPasswordValid($user, $password);
    }

    /**
     * @param $role
     */
    public function promote(UserInterface $user, $role) : void {
        if ( ! in_array($role, $this->roles, true)) {
            $this->logger->warning("Unknown role {$role}.");
        }
        $user->addRole($role);
    }

    /**
     * @param $role
     */
    public function demote(UserInterface $user, $role) : void {
        if ( ! in_array($role, $this->roles, true)) {
            $this->logger->warning("Unknown role {$role}.");
        }
        $user->removeRole($role);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendReset(User $user, array $data) : void {
        $email = new TemplatedEmail();
        $email->from($this->sender);
        $email->to($user->getEmail());
        $email->subject('Password Reset Request');

        $email->textTemplate('@NinesUser/security/reset_email.txt.twig');
        $email->context([
            'user' => $user,
            'ip' => $data['ip'] ?? '',
        ]);
        $this->mailer->send($email);
    }
}
