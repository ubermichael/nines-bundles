<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserManager.
 *
 * Convient user management functions.
 */
class UserManager {
    public const PASSWORD_BYTES = 24;

    public const TOKEN_BYTES = 24;

    public const TOKEN_EXPIRY = ' +1 day';

    private ?UserPasswordEncoderInterface $encoder = null;

    private ?LoggerInterface $logger = null;

    private ?UserRepository $repository = null;

    private ?MailerInterface $mailer = null;

    /**
     * @var array<string>
     */
    private ?array $roles = null;

    private ?string $afterLogin = null;

    private ?string $afterRequest = null;

    private ?string $afterReset = null;

    private ?string $afterLogout = null;

    private ?string $sender = null;

    /**
     * UserManager constructor.
     *
     * @param array<string> $roles
     */
    public function __construct(string $afterLogin, string $afterRequest, string $afterReset, string $afterLogout, array $roles = []) {
        $this->roles = $roles;
        $this->afterLogin = $afterLogin;
        $this->afterRequest = $afterRequest;
        $this->afterReset = $afterReset;
        $this->afterLogout = $afterLogout;
    }

    public function setSender(string $sender) : void {
        $this->sender = $sender;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEncoder(UserPasswordEncoderInterface $encoder) : void {
        $this->encoder = $encoder;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setRepository(UserRepository $repository) : void {
        $this->repository = $repository;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setMailer(MailerInterface $mailer) : void {
        $this->mailer = $mailer;
    }

    /**
     * @return array<string>
     */
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

    public function find(string $email) : ?User {
        return $this->repository->findOneByEmail($email);
    }

    public function findByToken(string $token) : ?User {
        /** @var ?User $user */
        $user = $this->repository->findOneBy(['resetToken' => $token]);
        if ($user && ($user->getResetExpiry() < new DateTimeImmutable())) {
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

    public function encodePassword(User $user, string $password) : string {
        return $this->encoder->encodePassword($user, $password);
    }

    public function changePassword(User $user, string $password) : void {
        $user->setPassword($this->encoder->encodePassword($user, $password));
    }

    public function validatePassword(User $user, string $password) : bool {
        return $this->encoder->isPasswordValid($user, $password);
    }

    public function promote(User $user, string $role) : void {
        if ( ! in_array($role, $this->roles, true)) {
            $this->logger->warning("Unknown role {$role}.");
        }
        $user->addRole($role);
    }

    public function demote(User $user, string $role) : void {
        if ( ! in_array($role, $this->roles, true)) {
            $this->logger->warning("Unknown role {$role}.");
        }
        $user->removeRole($role);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws TransportExceptionInterface
     */
    public function sendReset(User $user, array $data) : Email {
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

        return $email;
    }
}
