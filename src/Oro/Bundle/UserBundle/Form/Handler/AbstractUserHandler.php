<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractUserHandler
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
     * @var UserManager
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack       $request
     * @param UserManager   $manager
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        UserManager $manager
    ) {
        $this->form    = $form;
        $this->requestStack = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  User $user
     * @return bool True on successfull processing, false otherwise
     */
    public function process(User $user)
    {
        $this->form->setData($user);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param User $user
     */
    abstract protected function onSuccess(User $user);
}
