<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Comment Note.
 *
 * @ORM\Table(name="nines_feedback_comment_note", indexes={
 *     @ORM\Index(name="comment_note_ft", columns={"content"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentNoteRepository")
 */
class CommentNote extends AbstractEntity {
    /**
     * User who created the note.
     *
     * @ORM\ManyToOne(targetEntity="Nines\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private ?User $user = null;

    /**
     * Content of the note.
     *
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * Note the comment applies to.
     *
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="notes")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false)
     */
    private ?Comment $comment = null;

    /**
     * Return the content of the note.
     *
     * @codeCoverageIgnore
     */
    public function __toString() : string {
        return $this->content;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContent() : ?string {
        return $this->content;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setContent(string $content) : self {
        $this->content = $content;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getUser() : ?User {
        return $this->user;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setUser(?User $user) : self {
        $this->user = $user;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getComment() : ?Comment {
        return $this->comment;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setComment(?Comment $comment) : self {
        $this->comment = $comment;

        return $this;
    }
}
