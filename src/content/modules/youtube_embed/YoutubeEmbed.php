<?php

class YoutubeEmbed extends MainClass
{
    const MODULE_NAME = "youtube_embed";

    public function head(): void
    {
        enqueueStylesheet(ModuleHelper::buildModuleRessourcePath(self::MODULE_NAME, "css/youtube.css"));
        combinedStylesheetHtml();
    }

    public function adminHead(): void
    {
        $this->head();
    }

    public function contentFilter($html): string
    {
        // special case for blog module
        $blog_admin = Request::getVar("blog_admin");
        if (in_array($blog_admin, [
                    "edit_post", "add"
                ])) {
            return $html;
        }
        $youtube_embed_layout = Settings::get("youtube_embed_layout", "str");
        if (!$youtube_embed_layout) {
            $youtube_embed_layout = "player";
        }

        preg_match_all("/\[youtube=(.+)]/i", $html, $matches);
        if (count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $replaceCode = $matches[0][$i];
                $url = strip_tags($matches[1][$i]);
                $embedCode = $this->getYoutubeEmbedHtml($url, $youtube_embed_layout);
                $html = str_replace($replaceCode, $embedCode, $html);
            }
        }
        return $html;
    }

    public function thumbnail(): void
    {
        $image = $this->_thumbnail();

        if (!$image) {
            TextResult("NotFound", HttpStatusCode::NOT_FOUND);
        }
        Result($image, HttpStatusCode::OK, "image/jpeg");
    }

    public function _thumbnail(): ?string
    {
        $url = Request::getVar("url", "", "str");
        $number = Request::getVar("number", 0, "int");

        $videoId = $this->getVideoId($url);
        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/{$number}.jpg";

        $image = file_get_contents_wrapper($thumbnailUrl, false);
        if (!$image) {
            return null;
        }
        
        return $image;
    }

    public function getVideoId(string $url): ?string
    {
        $videoId = null;
        $query = parse_url($url, PHP_URL_QUERY);
        $domain = parse_url($url, PHP_URL_HOST);

        if (!$domain) {
            return null;
        }

        if (str_contains($domain, "youtu.be")) {
            $path = parse_url($url, PHP_URL_PATH);
            $videoId = basename($path);
        } else {
            $args = [];
            parse_str($query, $args);
            $videoId = isset($args["v"]) ? $args["v"] : null;
        }
        return $videoId;
    }

    public function getYoutubeEmbedHtml(string $url, string $layout = "player")
    {
        $videoId = $this->getVideoId($url);

        ViewBag::set("video_id", $videoId);
        return Template::executeModuleTemplate(
            self::MODULE_NAME,
            "{$layout}.php"
        );
    }

    public function settings(): ?string
    {
        if (Request::isPost()) {
            $youtube_embed_layout = Request::getVar("youtube_embed_layout");
            if (!in_array($youtube_embed_layout, array(
                        "player",
                        "thumbnail"
                    ))) {
                $youtube_embed_layout = "player";
            }
            Settings::set("youtube_embed_layout", $youtube_embed_layout);
            Request::redirect(
                ModuleHelper::buildAdminURL(self::MODULE_NAME, "save=1")
            );
        }

        return Template::executeModuleTemplate(
            self::MODULE_NAME,
            "settings.php"
        );
    }

    public function getSettingsHeadline(): string
    {
        return '<i class="fab fa-youtube text-red"></i> Youtube Embed';
    }
}
