<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

use Typecho\Cookie;
use Typecho\Db;
use Typecho\Widget;
use Typecho\Widget\Helper\Form\Element\Checkbox;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Utils\Helper;
use Widget\Base\Comments;

// InitialX 主题版本号
define('INITIALX_VERSION_NUMBER', '1.0.2');

// Gravatar 镜像源设置
if (Helper::options()->GravatarUrl)
    define('__TYPECHO_GRAVATAR_PREFIX__', Helper::options()->GravatarUrl);

function themeConfig($form): void
{
    $logoUrl = new Text('logoUrl', NULL, NULL, _t('站点标题 LOGO 地址'), _t('在这里填入一个图片 URL 地址, 以显示网站标题 LOGO'));
    $form->addInput($logoUrl);

    $customTitle = new Text('customTitle', NULL, NULL, _t('自定义站点标题'), _t('仅用于替换页面头部位置的“标题”显示，和Typecho后台设置的站点名称不冲突，留空则显示默认站点名称'));
    $form->addInput($customTitle);

    $titleForm = new Radio(
        'titleForm',
        array(
            'title' => _t('仅文字'),
            'logo' => _t('仅LOGO'),
            'all' => _t('LOGO+文字')
        ),
        'title',
        _t('站点标题显示内容'),
        _t('默认仅显示文字标题，若要显示LOGO，请在上方添加 LOGO 地址')
    );
    $form->addInput($titleForm);

    $subTitle = new Text('subTitle', NULL, NULL, _t('自定义站点副标题'), _t('浏览器副标题，仅在当前页面为首页时显示，显示格式为：<b>标题 - 副标题</b>，留空则不显示副标题'));
    $form->addInput($subTitle);

    $favicon = new Text('favicon', NULL, NULL, _t('Favicon 地址'), _t('在这里填入一个图片 URL 地址, 以添加一个Favicon，留空则不单独设置Favicon'));
    $form->addInput($favicon);

    $CustomCSS = new Textarea('CustomCSS', NULL, NULL, _t('自定义样式'), _t('在这里填入你的自定义样式（直接填入css，无需&lt;style&gt;标签）'));
    $form->addInput($CustomCSS);

    $primaryColor = new Text('primaryColor', NULL, '#cb82be', _t('主题色'), _t('设置网站的主题色（十六进制颜色值），如：<b class="notice">#cb82be</b>、<b>#4527A0</b>、<b>#2196F3</b>，留空则使用默认颜色'));
    $form->addInput($primaryColor);

    $cjcdnAddress = new Text('cjcdnAddress', NULL, NULL, _t('主题静态文件（css和js）的链接地址替换'), _t('请输入你的CDN云存储地址，例如：<b class="notice">//cdn.jsdelivr.net/gh/MUKAPP/InitialX/</b><br><b>被替换的原地址为主题文件位置，即：http://www.example.com/usr/themes/initial/</b>'));
    $form->addInput($cjcdnAddress);

    $AttUrlReplace = new Textarea('AttUrlReplace', NULL, NULL, _t('文章内的链接地址替换（建议用在图片等静态资源的链接上）'), _t('按照格式输入你的CDN链接以替换原链接，格式：<br><b class="notice">原地址=替换地址</b><br>原地址与新地址之间用等号“=”分隔，例如：<br><b>http://www.example.com/usr/uploads/=http://cdn.example.com/usr/uploads/</b><br>可设置多个规则，换行即可，一行一个'));
    $form->addInput($AttUrlReplace);

    $Navset = new Checkbox(
        'Navset',
        array(
            'ShowCategory' => _t('显示分类'),
            'AggCategory' => _t('↪合并分类'),
            'ShowPage' => _t('显示页面'),
            'AggPage' => _t('↪合并页面')
        ),
        array('ShowCategory', 'AggCategory', 'ShowPage'),
        _t('导航栏显示'),
        _t('默认显示合并的分类，显示页面')
    );
    $form->addInput($Navset->multiMode());

    $CategoryText = new Text('CategoryText', NULL, NULL, _t('导航栏-分类 下拉菜单显示名称（使用“导航栏显示-合并分类”时生效）'), _t('在这里输入导航栏<b>分类</b>下拉菜单的显示名称,留空则默认显示为“分类”'));
    $form->addInput($CategoryText);

    $PageText = new Text('PageText', NULL, NULL, _t('导航栏-页面 下拉菜单显示名称（使用“导航栏显示-合并页面”时生效）'), _t('在这里输入导航栏<b>页面</b>下拉菜单的显示名称,留空则默认显示为“其他”'));
    $form->addInput($PageText);

    $Breadcrumbs = new Checkbox(
        'Breadcrumbs',
        array(
            'Postshow' => _t('文章内显示'),
            'Text' => _t('↪文章标题替换为“正文”'),
            'Pageshow' => _t('页面内显示')
        ),
        array('Postshow', 'Text', 'Pageshow'),
        _t('面包屑导航显示'),
        _t('默认在文章与页面内显示，并将文章标题替换为“正文”')
    );
    $form->addInput($Breadcrumbs->multiMode());

    $WeChat = new Text('WeChat', NULL, NULL, _t('微信打赏二维码（建议图片尺寸不低于240*240）'), _t('在这里填入一个图片 URL 地址, 以添加一个微信打赏二维码，留空则不设置微信打赏'));
    $form->addInput($WeChat);

    $Alipay = new Text('Alipay', NULL, NULL, _t('支付宝打赏二维码（建议图片尺寸不低于240*240）'), _t('在这里填入一个图片 URL 地址, 以添加一个支付宝打赏二维码，留空则不设置支付宝打赏'));
    $form->addInput($Alipay);

    $LicenseInfo = new Text('LicenseInfo', NULL, NULL, _t('文章许可信息'), _t('填入后将在文章底部显示你填入的许可信息（支持HTML标签，输入数字“0”可关闭显示），留空则默认使用 (CC BY-SA 4.0)国际许可协议。'));
    $form->addInput($LicenseInfo);

    $HeadFixed = new Radio(
        'HeadFixed',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('浮动显示头部'),
        _t('默认关闭')
    );
    $form->addInput($HeadFixed);

    $SidebarFixed = new Radio(
        'SidebarFixed',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('动态显示侧边栏'),
        _t('默认关闭')
    );
    $form->addInput($SidebarFixed);

    $cjCDN = new Radio(
        'cjCDN',
        array(
            'jd' => _t('jsDelivr'),
            'sc' => _t('Staticfile'),
            'cf' => _t('CDNJS')
        ),
        'jd',
        _t('公共静态资源来源'),
        _t('默认jsDelivr，若JS文件异常，可尝试切换来源')
    );
    $form->addInput($cjCDN);

    $GravatarUrl = new Radio(
        'GravatarUrl',
        array(
            false => _t('官方源'),
            'https://cn.gravatar.com/avatar/' => _t('国内源'),
            'https://cravatar.cn/avatar/' => _t('Cravatar源'),
            'https://gravatar.loli.net/avatar/' => _t('loli源'),
            'https://sdn.geekzu.org/avatar/' => _t('极客族源'),
            'https://dn-qiniu-avatar.qbox.me/avatar/' => _t('七牛源')
        ),
        false,
        _t('Gravatar头像源'),
        _t('默认官方源，若头像显示异常，可尝试切换来源')
    );
    $form->addInput($GravatarUrl);

    $compressHtml = new Radio(
        'compressHtml',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('HTML压缩'),
        _t('默认关闭，启用则会对HTML代码进行压缩，可能与部分插件存在兼容问题，请酌情选择开启或者关闭')
    );
    $form->addInput($compressHtml);

    $PjaxOption = new Radio(
        'PjaxOption',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('全站无刷新导航'),
        _t('默认关闭，启用后将强制关闭“反垃圾保护”，并强制“将较新的评论显示在前面”')
    );
    $form->addInput($PjaxOption);

    $AjaxLoad = new Radio(
        'AjaxLoad',
        array(
            'auto' => _t('自动'),
            'click' => _t('点击'),
            0 => _t('关闭')
        ),
        0,
        _t('Ajax翻页'),
        _t('默认关闭，启用则会使用Ajax加载下一页的文章')
    );
    $form->addInput($AjaxLoad);

    $Highlight = new Radio(
        'Highlight',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('代码高亮'),
        _t('默认关闭，启用则会渲染页面内代码块”')
    );
    $form->addInput($Highlight);

    $HetiOption = new Radio(
        'HetiOption',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        1,
        _t('中文排版增强'),
        _t('默认启用，使用 Heti 自动为中英文之间添加空格，优化排版效果')
    );
    $form->addInput($HetiOption);

    $catalog = new Radio(
        'catalog',
        array(
            'post' => _t('使用文章内设定'),
            'open' => _t('全部启用'),
            0 => _t('全部关闭')
        ),
        'post',
        _t('文章目录'),
        _t('一键开关全部文章目录，默认使用文章内的设定，（若文章内无任何标题，则不显示目录）')
    );
    $form->addInput($catalog);

    $scrollTop = new Radio(
        'scrollTop',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('返回顶部'),
        _t('默认关闭，启用将在右下角显示“返回顶部”按钮')
    );
    $form->addInput($scrollTop);

    $MusicSet = new Radio(
        'MusicSet',
        array(
            'order' => _t('顺序播放'),
            'shuffle' => _t('随机播放'),
            0 => _t('关闭')
        ),
        0,
        _t('背景音乐'),
        _t('默认关闭，启用后请填写音乐地址,否则开启无效')
    );
    $form->addInput($MusicSet);

    $MusicUrl = new Textarea('MusicUrl', NULL, NULL, _t('背景音乐地址（建议使用mp3格式）'), _t('请输入完整的音频文件路径，例如：<br>https://music.163.com/song/media/outer/url?id=<b class="notice">{MusicID}</b>.mp3<br>可设置多个音频，换行即可，一行一个，留空则关闭背景音乐'));
    $form->addInput($MusicUrl);

    $MusicVol = new Text('MusicVol', NULL, NULL, _t('背景音乐播放音量（输入范围：0~1）'), _t('请输入一个0到1之间的小数（0为静音  0.5为50%音量  1为100%最大音量）输入错误内容或留空则使用默认音量100%'));
    $form->addInput($MusicVol);

    $InsideLinksIcon = new Radio(
        'InsideLinksIcon',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('显示链接图标（内页）'),
        _t('默认关闭，启用后内页（链接模板）链接将显示链接图标')
    );
    $form->addInput($InsideLinksIcon);

    $IndexLinksSort = new Text('IndexLinksSort', NULL, NULL, _t('首页显示的链接分类（支持多分类，请用英文逗号“,”分隔）'), _t('若只需显示某分类下的链接，请输入链接分类名（建议使用字母形式的分类名），留空则默认显示全部链接'));
    $form->addInput($IndexLinksSort);

    $InsideLinksSort = new Text('InsideLinksSort', NULL, NULL, _t('内页（链接模板）显示的链接分类（支持多分类，请用英文逗号“,”分隔）'), _t('若只需显示某分类下的链接，请输入链接分类名（建议使用字母形式的分类名），留空则默认显示全部链接'));
    $form->addInput($InsideLinksSort);

    $ShowLinks = new Checkbox('ShowLinks', array('footer' => _t('页脚'), 'sidebar' => _t('侧边栏')), array('sidebar'), _t('首页显示链接'));
    $form->addInput($ShowLinks->multiMode());

    $ShowWhisper = new Checkbox('ShowWhisper', array('index' => _t('首页'), 'sidebar' => _t('侧边栏')), array('sidebar'), _t('显示最新的“轻语”'));
    $form->addInput($ShowWhisper->multiMode());

    $sidebarBlock = new Checkbox(
        'sidebarBlock',
        array(
            'ShowHotPosts' => _t('显示热门文章（根据评论数量排序）'),
            'ShowRecentPosts' => _t('显示最新文章'),
            'ShowRecentComments' => _t('显示最近回复'),
            'IgnoreAuthor' => _t('↪不显示作者回复'),
            'ShowCategory' => _t('显示分类'),
            'ShowTag' => _t('显示标签'),
            'ShowArchive' => _t('显示归档'),
            'ShowOther' => _t('显示其它杂项')
        ),
        array('ShowRecentPosts', 'ShowRecentComments', 'ShowCategory', 'ShowTag', 'ShowArchive', 'ShowOther'),
        _t('侧边栏显示')
    );
    $form->addInput($sidebarBlock->multiMode());

    $OneCOL = new Radio(
        'OneCOL',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('单栏模式'),
        _t('关闭侧边栏，仅显示主栏内容。')
    );
    $form->addInput($OneCOL);

    $ICPbeian = new Text('ICPbeian', NULL, NULL, _t('ICP备案号'), _t('在这里输入ICP备案号,留空则不显示'));
    $form->addInput($ICPbeian);

    $siteStartYear = new Text('siteStartYear', NULL, NULL, _t('网站创建年份'), _t('填入网站创建的年份（如：2019），用于页脚版权信息显示。如创建年份与当前年份不同则显示"创建年份 - 当前年份"，留空或与当前年份相同则只显示当前年份'));
    $form->addInput($siteStartYear);

    // ===== 自定义内容选项 =====
    $HeaderCustom = new Textarea('HeaderCustom', NULL, NULL, _t('Head 自定义内容'), _t('在 &lt;head&gt; 标签内输出，适合放置统计代码、自定义 meta 标签、预加载等内容'));
    $form->addInput($HeaderCustom);

    $FooterCustom = new Textarea('FooterCustom', NULL, NULL, _t('Footer 自定义内容'), _t('在页脚版权区域内输出，支持 HTML，适合放置标语、徽章图片、额外链接等'));
    $form->addInput($FooterCustom);

    $CustomContent = new Textarea('CustomContent', NULL, NULL, _t('页面末尾脚本'), _t('在 &lt;/body&gt; 之前输出，适合放置需要在页面最后加载的 JS 脚本（若开启全站 Pjax，目前支持 Google 和百度统计的回调）'));
    $form->addInput($CustomContent);

    $PostBottomCustom = new Textarea('PostBottomCustom', NULL, NULL, _t('文章底部自定义内容'), _t('在文章底部、评论区之前输出，支持 HTML，适合放置广告代码等'));
    $form->addInput($PostBottomCustom);

    $SidebarCustom = new Textarea('SidebarCustom', NULL, NULL, _t('侧边栏自定义内容'), _t('在侧边栏中输出，支持 HTML，适合放置广告代码、推广信息等'));
    $form->addInput($SidebarCustom);

    $SidebarCustomPosition = new Text('SidebarCustomPosition', NULL, '0', _t('侧边栏自定义内容显示位置'), _t('填写数字指定在第几个侧边栏模块之后显示，0 表示末尾。侧边栏模块顺序：①轻语 ②热门文章 ③最新文章 ④最近回复 ⑤分类 ⑥标签 ⑦归档 ⑧链接 ⑨其它（仅计算实际显示的模块）'));
    $form->addInput($SidebarCustomPosition);

    $ListCustom = new Textarea('ListCustom', NULL, NULL, _t('文章列表自定义内容'), _t('在文章列表中每隔指定篇数插入一次，支持 HTML，适合放置信息流广告等。需配合下方"插入间隔"使用，留空则不插入'));
    $form->addInput($ListCustom);

    $ListCustomInterval = new Text('ListCustomInterval', NULL, '3', _t('文章列表自定义内容插入间隔'), _t('每隔多少篇文章插入一次自定义内容，默认为 3'));
    $form->addInput($ListCustomInterval);

    // ===== 主题设置备份与恢复 =====
    $backupHtml = new Textarea(
        'themeBackup',
        NULL,
        NULL,
        _t('📦 主题设置备份与恢复'),
        _t('
			<div style="background:#f5f5f5;padding:15px;border-radius:8px;margin:10px 0;">
				<p style="margin:0 0 10px 0;"><strong>备份设置：</strong>点击下方按钮将当前设置复制到剪贴板</p>
				<button type="button" id="backupBtn" style="padding:8px 16px;background:#4527A0;color:#fff;border:none;border-radius:4px;cursor:pointer;">📋 复制当前设置</button>
				<p style="margin:15px 0 10px 0;"><strong>恢复设置：</strong>在上方文本框中粘贴备份的 JSON 数据，然后点击恢复</p>
				<button type="button" id="restoreBtn" style="padding:8px 16px;background:#2E7D32;color:#fff;border:none;border-radius:4px;cursor:pointer;">🔄 恢复设置</button>
				<span id="statusMsg" style="margin-left:10px;"></span>
			</div>
			<script>
			(function(){
				var optionNames = ["logoUrl","customTitle","titleForm","subTitle","favicon","CustomCSS","cjcdnAddress","AttUrlReplace","Navset","CategoryText","PageText","Breadcrumbs","WeChat","Alipay","LicenseInfo","HeadFixed","SidebarFixed","cjCDN","GravatarUrl","compressHtml","PjaxOption","AjaxLoad","Highlight","catalog","scrollTop","MusicSet","MusicUrl","MusicVol","InsideLinksIcon","IndexLinksSort","InsideLinksSort","ShowLinks","ShowWhisper","sidebarBlock","OneCOL","ICPbeian","CustomContent","PostBottomCustom","SidebarCustom","SidebarCustomPosition","ListCustom","ListCustomInterval","HeaderCustom","FooterCustom"];
				
				document.getElementById("backupBtn").onclick = function(){
					var allOptions = {};
					optionNames.forEach(function(name){
						var inputs = document.querySelectorAll("[name=\"" + name + "\"], [name=\"" + name + "[]\"]");
						if(inputs.length === 0) return;
						
						var firstInput = inputs[0];
						if(firstInput.type === "radio"){
							inputs.forEach(function(r){ if(r.checked) allOptions[name] = r.value; });
						} else if(firstInput.type === "checkbox"){
							var vals = [];
							inputs.forEach(function(c){ if(c.checked) vals.push(c.value); });
							allOptions[name] = vals;
						} else {
							allOptions[name] = firstInput.value;
						}
					});
					
					var jsonStr = JSON.stringify(allOptions, null, 2);
					if(navigator.clipboard && navigator.clipboard.writeText){
						navigator.clipboard.writeText(jsonStr).then(function(){
							document.getElementById("statusMsg").innerHTML = "<span style=\"color:green\">✅ 已复制到剪贴板！</span>";
						}).catch(function(){
							document.querySelector("textarea[name=themeBackup]").value = jsonStr;
							document.getElementById("statusMsg").innerHTML = "<span style=\"color:orange\">⚠️ 请手动复制上方文本框内容</span>";
						});
					} else {
						document.querySelector("textarea[name=themeBackup]").value = jsonStr;
						document.getElementById("statusMsg").innerHTML = "<span style=\"color:orange\">⚠️ 请手动复制上方文本框内容</span>";
					}
				};
				
				document.getElementById("restoreBtn").onclick = function(){
					var textarea = document.querySelector("textarea[name=themeBackup]");
					try {
						var data = JSON.parse(textarea.value);
						for(var key in data){
							var val = data[key];
							var inputs = document.querySelectorAll("[name=\"" + key + "\"], [name=\"" + key + "[]\"]");
							if(inputs.length === 0) continue;
							
							var firstInput = inputs[0];
							if(firstInput.type === "radio"){
								inputs.forEach(function(r){ r.checked = (r.value == val); });
							} else if(firstInput.type === "checkbox"){
								inputs.forEach(function(c){ c.checked = (Array.isArray(val) ? val.indexOf(c.value) > -1 : c.value == val); });
							} else {
								firstInput.value = val;
							}
						}
						document.getElementById("statusMsg").innerHTML = "<span style=\"color:green\">✅ 设置已恢复！请点击下方保存按钮。</span>";
					} catch(e) {
						document.getElementById("statusMsg").innerHTML = "<span style=\"color:red\">❌ JSON 格式错误: " + e.message + "</span>";
					}
				};
			})();
			</script>
		')
    );
    $form->addInput($backupHtml);
}

function themeInit($archive)
{
    $options = Helper::options();
    // 仅 Pjax 无刷新导航模式下关闭评论反垃圾校验（其表单由 AJAX 提交，token 注入
    // 无法随新页面生效）；其余模式保留核心 CSRF 校验
    if ($options->PjaxOption) {
        $options->commentsAntiSpam = false;
    }
    if ($options->PjaxOption || FindContents('page-whisper.php', 'commentsNum', 'd')) {
        $options->commentsOrder = 'DESC';
        $options->commentsPageDisplay = 'first';
    }
    if ($archive->is('single')) {
        $archive->content = hrefOpen($archive->content);
        if ($options->AttUrlReplace) {
            $archive->content = UrlReplace($archive->content);
        }
        if ($archive->is('post') && (($options->catalog == 'post' && $archive->fields->catalog) || $options->catalog == 'open')) {
            $archive->content = createCatalog($archive->content);
        }
    }
}

function cjUrl($path)
{
    $options = Helper::options();
    $ver = '?ver=' . constant("INITIALX_VERSION_NUMBER");
    if ($options->cjcdnAddress) {
        echo rtrim($options->cjcdnAddress, '/') . '/' . $path . $ver;
    } else {
        $options->themeUrl($path . $ver);
    }
}

function hrefOpen($obj)
{
    return preg_replace_callback('/<a\b([^>]+?)\bhref="((?!' . addcslashes(Helper::options()->index, '/._-+=#?&') . '|\#).*?)"([^>]*?)>/i', function ($matches) {
        // 已有 rel 属性的链接不重复添加
        if (preg_match('/\brel\s*=/i', $matches[1] . $matches[3])) {
            return $matches[0];
        }
        return '<a' . $matches[1] . 'href="' . $matches[2] . '"' . $matches[3] . ' target="_blank" rel="noopener noreferrer">';
    }, $obj);
}

function UrlReplace($obj)
{
    $list = explode(PHP_EOL, Helper::options()->AttUrlReplace);
    foreach ($list as $tmp) {
        list($old, $new) = explode('=', $tmp);
        $obj = str_replace($old, $new, $obj);
    }
    return $obj;
}

function postThumb($obj)
{
    $thumb = $obj->fields->thumb;
    if (!$thumb) {
        return false;
    }
    if (is_numeric($thumb)) {
        preg_match_all('/<img.*?src="(.*?)".*?[\/]?>/i', $obj->content, $matches);
        if (isset($matches[1][$thumb - 1])) {
            $thumb = $matches[1][$thumb - 1];
        } else {
            return false;
        }
    }
    if (Helper::options()->AttUrlReplace) {
        $thumb = UrlReplace($thumb);
    }
    return '<img src="' . $thumb . '" />';
}

function Postviews($archive): void
{
    $db = Db::get();
    $cid = $archive->cid;
    if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))) {
        $db->query('ALTER TABLE `' . $db->getPrefix() . 'contents` ADD `views` INT(10) DEFAULT 0;');
    }
    $exist = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid))['views'];
    if ($archive->is('single')) {
        $cookie = Cookie::get('contents_views');
        $cookie = $cookie ? explode(',', $cookie) : array();
        if (!in_array($cid, $cookie)) {
            $db->query($db->update('table.contents')
                ->rows(array('views' => (int)$exist + 1))
                ->where('cid = ?', $cid));
            $exist = (int)$exist + 1;
            array_push($cookie, $cid);
            $cookie = implode(',', $cookie);
            Cookie::set('contents_views', $cookie);
        }
    }
    echo $exist == 0 ? '暂无阅读' : $exist . ' 次阅读';
}

