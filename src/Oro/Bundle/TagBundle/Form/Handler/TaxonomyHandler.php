<?php

namespace Oro\Bundle\TagBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\TagBundle\Entity\Taxonomy;
use Symfony\Component\HttpFoundation\RequestStack;

class TaxonomyHandler
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
     * @param Taxonomy $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(Taxonomy $entity)
    {
        $this->form->setData($entity);
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->manager->persist($entity);
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
