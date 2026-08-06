<?php

/**
 * 基于 Initial 主题二次开发的主题
 *
 * @package InitialX
 * @author MUKAPP
 * @version 1.1.0
 * @link https://github.com/MUKAPP/InitialX
 */

use Typecho\Widget;

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;
$this->need('header.php');
if ($this->_currentPage == 1 && !empty($this->options->ShowWhisper) && in_array('index', $this->options->ShowWhisper)): ?>
    <article class="post whisper">
        <?php Whisper(); ?>
    </article>
<?php endif; ?>
<?php $listCustomCounter = 0; ?>
<?php while ($this->next()): ?>
<?php $listCustomCounter++; ?>
    <article
            class="post list-post<?php if ($this->options->PjaxOption && $this->hidden): ?> protected<?php endif; ?> heti">
        <a href="<?php $this->permalink() ?>" title="<?php $this->title() ?>">
            <h2 class="post-title"><?php $this->title() ?></h2>
            <ul class="post-meta">
                <li><?php $this->date(); ?></li>
                <li><?php $this->category(',', false); ?></li>
                <li><?php $this->commentsNum('暂无评论', '%d 条评论'); ?></li>
                <li><?php Postviews($this); ?></li>
            </ul>
            <div class="post-content">
                <?php if ($this->options->PjaxOption && $this->hidden): ?>
                    <form <?php if (!$this->options->AjaxLoad): ?>action="<?php echo Widget::widget('Widget_Security')->getTokenUrl($this->permalink); ?>"
                          <?php endif; ?>method="post">
                        <p class="word">请输入密码访问</p>
                        <p>
                            <input type="password" class="text" name="protectPassword"/>
                            <input type="hidden" name="protectCID" value="<?php echo $this->cid; ?>"/>
                            <input type="submit" class="submit" value="提交"/>
                        </p>
                    </form>
                <?php else: ?>
                    <?php if (postThumb($this)): ?>
                        <p class="thumb"><?php echo postThumb($this); ?></p>
                    <?php endif; ?>
                    <p><?php $this->excerpt(200, ''); ?></p>
                <?php endif; ?>
            </div>
        </a>
    </article>
<?php // 文章列表自定义内容（如信息流广告）
if ($this->options->ListCustom && $this->options->ListCustomInterval):
    $listInterval = intval($this->options->ListCustomInterval);
    if ($listInterval > 0 && $listCustomCounter % $listInterval === 0): ?>
    <div class="list-custom"><?php $this->options->ListCustom(); ?></div>
<?php endif; endif; ?>
<?php endwhile; ?>
<?php $this->pageNav('上一页', $this->options->AjaxLoad ? '查看更多' : '下一页', 0, '..', $this->options->AjaxLoad ? array('wrapClass' => $this->options->AjaxLoad == 'auto' ? 'page-navigator ajaxload auto' : 'page-navigator ajaxload') : ''); ?>
    </div>
<?php if (!$this->options->OneCOL):
    $this->need('sidebar.php');
endif; ?>
<?php $this->need('footer.php'); ?>