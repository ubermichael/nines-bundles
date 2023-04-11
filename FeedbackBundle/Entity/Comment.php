<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment.
 *
 * @ORM\Table(name="nines_feedback_comment", indexes={
 *     @ORM\Index(name="comment_ft", columns={"fullname", "content"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentRepository")
 */
class Comment extends AbstractEntity implements LinkedEntityInterface {
    use LinkedEntityTrait;

    /**
     * Full name of the commenter.
     *
     * @ORM\Column(type="string", length=120)
     */
    private ?string $fullname = null;

    /**
     * Commenter's email.
     *
     * @ORM\Column(type="string", length=120)
     * @Assert\Email
     */
    private ?string $email = null;

    /**
     * True if the user would like a followup email.
     *
     * @ORM\Column(type="boolean")
     */
    private bool $followUp = false;

    /**
     * Content of the comment.
     *
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * Status of the comment.
     *
     * @ORM\ManyToOne(targetEntity="CommentStatus", inversedBy="comments")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    private ?CommentStatus $status = null;

    /**
     * Any notes the application users have added to the note.
     *
     * @var Collection<int,CommentNote>|CommentNote[]
     *
     * @ORM\OneToMany(targetEntity="CommentNote", mappedBy="comment", orphanRemoval=true)
     */
    private $notes;

    /**
     * Construct the comment.
     */
    public function __construct() {
        parent::__construct();
        $this->notes = new ArrayCollection();
    }

    /**
     * Return the content of the comment.
     *
     * @codeCoverageIgnore
     */
    public function __toString() : string {
        return $this->content;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFullname() : ?string {
        return $this->fullname;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setFullname(string $fullname) : self {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getEmail() : ?string {
        return $this->email;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setEmail(string $email) : self {
        $this->email = $email;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFollowUp() : ?bool {
        return $this->followUp;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setFollowUp(bool $followUp) : self {
        $this->followUp = $followUp;

        return $this;
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
    public function getStatus() : ?CommentStatus {
        return $this->status;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setStatus(?CommentStatus $status) : self {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int,CommentNote>|CommentNote[]
     * @codeCoverageIgnore
     */
    public function getNotes() : Collection {
        return $this->notes;
    }

    public function addNote(CommentNote $note) : self {
        if ( ! $this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setComment($this);
        }

        return $this;
    }

    public function removeNote(CommentNote $note) : self {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getComment() === $this) {
                $note->setComment(null);
            }
        }

        return $this;
    }
}
