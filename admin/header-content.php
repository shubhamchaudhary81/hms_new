<div class="content-header">
    <div>
        <h1 class="content-title"><?= $headerTitle ?? 'Default Title' ?></h1>
        <p class="content-subtitle"><?= $headerSubtitle ?? '' ?></p>
    </div>

    <?php if (!empty($showButton)) : ?>
        <a href="<?= $buttonLink ?? '#' ?>" class="btn-primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            <?= $buttonText ?? 'Add New' ?>
        </a>
    <?php endif; ?>
</div>