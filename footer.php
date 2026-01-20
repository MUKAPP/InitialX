<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

use Typecho\Widget;
?>

    </div>
    </div>
    <footer id="footer">
        <div class="container">
            <div id="thirty">
                <?php if (!empty($this->options->ShowLinks) && in_array('sidebar', $this->options->ShowLinks)): ?>
                    <section class="widget">
                        <h3 class="widget-title">链接</h3>
                        <ul class="widget-tile">
                            <?php Links($this->options->IndexLinksSort); ?>
                            <?php $linksPage = FindContents('page-links.php', 'order', 'a', 1); ?>
                            <?php if ($linksPage): ?>
                                <li class="more"><a href="<?php echo $linksPage[0]['permalink']; ?>">查看更多...</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if (!empty($this->options->sidebarBlock) && in_array('ShowOther', $this->options->sidebarBlock)): ?>
                    <section class="widget">
                        <h3 class="widget-title">其它</h3>
                        <ul class="widget-list">
                            <li><a href="<?php $this->options->feedUrl(); ?>" target="_blank">文章 RSS</a></li>
                            <li><a href="<?php $this->options->commentsFeedUrl(); ?>" target="_blank">评论 RSS</a></li>
                            <?php if ($this->user->hasLogin()): ?>
                                <li><a href="<?php $this->options->adminUrl(); ?>" target="_blank">进入后台
                                        (<?php $this->user->screenName(); ?>)</a></li>
                                <li>
                                    <a href="<?php $this->options->logoutUrl(); ?>" <?php if ($this->options->PjaxOption): ?>
                                        no-pjax<?php endif; ?>>退出</a></li>
                            <?php endif; ?>
                        </ul>
                    </section>
                <?php endif; ?>
            </div>

            <?php if (!empty($this->options->ShowLinks) && in_array('footer', $this->options->ShowLinks)): ?>
                <ul class="links">
                    <?php Links($this->options->IndexLinksSort); ?>
                    <?php $linksPageFooter = FindContents('page-links.php', 'order', 'a', 1); ?>
                    <?php if ($linksPageFooter): ?>
                        <li><a href="<?php echo $linksPageFooter[0]['permalink']; ?>">更多...</a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
            <?php if ($this->options->FooterCustom): ?>
                <?php $this->options->FooterCustom(); ?>
            <?php endif; ?>
            <?php
                $startYear = $this->options->siteStartYear;
                $currentYear = date('Y');
                $yearDisplay = ($startYear && $startYear != $currentYear) ? $startYear . ' - ' . $currentYear : $currentYear;
            ?>
            <p>&copy; <?php echo $yearDisplay; ?> <a
                        href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>. Powered by <a
                        href="https://www.typecho.org" target="_blank">Typecho</a> &amp; <a
                        href="https://github.com/MUKAPP/InitialX" target="_blank">InitialX</a>.</p>
            <?php if ($this->options->ICPbeian): ?>
                <p><a href="https://beian.miit.gov.cn" class="icpnum" target="_blank"
                      rel="noreferrer"><?php $this->options->ICPbeian(); ?></a></p>
            <?php endif;
            if ($this->options->AjaxLoad): ?>
                <input id="token" type="hidden"
                       value="<?php echo Widget::widget('Widget_Security')->getTokenUrl('Token'); ?>"
                       readonly="readonly"/>
            <?php endif; ?>
        </div>
    </footer>
<?php if ($this->options->scrollTop || ($this->options->MusicSet && $this->options->MusicUrl)): ?>
    <div id="cornertool">
        <ul>
            <?php if ($this->options->scrollTop): ?>
                <li id="top" class="hidden"></li>
            <?php endif; ?>
            <?php if ($this->options->MusicSet && $this->options->MusicUrl): ?>
                <li id="music" class="hidden">
                    <span><i></i></span>
                    <audio id="audio" data-src="<?php Playlist() ?>" <?php if ($this->options->MusicVol): ?>
                        data-vol="<?php $this->options->MusicVol(); ?>" <?php endif; ?> preload="none"></audio>
                </li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif;
if ($this->options->Highlight): ?>
    <link rel="stylesheet"
          href="https://<?php if ($this->options->cjCDN == 'cf'): ?>cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/github-dark.min.css<?php elseif ($this->options->cjCDN == 'sc'): ?>cdn.staticfile.org/highlight.js/11.10.0/styles/github-dark.min.css<?php else: ?>cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.10.0/build/styles/github-dark.min.css<?php endif; ?>">
    <script
            src="//<?php if ($this->options->cjCDN == 'cf'): ?>cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/highlight.min.js<?php elseif ($this->options->cjCDN == 'sc'): ?>cdn.staticfile.org/highlight.js/11.10.0/highlight.min.js<?php else: ?>cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.10.0/build/highlight.min.js<?php endif; ?>"></script>
<?php endif; ?>
    <script src="<?php cjUrl('dist/main.min.js') ?>"></script>

    <!-- FancyBox -->
    <script src="<?php cjUrl('libs/fancybox/fancybox.umd.js') ?>"></script>
    <script src="<?php cjUrl('libs/fancybox/l10n/zh_CN.umd.js') ?>"></script>
    <script>
        Fancybox.bind("[data-fancybox]", {
            l10n: Fancybox.l10n.zh_CN,
        });
    </script>

<?php $this->footer(); ?>
<?php if ($this->options->CustomContent):
    $this->options->CustomContent(); ?>

<?php endif; ?>

    <script type="text/javascript">
        <?php if ($this->options->HetiOption): ?>
        // Heti 中文排版增强
        const heti = new Heti('.heti');
        heti.autoSpacing();
        <?php endif; ?>
        // 代码块复制
        addCopyButtonsToCodeblocks();

        // 监听 pjax/ajax 完成事件（仅在 jQuery 存在时）
        if (typeof $ !== 'undefined') {
            $(document).on('pjax:complete', function () {
                <?php if ($this->options->HetiOption): ?>
                heti.autoSpacing();
                <?php endif; ?>
                addCopyButtonsToCodeblocks();
            });
            <?php if ($this->options->HetiOption): ?>
            $(document).ajaxComplete(function () {
                heti.autoSpacing();
            });
            <?php endif; ?>
        }
    </script>

    </body>

    </html>
<?php if ($this->options->compressHtml):
    $html_source = ob_get_contents();
    ob_clean();
    print compressHtml($html_source);
    ob_end_flush();
endif; ?>