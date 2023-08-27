<?php

namespace WilokeRepositoryTest\Helpers;

use PHPUnit\Framework\TestCase;
use WilokeRepository\Helpers\WilokeRepository;

class WilokeRepositoryTest extends TestCase
{
    public function testGetFile()
    {
        $oApp = WilokeRepository::init(dirname(dirname(__FILE__)) . '/configs/');
        $this->assertIsArray($oApp->setFile('app')->getAll());

        return $oApp;
    }

    /**
     * @depends testGetFile
     */
    public function testGetItem($oApp)
    {
        $this->assertEquals('wiloke', $oApp->get('username', false));
        return $oApp;
    }

    /**
     * @depends testGetFile
     */
    public function testGetSubItem($oApp)
    {
        $this->assertEquals('wiloke', $oApp->get('fullName', true)->get('firstName'));
    }
}