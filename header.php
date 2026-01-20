<?php if (!defined('__TYPECHO_ROOT_DIR__'))
  exit; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <!-- 字符编码与视口设置 -->
  <meta charset="<?php $this->options->charset(); ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon 图标 -->
  <?php if ($this->options->favicon): ?>
    <link rel="shortcut icon" href="<?php $this->options->favicon(); ?>" />
  <?php endif; ?>

  <!-- 页面标题：根据页面类型显示不同格式 -->
  <title><?php $this->archiveTitle(array(
            'category' => _t('分类 %s 下的文章'),
            'search' => _t('包含关键字 %s 的文章'),
            'tag' => _t('标签 %s 下的文章'),
            'date' => _t('在 %s 发布的文章'),
            'author' => _t('作者 %s 发布的文章')
          ), '', ' - '); ?><?php $this->options->title();
                            // 首页显示副标题
                            if ($this->is('index') && $this->options->subTitle): ?> -
  <?php $this->options->subTitle();
                            endif; ?>
  </title>

  <!-- Typecho 头部输出（禁用默认的生成器、评论回复等） -->
  <?php $this->header('generator=&template=&pingback=&xmlrpc=&wlw=&commentReply=&rss1=&rss2=&antiSpam=&atom='); ?>

  <!-- 主样式表 -->
  <link rel="stylesheet" href="<?php cjUrl('dist/style.min.css') ?>" />

  <!-- ===== jQuery 和 Pjax（按需加载） ===== -->
  <?php if ($this->options->PjaxOption || $this->options->AjaxLoad): ?>
    <script
      src="//<?php if ($this->options->cjCDN == 'cf'): ?>cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js<?php elseif ($this->options->cjCDN == 'sc'): ?>cdn.staticfile.org/jquery/2.1.4/jquery.min.js<?php else: ?>cdn.jsdelivr.net/npm/jquery@2.1.4/dist/jquery.min.js<?php endif; ?>"></script>
  <?php endif;
  if ($this->options->PjaxOption): ?>
    <script
      src="//<?php if ($this->options->cjCDN == 'cf'): ?>cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js<?php elseif ($this->options->cjCDN == 'sc'): ?>cdn.staticfile.org/jquery.pjax/2.0.1/jquery.pjax.min.js<?php else: ?>cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js<?php endif; ?>"></script>
  <?php endif; ?>

  <!-- 自定义 CSS 样式 -->
  <?php if ($this->options->CustomCSS): ?>
    <style type="text/css">
      <?php $this->options->CustomCSS(); ?>
    </style>
  <?php endif; ?>

  <!-- 自定义主题色 -->
  <?php if ($this->options->primaryColor && $this->options->primaryColor !== '#cb82be'): ?>
    <style type="text/css">
      :root { --primary-color: <?php $this->options->primaryColor(); ?>; }
    </style>
  <?php endif; ?>

  <!-- ===== Heti 中文排版增强（可选） ===== -->
  <?php if ($this->options->HetiOption): ?>
    <link rel="stylesheet" href="<?php cjUrl('libs/heti/heti.min.css') ?>">
    <script src="<?php cjUrl('libs/heti/heti-addon.min.js') ?>"></script>
  <?php endif; ?>

  <!-- FancyBox 样式（预加载避免闪烁） -->
  <link rel="stylesheet" href="<?php cjUrl('libs/fancybox/fancybox.css') ?>"/>

  <!-- Head 自定义内容（统计代码等） -->
  <?php if ($this->options->HeaderCustom): ?>
    <?php $this->options->HeaderCustom(); ?>
  <?php endif; ?>

  <!-- 告诉浏览器网站支持亮色和暗色模式，防止浏览器强制应用夜间模式 -->
  <meta name="color-scheme" content="light dark">
</head>

<!-- ===== 页面主体 ===== -->

