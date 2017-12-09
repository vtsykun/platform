<?php

namespace Oro\Bundle\CommentBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\CommentBundle\Entity\Comment;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentApiHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ObjectManager */
    protected $manager;

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $request
     * @param ObjectManager $manager
     * @param ConfigManager $configManager
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        ObjectManager $manager,
        ConfigManager $configManager
    ) {
        $this->form          = $form;
        $this->requestStack  = $request;
        $this->manager       = $manager;
        $this->configManager = $configManager;
    }

    /**
     * Process form
     *
     * @param Comment $entity
     *
     * @return bool
     */
    public function process(Comment $entity)
    {
        $this->form->setData($entity);
        $request = $this->requestStack->getCurrentRequest();

        if (in_array($request, ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Comment $entity
     */
    protected function onSuccess(Comment $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
