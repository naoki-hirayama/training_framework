<!--ページング処理-->
<?php if ($previous_page > 0) : ?>
    <a href="index.php/<?php echo $this->render(array('previouspage' => $previous_page)) ?>">前へ</a>
<?php endif ?>

<?php foreach ($pager->getPageNumbers() as $i) : ?>
    <?php if ($i === $pager->getCurrentPage()) : ?>
        <span>
            <?php echo $i ?>
        </span>
    <?php else : ?>
        <a href="<?php echo $pager->createUri($i) ?>">
            <?php echo $i ?>
        </a>
    <?php endif ?>
<?php endforeach ?>

<?php if ($pager->hasNextPage()) : ?>
    <a href="<?php echo $pager->createUri($pager->getNextPage()) ?>">次へ</a>
<?php endif ?>
<!--ここまで-->