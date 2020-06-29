// Show preview image on change selection
$('input[name=youtube_embed_layout]').change(
        () => {
    const thumbnail = $('input[name=youtube_embed_layout]:checked')
            .data("thumbnail");
    $("img#preview").attr("src", thumbnail);
});