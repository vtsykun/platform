<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\FormBundle\Form\Type\OroBirthdayType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Oro\Bundle\UserBundle\Form\Provider\PasswordFieldOptionsProvider;

class UserType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var PasswordFieldOptionsProvider */
    protected $optionsProvider;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenAccessorInterface $tokenAccessor
     * @param RequestStack $requestStack
     * @param PasswordFieldOptionsProvider $optionsProvider
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor,
        RequestStack $requestStack,
        PasswordFieldOptionsProvider $optionsProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
        $this->requestStack = $requestStack;

        $this->optionsProvider = $optionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // user fields
        $builder->addEventSubscriber(new UserSubscriber($builder->getFormFactory(), $this->tokenAccessor));
        $this->setDefaultUserFields($builder);
        if ($this->authorizationChecker->isGranted('oro_user_role_view')) {
            $builder->add(
                'roles',
                'entity',
                [
                    'property_path' => 'rolesCollection',
                    'label'         => 'oro.user.roles.label',
                    'class'         => 'OroUserBundle:Role',
                    'property'      => 'label',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.role <> :anon')
                            ->setParameter('anon', User::ROLE_ANONYMOUS)
                            ->orderBy('r.label');
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => !$this->isMyProfilePage(),
                    'read_only'     => $this->isMyProfilePage(),
                    'disabled'      => $this->isMyProfilePage(),
                    'translatable_options' => false
                ]
            );
        }
        if ($this->authorizationChecker->isGranted('oro_user_group_view')) {
            $builder->add(
                'groups',
                'entity',
                [
                    'label'     => 'oro.user.groups.label',
                    'class'     => 'OroUserBundle:Group',
                    'property'  => 'name',
                    'multiple'  => true,
                    'expanded'  => true,
                    'required'  => false,
                    'read_only' => $this->isMyProfilePage(),
                    'disabled'  => $this->isMyProfilePage(),
                    'translatable_options' => false
                ]
            );
        }
        $this->addOrganizationField($builder);
        $builder
            ->add(
                'plainPassword',
                'repeated',
                [
                    'invalid_message' => 'oro.user.message.password_mismatch',
                    'type'           => 'password',
                    'required'       => true,
                    'first_options' => [
                        'label' => 'oro.user.password.label',
                        'tooltip' => $this->optionsProvider->getTooltip(),
                        'attr' => [
                            'data-validation' => $this->optionsProvider->getDataValidationOption()
                        ]
                    ],
                    'second_options' => [
                        'label' => 'oro.user.password_re.label',
                    ],
                ]
            )
            ->add(
                'emails',
                'collection',
                [
                    'label'          => 'oro.user.emails.label',
                    'type'           => 'oro_user_email',
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'by_reference'   => false,
                    'prototype'      => true,
                    'prototype_name' => 'tag__name__'
                ]
            )
            ->add('change_password', ChangePasswordType::NAME)
            ->add('avatar', 'oro_image', ['label' => 'oro.user.avatar.label', 'required' => false]);

        $this->addInviteUserField($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\UserBundle\Entity\User',
                'intention'            => 'user',
                'validation_groups'    => function ($form) {
                    if ($form instanceof FormInterface) {
                        $user = $form->getData();
                    } elseif ($form instanceof FormView) {
                        $user = $form->vars['value'];
                    } else {
                        $user = null;
                    }

                    return $user && $user->getId()
                        ? ['Roles', 'Default']
                        : ['Registration', 'Roles', 'Default'];
                },
                'ownership_disabled'   => $this->isMyProfilePage()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_user_user';
    }

    /**
     * Set user fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function setDefaultUserFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('username', 'text', ['label' => 'oro.user.username.label', 'required' => true])
            ->add('email', 'email', ['label' => 'oro.user.email.label', 'required' => true])
            ->add('phone', 'text', ['label' => 'oro.user.phone.label', 'required' => false])
            ->add('namePrefix', 'text', ['label' => 'oro.user.name_prefix.label', 'required' => false])
            ->add('firstName', 'text', ['label' => 'oro.user.first_name.label', 'required' => true])
            ->add('middleName', 'text', ['label' => 'oro.user.middle_name.label', 'required' => false])
            ->add('lastName', 'text', ['label' => 'oro.user.last_name.label', 'required' => true])
            ->add('nameSuffix', 'text', ['label' => 'oro.user.name_suffix.label', 'required' => false])
            ->add('birthday', OroBirthdayType::class, ['label' => 'oro.user.birthday.label', 'required' => false]);
    }

    /**
     * Add Invite user fields
     *
     * @param FormBuilderInterface $builder
     */
    protected function addInviteUserField(FormBuilderInterface $builder)
    {
        $builder->add(
            'inviteUser',
            'checkbox',
            [
                'label'    => 'oro.user.invite.label',
                'mapped'   => false,
                'required' => false,
                'tooltip'  => 'oro.user.invite.tooltip',
                'data'     => true
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addOrganizationField(FormBuilderInterface $builder)
    {
        if ($this->authorizationChecker->isGranted('oro_organization_view')
            && $this->authorizationChecker->isGranted('oro_business_unit_view')
        ) {
            $builder->add(
                'organizations',
                'oro_organizations_select',
                [
                    'required' => false,
                ]
            );
        }
    }

    protected function isMyProfilePage()
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request->attributes->get('_route') === 'oro_user_profile_update';
    }
}
