<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>

<!-- 侧边栏容器 -->
<div id="secondary" <?php if ($this->options->SidebarFixed): ?> sidebar-fixed<?php endif; ?>>

    <?php // ===== 最新轻语 ===== 
    ?>
    <?php if (!empty($this->options->ShowWhisper) && in_array('sidebar', $this->options->ShowWhisper)): ?>
        <section class="widget">
            <?php Whisper(1); ?>
        </section>
    <?php endif; ?>

    <?php // ===== 热门文章（按评论数排序） ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowHotPosts', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">热门文章</h3>
            <ul class="widget-list">
                <?php Contents_Post_Initial($this->options->postsListSize, 'commentsNum'); ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 最新文章 ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentPosts', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">最新文章</h3>
            <ul class="widget-list">
                <?php Contents_Post_Initial($this->options->postsListSize); ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 最近回复 ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentComments', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">最近回复</h3>
            <ul class="widget-list">
                <?php
                // 获取最近评论，可选择是否忽略作者自己的回复
                $this->widget('Initial_Widget_Comments_Recent', in_array('IgnoreAuthor', $this->options->sidebarBlock) ? 'ignoreAuthor=1' : '')->to($comments);
                ?>
                <?php if ($comments->have()): ?>
                    <?php while ($comments->next()): ?>
                        <li>
                            <?php
                            // 判断评论对应的文章是否可访问
                            $contentInfo = FindContent($comments->cid);
                            $isHidden = ($contentInfo['hidden'] && $this->options->PjaxOption);
                            $isPrivate = ($contentInfo['status'] != 'publish' && $contentInfo['template'] != 'page-whisper.php' && $this->authorId !== $this->user->uid && !$this->user->pass('editor', true));
                            ?>
                            <a <?php echo ($isHidden || $isPrivate) ? '' : 'href="' . $comments->permalink . '" '; ?>title="来自: <?php echo $isPrivate ? '此内容被作者隐藏' : $comments->title ?>"><?php $comments->author(false); ?></a>:
                            <?php $comments->excerpt(35, '...'); ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>暂无回复</li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 分类列表 ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowCategory', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">分类</h3>
            <ul class="widget-tile">
                <?php $this->widget('Widget_Metas_Category_List')
                        ->parse('<li><a href="{permalink}">{name}</a></li>'); ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 标签云 ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowTag', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">标签</h3>
            <ul class="widget-tile">
                <?php $this->widget('Widget_Metas_Tag_Cloud', 'ignoreZeroCount=1&limit=30')->to($tags); ?>
                <?php if ($tags->have()): ?>
                    <?php while ($tags->next()): ?>
                        <li><a href="<?php $tags->permalink(); ?>"><?php $tags->name(); ?></a></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>暂无标签</li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 归档（按月份） ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowArchive', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">归档</h3>
            <ul class="widget-list">
                <?php $this->widget('Widget_Contents_Post_Date', 'type=month&format=Y 年 n 月')
                        ->parse('<li><a href="{permalink}">{date}</a></li>'); ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 友情链接 ===== 
    ?>
    <?php if (!empty($this->options->ShowLinks) && in_array('sidebar', $this->options->ShowLinks)): ?>
        <section class="widget">
            <h3 class="widget-title">链接</h3>
            <ul class="widget-tile">
                <?php Links($this->options->IndexLinksSort); ?>
                <?php
                // 如果存在链接页面，显示"查看更多"
                $linksPageSidebar = FindContents('page-links.php', 'order', 'a', 1);
                ?>
                <?php if ($linksPageSidebar): ?>
                    <li class="more"><a href="<?php echo $linksPageSidebar[0]['permalink']; ?>">查看更多...</a></li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php // ===== 其它杂项 ===== 
    ?>
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowOther', $this->options->sidebarBlock)): ?>
        <section class="widget">
            <h3 class="widget-title">其它</h3>
            <ul class="widget-list">
                <li><a href="<?php $this->options->feedUrl(); ?>" target="_blank">文章 RSS</a></li>
                <li><a href="<?php $this->options->commentsFeedUrl(); ?>" target="_blank">评论 RSS</a></li>
                <?php // 已登录用户显示后台入口
                if ($this->user->hasLogin()): ?>
                    <li><a href="<?php $this->options->adminUrl(); ?>" target="_blank">进入后台
                            (<?php $this->user->screenName(); ?>)</a></li>
                    <li>
                        <a href="<?php $this->options->logoutUrl(); ?>" <?php if ($this->options->PjaxOption): ?> no-pjax<?php endif; ?>>退出</a>
                    </li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

</div>