function Breadcrumbs($archive): void
{
    $options = Helper::options();
    if (!empty($options->Breadcrumbs) && in_array('Pageshow', $options->Breadcrumbs)) {
        echo '<div class="breadcrumbs">' . PHP_EOL . '<a href="' . $options->siteUrl . '">首页</a> &raquo; ' . $archive->title . PHP_EOL . '</div>' . PHP_EOL;
    }
}

function createCatalog($obj): string
{
    global $catalog;
    global $catalog_count;
    $catalog = array();
    $catalog_count = 0;
    $obj = preg_replace_callback('/<h([1-6])(.*?)>(.*?)<\/h\1>/i', function ($obj) {
        global $catalog;
        global $catalog_count;
        $catalog_count++;
        $catalog[] = array('text' => trim(strip_tags($obj[3])), 'depth' => $obj[1], 'count' => $catalog_count);
        return '<h' . $obj[1] . $obj[2] . '><a class="cl-offset" name="cl-' . $catalog_count . '"></a>' . $obj[3] . '</h' . $obj[1] . '>';
    }, $obj);
    return $obj . PHP_EOL . getCatalog();
}

function getCatalog(): string
{
    global $catalog;
    $index = '';
    if ($catalog) {
        $index = '<ul>' . PHP_EOL;
        $prev_depth = '';
        $to_depth = 0;
        foreach ($catalog as $catalog_item) {
            $catalog_depth = $catalog_item['depth'];
            if ($prev_depth) {
                if ($catalog_depth == $prev_depth) {
                    $index .= '</li>' . PHP_EOL;
                } elseif ($catalog_depth > $prev_depth) {
                    $to_depth++;
                    $index .= PHP_EOL . '<ul>' . PHP_EOL;
                } else {
                    $to_depth2 = ($to_depth > ($prev_depth - $catalog_depth)) ? ($prev_depth - $catalog_depth) : $to_depth;
                    if ($to_depth2) {
                        for ($i = 0; $i < $to_depth2; $i++) {
                            $index .= '</li>' . PHP_EOL . '</ul>' . PHP_EOL;
                            $to_depth--;
                        }
                    }
                    $index .= '</li>' . PHP_EOL;
                }
            }
            $index .= '<li><a href="#cl-' . $catalog_item['count'] . '" onclick="toggleCatalog()">' . $catalog_item['text'] . '</a>';
            $prev_depth = $catalog_item['depth'];
        }
        for ($i = 0; $i <= $to_depth; $i++) {
            $index .= '</li>' . PHP_EOL . '</ul>' . PHP_EOL;
        }
        $index = '<div id="catalog-col">' . PHP_EOL . '<b>文章目录</b>' . PHP_EOL . $index . '<script>function toggleCatalog(){document.getElementById("catalog-col").classList.toggle("catalog");document.getElementById("catalog").classList.toggle("catalog")}</script>' . PHP_EOL . '</div>' . PHP_EOL;
    }
    return $index;
}

