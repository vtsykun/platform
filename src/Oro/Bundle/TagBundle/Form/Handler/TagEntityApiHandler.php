<?php

namespace Oro\Bundle\TagBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Helper\TaggableHelper;
use Symfony\Component\HttpFoundation\RequestStack;

class TagEntityApiHandler implements FormAwareInterface
{
    /** @var TagManager */
    protected $tagManager;

    /** @var TaggableHelper */
    protected $taggableHelper;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @param FormInterface  $form
     * @param RequestStack        $request
     * @param ObjectManager  $entityManager
     * @param TagManager     $tagManager
     * @param TaggableHelper $helper
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        ObjectManager $entityManager,
        TagManager $tagManager,
        TaggableHelper $helper
    ) {
        $this->requestStack = $request;
        $this->entityManager = $entityManager;
        $this->form = $form;
        $this->tagManager     = $tagManager;
        $this->taggableHelper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareFormData($entity)
    {
        $tags = new ArrayCollection();
        $this->form->setData($tags);

        return ['target' => $entity, 'tags' => $tags];
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        if (!$this->taggableHelper->isTaggable($entity)) {
            throw new \LogicException('Target entity should be taggable.');
        }

        $entity = $this->prepareFormData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($request);
            if ($this->form->isValid()) {
                return $this->onSuccess($entity) ?: $entity;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function onSuccess($entity)
    {
        $targetEntity = $entity['target'];

        /** @var ArrayCollection $tags */
        $tags  = $entity['tags'];
        $names = array_map(
            function ($tag) {
                return $tag['name'];
            },
            $tags->getValues()
        );

        $tags = $this->tagManager->loadOrCreateTags($names);
        $this->tagManager->setTags($targetEntity, new ArrayCollection($tags));
        $this->tagManager->saveTagging($targetEntity);
    }


    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->form;
    }
}
