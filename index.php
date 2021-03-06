<?php
require 'bin/h2o/h2o.php';

// load db functions
include('model.php');

// connect to the DB in readonly mode
db_connect();


$guid   = mysql_real_escape_string($_GET['id']);
$page   = mysql_real_escape_string($_GET['page']);
$post   = mysql_real_escape_string($_GET['post']);

$recent = getRecentPostSummaries(5);

# data to hand to the template, no matter which path we take
$data = array(
    'blog' => $blog,
    'recent' => $recent
);

if ($post != null) {

    # single post via SEO-friendly title
    $details = getPostBySEOName($post);

    if (! $details['post']['guid']) { show404(); }

    # init template engine
    $h2o = new h2o('tmpl/blog-post.html');

    # add post data
    $data['post'] = $details['post'];
    $data['comments'] = $details['comments'];
    $data['tags'] = $details['tags'];

    if ($details['older']) { $data['older'] = $details['older']; }
    if ($details['newer']) { $data['newer'] = $details['newer']; }

} elseif ($guid != null) {

    # single post; more detail, get tags, etc
    $details = getPostDetails($guid);

    if (! $details['post']['guid']) { show404(); }

    # init template engine
    $h2o = new h2o('tmpl/blog-post.html');

    # add post data
    $data['post'] = $details['post'];
    $data['comments'] = $details['comments'];
    $data['tags'] = $details['tags'];

    if ($details['older']) { $data['older'] = $details['older']; }
    if ($details['newer']) { $data['newer'] = $details['newer']; }

} elseif ($page != null) {

    # single page get details
    $details = getPageDetails($page);

    if (! $details['name']) { show404(); }
    
    if ( $details['standalone'] == 1 ) {
        // no templating necessary
        echo $details['body'];
        exit;
    } else {
        $h2o = new h2o('tmpl/blog-page.tmpl');
        $data['page'] = $details;
    }

} else {
    # recent posts; comment summary is okay, and no need for comment details
    $num    = mysql_real_escape_string($_GET['posts']);
    $offset = mysql_real_escape_string($_GET['offset']);
    if ($num == '') { $num = 3; }
    if ($offset == '') { $offset = 0; }

    $posts = getRecentPosts($num, $offset);

    # init template engine
    $h2o = new h2o('tmpl/blog-index.html');

    # data to hand to the template
    $data['posts'] = $posts;
    $data['older'] = intval($offset) + intval($num);

    if ( $offset != 0 ) {
        $data['offset'] = $offset;
        $data['newer'] = intval($offset) - intval($num);
    }
}

# done!
db_close();

# render the page
echo $h2o->render(compact('data'));
?>