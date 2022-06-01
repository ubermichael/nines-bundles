<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Commenting service for Symfony.
 */
class CommentService {
    private ?EntityManagerInterface $em = null;

    private ?AuthorizationCheckerInterface $authChecker = null;

    private ?FormFactoryInterface $formFactory = null;

    private ?string $defaultStatusName = null;

    private ?string $publicStatusName = null;

    public function __construct(string $defaultStatusName, string $publicStatusName) {
        $this->defaultStatusName = $defaultStatusName;
        $this->publicStatusName = $publicStatusName;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authChecker) : void {
        $this->authChecker = $authChecker;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setFormFactory(FormFactoryInterface $formFactory) : void {
        $this->formFactory = $formFactory;
    }

    /**
     * Find the comments for an entity. If the current user has ROLE_ADMIN, then
     * all comments are returned, otherwise only public comments are returned.
     *
     * @param mixed $entity
     *
     * @throws Exception
     *
     * @return Comment[]
     */
    public function findComments($entity) : array {
        if (is_object($entity)) {
            $id = get_class($entity) . ':' . $entity->getId();
        } elseif (is_string($entity)) {
            $id = $entity;
        } else {
            throw new Exception('Unknown type ' . gettype($entity));
        }

        $comments = [];
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $comments = $this->em->getRepository(Comment::class)->findBy([
                'entity' => $id,
            ]);
        } else {
            $status = $this->em->getRepository(CommentStatus::class)->findOneBy([
                'name' => $this->publicStatusName,
            ]);
            if ($status) {
                $comments = $this->em->getRepository(Comment::class)->findBy([
                    'entity' => $id,
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
     * @throws Exception
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addComment($entity, Comment $comment) : Comment {
        $comment->setEntity(get_class($entity) . ':' . $entity->getId());
        if ( ! $comment->getStatus()) {
            $status = $this->em->getRepository(CommentStatus::class)->findOneBy([
                'name' => $this->defaultStatusName,
            ]);
            if ( ! $status) {
                throw new Exception('Cannot find default comment status ' . $this->defaultStatusName);
            }
            $comment->setStatus($status);
        }
        $this->em->persist($comment);

        return $comment;
    }

    public function getForm() : FormView {
        return $this->formFactory->create(CommentType::class)->createView();
    }
}
