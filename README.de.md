# Responsive Privacy Youtube Embed

UliCMS Modul - Binden Sie Youtube Videos in Seiten ein.

## Features

* Einbindung per ShortCode
* Enthält eine Funktion die auf Basis einer Video-URL einen HTML-Code generiert
* Komplett Reponsive
* Nutzt den erweiterten Privatsphärenmodus
* Videos können mit zwei Layouts angezeigt werden (eingebundener Player oder Video Thumbnail-Bild mit Verlinkung)

## Beispiele

### Short Code Beispiel

```
[youtube=https://www.youtube.com/watch?v=TloYaUCoO-0]
```

### API Beispiel

```php
$controller = ModuleHelper::getMainController("youtube_embed");
echo $controller->getYoutubeEmbedHtml("https://www.youtube.com/watch?v=TloYaUCoO-0");
```