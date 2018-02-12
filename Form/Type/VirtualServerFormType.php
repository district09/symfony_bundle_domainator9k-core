<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;


use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VirtualServerFormType extends AbstractType
{

    private $formService;

    /**
     * ApplicationEnvironmentFormType constructor.
     * @param FormService $formService
     */
    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('name');
        $builder->add('host');
        $builder->add('environment');
        $builder->add('taskServer');
        $builder->addEventSubscriber(new SettingFormListener($this->formService));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', VirtualServer::class);
    }
}