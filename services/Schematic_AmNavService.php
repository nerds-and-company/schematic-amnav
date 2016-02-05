<?php

namespace Craft;

use NerdsAndCompany\Schematic\Services\Base as Schematic_AbstractService;

/**
 * Class Schematic_AmNavService.
 */
class Schematic_AmNavService extends Schematic_AbstractService
{
    /**
     * @return AmNavService
     */
    private function getAmnavService()
    {
        return craft()->amNav;
    }

    /**
     * @param string $menuHandle
     * @param array  $menuDefinition
     *
     * @return AmNav_NavigationModel
     */
    private function populateMenu($menuHandle, array $menuDefinition)
    {
        $results = AmNav_NavigationRecord::model()->findByAttributes(['handle' => $menuHandle]);

        $menu = $results ? AmNav_NavigationModel::populateModel($results) : new AmNav_NavigationModel();
        $menu->setAttributes($menuDefinition);
        $menu->setAttribute('handle', $menuHandle);

        return $menu;
    }

    /**
     * @param AmNav_NavigationModel $navigation
     *
     * @return array
     */
    private function getMenuDefinition(AmNav_NavigationModel $navigation)
    {
        $attributes = $navigation->getAttributes();
        unset($attributes['dateCreated']);
        unset($attributes['dateUpdated']);
        unset($attributes['id']);

        return $attributes;
    }

    /**
     * Export all asset sources.
     *
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = array())
    {
        $menus = $this->getAmnavService()->getNavigations('handle', true);

        foreach ($menus as $menu) {
            $data[$menu->handle] = $this->getMenuDefinition($menu);
        }

        return $data;
    }

    /**
     * Import menu definitions.
     *
     * @param array $menuDefinitions
     * @param array $menus
     *
     * @return mixed
     */
    private function importMenuDefinitions(array $menuDefinitions, array &$menus)
    {
        foreach ($menuDefinitions as $menuHandle => $menu) {
            $menu = $this->populateMenu($menuHandle, $menu);

            if (!$this->getAmnavService()->saveNavigation($menu)) {
                $this->addErrors($menu->getAllErrors());
            } else {
                unset($menus[$menuHandle]);
            }
        }
    }

    /**
     * @param array $menus
     */
    private function deleteMenus(array $menus)
    {
        foreach ($menus as $menu) {
            $this->getAmnavService()->deleteNavigationById($menu->id);
        }
    }

    /**
     * Attempt to import menus.
     *
     * @param array $menuDefinitions
     * @param bool  $force           If set to true menus not included in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import(array $menuDefinitions, $force = false)
    {
        $menus = $this->getAmnavService()->getNavigations('handle', true);

        $this->importMenuDefinitions($menuDefinitions, $menus);

        if ($force) {
            $this->deleteMenus($menus);
        }

        return $this->resultModel;
    }
}
