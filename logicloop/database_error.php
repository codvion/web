<?php 
require 'config/database.php';
$pagetitle = 'DataBase Error';
include 'components/header_page.php';
?>

<style>
    .database-error-main {
        width: 100%;
        padding: 80px 5px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 50px;
        background: url('../img/hero-image.webp') no-repeat center center;
        background-size: cover;
    }

    .database-error-main h1{
        color: var(--text);
    }

    .database-error-main h1 span{
        color: var(--active);
    }
</style>

<div class="database-error-main">
    <h1>
        ERROR: DataBase Connection Unsuccessfull. <span>Try Again Later!</span>
    </h1>
</div>
<script src="scripts/script.js"></script>
<?php 
include 'components/footer.php';
?>