<?php

namespace Oro\Bundle\ReportBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\ReportBundle\Entity\Report;
use Symfony\Component\HttpFoundation\RequestStack;

class ReportHandler
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
     * @param  Report $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Report $entity)
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->form->setData($entity);

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
     * @param Report $entity
     */
    protected function onSuccess(Report $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
