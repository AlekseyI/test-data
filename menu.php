<li>
    <a href="/accordeon_index.php?group=<?= $value['id'] ?>"><?= $value['name'] ?></a>  <?= $value['count'] ?>
        <?php if (isset($value['childs'])): ?>
        <ul>
            <?= generateMenu($value['childs']) ?>
        </ul>
        <?php endif; ?>
</li>