function CommentAuthor($obj, $autoLink = NULL, $noFollow = NULL)
{
    $options = Helper::options();
    $autoLink = $autoLink ? $autoLink : $options->commentsShowUrl;
    $noFollow = $noFollow ? $noFollow : $options->commentsUrlNofollow;
    if ($obj->url && $autoLink) {
        echo '<a href="' . $obj->url . '"' . ($noFollow ? ' rel="external nofollow"' : NULL) . (strstr($obj->url, $options->index) == $obj->url ? NULL : ' target="_blank"') . '>' . $obj->author . '</a>';
    } else {
        echo $obj->author;
    }
}

function CommentAt($coid): void
{
    $db = Db::get();
    $prow = $db->fetchRow($db->select('parent')->from('table.comments')
        ->where('coid = ? AND status = ?', $coid, 'approved'));
    $parent = $prow['parent'];
    if ($prow && $parent != '0') {
        $arow = $db->fetchRow($db->select('author')->from('table.comments')
            ->where('coid = ? AND status = ?', $parent, 'approved'));
        echo '<b class="comment-at">@' . $arow['author'] . '</b>';
    }
}

function Contents_Post_Initial($limit = 10, $order = 'created'): void
{
    $db = Db::get();
    $options = Helper::options();
    $posts = $db->fetchAll($db->select()->from('table.contents')
        ->where('type = ? AND status = ? AND created < ?', 'post', 'publish', $options->time)
        ->order($order, Db::SORT_DESC)
        ->limit($limit));
    if ($posts) {
        foreach ($posts as $post) {
            // Typecho 1.3.0 兼容：手动生成 permalink
            $permalink = \Typecho\Common::url(\Typecho\Router::url('post', $post), $options->index);
            echo '<li><a href="' . $permalink . '">' . htmlspecialchars($post['title']) . '</a></li>' . PHP_EOL;
        }
    } else {
        echo '<li>暂无文章</li>' . PHP_EOL;
    }
}

