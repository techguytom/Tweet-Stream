<?php
/*
Plugin Name: Tweet Stream
Version: 1.2
Plugin URI: http://techguytom/tweet-stream
Description: Display your latest tweets and/or mentions in a widget for display in your sidebar or any other widgetized area.
Author: Tom Jenkins
Author URI: http://techguytom.com

Copyright 2010  Tom Jenkins (email: tom@techguytom.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/* TODO switch all triggers to be boolean */
// Register the plugin
register_activation_hook(__FILE__, 'tgt_install_tweet_stream');

function tgt_install_tweet_stream()
{
    // check worpdpress version for compatibility
    global $wp_version;
    if (version_compare($wp_version, "2.9", "<")) {
        deactivate_plugins(basename(__FILE__));
        wp_die("This plugin requires Wordpress version 2.9 or higher.");

    }

}

// Add Header Code
add_action('wp_print_styles', 'tgt_tweet_head');

function tgt_tweet_head()
{
    $tweet_url = WP_PLUGIN_URL . '/tweet-stream/tweet_stream.css';
    $tweet_file = WP_PLUGIN_DIR . '/tweet-stream/tweet_stream.css';

    if (file_exists($tweet_file)) {
        wp_register_style('tweet_stream_style', $tweet_url);
        wp_enqueue_style('tweet_stream_style');

    }

}

// Deactivate the plugin
register_deactivation_hook(__FILE__, 'tgt_uninstall_tweet_stream');

function tgt_uninstall_tweet_stream()
{
    // do something
}

// Prepare for localization
add_action('init', 'tgt_init_tweet_stream');

function tgt_init_tweet_stream()
{
    load_plugin_textdomain('tgt_tweet_stream', false, plugin_basename(dirname(__FILE__) . '/languages'));

}

// widget creation function
// This widget does: Adds users twitter stream
class tgt_tweet_stream_Widget extends WP_Widget
{
    function tgt_tweet_stream_Widget()
    {
        $widget_ops = array('classname' => 'tgt_TweetStream', 'description' => __('Display your latest tweets in the sidebar', 'tgt_tweet_stream'));
        $this->WP_Widget('tgt_TweetStream', 'Tweet Stream', $widget_ops);

    }

    function widget($args, $instance)
    {
        extract($args);
        // Get fields from database
        $username = empty($instance['username']) ? '' : $instance['username'];
        $header = empty($instance['header']) ? '' : $instance['header'];
        $header_text = empty($instance['header_text']) ? '' : $instance['header_text'];
        $display_number = empty($instance['display_number']) ? '1' : $instance['display_number'];
        $links = empty($instance['links']) ? 'no' : $instance['links'];
        $image = empty($instance['image']) ? 'no' : $instance['image'];
        $mentions = empty($instance['mentions']) ? 'no' : $instance['mentions'];
        $mentions_number = empty($instance['mentions_number']) ? '0' : $instance['mentions_number'];

        // Begin widget display
        echo $before_widget;
        echo $before_title;
        if ($header == "image") {
            echo '<a class="tgt-twitter-follow" href="http://twitter.com/' . $username . '" title="Follow ' . $username . ' On Twitter" target="_blank" rel="nofollow"><img src="http://twitter-badges.s3.amazonaws.com/follow_me-a.png"></a>';

        } elseif ($header == "text") {
            echo $header_text;

        }

        echo $after_title;

        // Check if cache is available so the page load isn't delayed
        $cache = $this->id . "_cache";
        if (false !== $displays = get_transient($cache)) {
            if (is_array($displays)) {
                foreach ($displays as $display) {
                    echo $display;

                }

            } else {
                echo "<li class='tgt_tweet'>Twitter is currently flying the Fail Whale.</li>\n";

            }


        } else {
            // Call twitter for tweets
            if ($username != "") {
                $displays = tgt_get_tweet_streams($username, $display_number, $links, $image, $mentions, $mentions_number);
                set_transient($cache, $displays, 600);
                if (is_array($displays)) {
                    foreach ($displays as $display) {
                        echo $display;

                    }

                } else {
                    echo "<li class='tgt_tweet'>Twitter is currently flying the Fail Whale.</li>\n";

                }

            } else {
                echo '<p>You didn&apos;t enter a username for tweet display.</p>';
                echo '<p>Please see the widget configuration to fix this error</p>';

            }

        }


        echo $after_widget;

    }

