<?php

namespace Oro\Bundle\AddressBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Symfony\Component\HttpFoundation\RequestStack;

class AddressHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     *
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
     * @param AbstractAddress $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(AbstractAddress $entity)
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
     * "Success" form handler
     *
     * @param AbstractAddress $address
     */
    protected function onSuccess(AbstractAddress $address)
    {
        $this->manager->persist($address);
        $this->manager->flush();
    }
}
