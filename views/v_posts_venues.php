<?php foreach($users as $user): ?>

    <!-- Print this user's name -->
    <?=$user['venue']?>

    <!-- If there exists a connection with this user, show a unfollow link -->

    <?php if(isset($connections[$user['venue']])): ?>
        <a href='/posts/unfollow/<?=$user['user_id']?>'>Unfollow</a>

    <!-- Otherwise, show the follow link -->
    <?php else: ?>
        <a href='/posts/follow/<?=$user['user_id']?>'>Follow</a>
    <?php endif; ?>

    <br><br>

<?php endforeach; ?>

