<?php

// Rails style partials
function okb_partial($name){
  $chunks = explode('/', $name);
  $last   = array_pop($chunks);
    
  if ($last[0] != '_') { $last = '_' . $last; }

  array_push($chunks,$last);

  return get_template_part(implode('/', $chunks));
}

