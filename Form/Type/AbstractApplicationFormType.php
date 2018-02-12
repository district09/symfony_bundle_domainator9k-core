<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AstractApplicationFormType
 * @package DigipolisGent\Domainator9k\CoreBundle\Form\Type
 */
abstract class AbstractApplicationFormType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var FormService $formService */
        $formService = $options['form_service'];

        $builder->add('name');
        $builder->add('gitRepo');
        $builder->add('hasDatabase');
        $builder->addEventSubscriber(new SettingFormListener($formService));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', AbstractApplication::class);
        $resolver->setRequired('form_service');
    }
}
