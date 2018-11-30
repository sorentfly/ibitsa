
<?php $redirectUrl = 'http' . ( _ENGINE_SSL ? 's' : '' ) . '://' .
    $_SERVER['HTTP_HOST'] . $this->url(array(), 'default', true) ?>

<?php // http://amirrajan.net/Blog/asp-mvc-and-facebook-single-sign-on ?>

<div id="fb-root"></div>
<script>
  
  function handleSessionResponse(response) {
    // if we dont have a session (which means the user has been logged out, redirect the user)
    if( !response.session ) {
      window.location = '<?php echo $redirectUrl ?>';
      return;
    }

    //if we do have a non-null response.session, call FB.logout(),
    //the JS method will log the user out
    //of Facebook and remove any authorization cookies
    FB.logout(function(response) {
      window.location = '<?php echo $redirectUrl ?>';
    });
  }
  
  // window.fbAsyncInit = function() {
    FB.init({
      appId  : '<?php echo $this->appId ?>',
      status : true, // check login status
      cookie : false, // enable cookies to allow the server to access the session
      xfbml  : false, // parse XFBML
      channelUrl : 'http' + '<?php echo ( _ENGINE_SSL ? 's' : '' ) ?>' + 
          '://' + '<?php echo $_SERVER['HTTP_HOST'] . rtrim(_ENGINE_R_BASE, '/') ?>' +
          '/application/modules/User/html/channel.html', // channel.html file
      //oauth  : true, // enable OAuth 2.0
      session : '<?php echo $this->fbSession ?>'
    });
    FB.getLoginStatus(handleSessionResponse);
  // };
  <?php   
    $url = rtrim(constant('_ENGINE_SSL') ? 'https://' : 'http://') . 'connect.facebook.net/en_US/all.js';
    $this->headScript()->appendFile($url);
  ?>
  // (function() {
  //   var e = document.createElement('script');
  //   e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
  //   e.async = true;
  //   document.getElementById('fb-root').appendChild(e);
  // }());
  
</script>

<?php $this->headMeta()->appendHttpEquiv('REFRESH', '5; url=' . $redirectUrl) ?>

