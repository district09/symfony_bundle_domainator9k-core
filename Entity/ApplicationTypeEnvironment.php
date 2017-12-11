<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApplicationTypeEnvironment
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 *
 * @ORM\Entity()
 */
class ApplicationTypeEnvironment
{

    use SettingImplementationTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ApplicationType
     *
     * @ORM\ManyToOne(targetEntity="ApplicationType",inversedBy="applicationTypeEnvironments")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $applicationType;

    /**
     * @var  Environment
     *
     * @ORM\ManyToOne(targetEntity="Environment")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $environment;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ApplicationType
     */
    public function getApplicationType()
    {
        return $this->applicationType;
    }

    /**
     * @param ApplicationType $applicationType
     */
    public function setApplicationType(ApplicationType $applicationType)
    {
        $this->applicationType = $applicationType;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getEnvironmentName(){
        return $this->getEnvironment()->getName();
    }
}