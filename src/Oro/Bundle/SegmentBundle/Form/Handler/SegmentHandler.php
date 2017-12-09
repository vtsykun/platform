<?php

namespace Oro\Bundle\SegmentBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SegmentBundle\Entity\Manager\StaticSegmentManager;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Symfony\Component\HttpFoundation\RequestStack;

class SegmentHandler
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
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var StaticSegmentManager
     */
    protected $staticSegmentManager;

    /**
     * @param FormInterface $form
     * @param RequestStack $request
     * @param ManagerRegistry $managerRegistry
     * @param StaticSegmentManager $staticSegmentManager
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        ManagerRegistry $managerRegistry,
        StaticSegmentManager $staticSegmentManager
    ) {
        $this->form = $form;
        $this->requestStack = $request;
        $this->managerRegistry = $managerRegistry;
        $this->staticSegmentManager = $staticSegmentManager;
    }

    /**
     * Process form
     *
     * @param  Segment $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Segment $entity)
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->form->setData($entity);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Segment $entity
     */
    protected function onSuccess(Segment $entity)
    {
        $entityManager = $this->managerRegistry->getManager();

        $isNewEntity = is_null($entity->getId());
        if ($isNewEntity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        if ($isNewEntity && $entity->isStaticType()) {
            $this->staticSegmentManager->run($entity);
        }
    }
}
