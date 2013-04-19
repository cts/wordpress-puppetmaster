<?php

// This is the wordpress database object. It's already initialized and ready to use.
global $wpdb;

// These are the tables in the WP Database.
/**$USER_TABLES = array(
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
*/
$USER_TABLES = array(
  $wpdb->posts,
  $wpdb->postmeta,
  $wpdb->comments,
  $wpdb->commentmeta,
  $wpdb->links,
  $wpdb->term_relationships,
  $wpdb->term_taxonomy,
  $wpdb->terms,
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
  global $USER_TABLES; 
  global $wpdb;
  foreach ($USER_TABLES as $table) {
    $sql = $wpdb->prepare("DELETE FROM $table");
    $wpdb->query($sql);
  }
};

function addPage($pageJSON) {
    global $wpdb;
    $title     = $pageJSON["title"];
    $content   = $pageJSON["content"];
    $url       = $pageJSON["url"];
    $author       = $pageJSON["author"];
    $comments = $pageJSON["comments"];    
    $type = "page";
    $authorRow = $wpdb->get_results($wpdb->prepare("select ID FROM $wpdb->users where user_login = %s", $author));
    foreach ($authorRow as $a)
      {
        $authorID = $a->ID;
      }
    $wpdb->insert($wpdb->posts, array('post_author' =>$authorID, 'post_content'=>$content, 'post_title'=>$title, 'guid'=>$url,'post_type'=>$type),array('%d', '%s', '%s', '%s','%s'));
    $postID=$wpdb->insert_id;  
   if ($comments) {
   for ($h=0; $h<count($comments); $h++) {
      addComment($postID, $comments[$h]);
    } 
}
}

function getOrAddTagID($tagName) {
  // adds to wp_term_taxonomy if not already there
   global $wpdb;
  $query = $wpdb->prepare("select $wpdb->term_taxonomy.term_taxonomy_id from $wpdb->term_taxonomy inner join $wpdb->terms on $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id where $wpdb->term_taxonomy.taxonomy=%s and $wpdb->terms.name=%s","post_tag", $tagName);
  $id = $wpdb->get_row($query,ARRAY_N);
  if (count($id) > 0) {
    return $id[0];
  } 
  else {
    $wpdb->insert($wpdb->terms, array('name' =>$tagName, 'slug'=>$tagName),array('%s', '%s'));
    $newID=$wpdb->insert_id; 
    $wpdb->insert($wpdb->term_taxonomy, array('term_id' =>$newID, 'taxonomy'=>'post_tag'),array('%d', '%s'));
    return $wpdb->insert_id;
   }
}

function getOrAddCategoryID($categoryName) {
  global $wpdb;
  // adds to wp_tern_taxonomy if not already there
  // returns tagID
//select wp_terms.term_id, wp_terms.name, wp_term_taxonomy.taxonomy
//from wp_terms
//inner join wp_term_taxonomy on wp_terms.term_id=wp_term_taxonomy.term_id
//where wp_term_taxonomy.taxonomy="category" and wp_terms.name="Uncategorized"
  $query = $wpdb->prepare("select $wpdb->term_taxonomy.term_taxonomy_id from $wpdb->term_taxonomy inner join $wpdb->terms on $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id where $wpdb->term_taxonomy.taxonomy=%s and $wpdb->terms.name=%s","category", $categoryName);
  $id = $wpdb->get_row($query,ARRAY_N);
  if (count($id) > 0) {
    return $id[0];
  } 
  else {
    $wpdb->insert($wpdb->terms, array('name' =>$categoryName, 'slug'=>$categoryName),array('%s', '%s'));
    $newID=$wpdb->insert_id;
   
    $wpdb->insert($wpdb->term_taxonomy, array('term_id' =>$newID, 'taxonomy'=>'category'),array('%d', '%s'));
    return $wpdb->insert_id;
   }
}

