<?php

/**
 * Note that that none array values are required
 * $options = array(
 *   'id' => array(
 *     'label'   => 'String',
 *     'desc'    => 'String',
 *     'query'   => Any valid field http://codex.wordpress.org/Class_Reference/WP_Query#Parameters
 *     'type'    => 'text|textarea|checkbox|select',
 *     'values'  => array('label' => 'value')
 *   )
 * );
 */
class GenericWidget extends WP_Widget {

  /*
   * Takes two arguments: 
   *
   * $fields - User editable fields present in the admin. To set query params add a key/value pair of "query" => "fieldname"
   *
   * $query - This should be query params that the user has no control over
   */
  function __construct($fields, $query) {
    $this->fields = $this->set_defaults($fields);
    $this->query = (array)$query;

    // Might only want to enable this when using a file field
    $uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : NULL ;
    $file = basename( parse_url( $uri, PHP_URL_PATH ) );

    if( $uri && in_array( $file, array( 'widgets.php' ) ) && IS_ADMIN ) {
      add_action('admin_init',array($this, 'admin_add_scripts'));
    }
  }

  function admin_add_scripts() {
    add_thickbox();
    wp_enqueue_script('media-upload');
    wp_enqueue_script( 'okb_widget_admin', get_stylesheet_directory_uri() . "/inc/widgets/admin.js", array('jquery', 'media-upload') );
  }

  function set_defaults($fields) {
    $ret = array();
    foreach ($fields as $key => $attrs) {
      $ret[$key] = array_merge(
        array(
          'name'   => $key,
          'label'  => ucwords(preg_replace('/_/',' ', $key)),
          'type'   => 'text',
        ), $attrs);
    }
    return $ret;
  }

  // Output the content of the widget
  function widget($args, $instance) {
    global $post;

    extract( $args );
    
    $out   = array();
    $title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base);

    if (empty($this->query)) {
      array_push($out, "<li>".$this->generate_markup($instance,null)."</li>");
    } else {
      
      $query = $this->query($this->build_query($instance));

      if( $query->have_posts() ) {
        while( $query->have_posts() ) {
          $query->the_post(); 
          $item = $this->generate_markup($instance,$post);
          if ($item) {
            array_push($out, "<li>".$item."</li>");
          }
        }
      }
    }

    wp_reset_query(); 

