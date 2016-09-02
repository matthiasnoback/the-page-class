<?php

namespace Test\Characterisation;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use phpunit\mink\TestCaseTrait;

class PageTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;
    use StartAndStopWebServer;

    protected function baseUrl()
    {
        return 'http://127.0.0.1:8080';
    }

    protected function docRoot()
    {
        return realpath(__DIR__ . '/../../public');
    }

    /**
     * @test
     */
    public function homepage()
    {
        $page = $this->visit($this->baseUrl() . '/');

        $this->assertSuccessfulResponse();
        $this->assertContentsEqual($page, 'This is the homepage!');

        $this->assertMenuItemExists($page, '/', 'Home');
        $this->assertMenuItemExists($page, '/about-me', 'About me');
    }

    /**
     * @test
     */
    public function about_page()
    {
        $page = $this->visit($this->baseUrl() . '/about-me');

        $this->assertSuccessfulResponse();
        $this->assertContentsEqual($page, 'I like to write code with PHP');

        $this->assertMenuItemExists($page, '/', 'Home');
        $this->assertMenuItemExists($page, '/about-me', 'About me');
    }

    private function assertMenuItemExists(DocumentElement $page, $href, $text)
    {
        foreach ($page->findAll('css', 'ul.main_navigation li.item a') as $item) {
            /** @var $item NodeElement */
            if ($item->getText() == $text && $item->getAttribute('href') == $href) {
                return;
            }
        }
        $this->fail('Menu item not found');
    }

    private function assertContentsEqual(DocumentElement $page, $expectedContents)
    {
        $this->assertEquals($expectedContents, $page->find('css', 'p.contents')->getText());
    }

    private function assertSuccessfulResponse()
    {
        $this->assertEquals(
            200,
            $this->session->getStatusCode(),
            "Expected successful response, got: " . $this->session->getPage()->getContent());
    }
}
