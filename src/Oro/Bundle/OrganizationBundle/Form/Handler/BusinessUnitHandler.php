<?php

namespace Oro\Bundle\OrganizationBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Symfony\Component\HttpFoundation\RequestStack;

class BusinessUnitHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, RequestStack $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->requestStack = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  BusinessUnit $entity
     * @return bool  True on successfull processing, false otherwise
     */
    public function process(BusinessUnit $entity)
    {
        $this->form->setData($entity);
        $request = $this->requestStack->getCurrentRequest();

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $appendUsers = $this->form->get('appendUsers')->getData();
                $removeUsers = $this->form->get('removeUsers')->getData();
                $this->onSuccess($entity, $appendUsers, $removeUsers);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param BusinessUnit  $entity
     * @param User[] $appendUsers
     * @param User[] $removeUsers
     */
    protected function onSuccess(BusinessUnit $entity, array $appendUsers, array $removeUsers)
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to business unit
     *
     * @param BusinessUnit  $businessUnit
     * @param User[] $users
     */
    protected function appendUsers(BusinessUnit $businessUnit, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->addBusinessUnit($businessUnit);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from business unit
     *
     * @param BusinessUnit  $businessUnit
     * @param User[] $users
     */
    protected function removeUsers(BusinessUnit $businessUnit, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->removeBusinessUnit($businessUnit);
            $this->manager->persist($user);
        }
    }
}
