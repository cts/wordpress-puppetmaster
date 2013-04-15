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
  //$query = $wpdb->prepare("select $wpdb->terms.term_id from $wpdb->terms inner join $wpdb->term_taxonomy on $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id");
  $id = $wpdb->get_row($query,ARRAY_N);
  if (count($id) > 0) {
    return $id[0];
  } 
  else {
    $wpdb->insert($wpdb->terms, array('name' =>$categoryName, 'slug'=>$categoryName),array('%s', '%s'));
    $newID=$wpdb->insert_id;
   
    if ($newID) {
      $wpdb->insert($wpdb->term_taxonomy, array('term_id' =>$newID, 'taxonomy'=>'category'),array('%d', '%s'));
      return $wpdb->insert_id;
    }
    else {
      echo "returning null \n";
      return null;
    }
   }
}

function addTagToPost($tagId, $postId) {
  // adds to wp_term_relationships
  global $wpdb;
  $wpdb->insert($wpdb->term_relationships, array('object_id'=>$postId,'term_taxonomy_id'=>$tagId), array('%d','%d'));
  echo '\n insert id: '.$wpdb->insert_id;
  $query = $wpdb->query($wpdb->prepare('update $wpdb->term_taxonomy set count = count+1 where term_taxonomy_id=%d',$tagId));
}

function addCategoryToPost($categoryId, $postId) {
  // adds to wp_term_relationships
  global $wpdb;
  $wpdb->insert($wpdb->term_relationships, array('object_id'=>$postId,'term_taxonomy_id'=>$categoryId), array('%d','%d'));
  echo '\n insert id: '.$wpdb->insert_id;
  $query = $wpdb->query($wpdb->prepare('update $wpdb->term_taxonomy set count = count+1 where term_taxonomy_id=%d',$categoryId));
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
    $date       = $postJSON["date-created"];
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
  for($x=0;$x<count($posts);$x++) {
    addPost($posts[$x]);
  }
};

?>
