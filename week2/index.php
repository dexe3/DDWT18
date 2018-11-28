<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week2', 'ddwt18','ddwt18');

/*Recurring code*/
/* Get Number of Series */
$nbr_series = count_series($db);
/*Get number of Users*/
$nbr_users = count_users($db);
/*Set view for right column to card*/
$right_column = use_template('cards');
/*Navigation template*/
$template = array(
    1 => array(
        'name' => 'Home',
        'url' => '/DDWT18/week2/'
    ),
    2 => array(
        'name' => 'Overview',
        'url' => '/DDWT18/week2/overview'
    ),
    3 => array(
        'name' => 'Add series',
        'url' => '/DDWT18/week2/add'
    ),
    4 => array(
        'name' => 'My Account',
        'url' => '/DDWT18/week2/myaccount'
    ),
    5 => array(
        'name' => 'Registration',
        'url' => '/DDWT18/week2/register'
    ),
    6 => array(
        'name' => 'Login',
        'url' => '/DDWT18/week2/login'
    )
);

/* Landing page */
if (new_route('/DDWT18/week2/', 'get')) {
    /* Get Number of Series */

/*Display potential error/feedback messages*/
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Home' => na('/DDWT18/week2/', True)
    ]);
    /*Dispaly navigation and set array 1 as active*/
    $navigation = get_navigation($template, 1);

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT18/week2/overview/', 'get')) {

    /*Display feedback*/
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview', True)
    ]);
    /*Display navigation*/
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table(get_series($db), $db);

    /* Choose Template */
    include use_template('main');
}

elseif (new_route('/DDWT18/week2/myaccount/', 'get')){
    /*Destrict route to logged in users, redirect*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }
    /*Regenerate session id*/
    session_regenerate_id();

    /*Display feedback*/
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /*Page content*/
    $page_title = 'My Account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'My Account' => na('/DDWT18/week2/myaccount', True)
    ]);
    /*Display navigation*/
    $navigation = get_navigation($template, 4);

    $page_subtitle = 'Your account page';
    $page_content = 'Here you find all your own series etc.';
    $name = get_username($db, $_SESSION['user_id']);
    $user = htmlspecialchars($name['firstname'].' '.$name['lastname']);

    include use_template('account');


}

elseif (new_route('/DDWT18/week2/register/', 'get')){

    /*Display feedback*/
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }


    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Register' => na('/DDWT18/week2/register', True)
    ]);

    /*Display navigation*/
    $navigation = get_navigation($template, 5);

    /*Page content*/
    $page_title = 'Register';
    $page_subtitle = 'Create an account so you can add series too!';

    include use_template('register');

}

elseif (new_route('/DDWT18/week2/register/', 'post')){
    /*Regenerate session id*/
    session_regenerate_id();

    /*Register new user*/
    $feedback = register_user($db, $_POST);

    /*Redirect to account-page*/
    redirect(sprintf('/DDWT18/week2/myaccount/?error_msg=%s',
        json_encode($feedback)));

}

elseif (new_route('/DDWT18/week2/login/', 'get')){
    /*Restrict to logged-in users*/
    if ( checklogin() ) {
        redirect('/DDWT18/week2/myaccount/');
    }

    /*Display feedback*/
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Login' => na('/DDWT18/week2/login', True)
    ]);
    /*Display navigation*/
    $navigation = get_navigation($template, 6);

    /*Page content*/
    $page_title = 'Login';
    $page_subtitle = 'Enter your account details';


    include use_template('login');

}

elseif (new_route('/DDWT18/week2/login/', 'post')){
    /*Regenerate session id*/
    session_regenerate_id();
    /*Login user*/
    $feedback = login_user($db, $_POST);

    /*Redirect to account-page*/
    redirect(sprintf('/DDWT18/week2/myaccount/?error_msg=%s',
        json_encode($feedback)));

}
elseif (new_route('/DDWT18/week2/logout/', 'get')){
    /*Lougout the current user*/
    $feedback = logout_user();

}

/* Single Serie */
elseif (new_route('/DDWT18/week2/serie/', 'get')) {

    /*Get user_id from session*/
    $session_user_id = get_user_id();

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Get series from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = $serie_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview/', False),
        $serie_info['name'] => na('/DDWT18/week2/serie/?serie_id='.$serie_id, True)
    ]);

    /*Get navigation with active page*/
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];
    /*Display buttons if logged in*/
    if($session_user_id == $serie_info['user']){
        $display_buttons = true;
    } else {
        $display_buttons = false;
    }

    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/DDWT18/week2/add/', 'get')) {
    /*Restrict to logged-in users*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Add Series' => na('/DDWT18/week2/new/', True)
    ]);
    /*Display navigation*/
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT18/week2/add/';

    /* Choose Template */
    include use_template('new');
}

/* Add serie POST */
elseif (new_route('/DDWT18/week2/add/', 'post')) {
    /*Restrict to logged-in users*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }
    /*Regenerate session id*/
    session_regenerate_id();

    /* Add serie to database */
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/add/?error_msg=%s',
        json_encode($feedback)));
}

/* Edit serie GET */
elseif (new_route('/DDWT18/week2/edit/', 'get')) {
    /*Restrict to logged-in users*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }


    /* Get serie info from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT18/week2/new/', True)
    ]);

    /*Display navigation*/
    $navigation = get_navigation($template, 0);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT18/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/DDWT18/week2/edit/', 'post')) {
    /*Regenerate session id*/
    session_regenerate_id();

    /*Restrict to logged-in users*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }

    /* Update serie to database */
    $feedback = update_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/serie/?serie_id='.$_POST['serie_id'].'?error_msg=%s',
        json_encode($feedback)));

    /* Choose Template */
    include use_template('serie');
}

/* Remove serie */
elseif (new_route('/DDWT18/week2/remove/', 'post')) {
    /*Restrict to logged-in users*/
    if ( !checklogin() ) {
        redirect('/DDWT18/week2/login/');
    }

    $feedback = remove_serie($db, $_POST['serie_id']);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/overview/?error_msg=%s',
        json_encode($feedback)));

    /* Choose Template */
    include use_template('main');
}

else {
    http_response_code(404);
}