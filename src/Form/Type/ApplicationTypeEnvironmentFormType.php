<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationTypeEnvironmentFormType extends AbstractType
{

    private $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new SettingFormListener($this->formService));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', ApplicationTypeEnvironment::class);
    }
}
