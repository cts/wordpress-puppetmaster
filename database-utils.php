<?php

// This is the wordpress database object. It's already initialized and ready to use.
global $wpdb;

// These are the tables in the WP Database.
$USER_TABLES = array(
  'wp_commenta',
  'wp_comments',
  'wp_links',
  'wp_postmeta',
  'wp_posts',
  'wp_term_relationships',
  'wp_term_taxonomy',
  'wp_terms',
  'wp_usermeta', // Note: this one is tricky: we'll have to pay careful attention at how it coordinates with wp_users
  'wp_users'
);

// Note: we don't want to blow away these tables. But we will need to take care to carefully set
// certain fields in here, such as 'blogname' and 'blogdescription'
$OPTIONS_TABLES = array(
  'wp_options'
);

/**
 * Resets the database back to its initial state, just after a new install.
 */
function resetDatabase() {
  $tables = array('table1', 'table2');
  foreach ($TABLES as $table) {
    $sql = "DELETE FROM $table;";
    $wpdb->query($sql);
  }
};

function getOrAddTagID($tagName) {
  // adds to wp_term_taxonomy if not already there
  // returns tagID
}

function getOrAddCategoryID($categoryName) {
  // adds to wp_tern_taxonomy if not already there
  // returns tagID
}

function addTagToPost($tagId, $postId) {
  // adds to wp_term_relationships
}

function addCategoryToPost($categoryId, $postId) {
  // adds to wp_term_relationships
}

function addAuthor($authorJSON) {
$wpdb->query($wpdb->prepare("insert into $wpdb->posts (post_title, post_name, post_author, post_content) values (%s,%s,%d,%s)",'testposttitle', 'testposttitle', 2, 'testpostcontent'));

}

function addPost($postJSON) {
    global $wpdb;
    $name      = $postJSON["name"];
    $title     = $postJSON["title"];
    $content   = $postJSON["content"];
    $url       = $postJSON["url"];
    $authorRow = $wpdb->get_results($wpdb->prepare("select ID FROM $wpdb->users where user_login = %s", $postJSON["author"]));
    foreach ($authorRow as $a)
      {
        $authorID = $a->ID;
      }
    $query  = $wpdb->prepare("insert into $wpdb->posts (post_title, guid,post_name, post_author, post_content) values (%s,%s,%s,%d,%s)", $title, $url, $name, $authorID, $content);
    $sucess = $wpdb->query($query);
}

// ... and so on

/**
 * Given a JSON blob that represents the complete data for a blog, load the database with the proper rows which
 * corresponds to this data.
 */
function loadBlogData($json) {
  // TODO: implement
  // Here is documentation for the $wpdb object
  // http://codex.wordpress.org/Class_Reference/wpdb
  //
  // And a sketch of the code here will basically be crawl over the JSON and call the helper functions above
  // to create appropriate rows in the DB
global $wpdb;
$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->posts"));
$posts = $json["posts"];
for($x=0;$x<count($posts);$x++) {
addPost($posts[$x]);
echo "adding post";
}
};

?>
