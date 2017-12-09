<?php

namespace Oro\Bundle\OrganizationBundle\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\HttpFoundation\RequestStack;

class OrganizationHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EntityManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack       $request
     * @param EntityManager $manager
     */
    public function __construct(FormInterface $form, RequestStack $request, EntityManager $manager)
    {
        $this->form    = $form;
        $this->requestStack = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Organization $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(Organization $entity)
    {
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
     * @param Organization $entity
     */
    protected function onSuccess(Organization $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
