<?php
require_once "common.php";

session_start();

$flag_loggedin = false;
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
{
    $flag_loggedin = true;
}
else if(isset($_COOKIE['rememberme']))
{
    $userID = decrypt_cookie($_COOKIE['rememberme']);

    $query = "SELECT * from bookmarks_auth WHERE userID='{$userID}'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    $count = mysqli_num_rows($result);

    if($count == 1)
    {
        $_SESSION['loggedin'] = true;
        $flag_loggedin = true;
    }
}

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true)
{
    echo<<<EOT
<script>
alert("You are not logged in.\\nRedirected to the login page.");
location.href="login.php";
</script>
EOT;
exit;
}

if(!isset($_GET['page_num']))
    $page_num = 1;
else
    $page_num = $_GET['page_num'];

$page_size = 20;
$page_scale = 5;

$result = mysqli_query($conn, "SELECT COUNT(*) FROM bookmarks_entries WHERE publicity = 'private'");
$total = mysqli_fetch_array($result)[0];

$page_max = ceil($total / $page_size);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<?php require_once "head.inc.php"; ?>
</head>

<body>
<header>
<?php require_once "nav.inc.php"; ?>
</header>
<div class="container">
    <div class="row">
        <div class="col-xs-12 w-100 p-3">
            <form action="insert_instant.php" method="post">
            <section class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Add a URL" name="URL">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Add</button>
                </div>
            </section>
            </form>
            <nav>
                <ul class="nav nav-pills">
<?php
if($flag_loggedin)
{
?>
                    <li class="nav-item">
                        <a class="nav-link active" href="list.php">Private</a>
                    </li>
<?php
}
?>
                    <li class="nav-item">
                        <a class="nav-link" href="list_public.php">Public</a>
                    </li>
                </ul>
            </nav>
            <div class="row px-3">
            <main class="col-lg-9 px-0">
<?php
if($total == 0)
{
?>
                <section class="card my-2">
                    <div class="card-body">There are no entries.</div>
                </section>
<?php
}
else
{
    $offset = ($page_num - 1) * $page_size;

    $block = floor(($page_num - 1) / $page_scale);

    $query = "SELECT * FROM bookmarks_entries WHERE publicity = 'private' ORDER BY pinned DESC, time DESC LIMIT {$offset}, {$page_size}";
    $result = mysqli_query($conn, $query);
?>
<?php
    while($entry = mysqli_fetch_array($result))
    {
        $entryID = $entry['entryID'];
        $title = $entry['title'];
        $URL = $entry['URL'];
        $datetime = $entry['time'];
        $tag_string = $entry['tags'];
        $tags = explode(",", (string)$tag_string);
        $note = $entry['note'];
        $pinned = (int)$entry['pinned'];
?>
                <section class="card my-2">
                    <div class="card-body p-2">
                    <p class="card-title mb-0"><?php if($pinned) echo "<img alt=\"pinned\" class=\"icon\" src=\"images/red-pin-256.png\">"; ?><a href="<?php echo $URL; ?>"><?php echo $title; ?></a> <?php if($pinned) echo "<small class=\"small\">(Pinned)</small>"; ?></p>
                        <p class="small mb-1"><?php echo $URL; ?></p>
                        <p class="small my-0"><?php echo $note; ?></p>
                        <p class="my-0">
<?php
        foreach($tags as $tag)
        {
?>
                            <span class="badge badge-info"><?php echo $tag; ?></span>
<?php
        }
?>
                        </p>
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-sm m-0 p-1 px-1" href="edit_entry.php?entryID=<?=$entryID?>">Edit</a>
                            <a class="btn btn-sm m-0 p-1 px-1" href="del_entry.php?entryID=<?=$entryID?>">Delete</a>
                            <a class="btn btn-sm m-0 p-1 px-1" href="make_public.php?entryID=<?=$entryID?>">Make Public</a>
<?php
        if(!$pinned)
        {
?>
                            <a class="btn btn-sm m-0 p-1 px-1" href="pin_entry.php?entryID=<?php echo $entry['entryID']; ?>">Pin</a>
<?php
        }
        else
        {
?>
                            <a class="btn btn-sm m-0 p-1 px-1" href="unpin_entry.php?entryID=<?php echo $entry['entryID']; ?>">Unpin</a>
<?php
        }
?>
                        </div>
                    </div>
                    <div class="card-footer p-1 px-2 small"><?php echo $datetime; ?></div>
                </section>
<?php
    }
}
?>
<?php
if($total > 0)
{
?>
                <section>
                    <ul class="pagination pagination-sm justify-content-center">
                        <li class="page-item">
<?php $prev_block = ($block - 1) * $page_scale + 1; ?>
                            <a class="page-link" href="<?php if($block > 0) { echo "?page_num={$prev_block}"; } else { echo "javascript:;"; } ?>">&laquo;</a>
                        </li>
                        <li class="page-item">
<?php $prev_page = $page_num - 1; ?>
                            <a class="page-link" href="<?php if($page_max > 1 && $offset != 0 && $page_num && $page_num > 1) { echo "?page_num={$prev_page}"; } else { echo "javascript:;"; } ?>">&lsaquo;</a>
                        </li>
<?php
    $start_page = $block * $page_scale + 1;
    for($i=1; $i<=$page_scale && $start_page<=$page_max; $i++, $start_page++)
    {
?>
                        <li class="page-item<?php if($start_page == $page_num) { echo " active"; } ?>">
                            <a class="page-link" href="<?php if($start_page == $page_num) { echo "javascript:;"; } else { echo "?page_num={$start_page}"; }; ?>"><?php echo "{$start_page}"; ?></a>
                        </li>
<?php
    }
?>
                        <li class="page-item">
<?php $next_page = $page_num + 1; ?>
                            <a class="page-link" href="<?php if($page_max > $page_num) { echo "?page_num={$next_page}"; } else { echo "javascript:;"; } ?>">&rsaquo;</a>
                        </li>
                        <li class="page-item">
<?php $next_block = ($block + 1)*$page_scale + 1; ?>
                            <a class="page-link" href="<?php if($page_max > ($block + 1)*$page_scale) { echo "?page_num={$next_block}"; } else { echo "javascript:;"; } ?>">&raquo;</a>
                        </li>
                    </ul>
                </section>
<?php
}
?>
            </main>
            <aside class="col-lg-3 px-0 pl-lg-4">
                <section class="card mt-2 mb-3">
                    <p class="card-header p-2 pl-3">Admin</p>
                    <div class="card-body p-2">
                        <ul class="mb-0 pl-4">
                            <li><a href="migration.php">Migration</a></li>
                        </ul>
                    </div>
                </section>
            </aside>
            </div>
        </div>
    </div>
</div>
<script crossorigin="anonymous" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script crossorigin="anonymous" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
