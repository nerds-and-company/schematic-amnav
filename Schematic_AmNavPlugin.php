<?php

namespace Craft;

/**
 * Class Schematic_AmNavPlugin.
 */
class Schematic_AmNavPlugin extends BasePlugin
{
    public function getName()
    {
        return 'Schematic - AmNav migrations';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Nerds and Company';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://www.nerds.company';
    }

    /**
     * @return array
     */
    public function registerMigrationService()
    {
        return array(
            'amnav' => craft()->schematic_amNav,
         );
    }
}
