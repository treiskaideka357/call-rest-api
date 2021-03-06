<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 11/2/16
 * Time: 12:41 PM
 */

namespace Services\Bundle\Rest\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class of unit test
 *
 * Class DefaultControllerTest
 * @package Services\Bundle\Rest\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{

    /**
     * This function make the test
     */
    public function testIndex()
    {

        //Test is working progress
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
