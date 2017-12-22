<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApplicationType
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 *
 * @ORM\Entity()
 */
class ApplicationType
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="type",type="string")
     */
    protected $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApplicationTypeEnvironment",mappedBy="applicationType",cascade={"all"},orphanRemoval=true)
     */
    protected $applicationTypeEnvironments;

    /**
     * ApplicationType constructor.
     */
    public function __construct()
    {
        $this->applicationTypeEnvironments = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param ApplicationTypeEnvironment $applicationTypeEnvironment
     */
    public function addApplicationTypeEnvironment(ApplicationTypeEnvironment $applicationTypeEnvironment){
        $this->applicationTypeEnvironments->add($applicationTypeEnvironment);
        $applicationTypeEnvironment->setApplicationType($this);
    }

    /**
     * @param ApplicationTypeEnvironment $applicationTypeEnvironment
     */
    public function removeApplicationTypeEnvironment(ApplicationTypeEnvironment $applicationTypeEnvironment){
        $this->applicationTypeEnvironments->removeElement($applicationTypeEnvironment);
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationTypeEnvironments(){
        return $this->applicationTypeEnvironments;
    }
}