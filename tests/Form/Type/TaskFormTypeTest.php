<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Form\Type\TaskFormType;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\ProvisionerInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskRunnerService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskFormTypeTest extends AbstractFormType
{

    public function testConfigureOptions()
    {
        $optionsResolver = $this->getOptionsResolverMock();

        $arguments = [
            ['data_class', Task::class],
            ['type', Task::TYPE_BUILD],
        ];

        $optionsResolver
            ->expects($this->atLeast(count($arguments)))
            ->method('setDefault')
            ->withConsecutive(...$arguments);

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
            $mock->expects($this->once())->method('isExecutedByDefault')->willReturn(false);
            $mock->expects($this->once())->method('isSelectable')->willReturn(true);
            $provisioners[] = $mock;
            $choices[$name] = get_class($mock);
        }

        $taskRunnerService
            ->expects($this->once())
            ->method('getBuildProvisioners')
            ->willReturn($provisioners);

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('add')
            ->with(
                'provisioners',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                    'choices' => $choices,
                    'data' => [],
                    'empty_data' => [],
                    'label' => 'Limit to following provisioners (selecting none will run the default provisioners)',
                ]
            );

        $formBuilder
            ->expects($this->atLeastOnce())
            ->method('addEventSubscriber');

        $formType = new TaskFormType($this->getFormServiceMock(), $taskRunnerService);
        $formType->buildForm($formBuilder, ['type' => Task::TYPE_BUILD]);
    }
}
