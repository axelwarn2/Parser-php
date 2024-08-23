<?php

include "framework/framework.php";

use Framework\CDatabase;
use Framework\Paginator;

$db = CDatabase::getInstanse()->connection;

$totalPartners = (int) $db->query("SELECT COUNT(*) FROM partners")->fetchColumn();
$itemsLimit = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$paginator = new Paginator($totalPartners, $itemsLimit, $currentPage);

$totalPages = $paginator->getTotalPages();
$range = 4;

$stmt = $db->prepare("SELECT id, name, details_url, website FROM partners LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $paginator->getLimit(), PDO::PARAM_INT);
$stmt->bindValue(':offset', $paginator->getOffset(), PDO::PARAM_INT);
$stmt->execute();
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h1 class="title">Partners List</h1>

    <?php foreach ($partners as $partner): ?>
        <div class="main">
            <div class="partner">
                <p><b>ID:</b> <?php echo $partner['id']; ?></p>
                <p><b>Name:</b> <?php echo $partner['name']; ?></p>
                <span><b>Details:</b> <a href="<?php echo $partner['details_url']; ?>"><?php echo $partner['details_url']; ?></a></span>
                <span><b>Website:</b> <a href="<?php echo $partner['website']; ?>"><?php echo $partner['website']; ?></a></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="paginator">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?=$paginator->previousPage()?>">Предыдущая</a>
        <?php endif; ?>

        <span>Страница <?=$currentPage?> из <?=$paginator->getTotalPages()?></span>

        <?php if ($currentPage < 192): ?>
            <a href="?page=<?=$paginator->nextPage()?>">Следующая</a>
        <?php endif; ?>
    </div>
    <div class="pages">
        <?php if ($currentPage > 1 + $range): ?>
            <a href="?page=1">1</a>
            <?php if ($currentPage > 2 + $range): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++): ?>
            <?php if($currentPage === $i): ?>
                <b><?=$currentPage?></b>
            <?php else: ?>
                <a href="?page=<?=$i?>"><?=$i?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages - $range): ?>
            <?php if ($currentPage < $totalPages - $range - 1): ?>
                <span>...</span>
            <?php endif; ?>
            <a href="?page=<?=$totalPages?>"><?=$totalPages?></a>
        <?php endif; ?>
    </div>
</body>
</html>