class Initial_Widget_Comments_Recent extends Comments
{
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault(array('pageSize' => $this->options->commentsListSize, 'parentId' => 0, 'ignoreAuthor' => false));
    }

    public function execute()
    {
        $select = $this->select()->limit($this->parameter->pageSize)
            ->where('table.comments.status = ?', 'approved')
            ->order('table.comments.coid', Db::SORT_DESC);
        if ($this->parameter->parentId) {
            $select->where('cid = ?', $this->parameter->parentId);
        }
        if ($this->options->commentsShowCommentOnly) {
            $select->where('type = ?', 'comment');
        }
        if ($this->parameter->ignoreAuthor) {
            $select->where('ownerId <> authorId');
        }
        $page_whisper = FindContents('page-whisper.php', 'commentsNum', 'd');
        if ($page_whisper) {
            $select->where('cid <> ? OR (cid = ? AND parent <> ?)', $page_whisper[0]['cid'], $page_whisper[0]['cid'], '0');
        }
        $this->db->fetchAll($select, array($this, 'push'));
    }
}

function FindContent($cid): ?array
{
    $db = Db::get();
    $row = $db->fetchRow($db->select()->from('table.contents')
        ->where('cid = ?', $cid)
        ->limit(1));
    if ($row) {
        // Typecho 1.3.0 兼容：手动计算 hidden 状态
        $row['hidden'] = (strlen($row['password'] ?? '') > 0);
    }
    return $row;
}

