<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;

class StatusHandler
{
    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;


    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected $um;

    /**
     *
     * @param FormInterface $form
     * @param RequestStack       $request
     * @param ObjectManager $em
     * @param UserManager   $um
     */
    public function __construct(FormInterface $form, RequestStack $request, ObjectManager $em, UserManager $um)
    {
        $this->form = $form;
        $this->requestStack = $request;
        $this->em = $em;
        $this->um = $um;
    }

    /**
     * Process Status form
     *
     * @param  User   $user
     * @param  Status $status
     * @param  bool   $updateCurrentStatus
     * @return bool
     */
    public function process(User $user, Status $status, $updateCurrentStatus = true)
    {
        $this->form->setData($status);
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($user, $status, $updateCurrentStatus);

                return true;
            }
        }

        return false;
    }

    /**
     * @param User   $user
     * @param Status $status
     * @param bool   $updateCurrentStatus
     */
    protected function onSuccess(User $user, Status $status, $updateCurrentStatus)
    {
        $status->setUser($user);
        $this->em->persist($status);
        if ($updateCurrentStatus) {
            $user->setCurrentStatus($status);
            $this->um->updateUser($user);
            $this->um->reloadUser($user);
        }
        $this->em->flush();
    }
}
