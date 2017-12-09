<?php

namespace Oro\Bundle\EmailBundle\Form\Handler;

use Psr\Log\LoggerInterface;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailHandler
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
     * @var Processor
     */
    protected $emailProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param FormInterface   $form
     * @param RequestStack         $request
     * @param Processor       $emailProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        FormInterface $form,
        RequestStack $request,
        Processor $emailProcessor,
        LoggerInterface $logger
    ) {
        $this->form                = $form;
        $this->requestStack        = $request;
        $this->emailProcessor      = $emailProcessor;
        $this->logger              = $logger;
    }

    /**
     * Process form
     *
     * @param  Email $model
     * @return bool True on successful processing, false otherwise
     */
    public function process(Email $model)
    {
        $this->form->setData($model);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                try {
                    $this->emailProcessor->process($model, $model->getOrigin());

                    return true;
                } catch (\Exception $ex) {
                    $this->logger->error('Email sending failed.', ['exception' => $ex]);
                    $this->form->addError(new FormError($ex->getMessage()));
                }
            }
        }

        return false;
    }
}
