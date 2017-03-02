<?php

namespace Nines\FeedbackBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comment", indexes={
 *  @ORM\Index(name="comment_ft_idx", 
 *      columns={"fullname", "content"}, 
 *      flags={"fulltext"}
 *  )
 * })
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentRepository")
 */
class Comment extends AbstractEntity {

    /**
     * Full name of the commenter.
     * @var string
     * @ORM\Column(type="string", length=120)
     */
    private $fullname;

    /**
     * Commenter's email.
     * @var string
     * @ORM\Column(type="string", length=120)
     * @Assert\Email()
     */
    private $email;

    /**
     * True if the user would like a followup email.
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $followUp;

    /**
     * A string of the form entity:id where entity is the un-namespaced
     * class name in lowercase and id is the numeric id.
     * @ORM\Column(type="string", length=120)
     * @var string
     */
    private $entity;

    /**
     * Content of the comment.
     * @ORM\Column(type="text")
     * @var string
     */
    private $content;

    /**
     * Status of the comment.
     * 
     * @var CommentStatus|null 
     * 
     * @ORM\ManyToOne(targetEntity="CommentStatus", inversedBy="comments")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    private $status;

    /**
     * Any notes the application users have added to the note.
     * 
     * @var Collection|CommentNote[] 
     * @ORM\OneToMany(targetEntity="CommentNote", mappedBy="comment")
     */
    private $notes;

    /**
     * Construct the comment.
     */
    public function __construct() {
        $this->status = null;
        parent::__construct();
    }

    /**
     * Return the content of the comment.
     * 
     * @return string
     */
    public function __toString() {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Comment
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set fullname
     *
     * @param string $fullname
     *
     * @return Comment
     */
    public function setFullname($fullname) {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullname() {
        return $this->fullname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Comment
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set followUp
     *
     * @param boolean $followUp
     *
     * @return Comment
     */
    public function setFollowUp($followUp) {
        $this->followUp = $followUp;

        return $this;
    }

    /**
     * Get followUp
     *
     * @return boolean
     */
    public function getFollowUp() {
        return $this->followUp;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Comment
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Set entity
     *
     * @param string $entity
     *
     * @return Comment
     */
    public function setEntity($entity) {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * Set status
     *
     * @param CommentStatus $status
     *
     * @return Comment
     */
    public function setStatus(CommentStatus $status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return CommentStatus
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Add note
     *
     * @param CommentNote $note
     *
     * @return Comment
     */
    public function addNote(CommentNote $note) {
        $this->notes[] = $note;

        return $this;
    }

    /**
     * Remove note
     *
     * @param CommentNote $note
     */
    public function removeNote(CommentNote $note) {
        $this->notes->removeElement($note);
    }

    /**
     * Get notes
     *
     * @return Collection
     */
    public function getNotes() {
        return $this->notes;
    }

}
