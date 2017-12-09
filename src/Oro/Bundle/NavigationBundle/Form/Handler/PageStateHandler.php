<?php

namespace Oro\Bundle\NavigationBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NavigationBundle\Entity\AbstractPageState;

class PageStateHandler
{
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
    protected $manager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     *
     * @param FormInterface         $form
     * @param RequestStack               $request
     * @param ObjectManager         $manager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        ObjectManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->form = $form;
        $this->requestStack = $request;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Process form
     *
     * @param  AbstractPageState $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(AbstractPageState $entity)
    {
        if ($this->tokenStorage->getToken() && is_object($user = $this->tokenStorage->getToken()->getUser())) {
            $entity->setUser($user);
        }

        $this->form->setData($entity);
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param AbstractPageState $entity
     */
    protected function onSuccess(AbstractPageState $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
