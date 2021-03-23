<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * CommentStatus.
 *
 * @ORM\Table(name="comment_status")
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\CommentStatusRepository")
 */
class CommentStatus extends AbstractTerm
{
    /**
     * List of the comments with this status.
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="status")
     *
     * @var Collection|Comment[]
     */
    private $comments;

    public function __construct() {
        parent::__construct();
        $this->comments = new ArrayCollection();
    }
}
