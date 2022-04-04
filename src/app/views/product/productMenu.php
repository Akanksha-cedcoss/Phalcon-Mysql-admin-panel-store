<div class="navbar-nav" id="sub-menu">
    <?php
    echo $this->tag->linkTo(['product/index', 'All Products', 'class' => 'nav-item nav-link']);
    echo $this->tag->linkTo(['product/add', 'Add New Product', 'class' => 'nav-item nav-link']);
    ?>
</div>