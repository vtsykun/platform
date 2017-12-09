<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * TODO: Remove this class after api for acl is ready
 */
class RoleHandler
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
     * @param  Role $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(Role $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
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
     * @param Role   $entity
     * @param User[] $appendUsers
     * @param User[] $removeUsers
     */
    protected function onSuccess(Role $entity, array $appendUsers, array $removeUsers)
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to role
     *
     * @param Role   $role
     * @param User[] $users
     */
    protected function appendUsers(Role $role, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->addRole($role);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from role
     *
     * @param Role   $role
     * @param User[] $users
     */
    protected function removeUsers(Role $role, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->removeRole($role);
            $this->manager->persist($user);
        }
    }
}
