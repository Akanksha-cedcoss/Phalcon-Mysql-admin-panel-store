<div class="navbar-nav" id="sub-menu">
    <?php
    echo $this->tag->linkTo(['order/index', 'All Orders', 'class' => 'nav-item nav-link']);
    echo $this->tag->linkTo(['order/add', 'Add New Order', 'class' => 'nav-item nav-link']);
    ?>
</div>