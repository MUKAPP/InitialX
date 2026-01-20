<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

// 加载页面头部
$this->need('header.php'); ?>

    <!-- 404 错误提示区域 -->
    <div class="error-page">
        <h1 class="post-title">404 Not Found</h1>
        <p>你要查看的页面已被转移或删除了</p>
        <a href="<?php $this->options->siteUrl(); ?>">点击这里回首页</a>
    </div>
    </div>

<?php
// 如果不是单栏模式，加载侧边栏
if (!$this->options->OneCOL):
    $this->need('sidebar.php');
endif; ?>

<?php
// 加载页面底部
$this->need('footer.php'); ?>