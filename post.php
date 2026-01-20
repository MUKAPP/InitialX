<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

// 加载页面头部
$this->need('header.php');

// 显示面包屑导航（如果启用）
if (!empty($this->options->Breadcrumbs) && in_array('Postshow', $this->options->Breadcrumbs)): ?>
    <div class="breadcrumbs">
        <a href="<?php $this->options->siteUrl(); ?>">首页</a> &raquo;
        <?php $this->category(); ?> &raquo;
        <?php // 显示文章标题或"正文"
        echo !empty($this->options->Breadcrumbs) && in_array('Text', $this->options->Breadcrumbs) ? '正文' : $this->title; ?>
    </div>
<?php endif; ?>

    <article class="post<?php if ($this->options->PjaxOption && $this->hidden): ?> protected<?php endif; ?> heti">
        <!-- 文章标题 -->
        <h1 class="post-title"><?php $this->title() ?></h1>

        <!-- 文章元信息 -->
        <ul class="post-meta">
            <li><?php $this->date(); ?></li>
            <li><?php $this->category(','); ?></li>
            <li><a href="<?php $this->permalink() ?>#comments"><?php $this->commentsNum('暂无评论', '%d 条评论'); ?></a>
            </li>
            <li><?php Postviews($this); ?></li>
        </ul>

        <!-- 文章正文内容 -->
        <div class="post-content">
            <?php
            /**
             * 为文章中的图片添加 FancyBox 灯箱支持
             * 自动为所有 img 标签包裹 a 标签，使图片可点击放大
             */
            $content = preg_replace_callback(
                    '/<img\s+([^>]*?)>/i',
                    function ($matches) {
                        $attrs = $matches[1];

                        // 提取 src 属性
                        if (preg_match('/src=["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
                            $src = $srcMatch[1];
                        } else {
                            return $matches[0]; // 没有 src，返回原始内容
                        }

                        // 提取 alt 属性（可选）
                        $alt = '';
                        if (preg_match('/alt=["\']([^"\']*)/i', $attrs, $altMatch)) {
                            $alt = $altMatch[1];
                        }

                        // 生成带 FancyBox 的图片 HTML
                        if (empty($alt)) {
                            return '<a href="' . $src . '" data-fancybox="gallery"><img src="' . $src . '" loading="lazy" /></a>';
                        } else {
                            return '<a href="' . $src . '" data-fancybox="gallery" data-caption="' . htmlspecialchars($alt) . '"><img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" loading="lazy" /></a>';
                        }
                    },
                    $this->content
            );
            echo $content;
            ?>
        </div>

        <?php // 打赏功能区域
        if ($this->options->WeChat || $this->options->Alipay): ?>
            <p class="rewards">打赏:
                <?php if ($this->options->WeChat): ?>
                    <a><img src="<?php $this->options->WeChat(); ?>" alt="微信收款二维码"/>微信</a>
                <?php endif; ?>
                <?php if ($this->options->WeChat && $this->options->Alipay): ?>,<?php endif; ?>
                <?php if ($this->options->Alipay): ?>
                    <a><img src="<?php $this->options->Alipay(); ?>" alt="支付宝收款二维码"/>支付宝</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <!-- 文章标签 -->
        <p class="tags">标签: <?php $this->tags(', ', true, 'none'); ?></p>

        <?php // 版权许可信息（设置为"0"时隐藏）
        ?>
        <?php if ($this->options->LicenseInfo !== '0'): ?>
            <p class="license">
                <?php echo $this->options->LicenseInfo ? $this->options->LicenseInfo : '本作品采用 <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="license nofollow">知识共享署名-相同方式共享 4.0 国际许可协议</a> 进行许可。' ?>
            </p>
        <?php endif; ?>
    </article>

<?php // 加载评论模板 
?>
<?php $this->need('comments.php'); ?>

    <!-- 上一篇/下一篇导航 -->
    <ul class="post-near">
        <li>上一篇: <?php $this->thePrev('%s', '没有了'); ?></li>
        <li>下一篇: <?php $this->theNext('%s', '没有了'); ?></li>
    </ul>
    </div>

<?php
// 如果不是单栏模式，加载侧边栏
if (!$this->options->OneCOL):
    $this->need('sidebar.php');
endif; ?>

<?php // 加载页面底部
$this->need('footer.php'); ?>