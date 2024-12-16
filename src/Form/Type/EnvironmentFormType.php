<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EnvironmentFormType
 * @package DigipolisGent\Domainator9k\CoreBundle\Form\Type
 */
class EnvironmentFormType extends AbstractType
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('name');
        $builder->add('prod');
        $builder->add('gitRef');
        $builder->add('priority');
        $builder->addEventSubscriber(new SettingFormListener($this->formService));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Environment::class);
    }
}

