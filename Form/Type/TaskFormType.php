<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use DigipolisGent\SettingBundle\EventListener\SettingFormListener;
use DigipolisGent\SettingBundle\Service\FormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskFormType extends AbstractType
{

    /**
     * @var FormService
     */
    protected $formService;

    /**
     * @var TaskRunnerService
     */
    protected $taskRunnerService;

    /**
     * TaskFormType constructor.
     *
     * @param FormService $formService
     * @param TaskRunnerService $taskRunnerService
     */
    public function __construct(FormService $formService, TaskRunnerService $taskRunnerService)
    {
        $this->formService = $formService;
        $this->taskRunnerService = $taskRunnerService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $choices = [];
        $provisioners = [];
        switch ($options['type']) {
            case Task::TYPE_BUILD:
                $provisioners = $this->taskRunnerService->getBuildProvisioners();
                break;

            case Task::TYPE_DESTROY:
                $provisioners = $this->taskRunnerService->getDestroyProvisioners();
                break;
        }

        foreach ($provisioners as $provisioner) {
            $choices[$provisioner->getName()] = get_class($provisioner);
        }
        $builder->add('provisioners', ChoiceType::class, [
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'choices' => $choices,
            'label' => 'Limit to following provisioners (selecting none will run all provisioners)'
        ]);
        $builder->addEventSubscriber(new SettingFormListener($this->formService));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Task::class);
        $resolver->setDefault('type', Task::TYPE_BUILD);
    }
}