<body class="<?php if ($this->options->OneCOL): ?>one-col<?php else: ?>bd<?php endif;
                                                                        if ($this->options->HeadFixed): ?> head-fixed<?php endif; ?>">

  <!-- IE 低版本浏览器提示 -->
  <!--[if lt IE 9]>
    <div class="browsehappy">当前网页可能 <strong>不支持</strong> 您正在使用的浏览器. 为了正常的访问, 请 <a href="https://browsehappy.com/">升级您的浏览器</a>.</div>
  <![endif]-->

  <!-- ===== 网站头部导航 ===== -->
  <header id="header">
    <div class="container clearfix">
      <!-- 站点标题/Logo -->
      <div class="site-name">
        <<?php echo $this->is('post') || $this->is('page') ? 'p' : 'h1' ?> class="site-title">
          <a id="logo" href="<?php $this->options->siteUrl(); ?>" rel="home">
            <?php // 显示 Logo 图片（如果设置） 
            ?>
            <?php if ($this->options->logoUrl && ($this->options->titleForm == 'logo' || $this->options->titleForm == 'all')): ?>
              <img src="<?php $this->options->logoUrl() ?>" alt="<?php $this->options->title() ?>" title="<?php $this->options->title() ?>" />
            <?php endif;
            // 显示站点标题文字（除非只显示 Logo）
            ($this->options->titleForm == 'logo' && $this->options->logoUrl) ? '' : ($this->options->customTitle ? $this->options->customTitle() : $this->options->title()) ?>
          </a>
        </<?php echo $this->is('post') || $this->is('page') ? 'p' : 'h1' ?>>
      </div>

      <!-- 移动端菜单切换按钮 -->
      <script>
        function Navswith() {
          document.getElementById("header").classList.toggle("on")
        }
      </script>
      <button id="nav-swith" onclick="Navswith()" aria-label="菜单"><span></span></button>

      <!-- ===== 导航菜单 ===== -->
      <div id="nav">
        <!-- 搜索框 -->
        <div id="site-search">
          <form id="search" method="post" action="<?php $this->options->siteUrl(); ?>">
            <input type="text" id="search-input" name="s" class="text" placeholder="输入关键字搜索" required />
            <button type="submit"></button>
          </form>
        </div>

        <!-- 导航菜单项 -->
        <ul class="nav-menu">
          <li><a href="<?php $this->options->siteUrl(); ?>">首页</a></li>

          <?php // ===== 分类菜单 =====
          if (!empty($this->options->Navset) && in_array('ShowCategory', $this->options->Navset)):
            // 合并分类模式：显示下拉菜单
            if (in_array('AggCategory', $this->options->Navset)): ?>
              <li class="menu-parent">
                <a><?php echo $this->options->CategoryText ? $this->options->CategoryText : '分类' ?></a>
                <ul>
                  <?php
                endif;
                // 遍历所有分类
                $this->widget('Widget_Metas_Category_List')->to($categorys);
                while ($categorys->next()):
                  // 只显示顶级分类
                  if ($categorys->levels == 0):
                    $children = $categorys->getAllChildren($categorys->mid);
                    // 没有子分类：直接显示链接
                    if (empty($children)):
                  ?>
                      <li><a href="<?php $categorys->permalink(); ?>" title="<?php $categorys->name(); ?>"><?php $categorys->name(); ?></a></li>
                    <?php else: ?>
                      <!-- 有子分类：显示下拉菜单 -->
                      <li class="menu-parent">
                        <a href="<?php $categorys->permalink(); ?>" title="<?php $categorys->name(); ?>"><?php $categorys->name(); ?></a>
                        <ul class="menu-child">
                          <?php foreach ($children as $mid) {
                            $child = $categorys->getCategory($mid); ?>
                            <li><a href="<?php echo $child['permalink'] ?>" title="<?php echo $child['name']; ?>"><?php echo $child['name']; ?></a></li>
                          <?php } ?>
                        </ul>
                      </li>
                <?php
                    endif;
                  endif;
                endwhile;
                ?>
                <?php if (in_array('AggCategory', $this->options->Navset)): ?>
                </ul>
              </li>
            <?php
                endif;
              endif;

              // ===== 页面菜单 =====
              if (!empty($this->options->Navset) && in_array('ShowPage', $this->options->Navset)):
                // 合并页面模式：显示下拉菜单
                if (in_array('AggPage', $this->options->Navset)):
            ?>
              <li class="menu-parent">
                <a><?php echo $this->options->PageText ? $this->options->PageText : '其他' ?></a>
                <ul>
                <?php
                endif;
                // 遍历所有独立页面
                $this->widget('Widget_Contents_Page_List')->to($pages);
                while ($pages->next()):
                ?>
                  <li><a href="<?php $pages->permalink(); ?>" title="<?php $pages->title(); ?>"><?php $pages->title(); ?></a></li>
                <?php endwhile;
                if (in_array('AggPage', $this->options->Navset)): ?>
                </ul>
              </li>
          <?php endif;
              endif; ?>
        </ul>
      </div>
    </div>
  </header>

  <!-- ===== 页面主体内容区域 ===== -->
  <div id="body" <?php if ($this->options->PjaxOption): ?> in-pjax<?php endif; ?>>
    <div class="container clearfix">
      <div id="main">