    function update($new_instance, $old_instance)
    {
        delete_transient($this->id . "_cache");
        $instance = $old_instance;
        $instance['username'] = strip_tags(stripslashes($new_instance['username']));
        $instance['header'] = strip_tags(stripslashes($new_instance['header']));
        $instance['header_text'] = strip_tags(stripslashes($new_instance['header_text']));
        $instance['display_number'] = strip_tags(stripslashes($new_instance['display_number']));
        $instance['links'] = strip_tags(stripslashes($new_instance['links']));
        $instance['image'] = strip_tags(stripslashes($new_instance['image']));
        $instance['mentions'] = strip_tags(stripslashes($new_instance['mentions']));
        $instance['mentions_number'] = strip_tags(stripslashes($new_instance['mentions_number']));

        return $instance;

    }

    function form($instance)
    {
        $defaults = array('username' => __('Username', 'tgt_tweet_stream'), 'header' => 'image', 'header_text' => '', 'display_number' => '0', 'links' => 'yes', 'image' => 'no', 'mentions' => 'no', 'mentions_number' => '0');
        $instance = wp_parse_args((array)$instance, $defaults);
        $username = htmlspecialchars($instance['username']);
        $header = htmlspecialchars($instance['header']);
        $header_text = htmlspecialchars($instance['header_text']);
        $display_number = htmlspecialchars($instance['display_number']);
        $links = htmlspecialchars($instance['links']);
        $image = htmlspecialchars($instance['image']);
        $mentions = htmlspecialchars($instance['mentions']);
        $mentions_number = htmlspecialchars($instance['mentions_number']);

        echo '<p><label for="' . $this->get_field_id('username') . '">' . __("Your Twitter Username:", "tgt_tweet_stream") . ' <input class="widefat" id="' . $this->get_field_id('username') . '" name="' . $this->get_field_name('username') . '" type="text" value="' . $username . '" /></label></p>';

        echo '<p><label for="' . $this->get_field_id('header') . '">' . __("Would you like to use a twitter image for your title, or a custom message?)", "tgt_tweet_stream") . ' <input name="' . $this->get_field_name('header') . '" type="radio" value="image" ';
        if ($header == "image") {
            echo "checked";
        }
        echo ' /> <img src="http://twitter-badges.s3.amazonaws.com/follow_me-a.png" height="17px" style="vertial-align:middle;"> <br/>
		<input name="' . $this->get_field_name('header') . '" type="radio" value="text" ';
        if ($header == "text") {
            echo "checked";
        }
        echo ' /></label><input type="text" name=' . $this->get_field_name('header_text') . ' value="' . $header_text . '" /></p>';

        echo '<p><label for="' . $this->get_field_id('display_number') . '">' . __("How many of your latest tweets would you like displayed?", "tgt_tweet_streem") . '</label>';
        echo '<select class="widefat" id="' . $this->get_field_id('display_number') . '" name="' . $this->get_field_name('display_number') . '">';
        echo '<option value="' . $display_number . '">' . $display_number . '</option>';
        echo '<option value="1">1</option>';
        echo '<option value="2">2</option>';
        echo '<option value="3">3</option>';
        echo '<option value="4">4</option>';
        echo '<option value="5">5</option>';
        echo '<option value="6">6</option>';
        echo '</select>';
        echo '</p>';

        echo '<p><label for="' . $this->get_field_id('links') . '">' . __("Would you like the links to be clickable? (They are already \"nofollow\")", "tgt_tweet_stream") . ' <input name="' . $this->get_field_name('links') . '" type="radio" value="yes" ';
        if ($links == "yes") {
            echo "checked";
        }
        echo ' /> ' . __("Yes", "tweet_stream") . ' <input name="' . $this->get_field_name('links') . '" type="radio" value="no" ';
        if ($links == "no") {
            echo "checked";
        }
        echo ' /> ' . __("No", "tweet_stream") . ' </label></p>';

        echo '<p><label for="' . $this->get_field_id('image') . '">' . __("Would you like to display your twitter avatar?", "tgt_tweet_stream") . ' <input name="' . $this->get_field_name('image') . '" type="radio" value="yes" ';
        if ($image == "yes") {
            echo "checked";
        }
        echo ' /> ' . __("Yes", "tweet_stream") . ' <input name="' . $this->get_field_name('image') . '" type="radio" value="no" ';
        if ($image == "no") {
            echo "checked";
        }
        echo ' /> ' . __("No", "tweet_stream") . ' </label></p>';

        echo '<p><label for="' . $this->get_field_id('mentions') . '">' . __("Would you like to show your latest mentions?", "tgt_tweet_stream") . ' <input name="' . $this->get_field_name('mentions') . '" type="radio" value="yes" ';
        if ($mentions == "yes") {
            echo "checked";
        }
        echo ' /> ' . __("Yes", "tweet_stream") . ' <input name="' . $this->get_field_name('mentions') . '" type="radio" value="no" ';
        if ($mentions == "no") {
            echo "checked";
        }
        echo ' /> ' . __("No", "tweet_stream") . ' </label></p>';

        echo '<p><label for="' . $this->get_field_id('mentions_number') . '">' . __("How many of your latest mentions would you like displayed?", "tgt_tweet_stream") . '</label> <select class="widefat" id="' . $this->get_field_id('mentions_number') . '" name="' . $this->get_field_name('mentions_number') . '">';
        echo '<option value="' . $mentions_number . '">' . $mentions_number . '</option>';
        echo '<option value="1">1</option>';
        echo '<option value="2">2</option>';
        echo '<option value="3">3</option>';
        echo '<option value="4">4</option>';
        echo '<option value="5">5</option>';
        echo '<option value="6">6</option>';
        echo '</select>';
        echo '</p>';

    }

}

