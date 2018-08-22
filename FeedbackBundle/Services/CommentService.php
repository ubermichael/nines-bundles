<?php

namespace Nines\FeedbackBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Exception;
use Monolog\Logger;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Form\CommentType;
use ReflectionClass;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Commenting service for Symfony.
 */
class CommentService {

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
     *
     * @param Registry $registry
     */
    public function setDoctrine(Registry $registry) {
        $this->em = $registry->getManager();
    }

    /**
     * Set the logger.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * Set the Symfony router.
     *
     * @param Router $router
     */
    public function setRouter(Router $router) {
        $this->router = $router;
    }

    /**
     * Set the Symfony Auth Checker.
     *
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authChecker) {
        $this->authChecker = $authChecker;
    }

    /**
     * Set the form factory.
     *
     * @param FormFactory $formFactory
     */
    public function setFormFactory(FormFactory $formFactory) {
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
        return array_key_exists($name, $this->routing['commenting']);
    }

    /**
     * Find the entity corresponding to a comment.
     *
     * @param Comment $comment
     * @return mixed
     */
    public function findEntity(Comment $comment) {
        list($class, $id) = explode(':', $comment->getEntity());
        $entity = $this->em->getRepository($class)->find($id);
        return $entity;
    }

    /**
     * Return the short class name for the entity a comment refers to.
     *
     * @param Comment $comment
     * @return string
     */
    public function entityType(Comment $comment) {
        $entity = $this->findEntity($comment);
        $reflection = new ReflectionClass($entity);
        return $reflection->getShortName();
    }

    /**
     * Get the URL to view an entity based on a comment, based on how the
     * entity commenting is configured in config.yml.
     *
     * @param Comment $comment
     * @return string
     * @throws Exception
     */
    public function entityUrl(Comment $comment) {
        list($class, $id) = explode(':', $comment->getEntity());
        if( ! array_key_exists($class, $this->routing['commenting'])) {
            throw new Exception("Cannot map {$class} to a route.");
        }
        return $this->router->generate($this->routing['commenting'][$class], ['id' => $id]);
    }

    /**
     * Find the comments for an entity. If the current user has ROLE_ADMIN, then
     * all comments are returned, otherwise only public comments are returned.
     *
     * @param mixed $entity
     * @return Collection|Comment[]
     */
    public function findComments($entity) {
        $class = get_class($entity);
        $comments = array();
        if($this->authChecker->isGranted('ROLE_ADMIN')) {
            $comments = $this->em->getRepository('NinesFeedbackBundle:Comment')->findBy(array(
                'entity' => $class . ':' . $entity->getId()
            ));
        } else {
            $status = $this->em->getRepository('NinesFeedbackBundle:CommentStatus')->findOneBy(array(
                'name' => $this->publicStatusName
            ));
            if( $status) {
                $comments = $this->em->getRepository('NinesFeedbackBundle:Comment')->findBy(array(
                    'entity' => $class . ':' . $entity->getId(),
                    'status' => $status,
                ));
            }
        }
        return $comments;
    }

    /**
     * Add a comment to an entity. Also sets the comment's status to the default
     * one.
     *
     * @param mixed $entity
     * @param Comment $comment
     *
     * @return Comment
     */
    public function addComment($entity, Comment $comment) {
        $comment->setEntity(get_class($entity) . ':' . $entity->getId());
        if( ! $comment->getStatus()) {
            $status = $this->em->getRepository('NinesFeedbackBundle:CommentStatus')->findOneBy(array(
                'name' => $this->defaultStatusName,
            ));
            if( ! $status) {
                throw new Exception("Cannot find default comment status " . $this->defaultStatusName);
            }
            $comment->setStatus($status);
        }
        $this->em->persist($comment);
        $this->em->flush($comment);
        return $comment;
    }

    public function getForm() {
        return $this->formFactory->create(CommentType::class)->createView();
    }

}
