<div class="navbar-nav" id="sub-menu">
    <?php
    echo $this->tag->linkTo(['user/index', 'All Users', 'class' => 'nav-item nav-link']);
    echo $this->tag->linkTo(['user/add', 'Add New user', 'class' => 'nav-item nav-link']);
    ?>
</div>