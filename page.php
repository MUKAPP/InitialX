<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

// 加载页面头部
$this->need('header.php');

// 显示面包屑导航
Breadcrumbs($this); ?>

    <article class="post">
        <!-- 页面标题 -->
        <h1 class="post-title"><?php $this->title() ?></h1>

        <!-- 页面内容 -->
        <div class="post-content">
            <?php $this->content(); ?>
        </div>
    </article>

<?php // 加载评论模板
$this->need('comments.php'); ?>
    </div>

<?php
// 如果不是单栏模式，加载侧边栏
if (!$this->options->OneCOL):
    $this->need('sidebar.php');
endif; ?>

<?php // 加载页面底部
$this->need('footer.php'); ?>