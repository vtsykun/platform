<?php

namespace Oro\Bundle\ActivityBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

class ActivityEntityApiHandler
{
    /** @var ActivityManager */
    protected $activityManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var FormInterface
     */
    protected $form;


    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param ObjectManager $entityManager
     * @param ActivityManager $activityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $entityManager,
        ActivityManager $activityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->activityManager = $activityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->form = $form;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareFormData($entity)
    {
        $relations = new ArrayCollection();
        $this->form->setData($relations);

        return ['activity' => $entity, 'relations' => $relations];
    }

    /**
     * Process form
     *
     * @param mixed $entity
     *
     * @return mixed|null The instance of saved entity on successful processing; otherwise, null
     */
    public function process($entity)
    {
        $this->checkPermissions($entity);
        $request = $this->requestStack->getCurrentRequest();
        $entity = $this->prepareFormData($entity);

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
        /** @var ActivityInterface $activity */
        $activity = $entity['activity'];
        /** @var ArrayCollection $relations */
        $relations = $entity['relations'];

        $this->activityManager->addActivityTargets($activity, $relations->toArray());

        $this->entityManager->flush();
        
        return true;
    }

    /**
     * @param object $entity
     */
    protected function checkPermissions($entity)
    {
        if (!$this->authorizationChecker->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException();
        }
    }
}
