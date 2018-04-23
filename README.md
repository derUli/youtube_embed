# Responsive Privacy Youtube Embed

UliCMS Modul - Embeds youtube videos into pages

## Features

* Provides a short code
* Provides a function to get embed html from an Youtube URL
* Fully Reponsive
* Uses Privacy Enhanced Mode

## Examples

### Short Code Example

```
[youtube=https://www.youtube.com/watch?v=TloYaUCoO-0]
```

### API

```php
$controller = ModuleHelper::getMainController("youtube_embed");
echo $controller->getYoutubeEmbedHtml("https://www.youtube.com/watch?v=TloYaUCoO-0");
```