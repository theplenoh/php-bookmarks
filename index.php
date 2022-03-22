<?php
require_once "common.php";

session_start();

if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
    echo "<script>alert('로그인 되었습니다!');</script>";

if(!isset($_GET['page_num']))
    $page_num = 1;
else
    $page_num = $_GET['page_num'];

$page_size = 10;
$page_scale = 5;

$result = mysqli_query($conn, "SELECT COUNT(*) FROM bookmarks_entries");
$total = mysqli_fetch_array($result)[0];

$page_max = ceil($total / $page_size);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Scibx Bookmarks</title>
    <meta name="viewport" content="width=device-width, intial-scale=1, shrink-to-fit=no">
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" rel="stylesheet">
</head>

<body>
<header>
<?php require_once "nav.php"; ?>
</header>
<div class="container">
    <div class="row">
        <div class="col-xs-12 w-100 p-3">
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

    $query = "SELECT * FROM bookmarks_entries ORDER BY time DESC LIMIT ${offset}, ${page_size}";
    $result = mysqli_query($conn, $query);
?>
<?php
    while($entry = mysqli_fetch_array($result))
    {
        $title = $entry['title'];
        $URL = $entry['URL'];
        $datetime = $entry['time'];
        $tag_string = $entry['tags'];
        $tags = explode(",", $tag_string);
        $note = $entry['note'];
?>
            <section class="card my-2">
                <div class="card-body p-2">
                    <p class="card-title mb-0"><a href="<?php echo $URL; ?>"><?php echo $title; ?></a></p>
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
                        <a class="btn btn-sm m-0 p-1 px-1">Edit</a>
                        <a class="btn btn-sm m-0 p-1 px-1">Delete</a>
                        <a class="btn btn-sm m-0 p-1 px-1">Make Public</a>
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
                <ul class="pagination justify-content-center">
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
        </div>
    </div>
</div>
<script crossorigin="anonymous" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script crossorigin="anonymous" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
