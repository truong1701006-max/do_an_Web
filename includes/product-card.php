<article class="product-card">
    <a class="thumb" href="<?= app_url('product-detail.php?id=' . (int) $product['product_id']) ?>">
        <img src="<?= app_url($product['image'] ?: 'assets/images/iphone-dark.svg') ?>" alt="<?= e($product['product_name']) ?>">
    </a>
    <div class="product-card-top">
        <span class="badge"><?= e(product_badge($product)) ?></span>
        <span class="stock-pill"><?= (int) $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng' ?></span>
    </div>
    <h3><a href="<?= app_url('product-detail.php?id=' . (int) $product['product_id']) ?>"><?= e($product['product_name']) ?></a></h3>
    <div class="meta"><?= e($product['category_name']) ?> · <?= (int) $product['stock'] ?> máy sẵn kho</div>
    <div class="price">
        <?= format_price($product['display_price']) ?>
        <?php if (!empty($product['sale_price'])): ?>
            <span class="old-price"><?= format_price($product['price']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <span>Trả góp/COD</span>
        <a class="btn light" href="<?= app_url('product-detail.php?id=' . (int) $product['product_id']) ?>">Chi tiết</a>
    </div>
</article>
