<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

render_header('About');
?>
<section class="info-page-shell">
    <article class="info-card">
        <p class="eyebrow">About QuickBite</p>
        <h1>Built as a modern starter for food ordering and delivery workflows.</h1>
        <p>
            QuickBite combines a clean customer storefront with a lightweight admin dashboard so you can demo or extend a complete food delivery flow using plain PHP and MySQL.
        </p>
        <p>
            The project includes customer registration, login, menu browsing, add-to-cart, checkout, and order status management in a structure that is easy to customize for college projects or small business demos.
        </p>
    </article>
</section>
<?php render_footer(); ?>