function FindContents($val = NULL, $order = 'order', $sort = 'a', $publish = NULL): ?array
{
    $db = Db::get();
    $options = Helper::options();
    $sort = ($sort == 'a') ? Db::SORT_ASC : Db::SORT_DESC;
    $select = $db->select()->from('table.contents')
        ->where('created < ?', $options->time)
        ->order($order, $sort);
    if ($val) {
        $select->where('template = ?', $val);
    }
    if ($publish) {
        $select->where('status = ?', 'publish');
    }
    $contents = $db->fetchAll($select);
    if (!empty($contents)) {
        // Typecho 1.3.0 兼容：手动生成 permalink
        foreach ($contents as &$content) {
            $routeType = ($content['type'] === 'page') ? 'page' : 'post';
            $content['permalink'] = \Typecho\Common::url(\Typecho\Router::url($routeType, $content), $options->index);
        }
        unset($content);
    }
    return empty($contents) ? NULL : $contents;
}

function Whisper($sidebar = NULL): void
{
    $db = Db::get();
    $options = Helper::options();
    $page = FindContents('page-whisper.php', 'commentsNum', 'd');
    $p = $sidebar ? 'li' : 'p';
    $remind = '';
    if (Widget::widget('Widget_User')->pass('editor', true) && (!$page || isset($page[1]))) {
        $remind = '<' . $p . ' class="notice"><b>仅管理员可见: </b>' . ($page ? '发现多个"轻语"模板页面，已自动选取内容较多的页面来展示，请删除多余模板页面。' : '未找到"轻语"模板页面，请创建"轻语"模板页面。') . '</' . $p . '>' . PHP_EOL;
    }
    if ($page) {
        $page = $page[0];
        $title = $sidebar ? '<h3 class="widget-title">' . htmlspecialchars($page['title']) . '</h3>' : '<h2 class="post-title"><a href="' . htmlspecialchars($page['permalink']) . '">' . htmlspecialchars($page['title']) . '<span class="more">···</span></a></h2>';
        $comment = $db->fetchAll($db->select()->from('table.comments')
            ->where('cid = ? AND status = ? AND parent = ?', $page['cid'], 'approved', '0')
            ->order('coid', Db::SORT_DESC)
            ->limit(1));
        if ($comment) {
            // 评论内容以纯文本输出：评论 text 原样入库，若经 Markdown 转换后再用原生
            // strip_tags 白名单渲染，事件属性（onerror/onclick 等）与 javascript: 链接
            // 会原样保留，构成存储型 XSS。这里只保留纯文本与换行。
            $content = '<' . $p . '>' . nl2br(htmlspecialchars($comment[0]['text'])) . '</' . $p . '>' .
                ($sidebar ? PHP_EOL . '<li class="more"><a href="' . htmlspecialchars($page['permalink']) . '">查看更多...</a></li>' : '');
        } else {
            $content = '<' . $p . '>暂无内容</' . $p . '>';
        }
    } else {
        $title = $sidebar ? '<h3 class="widget-title">轻语</h3>' : '<h2 class="post-title"><a>轻语</a></h2>';
        $content = '<' . $p . '>暂无内容</' . $p . '>';
    }
    echo $title . PHP_EOL . ($sidebar ? '<ul class="widget-list whisper">' : '<div class="post-content">') . PHP_EOL . $content . PHP_EOL . $remind . ($sidebar ? '</ul>' : '</div>') . PHP_EOL;
}