    if ( !empty( $out ) ) {
      echo $before_widget;

      if ( $title) {
        echo $before_title . $title . $after_title;
      }

      echo "<ul>" . implode('', $this->after($out)) . "</ul>" . $after_widget;

    }
  } 

  // Allow updating the html after each item has been generated
  function after($out){ 
    return $out; 
  }

  // Allow applying filters due to WP's crap implementation of date querying
  function query($args) {
    return new wp_query($args);
  }

  /*
   * If a field has a query key with a valid query param 
   * value, we'll use the saved value as a query param
   * Note that there are two special fields: orderby and category
   * 
   * orderby will be split into both orderby and order param:
   * E.g.
   * orderby => 'post_date.desc'
   * Results in:
   * 'orderby' => 'post_date'
   * 'order' => 'desc'
   *
   * If category is given, it will default to the 'category_name' taxonomy 
   * unless taxonomy is specified.
   */
  function build_query($instance){
    $query = $this->query;

    foreach ($this->fields as $id => $attrs) {
      $param = $attrs['query'];
      if ($param) {
        $value = $instance[$id];

        if (!$value) { $value = $attrs['default']; }

        switch ( $param ) {
          case 'orderby' :
            $order_args = explode('.', $value);
            $query['order_by'] = $value[0];
            $query['order']    = $value[1] ? $value[1] : null;
            break;
          default:
            $query[$param] = $value; 
        }
      }
    }

    if ($query['category'] && !$query['taxonomy']) {
      $query['taxonomy'] = 'category_name';
    }

    return $query;
  }

  /*
   * Inside this function the post will be available,
   * It should return the markup for a single item
   */
  function generate_markup($instance, $post){
    return "IMPLEMENT ME";
  }

  // Processes widget options to be saved
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    foreach($new_instance as $k=>$v) { $instance[$k] = strip_tags($v); } 
    return $instance;
  }

  // Output the form
  function form($instance) {
    foreach ($this->fields as $id => $attrs) {
      $action = $attrs['type'] . '_field';

      $field  = array_merge($attrs, array(
        'value' => esc_attr($instance[$id]),
        'id'    => $this->get_field_id($id),
        'name'  => $this->get_field_name($id),
      ));

      if (is_callable(array($this, $action))) {
        $this->{$action}($field);
      }
    }
  }

  function select_field($field) {
    $id      = $field['id'];
    $name    = $field['name'];
    $value   = $field['value'];
    $label   = $field['label'];
    $desc    = $field['description'];
    $options = $field['options'];
    ?>
      <p>
        <label for="<?php echo $id; ?>"><?php _e("$label:"); ?></label>
        <select id="<?php echo $id; ?>" name="<?php echo $name; ?>" >
          <option value=""> - <?php echo __( 'Select' ); ?> - </option>
          <?php 
          foreach ($options as $name => $option ) { ?>
            <option value="<?php echo $option; ?>" <?php if( $option == $value) { echo 'selected="selected"'; } ?>><?php echo $name;?></option>
          <?php }	?>
        </select>
      </p>
    <?php 
  }

  function text_field($field) {
    $id     = $field['id'];
    $name   = $field['name'];
    $value  = $field['value'];
    $label  = $field['label'];
    $desc   = $field['description'];
    ?>
      <p>
        <label for="<?php echo $id; ?>"><?php _e("$label:"); ?> <input class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="text" value="<?php echo $value; ?>" /></label>
        <?php echo $desc ?>
      </p>
    <?php 
  }

  function textarea_field($field) {
    $id     = $field['id'];
    $name   = $field['name'];
    $value  = $field['value'];
    $label  = $field['label'];
    $desc   = $field['description'];
    ?>
      <p><label for="<?php echo $id; ?>"><?php _e("$label:"); ?></label> <textarea id="<?php echo $id; ?>" name="<?php echo $name; ?>"><?php echo $value; ?></textarea><?php echo $desc ?></p>
    <?php 
  }

  function checkbox_field($field) {
    $id     = $field['id'];
    $name   = $field['name'];
    $value  = $field['value'];
    $label  = $field['label'];
    $desc   = $field['description'];
    ?>
      <p>
        <label for="<?php echo $id; ?>"><?php _e("$label:"); ?> 
          <input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="hidden" value="0" />
          <input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="checkbox" <?php echo $value ? 'checked="checked"' : '' ?> />
        </label>
        <?php echo $desc ?>
      </p>
    <?php 
  }

  // Based on http://mondaybynoon.com/20120206/wordpress-widget-image-field/
  function file_field($field) {
    $id     = $field['id'];
    $name   = $field['name'];
    $value  = $field['value'];
    $alabel = $field['label'];
    $desc   = $field['description'];

    $attachment = wp_get_attachment_url($value);

    if ($attachment) {
      $attachment = explode('/',$attachment);
      $txt = $attachment[count($attachment)-1];
      $label = "<label for='$id'>Selected: '$txt'</label><br/><br/>";
    }
        
    ?>
      <p>
        <?php echo $label ?>
        <input class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="hidden" value="<?php echo $value; ?>" />
        <a id="<?php echo $id; ?>-trigger" class='okb-file-widget button' onclick="return false;" href='media-upload.php?TB_iframe=1&amp;width=640&amp;height=400' title='<?php echo _e($alabel) ?>'><?php echo _e($alabel) ?></a>
        <?php echo $desc ?>
      </p>
    <?php
  }

} // class GenericWidget
