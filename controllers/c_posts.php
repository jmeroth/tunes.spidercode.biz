<?php
class posts_controller extends base_controller {

    public function __construct() {
        parent::__construct();
		#echo "posts_controller construct called<br><br>";
        # Make sure user is logged in if they want to use anything in this controller
        if(!$this->user) {
            die("Members only. <a href='/users/login'>Login</a>");
        }
    }

    public function add() {

        # Setup view
        $this->template->content = View::instance('v_posts_add');
        $this->template->title   = "New Post";

        # Render template
        echo $this->template;

    }

    public function edit() {

        # Setup view
        $this->template->content = View::instance('v_posts_edit');
        $this->template->title   = "Edit Post";

        # Render template
        echo $this->template;

    }

    public function p_add() {

        # Associate this post with this user
        $_POST['user_id']  = $this->user->user_id;

        # Unix timestamp of when this post was created / modified
        $_POST['created']  = Time::now();
        $_POST['modified'] = Time::now();

        # Insert
        # Note didn't have to sanitize $_POST data because the insert method does it for us
        DB::instance(DB_NAME)->insert('posts', $_POST);  //insert('table-name', array from forms post method)

		echo "<br/>***<br/>";
		# current post
        print_r($_POST);
		echo "<br/>***<br/>";

        # Quick and dirty feedback
        echo "Your post has been added. <a href='/posts/add'>Add another</a>";

    }

    

	public function index() {

		#echo "c_posts index method called<br><br>";

	    # Set up the View
	    $this->template->content = View::instance('v_posts_index');
	    $this->template->title   = "All Posts";

	    # Build the query
	    $q = 'SELECT 
				posts.post_id,
				posts.venue,
	            posts.content,
	            posts.created,
				posts.modified,
	            posts.user_id AS post_user_id,
	            users_users.user_id AS follower_id,
	            users.first_name,
	            users.last_name
	        FROM posts

	        INNER JOIN users_users 
	            ON posts.user_id = users_users.user_id_followed

			INNER JOIN users_venues 
	            ON posts.venue = users_venues.venue_followed

	        INNER JOIN users 
	            ON posts.user_id = users.user_id

	        WHERE users_users.user_id = '.$this->user->user_id.
				' AND users_venues.user_id = '.$this->user->user_id.
			' ORDER BY posts.user_id';

	    # Run the query
	    $posts = DB::instance(DB_NAME)->select_rows($q);

	    # Pass data to the View
	    $this->template->content->posts = $posts;

	    # Render the View
	    echo $this->template;

	}


	public function p_index($post_id) {

		# Unix timestamp of when this post was created / modified
		$_POST['modified'] = Time::now();

		# Modify post
		DB::instance(DB_NAME)->update('posts', $_POST, "WHERE post_id ='".$post_id."'");
		# Quick and dirty feedback
        echo "Your post has been modified. <a href='/posts'>return to posts</a>";
    }

	public function users() {

	    # Set up the View
	    $this->template->content = View::instance('v_posts_users');
	    $this->template->title   = "Users";

	    # Build the query to get all the users
	    $q = "SELECT *
	        FROM users";

	    # Execute the query to get all the users. 
	    # Store the result array in the variable $users
	    $users = DB::instance(DB_NAME)->select_rows($q);

	    # Build the query to figure out what connections does this user already have? 
	    # I.e. who are they following
	    $q = "SELECT * 
	        FROM users_users
	        WHERE user_id = ".$this->user->user_id;

	    # Execute this query with the select_array method
	    # select_array will return our results in an array and use the "users_id_followed" field as the index.
	    # This will come in handy when we get to the view
	    # Store our results (an array) in the variable $connections
	    $connections = DB::instance(DB_NAME)->select_array($q, 'user_id_followed');

	    # Pass data (users and connections) to the view
	    $this->template->content->users       = $users;
	    $this->template->content->connections = $connections;

	    # Render the view
	    echo $this->template;
	}

	public function venues() {

	    # Set up the View
	    $this->template->content = View::instance('v_posts_venues');
	    $this->template->title   = "Venues";

	    # Build the query to get all the Venues
	    $q = "SELECT venue

	        FROM posts GROUP BY venue";

	    # Execute the query to get all the venues. 
	    # Store the result array in the variable $venues
	    $venues = DB::instance(DB_NAME)->select_rows($q);

	    # Build the query to figure out what connections does this user already have? 
	    # I.e. who are they following
	    $q = "SELECT * 
	        FROM users_venues
	        WHERE user_id = ".$this->user->user_id;

	    # Execute this query with the select_array method
	    # select_array will return our results in an array and use the "venue_id_followed" field as the index.
	    # This will come in handy when we get to the view
	    # Store our results (an array) in the variable $connections
	    $connections = DB::instance(DB_NAME)->select_array($q, 'venue_followed');

	    # Pass data (venues and connections) to the view
	    $this->template->content->venues      = $venues;
	    $this->template->content->connections = $connections;

	    # Render the view
	    echo $this->template;
	}

	public function follow($user_id_followed) {

	    # Prepare the data array to be inserted
	    $data = Array(
	        "created" => Time::now(),
	        "user_id" => $this->user->user_id,
	        "user_id_followed" => $user_id_followed
	        );

	    # Do the insert
	    DB::instance(DB_NAME)->insert('users_users', $data);

	    # Send them back
	    Router::redirect("/posts/users");

	}


	public function unfollow($user_id_followed) {

	    # Delete this connection
	    $where_condition = 'WHERE user_id = '.$this->user->user_id.' AND user_id_followed = '.$user_id_followed;
	    DB::instance(DB_NAME)->delete('users_users', $where_condition);

	    # Send them back
	    Router::redirect("/posts/users");

	}

	public function follow_venue($venue_followed) {

	    # Prepare the data array to be inserted
	    $data = Array(
	        "created" => Time::now(),
	        "user_id" => $this->user->user_id,
	        "venue_followed" => $venue_followed
	        );

	    # Do the insert
	    DB::instance(DB_NAME)->insert('users_venues', $data);

	    # Send them back
	    Router::redirect("/posts/venues");

	}


	public function unfollow_venue($venue_followed) {

	    # Delete this connection
	    $where_condition = 'WHERE user_id = '.$this->user->user_id." AND venue_followed = '".$venue_followed."'";
	    DB::instance(DB_NAME)->delete('users_venues', $where_condition);

	    # Send them back
	    Router::redirect("/posts/venues");

	}
}
?>