function Links($sorts = NULL, $icon = 0)
{
    // 同一请求内（侧边栏/页脚多处调用）只查询并渲染一次
    static $cache = array();
    $key = ($sorts === NULL ? '' : $sorts) . '|' . $icon;
    if (array_key_exists($key, $cache)) {
        echo $cache[$key];
        return;
    }
    $db = Db::get();
    $link = NULL;
    $list = NULL;
    $page_links = FindContents('page-links.php', 'order', 'a');
    if ($page_links) {
        $exist = $db->fetchRow($db->select()->from('table.fields')
            ->where('cid = ? AND name = ?', $page_links[0]['cid'], 'links'));
        if (empty($exist)) {
            $db->query($db->insert('table.fields')
                ->rows(array(
                    'cid' => $page_links[0]['cid'],
                    'name' => 'links',
                    'type' => 'str',
                    'str_value' => NULL,
                    'int_value' => 0,
                    'float_value' => 0
                )));
            return NULL;
        }
        $list = $exist['str_value'];
    }
    if ($list) {
        $list = explode(PHP_EOL, $list);
        foreach ($list as $val) {
            // 链接行格式: 名称,URL,描述,图标,分类；字段缺失时按空字符串处理
            $fields = explode(',', $val);
            $name = $fields[0] ?? '';
            $url = $fields[1] ?? '';
            $description = $fields[2] ?? '';
            $logo = $fields[3] ?? '';
            $sort = $fields[4] ?? '';
            // 输出统一转义，URL 仅允许 http/https，防止 javascript: 等协议注入
            $safeUrl = $url ? htmlspecialchars(\Typecho\Common::safeUrl($url)) : '';
            $safeName = htmlspecialchars($name);
            $safeDescription = htmlspecialchars($description);
            $safeLogo = $logo ? htmlspecialchars($logo) : '';
            if ($sorts) {
                $arr = explode(',', $sorts);
                if ($sort && in_array($sort, $arr)) {
                    $link .= '<li><a' . ($url ? ' href="' . $safeUrl . '"' : '') . ($icon == 1 && $url ? ' class="l_logo"' : '') . ' title="' . $safeDescription . '" target="_blank">' . ($icon == 1 && $url ? '<img src="' . ($safeLogo ? $safeLogo : htmlspecialchars(rtrim($url, '/') . '/favicon.ico')) . '" onerror="erroricon(this)">' : '') . '<span>' . ($url ? $safeName : '<del>' . $safeName . '</del>') . '</span></a></li>' . PHP_EOL;
                }
            } else {
                $link .= '<li><a' . ($url ? ' href="' . $safeUrl . '"' : '') . ($icon == 1 && $url ? ' class="l_logo"' : '') . ' title="' . $safeDescription . '" target="_blank">' . ($icon == 1 && $url ? '<img src="' . ($safeLogo ? $safeLogo : htmlspecialchars(rtrim($url, '/') . '/favicon.ico')) . '" onerror="erroricon(this)">' : '') . '<span>' . ($url ? $safeName : '<del>' . $safeName . '</del>') . '</span></a></li>' . PHP_EOL;
            }
        }
    }
    $output = $link ? $link : '<li>暂无链接</li>' . PHP_EOL;
    $cache[$key] = $output;
    echo $output;
}

