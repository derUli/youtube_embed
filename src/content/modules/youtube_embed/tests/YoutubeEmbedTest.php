<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use UliCMS\Helpers\TestHelper;

class YoutubeEmbedTest extends TestCase
{
    use MatchesSnapshots;

    protected function setUp(): void
    {
        Translation::loadAllModuleLanguageFiles("en");
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    // youtube.com/watch?v=xyz
    public function testGetVideoIdFromFullYoutubeUrl()
    {
        $videoUrl = "https://www.youtube.com/watch?v=d6sfgRTEzjI";
        $controller = new YoutubeEmbed();
        $this->assertEquals("d6sfgRTEzjI", $controller->getVideoId($videoUrl));
    }

    // youtu.be/xyz
    public function testGetVideoIdFromShortYoutubeUrl()
    {
        $videoUrl = "https://youtu.be/qyqWGSmo9PY";
        $controller = new YoutubeEmbed();
        $this->assertEquals("qyqWGSmo9PY", $controller->getVideoId($videoUrl));
    }

    // Invalid url returns null
    public function testGetVideoIdFromInvalidUrl()
    {
        $videoUrl = "http://this-url-is-crap.com/?a=213213123123";
        $controller = new YoutubeEmbed();
        
        $this->assertNull($controller->getVideoId($videoUrl));
    }

    public function testGetYoutubeEmbedHtml()
    {
        $videoUrl = "https://youtu.be/qyqWGSmo9PY";
        $controller = new YoutubeEmbed();
        $html = $controller->getYoutubeEmbedHtml($videoUrl);
        $this->assertMatchesHtmlSnapshot($html);
    }

    public function testContentFilter()
    {
        $input = "Foo [youtube=https://youtu.be/qyqWGSmo9PY] Bar";
        $controller = new YoutubeEmbed();
        $html = $controller->contentFilter($input);
        $this->assertMatchesHtmlSnapshot($html);
    }
    public function testContentFilterOnBlogPage()
    {
        $_GET["blog_admin"] = "add";
        $input = "Foo [youtube=https://youtu.be/qyqWGSmo9PY] Bar";
        $controller = new YoutubeEmbed();
        $html = $controller->contentFilter($input);
        $this->assertMatchesHtmlSnapshot($html);
    }

    public function testGetSettingsHeadline()
    {
        $controller = new YoutubeEmbed();
        $html = $controller->getSettingsHeadline();
        $this->assertMatchesHtmlSnapshot($html);
    }

    public function testSettings()
    {
        $controller = new YoutubeEmbed();
        $html = $controller->settings();
        $this->assertStringContainsStringIgnoringCase(
            'data-thumbnail="content/modules/youtube_embed/images/player.jpg"',
            $html
        );
    }

    public function testHead()
    {
        $output = TestHelper::getOutput(function () {
            $controller = new YoutubeEmbed();
            $controller->head();
        });
        $this->assertStringStartsWith(
            '<link rel="stylesheet" href="content/cache/legacy/stylesheets/',
            $output
        );
    }

    public function testAdminHead()
    {
        $output = TestHelper::getOutput(function () {
            $controller = new YoutubeEmbed();
            $controller->adminHead();
        });
        $this->assertStringStartsWith(
            '<link rel="stylesheet" href="content/cache/legacy/stylesheets/',
            $output
        );
    }

    public function testThumbnailReturnsNull()
    {
        $controller = new YoutubeEmbed();
        $this->assertNull($controller->_thumbnail());
    }

    public function testThumbnailReturnsString()
    {
        $_GET["url"] = "https://youtu.be/qyqWGSmo9PY";
        $_GET["number"] = "2";
        $controller = new YoutubeEmbed();
        $this->assertMatchesSnapshot($controller->_thumbnail());
    }
}
