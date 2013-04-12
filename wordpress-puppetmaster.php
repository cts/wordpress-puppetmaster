<?php
/*
Plugin Name: Wordpress Puppetmaster
Plugin URI: http://www.github.com/cts/wordpress-puppetmaster
Description: Turns Wordpress into a data rendering API.
Version: 0.1 
Author: The Haystack Group @ MIT
Author URI: http://haystack.csail.mit.edu/
*/

include_once('database-utils.php');

// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(~0);

function puppetmaster()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        if ((isset($_POST['resetblog'])) && ($_POST['resetblog'] == '1'))
          {
            //  resetDatabase();
            if (isset($_POST['newdata']))
              {
                // $json = json_decode(file_get_contents($_POST['newdata'], true));
                chdir(__DIR__);
                //$path = getcwd();
                //$path .="/";
                //$path .=$_POST['newdata']; 
                $path = realpath($_POST['newdata']);
                $map  = file_get_contents($path, true);
                $json = json_decode($map, true);
                loadBlogData($json);
              }
          }
      }
  }

// Tells Wordpress to run puppetmaster() early on in the rendering pipeline, which can be found here:
// http://codex.wordpress.org/Plugin_API/Action_Reference
add_action('plugins_loaded', 'puppetmaster');
