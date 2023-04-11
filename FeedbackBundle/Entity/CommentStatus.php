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
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * CommentStatus.
 *
 * @ORM\Table(name="nines_feedback_comment_status")
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentStatusRepository")
 */
class CommentStatus extends AbstractTerm {
    /**
     * List of the comments with this status.
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="status")
     *
     * @var Collection<int,Comment>|Comment[]
     */
    private $comments;

    public function __construct() {
        parent::__construct();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return Collection<int,Comment>|Comment[]
     */
    public function getComments() : Collection {
        return $this->comments;
    }

    public function addComment(Comment $comment) : self {
        if ( ! $this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setStatus($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment) : self {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getStatus() === $this) {
                $comment->setStatus(null);
            }
        }

        return $this;
    }
}
