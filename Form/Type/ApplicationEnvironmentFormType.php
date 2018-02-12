<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Build;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApplicationEnvironmentFormType
 * @package DigipolisGent\Domainator9k\CoreBundle\Form\Type
 */
class ApplicationEnvironmentFormType extends AbstractType
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
        $builder->add('domain');
        $builder->add('databaseName');
        $builder->add('databaseUser');
        $builder->add('databasePassword');
        $builder->add('gitRef');
        $builder->addEventSubscriber(new SettingFormListener($this->formService));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ApplicationEnvironment::class);
    }
}