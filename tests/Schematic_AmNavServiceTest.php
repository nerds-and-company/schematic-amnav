<?php

namespace Craft;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class Schematic_AmNavServiceTest.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 *
 * @coversDefaultClass Craft\Schematic_AmNavService
 * @covers ::__construct
 * @covers ::<!public>
 */
class Schematic_AmNavServiceTest extends UnitTestSuite_AbstractTest
{
    /**
     * @var Schematic_AmNavService
     */
    private $amnavService;

    /**
     * @var bool
     */
    private $ranBefore = false;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->amnavService = new Schematic_AmNavService();

        $this->mockCraftDb();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../amnav/models/AmNav_NavigationModel.php';
        require_once __DIR__.'/../../amnav/records/AmNav_NavigationRecord.php';
        require_once __DIR__.'/../../amnav/services/AmNavService.php';
        require_once __DIR__.'/../../schematic/models/Schematic_ResultModel.php';
        require_once __DIR__.'/../../schematic/services/Schematic_AbstractService.php';
        require_once __DIR__.'/../Schematic_AmNavPlugin.php';
        require_once __DIR__.'/../services/Schematic_AmNavService.php';
    }

    /**
     * @return Mock
     */
    public function getMockAmnavService()
    {
        $mockNavigationRecord = $this->getMockAmnavNavigationRecord('existing', 'existing', 1, array());

        $mock = $this->getMockBuilder(AmNavService::class)->getMock();

        $mock->expects($this->exactly(1))->method('getNavigations')->willReturn(array(
            'existing' => $mockNavigationRecord,
        ));

        return $mock;
    }

    /**
     * @return Mock
     */
    protected function getMockCDbSchema()
    {
        $mockCDbSchema = parent::getMockCDbSchema();
        $mockCDbSchema->expects($this->any())->method('quoteTableName')->willReturn('tableName');

        return $mockCDbSchema;
    }

    /**
     * Returns amnav navigation record.
     *
     * @param string $name
     * @param string $handle
     * @param int    $id
     * @param array  $settings
     *
     * @return Mock
     */
    public function getMockAmnavNavigationRecord($name, $handle, $id, array $settings)
    {
        $mock = $this->getMockBuilder(AmNav_NavigationModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->id = $id;
        $mock->name = $name;
        $mock->handle = $handle;
        $mock->settings = (Object) $settings;

        return $mock;
    }

    /**
     * Test amnav plugin import.
     *
     * @param array $input
     * @param array $expected
     *
     * @dataProvider amnavMenuProvider
     * @covers ::import
     */
    public function testImportWithForceAndExistingMenu(array $input, array $expected)
    {
        $mockAmnavService = $this->getMockAmnavService();
        $mockAmnavService->expects($this->exactly(1))->method('deleteNavigationById')->willReturn(null);
        $mockAmnavService->expects($this->exactly(2))->method('saveNavigation')->willReturnCallback(function () {
            $result = (!$this->ranBefore);
            $this->ranBefore = true;

            return $result;
        });

        $this->setComponent(craft(), 'amNav', $mockAmnavService);

        $results = $this->amnavService->import($input, true);

        $this->assertTrue($results instanceof Schematic_ResultModel);
        $this->assertTrue(is_array($results->getErrors('errors')));
    }

    /**
     * @dataProvider amnavMenuProvider
     * @covers ::export
     */
    public function testExport()
    {
        $mockAmnavService = $this->getMockAmnavService();
        $this->setComponent(craft(), 'amNav', $mockAmnavService);

        $results = $this->amnavService->export();
        $this->assertTrue(is_array($results));
    }

    /**
     * Menu data provider.
     *
     * @return array
     */
    public function amnavMenuProvider()
    {
        return array(
            array(
                array(
                    'test' => array(
                        'name' => 'Test menu item',
                        'handle' => 'test',
                        'settings' => array(
                            'entrySources' => array('section:2'),
                            'maxLevels' => 1,
                            'canMoveFromLevel' => '',
                            'canDeleteFromLevel' => '',
                        ),
                    ),
                    'test2' => array(
                        'name' => 'Test menu item fail',
                        'handle' => 'testFail',
                        'settings' => array(),
                    ),
                ),
                array(),
            ),
        );
    }
}