function addTagToPost($tagId, $postId) {
  // adds to wp_term_relationships
  global $wpdb;
  $wpdb->insert($wpdb->term_relationships, array('object_id'=>$postId,'term_taxonomy_id'=>$tagId), array('%d','%d'));
  $query = $wpdb->query($wpdb->prepare("update $wpdb->term_taxonomy set count = count+1 where term_taxonomy_id=%d",$tagId));
}

function addComment( $postId,$commentJSON) {
  // adds to wp_term_relationships
  global $wpdb;
  $user=1;
  $wpdb->insert($wpdb->comments, array('comment_post_ID'=>$postId,'comment_author_email'=>$commentJSON["email"], 'comment_author'=>$commentJSON["author"], 'comment_content'=>$commentJSON["content"], 'user_id'=>$user), array('%d','%s', '%s', '%s', '%d'));
  $wpdb->query($wpdb->prepare("update $wpdb->posts set $wpdb->posts.comment_count = $wpdb->posts.comment_count+1 where $wpdb->posts.ID=%d",$postId));
}


function addCategoryToPost($categoryId, $postId) {
  // adds to wp_term_relationships
  global $wpdb;
  $wpdb->insert($wpdb->term_relationships, array('object_id'=>$postId,'term_taxonomy_id'=>$categoryId), array('%d','%d'));
  $query = $wpdb->query($wpdb->prepare("update $wpdb->term_taxonomy set count = count+1 where term_taxonomy_id=%d",$categoryId));
}

function addAuthor($authorJSON) {
  global $wpdb;
  $name = $authorJSON['name'];
  $email = $authorJSON['email']; 
  $query = $wpdb->prepare("select $wpdb->users.user_login from $wpdb->users where $wpdb->term_taxonomy.user_login=%s", $name);
  $id = $wpdb->get_row($query,ARRAY_N);
  if (count($id) < 1) {
    $wpdb->insert($wpdb->users, array('user_login'=>$name,'user_nicename'=>$name, 'display_name'=>$name,'user_email'=>$email), array('%s','%s','%s','%s'));
  }
}

function addPost($postJSON) {
    global $wpdb;
    $name      = $postJSON["name"];
    $title     = $postJSON["title"];
    $content   = $postJSON["content"];
    $url       = $postJSON["url"];
    $format    = $postJSON["format"];
    $tags    = $postJSON["tags"];
    $categories    = $postJSON["categories"];
    $comments    = $postJSON["comments"];
 $authorRow = $wpdb->get_results($wpdb->prepare("select ID FROM $wpdb->users where user_login = %s", $postJSON["author"]));
    foreach ($authorRow as $a)
      {
        $authorID = $a->ID;
      }
    $query  = $wpdb->prepare("insert into $wpdb->posts (post_title, guid,post_name, post_author, post_content) values (%s,%s,%s,%d,%s)", $title, $url, $name, $authorID, $content);
    $wpdb->query($query);
    $postID=$wpdb->insert_id;  
    for ($i=0;$i<count($categories);$i++) {
      $categoryID = getOrAddCategoryID($categories[$i]);
      echo '\n CATEGORY ID: '.$categoryID;
      echo '\n and post id: '.$postID;
      addCategorytoPost($categoryID, $postID);
    } 
    for ($g=0;$g<count($tags);$g++) {
      $tagID = getOrAddTagID($tags[$g]);
      echo '\n TAG ID: '.$tagID;
      echo '\n and post id: '.$postID;
      addTagtoPost($tagID, $postID);
    } 
   for ($h=0; $h<count($comments); $h++) {
      addComment($postID, $comments[$h]);
    } 

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
  $posts = $json["content"]["posts"];
  $categories = $json["content"]["categories"];
  $pages = $json["content"]["pages"];
  $author = $json["author"];
  addAuthor($author);
  for($y=0;$y<count($categories);$y++) {
    getOrAddCategoryID($categories[$y]);
  }
  for($z=0;$z<count($pages);$z++) {
    addPage($pages[$z]);
  }
  for($x=0;$x<count($posts);$x++) {
    addPost($posts[$x]);
  }
};

?>
