<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\TaskFormType;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskFormTypeTest extends AbstractFormTypeTest
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $optionsResolver
            ->expects($this->at(0))
            ->method('setDefault')
            ->with('data_class', Task::class);
        $optionsResolver
            ->expects($this->at(1))
            ->method('setDefault')
            ->with('type', Task::TYPE_BUILD);

        $taskRunnerService = $this->getMockBuilder(TaskRunnerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formType = new TaskFormType($this->getFormServiceMock(), $taskRunnerService);
        $formType->configureOptions($optionsResolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $taskRunnerService = $this->getMockBuilder(TaskRunnerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provisioners = [];
        $choices = [];
        foreach(range(0,5) as $index) {
            $mock = $this->getMockBuilder(ProvisionerInterface::class)->getMock();
            $name = 'Provisioner' . $index;
            $mock->expects($this->once())->method('getName')->willReturn($name);
            $provisioners[] = $mock;
            $choices[$name] = get_class($mock);
        }

        $taskRunnerService
            ->expects($this->once())
            ->method('getBuildProvisioners')
            ->willReturn($provisioners);

        $formBuilder
            ->expects($this->at(0))
            ->method('add')
            ->with(
                'provisioners',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                    'choices' => $choices,
                    'label' => 'Limit to following provisioners (selecting none will run all provisioners)',
                ]
            );

        $formBuilder
            ->expects($this->at(1))
            ->method('addEventSubscriber');

        $formType = new TaskFormType($this->getFormServiceMock(), $taskRunnerService);
        $formType->buildForm($formBuilder, ['type' => Task::TYPE_BUILD]);
    }
}
