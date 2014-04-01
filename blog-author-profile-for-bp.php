<?php
 /**
 * Plugin Name: Blog Author Profile for Buddypress sites
 * Version: 1.1
 * Description: Let Blog Admins show all/some of their Buddypress authors profile on author/post/page pages
 * credits: Bowe, Shawn and many of BuddyDev members
 * Requires at least: BuddyPress 1.1
 * Tested up to: BuddyPress 1.9.2
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com
 * Plugin URI: http://buddydev.com/plugins/blog-author-profile-for-buddypress/
 * Last updated: April 1, 2014
 * 
 */
 
 /***
    Copyright (C) 2010 Brajesh Singh(buddydev.com)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or  any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses>.

    */
 
 class BPDEV_BPAuthorProfile_Widget extends WP_Widget {
     
 	public function __construct() {
            
		parent::WP_Widget( false, $name = __( 'BP Author Profile for Blogs', 'bpdev' ) );
	}
        
	public function widget( $args, $instance ) {
            
            if( !bpdev_get_blog_author_id() )
                return;
            
		extract( $args );
	
		echo $before_widget; 
		echo $before_title
		  . $instance['title']
		  . $after_title; 
			
		self::bpdev_show_blog_profile( $instance );/*** show the profile fields**/
			
	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		
            $instance = $old_instance;
            
            foreach( $new_instance as $key => $val )
                $instance[$key] = $val;//update the instance
		
		return $instance;
	}
        

	function form( $instance ) {
		$instance = wp_parse_args(
                            (array) $instance,
                            array( 
                                'title'         => __( 'Author Profile', 'blog-author-profile-bp' ),
                                'show_avatar'   => 'yes'
                                )
                        );
                
		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );
		
	?>
		

            <p>
                <label for="bpdev-widget-title"><?php _e('Title:', 'blog-author-profile-bp'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( stripslashes( $title ) ); ?>" />
                </label>
            </p>
            <p>
                <label for="bpdev-widget-show-avatar"><?php _e( 'Show Avatar' , 'blog-author-profile-bp'); ?>
                        <input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="yes" <?php if( $show_avatar  =="yes") echo "checked='checked'";?> >Yes
                        <input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="no" <?php if(esc_attr(  $show_avatar  )!="yes") echo "checked='checked'";?>>No
                </label>
            </p>
			<?php
			//get all xprofile fields and ask user whether to show them or not
			
			?>
			<h3><?php _e("Profile Fields Visibility","bpdev");?></h3>
			<table>
	
                            <?php if ( function_exists( 'bp_has_profile' ) ) : 
                                    if ( bp_has_profile(  ) ) : while ( bp_profile_groups() ) : bp_the_profile_group();
                             ?>
                            <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
			
                            <?php $fld_name = bp_get_the_profile_field_input_name();
                                  $fld_val = ${$fld_name};
                            ?>
			
                            <tr>
                                <td>
                                    <label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
			
                                </td>
                                <td>
			
                                    <input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="yes" <?php if($fld_val=="yes") echo "checked='checked'";?> >Show
			
                                    <input type="radio" id="<?php echo $this->get_field_id( $fld_name); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="no" <?php if($fld_val!="yes") echo "checked='checked'";?>>Hide
			
                                </td>
			</tr>
			
		<?php endwhile;
		endwhile;
		endif;
		endif;?>
        </table>
		
	<?php
	}

//just a hack for now, code can be cleaned later
/**
 * @desc Get the admin users of current blog
 */
 function get_admin_users_for_current_blog() {
	global $wpdb,$current_blog;
        $users = get_users(
                array(
                        'role'      => 'administrator',
                        'blog_id'   => get_current_blog_id(),
                        'fields'    => 'ID' 
                )
                );
    
        return $users;
}


 function bpdev_show_blog_profile($instance){

     $show_avatar = $instance['show_avatar'];//we need to preserve for multi admin
     
     unset( $instance['show_avatar'] );
     unset( $instance['title'] );//unset the title of the widget,because we will be iterating over the instance fields
     

    $author = bpdev_get_blog_author_id();

    if( empty( $author ) )
        return;

    $user_id = $author;

    $op = '<table class="my-blog-profile">';
    if( $show_avatar == 'yes' ){
        $op .= '<tr class="user-avatar"><td>' . bp_core_get_userlink( $user_id ) . '</td>';

        $op .= '<td>' . bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb' ) ) . '</td></tr>';
    }
//bad approach, because buddypress does not allow to fetch the field name from field key
    
        if ( function_exists( 'bp_has_profile' ) ) :
            if ( bp_has_profile( 'user_id=' . $user_id ) ) :
                    while ( bp_profile_groups() ) : bp_the_profile_group();
                            while ( bp_profile_fields() ) : bp_the_profile_field();
                            
                                            $fld_name = bp_get_the_profile_field_input_name();
                                            
                                            if( array_key_exists( $fld_name, $instance ) && $instance[$fld_name] == 'yes' )
                                                    $op .= '<tr><td>' . bp_get_the_profile_field_name(). '</td><td>' . bp_get_profile_field_data( array('field' => bp_get_the_profile_field_id(),'user_id' => $user_id ) ) . '</td></tr>';
                            endwhile;
                    endwhile;
            endif;
        endif;
	
        $op .= '</table>';
	
        echo $op;
	
 }
 }
 /** Let us register the widget*/
 function bp_blog_authorprofile_register_widget() {
	
     register_widget( 'BPDEV_BPAuthorProfile_Widget' );
     
 }
add_action( 'bp_widgets_init', 'bp_blog_authorprofile_register_widget' );
/**
 * Get the author Id for current page/post
 */

function bpdev_get_blog_author_id(){
    //if this is a single user blog, admin will not need this widgets as Bp profile for blogs will do that
    $author_id = null;
    if ( in_the_loop() ) {
        //inside post loop
	$author_id = get_the_author_ID();
	} elseif ( is_singular() && !is_buddypress() ) {
            global $wp_the_query;
            $author_id = $wp_the_query->posts[0]->post_author;
	} elseif ( is_author() ) {
            global $wp_the_query;
            $author_id = $wp_the_query->get_queried_object_id();
	}

        return $author_id;

}
