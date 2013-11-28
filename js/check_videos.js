$(document).ready(function() {
  $("#video_wrapper > .video").each(function(){
    var title = $(this).attr('title');
    var yt_id = $(this).attr('yt_id');
    var start = $(this).attr('start');
    var end = $(this).attr('end');
    
    var video = new YoutubePlayer($(this).attr('id'), yt_id, {
      width: 600,
      height: 336,
      objparams: { allowFullScreen: "true" },
          ytparams: { autohide: 1, autoplay: 0, controls: 0, enablejsapi: 1, iv_load_policy: 3, modestbranding: 1, start: start, end: end, rel: 0, showinfo: 0 },
      attrs:  { class: 'ytembed' }
    });
  });
});