function tgt_tweet_streamInit()
{
    register_widget('tgt_tweet_stream_Widget');

}

add_action('widgets_init', 'tgt_tweet_streamInit');

/**
 * Get the latest tweets for the user
 */
function tgt_get_tweet_streams($username, $display_number, $links, $image, $mentions, $mentions_number)
{

    $count = 0;
    $displays = array();

    // Make sure the user wants to display at least one tweet
    if ($display_number > 0) {
        // Build the twitter url and get contents
        $url = "http://api.twitter.com/1/statuses/user_timeline.json?screen_name=$username&count=$display_number";
        $args = array('timeout' => 2);
        $response = wp_remote_get($url, $args);

        // Proceed if no errors existed with get request
        if ((!is_wp_error($response)) && ($response['response']['code'] == 200)) {
            $tweets = json_decode($response['body']);

            // Build the display
            foreach ($tweets as $tweet) {
                $displays[$count] = "<li class='tgt_tweet'>\n";
                if ($image == 'yes' && $links == 'yes') {
                    $displays[$count] .= "<a href='http://twitter.com/" . $tweet->user->screen_name . "' title='Follow " . $tweet->user->screen_name . " On Twitter' target='_blank' rel='nofollow'><img class='alignleft' src='" . $tweet->user->profile_image_url . "' alt='avatar' /></a>\n";

                } elseif ($image == 'yes' && $links == 'no') {
                    $displays[$count] .= "<img class='alignleft' src='" . $tweet->user->profile_image_url . "' alt='avatar' />\n";

                }

                if ($links == 'yes') {
                    $tgt_linked_text = tgt_link_it($tweet->text);

                } else {
                    $tgt_linked_text = $tweet->text;

                }

                $displays[$count] .= "<div class='tgt_tweet_dat'>\n" . $tgt_linked_text . "";

                $tgt_tweet_time = tgt_relative_time($tweet->created_at);
                $displays[$count] .= "<span class='tgt_twitter_meta'>" . $tgt_tweet_time . "";

                if ($tweet->source && $links == 'yes') {
                    $displays[$count] .= " via " . $tweet->source . "";

                }

                $displays[$count] .= "</span>\n</div>\n</li>";

                $count++;

            }

        } else {
            $displays[$count] = "<li class='tgt_tweet'>Twitter is currently flying the fail whale</li>\n";
            $count++;

        }

    } else {
        $displays[0] = "You need to select at least one tweet to display.";

    }

    // Make sure the user wants to display at least one mention
    if (($mentions == "yes") && ($mentions_number > 0)) {

        // Build the twitter url and get contents
        $url = "http://search.twitter.com/search.json?q=@$username&rpp=$mentions_number&page=1";

        //Set a timeout value on the get request of 2 seconds
        $args = array('timeout' => 2);
        $response = wp_remote_get($url, $args);

        // Proceed if no errors existed with get request
        if ((!is_wp_error($response)) && ($response['response']['code'] == 200)) {
            $mentions = json_decode($response['body']);

            // Build the display
            $displays[$count] = "<span class='tgt_mention_head'>Mentions</span>";
            foreach ($mentions->results as $mention) {
                $displays[$count] .= "<li class='tgt_tweet'>";

                if ($image == 'yes' && $links == 'yes') {
                    $displays[$count] .= "<a href='http://twitter.com/" . $mention->from_user . "' title='Follow " . $mention->from_user . " On Twitter' target='_blank' rel='nofollow'><img class='alignleft' src='" . $mention->profile_image_url . "' alt='avatar' /></a>";

                } elseif ($image == 'yes' && $links == 'no') {
                    $displays[$count] .= "<img class='alignleft' src='" . $mention->profile_image_url . "' alt='avatar' />";

                }

                $tgt_linked_text = tgt_link_it($mention->text);
                $displays[$count] .= "<div class='tgt_tweet_dat'>" . $tgt_linked_text . "";

                $tgt_mention_time = tgt_relative_time($mention->created_at);
                $displays[$count] .= "<span class='tgt_twitter_meta'>" . $tgt_mention_time . "";

                if ($mention->source && $links == 'yes') {
                    $displays[$count] .= " via " . html_entity_decode($mention->source) . "";

                }

                $displays[$count] .= " from ";

                if ($links == 'yes') {
                    $displays[$count] .= "<a href='http://twitter.com/" . $mention->from_user . "' title='Follow " . $mention->from_user . " On Twitter' target='_blank' rel='nofollow'>" . $mention->from_user . "</a>";

                } else {
                    $displays[$count] .= $mention->from_user . "";

                }

                $displays[$count] .= "</span></div></li>";

                $count++;

            }

        }

    }

    return $displays;

}

