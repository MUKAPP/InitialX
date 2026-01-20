<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

// 加载页面头部
$this->need('header.php');

// 显示面包屑导航
Breadcrumbs($this); ?>

    <article class="post">
        <!-- 页面标题 -->
        <h1 class="post-title"><?php $this->title() ?></h1>

        <div class="post-content">
            <!-- 显示页面自定义内容 -->
            <?php $this->content(); ?>

            <!-- 友情链接列表 -->
            <ul class="links">
                <?php
                // 如果启用链接图标，添加图标加载失败时的备用处理
                if ($this->options->InsideLinksIcon): ?>
                    <script>
                        // 图标加载失败时显示星号作为替代
                        function erroricon(obj) {
                            var a = obj.parentNode,
                                i = document.createElement("i");
                            i.appendChild(document.createTextNode("★"));
                            a.removeChild(obj);
                            a.insertBefore(i, a.childNodes[0])
                        }
                    </script>
                <?php endif; ?>

                <?php
                // 调用 Links 函数显示链接列表
                // 参数1: 链接分类筛选
                // 参数2: 是否显示图标 (1=显示, 0=不显示)
                Links($this->options->InsideLinksSort, $this->options->InsideLinksIcon ? 1 : 0);
                ?>
            </ul>
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