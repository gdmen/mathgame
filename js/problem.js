$(document).ready(function() {
  $('#input_answer').contentEditable=true;
  $('#input_answer').focus();
  var milli = 5 * 60 * 1000; //5 minutes
  function idle(){
    document.location = "/";
  }
  setTimeout(idle, milli);
});