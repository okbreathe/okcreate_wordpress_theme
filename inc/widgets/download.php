<?php

class FileDownloadWidget extends GenericWidget {

  function __construct() {
    $widget_ops = array('classname' => 'widget_file_download', 'description' => __( 'File Download Widget') );
    $this->WP_Widget('file_download_widget', __('File Download Widget'), $widget_ops);

    $fields = array(
      'title' => array(
        'label'   => 'Widget Title',
        'type'    => 'text',
      ),
      'file' => array(
        'label' => 'Select Attachment',
        'type' => 'file'
      )
    );

    parent::__construct($fields, array());
  }

  function generate_markup($instance, $post){
    $title      = $instance['title'];
    $attachment = wp_get_attachment_url($instance['file']);

    return "<a class='entry-content' href='$attachment' target='_blank'>$title</a>";
  }

} // class FileDownloadWidget

register_widget('FileDownloadWidget');
