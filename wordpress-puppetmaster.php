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

function puppetmaster() {
  echo 'in top';
$string = '{"posts": [
      {
        "name": "zzzPostTitle2",
        "url": "http://people.csail.mit.edu/eob/theme_jailbreak/?p=5",
        "content": "zzzPost1Content",
        "author" : "sarah",
        "title" : "Test Title",
        "date-created" : "2013-03-21 18:46:03",
        "dates-modified" : ["2013-03-21 10:32:13", "2013-03-21 18:46:03"],
        "categories" : ["uncategorized", "zzzCategory1"],
        "tags" : ["zzztag", "sarahtag", "testtag"],
        "format" : "standard",
        "comments" : [
          {"author" : "sarah" ,"email" : "testemail@blah.edu", "content": "zzzcomment1", "date" : "2013-04-10 20:49:46"}
        ]
      }]}';
 $json = json_decode($string, true);
        loadBlogData($json);
echo 'loaded';
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   // if ((isset $_POST['resetblog']) && ($_POST['resetblog'] == '1')) {
     // resetDatabase();
     // if ((isset $_POST['newdata'])) {
      //  $json = json_decode($_POST['newdata']);
        $json = json_decode('map.json');
        loadBlogData($json);
      //}
  echo 'in puppet master down';
  //  }
  }
}

// Tells Wordpress to run puppetmaster() early on in the rendering pipeline, which can be found here:
// http://codex.wordpress.org/Plugin_API/Action_Reference
add_action('plugins_loaded', 'puppetmaster');
