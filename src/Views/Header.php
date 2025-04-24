<?php

use Jkdow\SimplyBook\Support\Flash;
?>
<div class="wrap">
    <?php Flash::render() ?>
    <h1><?= esc_html_e($smbkPageHeader, 'smbk') ?></h1>
</div>
