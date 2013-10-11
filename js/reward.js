var player;
function onYouTubePlayerAPIReady() {
  $(document).ready(function() {
    var title = $('#video').attr('title');
    var yt_id = $('#video').attr('yt_id');
    var start = $('#video').attr('start');
    var end = $('#video').attr('end');
    player = new YT.Player('video', {
      height: '680',
      width: '1200',
      videoId: yt_id,
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      },
      playerVars: { autohide: 1, autoplay: 1, controls: 0, enablejsapi: 1, iv_load_policy: 3, modestbranding: 1, start: start, end: end, rel: 0, showinfo: 0 }
    });
  });
}

// autoplay video
function onPlayerReady(event) {
  event.target.playVideo();
}

// when video ends
function onPlayerStateChange(event) {        
  if(event.data === 0) {
    window.location.replace('reward_end.php');
  }
}
