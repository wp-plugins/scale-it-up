<?php
/*
 * Plugin Name: Scale It UP!
 * Description: This plugin permits you to scale images up as well as down, in the wordpress media editor. 
 * Version: 1.0
 * Author: MagicAce
 */
/* Scale up images functionality in "Edit image" ...
* This is slightly changed function image_resize_dimensions()
in wp-icludes/media.php */
function my_image_resize_dimensions( $nonsense, $orig_w, $orig_h, $dest_w, $dest_h, $crop = false) {

    if ( $crop ) {
        // crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
        $aspect_ratio = $orig_w / $orig_h;
        $new_w = min($dest_w, $orig_w);
        $new_h = min($dest_h, $orig_h);

        if ( !$new_w ) {
            $new_w = intval($new_h * $aspect_ratio);
        }

        if ( !$new_h ) {
            $new_h = intval($new_w / $aspect_ratio);
        }

        $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

        $crop_w = round($new_w / $size_ratio);
        $crop_h = round($new_h / $size_ratio);

        $s_x = floor( ($orig_w - $crop_w) / 2 );
        $s_y = floor( ($orig_h - $crop_h) / 2 );
    } else {
        // don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
        $crop_w = $orig_w;
        $crop_h = $orig_h;

        $s_x = 0;
        $s_y = 0;

        /* wp_constrain_dimensions() doesn't consider higher values for $dest :( */
        /* So just use that function only for scaling down ... */
        if ($orig_w >= $dest_w && $orig_h >= $dest_h ) {
            list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
        } else {
            $ratio = $dest_w / $orig_w;
            $w = intval( $orig_w  * $ratio );
            $h = intval( $orig_h * $ratio );
            list( $new_w, $new_h ) = array( $w, $h );
        }
    }

    // if the resulting image would be the same size or larger we don't want to resize it
    // Now WE need larger images ...
    //if ( $new_w >= $orig_w && $new_h >= $orig_h )
    if ( $new_w == $orig_w && $new_h == $orig_h )
        return false;

    // the return array matches the parameters to imagecopyresampled()
    // int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
    return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

}
add_filter( 'image_resize_dimensions', 'my_image_resize_dimensions', 1, 6 );

?>