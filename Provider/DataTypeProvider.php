<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Provider;


use DigipolisGent\SettingBundle\Provider\DataTypeProviderInterface;

/**
 * Class DataTypeProvider
 * @package DigipolisGent\Domainator9k\CoreBundle\Provider
 */
class DataTypeProvider implements DataTypeProviderInterface
{

    /**
     * @return array
     */
    public function getDataTypes()
    {
        return [
            [
                'key' => 'database_host',
                'label' => 'Database host',
                'required' => true,
                'field_type' => 'string',
                'entity_types' => ['environment'],
            ],
            [
                'key' => 'redis_password',
                'label' => 'Redis password',
                'required' => true,
                'field_type' => 'string',
                'entity_types' => ['environment'],
            ],
        ];
    }
}