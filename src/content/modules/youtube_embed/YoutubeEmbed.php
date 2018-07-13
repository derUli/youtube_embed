<?php

class YoutubeEmbed extends MainClass
{

    public function frontendFooter()
    {
        echo '<style type="text/css">.yt-iframe-container { position:relative; margin-bottom: 30px; padding-bottom:56.25%; padding-top:25px; height:0; max-width:100%; } .yt-iframe-container iframe { position:absolute; top:0; left:0; width:100%; height:100%; border:none; }</style>';
    }

    public function adminFooter()
    {
        $this->frontendFooter();
    }

    public function contentFilter($html)
    {
        preg_match_all("/\[youtube=(.+)]/i", $html, $matches);
        if (count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i ++) {
                $replaceCode = $matches[0][$i];
                $url = $matches[1][$i];
                $embedCode = $this->getYoutubeEmbedHtml($url);
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
        
        $cachedImage = Path::resolve("ULICMS_CACHE/" . md5("video-thumb-{$videoId}") . ".jpg");
        if (is_file($cachedImage)) {
            $this->displayGraphicFile($cachedImage, $fileType = 'jpeg');
        }
        
        $image = file_get_contents_wrapper($thumbnailUrl);
        
        if (! $image) {
            TextResult("Not Found", HttpStatusCode::NOT_FOUND);
        }
        file_put_contents($cachedImage, $image);
        $this->displayGraphicFile($cachedImage, $fileType = 'jpeg');
    }

    public function getYoutubeEmbedHtml($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $args = array();
        parse_str($query, $args);
        $videoId = isset($args["v"]) ? $args["v"] : null;
        if (! $videoId) {
            return null;
        }
        return "<div class=\"yt-iframe-container\"><iframe src=\"https://www.youtube-nocookie.com/embed/{$videoId}\" allowfullscreen=\"\"></iframe><br /></div>";
    }

    // Return the requested graphic file to the browser
    // or a 304 code to use the cached browser copy
    private function displayGraphicFile($graphicFileName, $fileType = 'jpeg')
    {
        $fileModTime = filemtime($graphicFileName);
        // Getting headers sent by the client.
        $headers = $this->getRequestHeaders();
        // Checking if the client is validating his cache and if it is current.
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $fileModTime)) {
            
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 304);
        } else {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 200);
            header('Content-type: image/' . $fileType);
            header('Content-transfer-encoding: binary');
            header('Content-length: ' . filesize($graphicFileName));
            // make sure caching is turned on
            header('Pragma: public');
            header('Cache-Control: max-age=86400, public');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
            
            $etagFile = md5($graphicFileName);
            header("Etag: $etagFile");
            readfile($graphicFileName);
        }
        exit();
    }

    // return the browser request header
    // use built in apache ftn when PHP built as module,
    // or query $_SERVER when cgi
    private function getRequestHeaders()
    {
        if (function_exists("apache_request_headers")) {
            if ($headers = apache_request_headers()) {
                return $headers;
            }
        }
        $headers = array();
        // Grab the IF_MODIFIED_SINCE header
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $headers['If-Modified-Since'] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        }
        return $headers;
    }
}
