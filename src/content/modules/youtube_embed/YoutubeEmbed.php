<?php

class YoutubeEmbed extends MainClass
{

    const MODULE_NAME = "youtube_embed";

    public function head()
    {
        enqueueStylesheet(ModuleHelper::buildModuleRessourcePath(self::MODULE_NAME, "css/youtube.css"));
        combinedStylesheetHtml();
    }

    public function adminHead()
    {
        $this->head();
    }

    public function contentFilter($html)
    {
        $youtube_embed_layout = Settings::get("youtube_embed_layout", "str");
        if (! $youtube_embed_layout) {
            $youtube_embed_layout = "player";
        }
        preg_match_all("/\[youtube=(.+)]/i", $html, $matches);
        if (count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i ++) {
                $replaceCode = $matches[0][$i];
                $url = $matches[1][$i];
                $embedCode = $this->getYoutubeEmbedHtml($url, $youtube_embed_layout);
                $html = str_replace($replaceCode, $embedCode, $html);
            }
        }
        return $html;
    }

    public function thumbnail()
    {
        $url = Request::getVar("url");
        $number = Request::getVar("number") ? Request::getVar("number") : 0;
        
        $query = parse_url($url, PHP_URL_QUERY);
        $args = array();
        parse_str($query, $args);
        $videoId = isset($args["v"]) ? $args["v"] : null;
        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/{$number}.jpg";
        
        $image = file_get_contents_wrapper($thumbnailUrl, false);
        
        if (! $image) {
            TextResult("NotFound", HttpStatusCode::NOT_FOUND);
        }
        Result($image, HttpStatusCode::OK, "image/jpeg");
    }

    public function getYoutubeEmbedHtml($url, $layout = "player")
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $args = array();
        parse_str($query, $args);
        $videoId = isset($args["v"]) ? $args["v"] : null;
        if (! $videoId) {
            return null;
        }
        ViewBag::set("video_id", $videoId);
        return Template::executeModuleTemplate(self::MODULE_NAME, "{$layout}.php");
    }
}