function Playlist(): void
{
    $options = Helper::options();
    $arr = explode(PHP_EOL, $options->MusicUrl);
    if ($options->MusicSet == 'shuffle') {
        shuffle($arr);
    }
    echo implode(',', $arr);
}

function compressHtml($html_source): string
{
    $chunks = preg_split('/(<!--<nocompress>-->.*?<!--<\/nocompress>-->|<nocompress>.*?<\/nocompress>|<pre.*?\/pre>|<textarea.*?\/textarea>|<script.*?\/script>)/msi', $html_source, -1, PREG_SPLIT_DELIM_CAPTURE);
    $compress = '';
    foreach ($chunks as $c) {
        if (strtolower(substr($c, 0, 19)) == '<!--<nocompress>-->') {
            $c = substr($c, 19, strlen($c) - 19 - 20);
            $compress .= $c;
            continue;
        } else if (strtolower(substr($c, 0, 12)) == '<nocompress>') {
            $c = substr($c, 12, strlen($c) - 12 - 13);
            $compress .= $c;
            continue;
        } else if (strtolower(substr($c, 0, 4)) == '<pre' || strtolower(substr($c, 0, 9)) == '<textarea') {
            $compress .= $c;
            continue;
        } else if (strtolower(substr($c, 0, 7)) == '<script' && strpos($c, '//') != false && (strpos($c, PHP_EOL) !== false || strpos($c, PHP_EOL) !== false)) {
            $tmps = preg_split('/(\r|\n)/ms', $c, -1, PREG_SPLIT_NO_EMPTY);
            $c = '';
            foreach ($tmps as $tmp) {
                if (strpos($tmp, '//') !== false) {
                    if (substr(trim($tmp), 0, 2) == '//') {
                        continue;
                    }
                    $chars = preg_split('//', $tmp, -1, PREG_SPLIT_NO_EMPTY);
                    $is_quot = $is_apos = false;
                    foreach ($chars as $key => $char) {
                        if ($char == '"' && $chars[$key - 1] != '\\' && !$is_apos) {
                            $is_quot = !$is_quot;
                        } else if ($char == '\'' && $chars[$key - 1] != '\\' && !$is_quot) {
                            $is_apos = !$is_apos;
                        } else if ($char == '/' && $chars[$key + 1] == '/' && !$is_quot && !$is_apos) {
                            $tmp = substr($tmp, 0, $key);
                            break;
                        }
                    }
                }
                $c .= $tmp;
            }
        }
        $c = preg_replace('/[\\n\\r\\t]+/', ' ', $c);
        $c = preg_replace('/\\s{2,}/', ' ', $c);
        $c = preg_replace('/>\\s</', '> <', $c);
        $c = preg_replace('/\\/\\*.*?\\*\\//i', '', $c);
        $c = preg_replace('/<!--[^!]*-->/', '', $c);
        $compress .= $c;
    }
    return $compress;
}

function themeFields($layout): void
{
    $thumb = new Text('thumb', NULL, NULL, _t('自定义缩略图'), _t('在这里填入一个图片 URL 地址, 以添加本文的缩略图，若填入纯数字，例如 <b>3</b> ，则使用文章第三张图片作为缩略图（对应位置无图则不显示缩略图），留空则默认不显示缩略图'));
    $thumb->input->setAttribute('class', 'w-100');
    $layout->addItem($thumb);

    $catalog = new Radio(
        'catalog',
        array(
            1 => _t('启用'),
            0 => _t('关闭')
        ),
        0,
        _t('文章目录'),
        _t('默认关闭，启用则会在文章内显示“文章目录”（若文章内无任何标题，则不显示目录），需要在“控制台-设置外观-文章目录”启用“使用文章内设定”后，方可生效')
    );
    $layout->addItem($catalog);
}