/**
 * Function to convert plain text url's into hypertext
 */
function tgt_link_it($tweet)
{
    $pattern = '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@';
    return preg_replace($pattern, '<a href="$1" target="_blank" rel="nofollow">$1</a>', $tweet);

}

/**
 * Function to parse a twitter time string into more readable text
 */
function tgt_relative_time($time_value)
{
    $utc_time = strtotime($time_value);
    $time = time();
    $off_set = $time - $utc_time;
    if ($off_set < 60) {
        $return = 'a minute ago';

    } else if ($off_set < 120) {
        $return = 'couple of minutes ago';

    } else if ($off_set < (45 * 60)) {
        $return = round($off_set / 60) . ' minutes ago';

    } else if ($off_set < (90 * 60)) {
        $return = 'an hour ago';

    } else if ($off_set < (24 * 60 * 60)) {
        $return = round($off_set / 3600) . ' hours ago';

    } else if ($off_set < (48 * 60 * 60)) {
        $return = '1 day ago';

    } else {
        $return = round($off_set / 86400) . ' days ago';

    }

    return $return;
}

function tgt_get_tweets($args = array())
{
    $username = ($args['username']) ? $args['username'] : 'techguytom';
    $number = ($args['tweets_number']) ? $args['tweets_number'] : 4;
    $links = (false === $args['links']) ? $args['links'] : true;
    $image = (false === $args['images']) ? $args['images'] : true;
    $mentions = (false !== $args['mentions']) ? $args['mentions'] : false;
    $m_number = ($args['mentions_number']) ? $args['mentions_number'] : 0;
    $trans = (false !== $args['cache']) ? $args['cache'] : false;

    if ($links) {
        $links = 'yes';
    } else {
        $links = 'no';
    }
    if ($image) {
        $image = 'yes';
    } else {
        $image = 'no';
    }
    if ($mentions) {
        $mentions = 'yes';
    } else {
        $mentions = 'no';
    }

    $return = "<ul>";
    $cache = "tgt_tweet_tag_" . $username;
    if ($trans) {
        if (false !== $tweets = get_transient($cache)) {
            if (is_array($tweets)) {
                foreach ($tweets as $tweet) {
                    $return .= $tweet;

                }

            } else {
                $return .= tgt_get_stream($username, $number, $links, $image, $mentions, $m_number, $trans);

            }

        } else {
            $return .= tgt_get_stream($username, $number, $links, $image, $mentions, $m_number, $trans);

        }

    } else {
        delete_transient($cache);
        $return .= tgt_get_stream($username, $number, $links, $image, $mentions, $m_number, $trans);

    }
    $return .= "</ul>";

    echo $return;

}

function tgt_get_stream($username, $number, $links, $image, $mentions, $m_number, $trans)
{

    $cache = "tgt_tweet_tag_" . $username;
    $tweets = tgt_get_tweet_streams($username, $number, $links, $image, $mentions, $m_number);
    if (is_array($tweets)) {
        foreach ($tweets as $tweet) {
            $return .= $tweet;

        }

    }
    if ($trans)
        set_transient($cache, $tweets, 600);

    return $return;

}