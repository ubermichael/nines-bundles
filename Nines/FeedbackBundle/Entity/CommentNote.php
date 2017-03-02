<?php

namespace Nines\FeedbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Comment Note
 *
 * @ORM\Table(name="comment_note", indexes={
 *  @ORM\Index(name="commentnote_ft_idx", 
 *      columns={"content"}, 
 *      flags={"fulltext"}
 *  )
 * })
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentNoteRepository")
 */
class CommentNote extends AbstractEntity
{

    /**
     * User who created the note.
     * @var User
     * @ORM\ManyToOne(targetEntity="Nines\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;
    
    /**
     * Content of the note.
     * @ORM\Column(type="text")
     * @var string
     */
    private $content;
    
    /**
     * Note the comment applies to.
     * @var Comment
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="notes")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false)
     */
    private $comment;
    
    /**
     * Return the content of the note.
     * 
     * @return string
     */
    public function __toString() {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return CommentNote
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set comment
     *
     * @param Comment $comment
     *
     * @return CommentNote
     */
    public function setComment(Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return CommentNote
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
