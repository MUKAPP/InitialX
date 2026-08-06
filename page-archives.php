<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

use Utils\Helper;

$this->need('header.php');
Breadcrumbs($this); ?>
    <article class="post">
        <h1 class="post-title"><a href="<?php $this->permalink() ?>"><?php $this->title() ?></a></h1>
        <?php
        // 仅查询归档展示所需的列（不拉取正文 text），补齐派生字段后生成 permalink
        $db = \Typecho\Db::get();
        $options = Helper::options();
        $archives = initialx_permalinkize($db->fetchAll($db->select('cid', 'title', 'slug', 'type', 'created', 'password')
            ->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->where('created < ?', $options->time)
            ->order('created', \Typecho\Db::SORT_DESC)));
        $year = 0;
        $output = '<div id="archives">';
        if ($archives) {
            foreach ($archives as $archive) {
                $year_tmp = date('Y', $archive['created']);
                if ($year > $year_tmp) {
                    $output .= '</ul>';
                }
                if ($year != $year_tmp) {
                    $year = $year_tmp;
                    $output .= '<h3>' . date('Y 年', $archive['created']) . '</h3><ul>';
                }
                $safeTitle = htmlspecialchars($archive['title']);
                // Pjax 模式下不直接链接到密码保护文章，与列表页行为一致
                if ($this->options->PjaxOption && strlen($archive['password'] ?? '') > 0) {
                    $output .= '<li>' . date('m/d：', $archive['created']) . '<a>' . $safeTitle . '</a></li>';
                } else {
                    $output .= '<li>' . date('m/d：', $archive['created']) . '<a href="' . htmlspecialchars($archive['permalink']) . '">' . $safeTitle . '</a></li>';
                }
            }
        }
        $output .= '</ul></div>';
        echo $output;
        ?>
    </article>
    </div>
<?php if (!$this->options->OneCOL):
    $this->need('sidebar.php');
endif; ?>
<?php $this->need('footer.php'); ?>