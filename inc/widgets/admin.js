(function($){
  var clicked, field, widget, updater;

  function save(id, thumb){
    // Save our attachment id. In the future an image upload widget could take advantage of sending the thumbnail back
    field.val(id);
  }

  function update(){
    var iframe = $('#TB_iframeContent');

    if(widget && widget.length){

      // need to add our own button
      if (iframe.contents().find('td.savesend').length){
        iframe.contents().find('td.savesend').each(function(){
          if ($(this).find('input.okb-file-selector').length === 0){
            $(this).find('input').hide();
            $(this).prepend('<input type="submit" name="okb_widget_file_field_chooser" class="okb-file-selector button" value="Use this image" />');
          }
        });
      }

      // need to handle the click event
      iframe.contents().find('td.savesend input.okb-file-selector').unbind('click').click(function(e){
        e.preventDefault();
        var parent    = $(this).parent().parent().parent(),
            image_id  = parent.find('td.imgedit-response').attr('id').replace('imgedit-response-',''),
            thumb_url = parent.parent().parent().find('img.pinkynail').attr('src');

        save(image_id,thumb_url);

        // close everything and wrap up
        widget = false;
        tb_remove();
      });

      // update button
      if (iframe.contents().find('.media-item .savesend input[type=submit], #insertonlybutton').length){
        iframe.contents().find('.media-item .savesend input[type=submit], #insertonlybutton').val('Use this file');
      }

      if (iframe.contents().find('#tab-type_url').length){
        iframe.contents().find('#tab-type_url').hide();
      }

      if (iframe.contents().find('tr.post_title').length){
        // we need to ALWAYS get the fullsize since we're retrieving the guid
        // if the user inserts an image somewhere else and chooses another size, everything breaks
        iframe.contents().find('tr.image-size input[value="full"]').prop('checked', true);
        iframe.contents().find('tr.post_title,tr.image_alt,tr.post_excerpt,tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link').hide();
      }
    }

    if (iframe.contents().length === 0 && widget){
      // the thickbox was closed
      clearInterval(updater);
      widget = false;
    }
  }

  $(document).ready(function(){
    $('.widgets-holder-wrap').on('click', 'a.okb-file-widget', function(e){
      e.preventDefault();
      clicked = $(this);
      field   = $('#' + clicked.attr('id').replace(/\-trigger$/,''));

      var href  = clicked.attr('href'), 
          width = $(window).width(), 
          H     = $(window).height(), 
          W     = ( 720 < width ) ? 720 : width;

      if ( ! href ) return;

      href = href.replace(/&width=[0-9]+/g, '');
      href = href.replace(/&height=[0-9]+/g, '');
      $(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );

      widget = $(this).closest('.widget');

      $('#TB_title').remove();       // TODO: why is this necessary?

      tb_show($(this).attr('title'), e.target.href, false);
      updater = setInterval( update, 500 );
    });
  });
})(jQuery);
