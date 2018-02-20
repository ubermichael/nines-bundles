<?php

namespace Nines\FeedbackBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Exception;
use Monolog\Logger;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\Flag;
use Nines\FeedbackBundle\Entity\FlagEntity;
use Nines\FeedbackBundle\Form\FlagEntityType;
use ReflectionClass;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Commenting service for Symfony.
 */
class FlagService {
    
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
     * Form factory, for building the comment form.
     *
     * @var FormFactory 
     */    
    private $formFactory;
    
    /**
     * Build the commenting service.
     * 
     * @param array $routing
     */
    public function __construct($routing) {
        $this->routing = $routing;
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
    public function acceptsFlags($name) {
        return array_key_exists($name, $this->routing['flagging']);
    }
    
    /**
     * Find the entity corresponding to a comment.
     * 
     * @param FlagEntity $fe
     * @return mixed
     */
    public function findEntity(FlagEntity $fe) {
        list($class, $id) = explode(':', $fe->getEntity());
        $entity = $this->em->getRepository($class)->find($id);
        return $entity;
    }

    /**
     * Return the short class name for the entity a comment refers to.
     * 
     * @param FlagEntity $fe
     * @return string
     */
    public function entityType(FlagEntity $fe) {
        $entity = $this->findEntity($fe);
        $reflection = new ReflectionClass($entity);
        return $reflection->getShortName();
    }
    
    /**
     * Get the URL to view an entity based on a comment, based on how the
     * entity commenting is configured in config.yml.
     * 
     * @param mixed $entity
     * @return string
     * @throws Exception
     */
    public function entityUrl($entity) {
        $class = get_class($entity);
        $id = $entity->getId();
        if( ! array_key_exists($class, $this->routing['flagging'])) {
            throw new Exception("Cannot map {$class} to a route.");
        }
        return $this->router->generate($this->routing['flagging'][$class], ['id' => $id]);
    }
    
    /**
     * Find the flags for an entity.
     * 
     * @param mixed $entity
     * @return Collection|Comment[]
     */
    public function findFlags($entity) {
        $class = get_class($entity);
        $flags = $this->em->getRepository('NinesFeedbackBundle:FlagEntity')->findBy(array(
            'entity' => $class . ':' . $entity->getId(),
        )); 
        return array_map(function(FlagEntity $fe) {return $fe->getFlag();}, $flags);
    }
    
    /**
     * Add flags to an entity. Removes any pre-existing flags on the entity.
     * 
     * @param mixed $entity
     * @param Flag[] $newFlags
     * 
     * @return Comment
     */
    public function setFlags($entity, $newFlags) {
        $class = get_class($entity);
        $oldFlags = $this->em->getRepository(FlagEntity::class)->findBy(array(
            'entity' => $class . ':' . $entity->getId(),
        ));
        foreach($oldFlags as $old) {
            $this->em->remove($old);            
        }
        foreach($newFlags as $new) {
            $fe = new FlagEntity();
            $fe->setFlag($new);
            $fe->setEntity($class . ':' . $entity->getId());
            $this->em->persist($fe);
        }
        $this->em->flush();
    }
    
    public function addFlag($entity, $flag) {
        $class = get_class($entity);
        $oldFlags = $this->em->getRepository(FlagEntity::class)->findBy(array(
            'entity' => $class . ':' . $entity->getId(),
        ));
        if(in_array($flag, $oldFlags)) {
            return;
        }
        $fe = new FlagEntity();
        $fe->setFlag($flag);
        $fe->setEntity($class . ':' . $entity->getId());
        $this->em->persist($fe);
        $this->em->flush($fe);
    }
    
    public function getEntities(Flag $flag, $class = null) {
        $repo = $this->em->getRepository(FlagEntity::class);
        $flaggedEntities = $repo->findBy(array(
            'flag' => $flag,
        ));
        $entities = array();
        foreach($flaggedEntities as $fe) {
            if($class && ! substr($fe->getEntity(), 0, strlen($class)) === $class) {
                continue;
            }
            $entities[] = $this->findEntity($fe);
        }
        return $entities;
    }
    
    public function getForm($entity) {
        return $this->formFactory->create(FlagEntityType::class, null, array(
            'entity_manager' => $this->em,
            'flags' => $this->findFlags($entity),
        ))->createView();
    }
    
}
