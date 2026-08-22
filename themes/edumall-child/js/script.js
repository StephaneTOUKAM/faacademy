(function($) {
    $(document).ready(function () {
  
      $('.course-loop-title-collapse-2-rows').click( function() {
          
          var user_id = $('.widefat"').attr('data-post-id');
          
          $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              'action': 'load_comments',
              'post_id': post_id
            }
          }).done(function(response) {
            $('.comments').html(response); // Afficher le HTML
            $('.comments-load-button').hide(); // Cacher le bouton
          });
          
      });
  
    });
  })(jQuery);