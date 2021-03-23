<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Monolog\Logger;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Form\CommentType;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Commenting service for Symfony.
 */
class CommentService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * Mapping of class name to route name.
     *
     * @var array
     */
    private $routing;

    /**
     * Name of the default comment status.
     *
     * @var string
     */
    private $defaultStatusName;

    /**
     * Name of the public status.
     *
     * @var string
     */
    private $publicStatusName;

    /**
     * Form factory, for building the comment form.
     *
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Build the commenting service.
     *
     * @param array $routing
     * @param string $defaultStatusName
     * @param string $publicStatusName
     */
    public function __construct($routing, $defaultStatusName, $publicStatusName) {
        $this->routing = $routing;
        $this->defaultStatusName = $defaultStatusName;
        $this->publicStatusName = $publicStatusName;
    }

    /**
     * Set the Doctrine Registry.
     */
    public function setDoctrine(Registry $registry) : void {
        $this->em = $registry->getManager();
    }

    /**
     * Set the logger.
     */
    public function setLogger(Logger $logger) : void {
        $this->logger = $logger;
    }

    /**
     * Set the Symfony router.
     */
    public function setRouter(Router $router) : void {
        $this->router = $router;
    }

    /**
     * Set the Symfony Auth Checker.
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authChecker) : void {
        $this->authChecker = $authChecker;
    }

    /**
     * Set the form factory.
     */
    public function setFormFactory(FormFactory $formFactory) : void {
        $this->formFactory = $formFactory;
    }

    /**
     * Check if an entity is configured to accept comments in config.yml.
     *
     * @param string $name The FQCN of the entity.
     *
     * @return bool
     */
    public function acceptsComments($name) {
        return array_key_exists($name, $this->routing);
    }

    /**
     * Find the entity corresponding to a comment.
     *
     * @return mixed
     */
    public function findEntity(Comment $comment) {
        list($class, $id) = explode(':', $comment->getEntity());

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a comment refers to or null if
     * the entity cannot be found.
     *
     * @throws ReflectionException
     */
    public function entityType(Comment $comment) : ?string {
        $entity = $this->findEntity($comment);
        if ( ! $entity) {
            return null;
        }
        $reflection = new ReflectionClass($entity);

        return $reflection->getShortName();
    }

    /**
     * Get the URL to view an entity based on a comment, based on how the
     * entity commenting is configured in config.yml.
     *
     * @param mixed $full
     *
     * @throws Exception
     *
     * @return string
     */
    public function entityUrl(Comment $comment, $full = false) {
        list($class, $id) = explode(':', $comment->getEntity());
        if ( ! array_key_exists($class, $this->routing)) {
            throw new Exception("Cannot map {$class} to a route.");
        }
        if ($full) {
            return $this->router->generate($this->routing[$class], ['id' => $id], Router::ABSOLUTE_URL);
        }

        return $this->router->generate($this->routing[$class], ['id' => $id]);
    }

    /**
     * Find the comments for an entity. If the current user has ROLE_ADMIN, then
     * all comments are returned, otherwise only public comments are returned.
     *
     * @param mixed $entity
     *
     * @return Collection|Comment[]
     */
    public function findComments($entity) {
        $class = get_class($entity);
        $comments = [];
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $comments = $this->em->getRepository('NinesFeedbackBundle:Comment')->findBy([
                'entity' => $class . ':' . $entity->getId(),
            ]);
        } else {
            $status = $this->em->getRepository('NinesFeedbackBundle:CommentStatus')->findOneBy([
                'name' => $this->publicStatusName,
            ]);
            if ($status) {
                $comments = $this->em->getRepository('NinesFeedbackBundle:Comment')->findBy([
                    'entity' => $class . ':' . $entity->getId(),
                    'status' => $status,
                ]);
            }
        }

        return $comments;
    }

    /**
     * Add a comment to an entity. Also sets the comment's status to the default
     * one.
     *
     * @param mixed $entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Comment
     */
    public function addComment($entity, Comment $comment) {
        $comment->setEntity(get_class($entity) . ':' . $entity->getId());
        if ( ! $comment->getStatus()) {
            $status = $this->em->getRepository('NinesFeedbackBundle:CommentStatus')->findOneBy([
                'name' => $this->defaultStatusName,
            ]);
            if ( ! $status) {
                throw new Exception('Cannot find default comment status ' . $this->defaultStatusName);
            }
            $comment->setStatus($status);
        }
        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function getForm() {
        return $this->formFactory->create(CommentType::class)->createView();
    }
}
