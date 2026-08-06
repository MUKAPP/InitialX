// 检查是否启用了无刷新导航
if (document.getElementById("body").hasAttribute("in-swup")) {
    // 定义jQuery shake动画效果
    jQuery.fn.shake = function (times, distance) {
        this.each(function () {
            var $element = $(this);
            $element.css({ position: "relative" });
            for (var i = 1; i <= times; i++) {
                $element.animate({ left: -distance }, 50)
                    .animate({ left: distance }, 50)
                    .animate({ left: 0 }, 50);
            }
        });
        return this;
    };

    // 设置 Swup 无刷新导航
    var swup = new Swup({
        containers: ["#main"],
        timeout: 10000,
        ignoreVisit: function (url, context) {
            return Boolean(
                context &&
                    context.el &&
                    (context.el.closest("[no-pjax]") ||
                        context.el.closest(".comment-reply a, .whisper-reply, #cancel-comment-reply-link")),
            );
        },
        plugins: [
            new SwupFormsPlugin({
                formSelector: "form#search",
            }),
        ],
    });

    // 显示页面加载状态
    swup.hooks.on("visit:start", function () {
        $("#header").prepend("<div id='loading-bar'></div>");
    });

    // 页面内容替换后清理持久区域状态
    swup.hooks.on("content:replace", function () {
        setTimeout(function () {
            $("#loading-bar").remove();
        }, 300);
        $("#header").removeClass("on");
        $("#search-input").val("");
        $("#secondary").removeAttr("style");
        initHighlight();
    });

    // 新页面可见后重新绑定页面内交互
    swup.hooks.on("page:view", function () {
        if ($(".ajaxload").length) {
            loadMoreContent();
        }
        initCatalog();
        initCommentForm();
        initProtectedContent();
        if (typeof _hmt !== "undefined") {
            _hmt.push(["_trackPageview", location.pathname + location.search]);
        }
        if (typeof ga !== "undefined") {
            ga("send", "pageview", location.pathname + location.search);
        }
    });

    // 执行新页面容器内的脚本，兼容依赖 PJAX 内联脚本的插件
    function executePageScripts(visit) {
        $("#main script").each(function () {
            var type = (this.getAttribute("type") || "").toLowerCase();
            if (this.hasAttribute("data-swup-executed") || (type && type !== "text/javascript" && type !== "application/javascript" && type !== "module")) {
                return;
            }
            this.setAttribute("data-swup-executed", "true");
            if (!this.src && type !== "module") {
                window.eval(this.textContent);
                return;
            }
            var script = document.createElement("script");
            Array.prototype.forEach.call(this.attributes, function (attribute) {
                script.setAttribute(attribute.name, attribute.value);
            });
            script.text = this.textContent;
            this.parentNode.replaceChild(script, this);
        });

        if (!visit.to.html) {
            return;
        }
        function typesetMathJax() {
            var main = document.getElementById("main");
            if (!main || !window.MathJax || typeof window.MathJax.typesetPromise !== "function") {
                return;
            }
            window.MathJax.typesetClear([main]);
            window.MathJax.typesetPromise([main]).catch(function (error) {
                console.error("Failed to typeset MathJax content:", error);
            });
        }

        var incomingDocument = new DOMParser().parseFromString(visit.to.html, "text/html");
        var resourceState = window.__initialXSwupResources || (window.__initialXSwupResources = {});
        var hasMathJaxResource = false;
        $(incomingDocument)
            .find("script")
            .each(function () {
                var source = this.textContent || "";
                var src = this.getAttribute("src") || "";
                var isMermaidResource = source.indexOf("mermaid.initialize") !== -1 || /mermaid/i.test(src);
                var isMathJaxResource = source.indexOf("MathJax=") !== -1 || /mathjax/i.test(src);
                var isMarkdownResource = isMermaidResource || isMathJaxResource || /polyfill\.alicdn\.com/i.test(src);
                if ($(this).closest("#main").length || !isMarkdownResource) {
                    return;
                }
                hasMathJaxResource = hasMathJaxResource || isMathJaxResource;
                var key = src || source;
                if (resourceState[key] && !isMermaidResource) {
                    return;
                }
                resourceState[key] = true;
                var script = document.createElement("script");
                Array.prototype.forEach.call(this.attributes, function (attribute) {
                    if (attribute.name !== "id") {
                        script.setAttribute(attribute.name, attribute.value);
                    }
                });
                if (isMermaidResource && !src) {
                    script.text = source.replace(/startOnLoad\s*:\s*true/, "startOnLoad: false") +
                        "\nif (typeof mermaid.run === \"function\") { mermaid.run({ nodes: document.querySelectorAll(\"#main .mermaid\") }); } else { mermaid.init(undefined, document.querySelectorAll(\"#main .mermaid\")); }";
                } else {
                    script.text = source;
                }
                if (isMathJaxResource && /mathjax/i.test(src)) {
                    script.addEventListener("load", typesetMathJax, { once: true });
                }
                document.head.appendChild(script);
            });
        if (hasMathJaxResource) {
            typesetMathJax();
        }
    }

    swup.hooks.on("page:view", function (visit) {
        executePageScripts(visit);
    });

    // 兼容依赖 jquery-pjax 生命周期事件的第三方脚本
    function getPjaxMetadata(visit) {
        if (!visit.meta.pjax) {
            var url = window.location.origin + visit.to.url + visit.to.hash;
            visit.meta.pjax = {
                options: {
                    url: url,
                    container: "#main",
                    push: visit.history.action === "push",
                    replace: visit.history.action === "replace",
                    timeout: swup.options.timeout,
                    target: visit.trigger.el || null,
                },
                state: {
                    id: visit.id,
                    url: url,
                    title: document.title,
                    container: "#main",
                    timeout: swup.options.timeout,
                },
                previousState: window.history.state,
            };
        }
        return visit.meta.pjax;
    }

    function triggerPjaxEvent(type, visit, args, props) {
        var metadata = getPjaxMetadata(visit);
        var event = $.Event(
            type,
            $.extend(
                {
                    relatedTarget: metadata.options.target,
                },
                props,
            ),
        );
        $("#main").trigger(event, args || [null, metadata.options]);
    }

    swup.hooks.on("history:popstate", function (visit) {
        var metadata = getPjaxMetadata(visit);
        triggerPjaxEvent("pjax:popstate", visit, [], {
            state: metadata.state,
            direction: visit.history.direction,
        });
    });

    swup.hooks.on("visit:start", function (visit) {
        var metadata = getPjaxMetadata(visit);
        triggerPjaxEvent("pjax:start", visit, [null, metadata.options]);
        triggerPjaxEvent("pjax:send", visit, [null, metadata.options]);
    });

    swup.hooks.before("content:replace", function (visit, context) {
        var metadata = getPjaxMetadata(visit);
        triggerPjaxEvent("pjax:beforeReplace", visit, [context.page.html, metadata.options], {
            state: metadata.state,
            previousState: metadata.previousState,
        });
    });

    swup.hooks.on("page:view", function (visit) {
        var metadata = getPjaxMetadata(visit);
        metadata.state.title = document.title;
        triggerPjaxEvent("pjax:success", visit, [visit.to.html, "success", null, metadata.options]);
        triggerPjaxEvent("pjax:complete", visit, [null, "success", metadata.options]);
        triggerPjaxEvent("pjax:end", visit, [null, metadata.options]);
    });

    // 初始化评论表单
    function initCommentForm() {
        var $body = $("html,body");
        var commentListSelector = ".comment-list";
        var commentNumSelector = ".comment-num";
        var commentReplySelector = ".comment-reply a";
        var whisperReplySelector = ".whisper-reply";
        var textareaSelector = "#textarea";
        var parentCommentId = "";
        var newCommentId = "";

        bindCommentEvents();

        // 处理评论表单提交
        $("#comment-form").submit(function () {
            // 将 #comments 里的button type="submit"文字改为 提交中...
            $("#comments").find("button[type='submit']").text("提交中...");
            $.ajax({
                url: $(this).attr("action"),
                type: "post",
                data: $(this).serializeArray(),
                error: function () {
                    $("#comments").find("button[type='submit']").text("提交评论");
                    alert("提交失败，请确保通过验证码并且网络连接良好，或者联系管理员。");
                    return false;
                },
                success: function (response) {
                    $("#comments").find("button[type='submit']").text("提交评论");
                    if (!$(commentListSelector, response).length) {
                        alert("您输入的内容不符合规则或者回复太频繁，请修改内容或者稍等片刻。");
                        return false;
                    } else {
                        // 从响应中按 DOM id 提取最新评论 id，替代脆弱的正则匹配
                        var newCommentIds = $(commentListSelector, response)
                            .find("[id^='comment-']")
                            .map(function () {
                                return parseInt(this.id.replace(/^comment-/, ""), 10);
                            })
                            .get();
                        newCommentId = newCommentIds.length ? Math.max.apply(null, newCommentIds) : "";
                        if ($(".page-navigator .prev").length && parentCommentId == "") {
                            newCommentId = "";
                        }
                        if (parentCommentId) {
                            var newComment = $("#li-comment-" + newCommentId, response).hide();
                            if ($("#" + parentCommentId).find(".comment-children").length <= 0) {
                                $("#" + parentCommentId).append(
                                    "<div class='comment-children'><ol class='comment-list'></ol></div>",
                                );
                            }
                            if (newCommentId) $("#" + parentCommentId + " .comment-children .comment-list").prepend(newComment);
                            parentCommentId = "";
                        } else {
                            var newComment = $("#li-comment-" + newCommentId, response).hide();
                            if (!$(commentListSelector).length)
                                $("#comments").prepend(
                                    "<h3>已有 <span class='comment-num'>0</span> 条评论</h3><ol class='comment-list'></ol>",
                                );
                            $(commentListSelector).prepend(newComment);
                        }
                        $("#li-comment-" + newCommentId).fadeIn();
                        var commentCount;
                        var commentNumMatch = $(commentNumSelector).length
                            ? $(commentNumSelector).text().match(/\d+/)
                            : null;
                        commentCount = commentNumMatch ? parseInt(commentNumMatch[0], 10) : 0;
                        if ($(commentNumSelector).length) {
                            $(commentNumSelector).html(
                                $(commentNumSelector)
                                    .html()
                                    .replace(commentCount, commentCount + 1),
                            );
                        }
                        TypechoComment.cancelReply();
                        $(textareaSelector).val("");
                        $(commentReplySelector + "," + whisperReplySelector + ", #cancel-comment-reply-link").off("click");
                        bindCommentEvents();
                        if (newCommentId) {
                            $body.animate(
                                { scrollTop: $("#li-comment-" + newCommentId).offset().top - 50 },
                                300,
                            );
                        } else {
                            $body.animate(
                                { scrollTop: $("#comments").offset().top - 50 },
                                300,
                            );
                        }
                    }
                },
            });
            return false;
        });

        // 绑定评论相关事件
        function bindCommentEvents() {
            $(commentReplySelector + "," + whisperReplySelector).click(function () {
                parentCommentId = $(this).parent().parent().parent().attr("id");
            });
            $("#cancel-comment-reply-link").click(function () {
                parentCommentId = "";
            });
        }
    }

    initCommentForm();

    if (document.getElementById("token")) {
        var protectionToken = document.getElementById("token").value.replace("Token", "");
    }

    // 处理受保护内容
    function initProtectedContent() {
        $(".protected .post-title a, .protected .more a").click(function () {
            var $protectedContent = $(this).parent().parent();
            $protectedContent.find(".word").text("请输入密码访问").css("color", "red").shake(2, 10);
            $protectedContent.find(":password").focus();
            return false;
        });

        $(".protected form").submit(function () {
            var $form = $(this);
            var $word = $form.find(".word");
            $word.removeAttr("style").addClass("loading").text("请稍等");
            $(".ajaxload").length ? submitProtectedContent($form, $word) : fetchProtectionToken($form, $word);
            return false;
        });
    }

    initProtectedContent();

    // 获取保护令牌
    function fetchProtectionToken($form, $word) {
        var postUrl = $(".protected .post-title a").attr("href");
        if ($("h1.post-title").length) {
            protectionToken = $(".protected form").attr("action").replace(postUrl, "");
            submitProtectedContent($form, $word);
        } else {
            $.ajax({
                url: window.location.href,
                error: function () {
                    $word
                        .removeAttr("style")
                        .text("获取安全令牌失败，请刷新页面后重试。")
                        .css("color", "red");
                    return false;
                },
                success: function (response) {
                    protectionToken = $('.protected form[action^="' + postUrl + '"]', response)
                        .attr("action")
                        .replace(postUrl, "");
                    submitProtectedContent($form, $word);
                },
            });
        }
    }

    // 提交受保护内容
    function submitProtectedContent($form, $word) {
        var postUrl = $form.parent().parent().find(".post-title a").attr("href");
        $.ajax({
            url: postUrl + protectionToken,
            type: "post",
            data: $form.serializeArray(),
            error: function () {
                resetLoadingState();
                $word
                    .text("提交失败，请检查网络并重试或者联系管理员。")
                    .css("color", "red")
                    .shake(2, 10);
                return false;
            },
            success: function (response) {
                if (!$("h1.post-title", response).length) {
                    resetLoadingState();
                    $word
                        .text("对不起,您输入的密码错误。")
                        .css("color", "red")
                        .shake(2, 10);
                    $(":password").val("");
                    return false;
                } else {
                    resetLoadingState();
                    $word
                        .text("密码正确，正在刷新页面...")
                        .css("color", "blue");
                    if ($("h1.post-title").length) {
                        swup.navigate(window.location.href, {
                            history: "replace",
                            cache: { read: false, write: true },
                        });
                    } else {
                        swup.navigate(postUrl, {
                            cache: { read: false, write: true },
                        });
                    }
                }
            },
        });

        function resetLoadingState() {
            $word.removeClass("loading");
        }
    }
}

// 加载更多内容
var isLoading = true;

function loadMoreContent() {
    $('.ajaxload li[class!="next"]').remove();
    $(".ajaxload .next a").click(function () {
        if (isLoading) {
            isLoading = false;
            loadMore();
        }
        return false;
    });
}

function loadMore() {
    var $nextLink = ".ajaxload .next a";
    var nextUrl = $($nextLink).attr("href");
    $($nextLink).addClass("loading").text("正在加载");
    if (nextUrl) {
        $.ajax({
            url: nextUrl,
            error: function () {
                alert("请求失败，请检查网络并重试或者联系管理员");
                $($nextLink).removeAttr("class").text("查看更多");
                isLoading = true;
                return false;
            },
            success: function (response) {
                var $newPosts = $(response).find("#main .post, #main .list-custom");
                var nextPageUrl = $(response).find($nextLink).attr("href");
                if ($newPosts.length) {
                    $(".ajaxload").before($newPosts);
                }
                $($nextLink).removeAttr("class");
                if (nextPageUrl) {
                    $($nextLink).text("查看更多").attr("href", nextPageUrl);
                } else {
                    $($nextLink).remove();
                    $(".ajaxload .next").text("没有更多文章了");
                }
                if ($(".protected", response).length) {
                    $(".protected *").off();
                    initProtectedContent();
                }
                isLoading = true;
                return false;
            },
        });
    }
}

if (document.getElementsByClassName("ajaxload").length) {
    loadMoreContent();
    if ($(".ajaxload.auto").length) {
        $(window).scroll(function () {
            if (
                isLoading &&
                $(".ajaxload .next a").attr("href") &&
                $(this).scrollTop() + $(window).height() + 5 >= $(document).height()
            ) {
                isLoading = false;
                loadMore();
            }
        });
    }
}

// 处理滚动事件与返回顶部
// 返回顶部动画状态：使用 rAF id 正确取消，避免动画结束后滚轮被“粘住”
var scrollToTopRafId = 0;
var isScrollingToTop = false;

function getPageScrollTop() {
    return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
}

function stopScrollToTop() {
    if (scrollToTopRafId) {
        cancelAnimationFrame(scrollToTopRafId);
        scrollToTopRafId = 0;
    }
    isScrollingToTop = false;
}

function animateScrollToTop() {
    var current = getPageScrollTop();
    if (current <= 0) {
        // 强制归零，清掉可能残留的亚像素/合成层滚动状态
        window.scrollTo(0, 0);
        stopScrollToTop();
        return;
    }

    // 每帧按比例回退，并保证至少前进 1px，避免浮点停滞
    var next = Math.floor(current * 0.8);
    if (next >= current) {
        next = current - 1;
    }
    window.scrollTo(0, Math.max(0, next));
    scrollToTopRafId = requestAnimationFrame(animateScrollToTop);
}

function startScrollToTop() {
    if (isScrollingToTop) {
        return;
    }
    isScrollingToTop = true;
    if (scrollToTopRafId) {
        cancelAnimationFrame(scrollToTopRafId);
    }
    scrollToTopRafId = requestAnimationFrame(animateScrollToTop);
}

// 用户手动滚动时立即中断回顶动画，避免程序滚动与手势冲突
window.addEventListener("wheel", stopScrollToTop, { passive: true });
window.addEventListener("touchstart", stopScrollToTop, { passive: true });
window.addEventListener("keydown", function (event) {
    var keys = ["ArrowDown", "ArrowUp", "PageDown", "PageUp", "Home", "End", " ", "Spacebar"];
    if (keys.indexOf(event.key) !== -1) {
        stopScrollToTop();
    }
});

// 按钮点击只绑定一次，不要在 onscroll 里重复赋值
var $topButton = document.getElementById("top");
if ($topButton) {
    $topButton.addEventListener("click", startScrollToTop);
}

// 滚动处理：rAF 合并，避免每帧触发重排；用 addEventListener 避免覆盖其他脚本的处理器
var scrollFramePending = false;
function handleScroll() {
    scrollFramePending = false;
    var scrollTop = getPageScrollTop();
    var $secondary = document.getElementById("secondary");
    var isHeadFixed = document
        .getElementsByTagName("body")[0]
        .classList.contains("head-fixed");

    if ($topButton) {
        if (scrollTop >= 200) {
            $topButton.removeAttribute("class");
        } else {
            $topButton.setAttribute("class", "hidden");
        }
    }

    if (isHeadFixed) {
        var $header = document.getElementById("header");
        if (scrollTop > 0 && scrollTop < 30) {
            $header.style.padding = 15 - scrollTop / 2 + "px 0";
        } else if (scrollTop >= 30) {
            $header.style.padding = 0;
        } else {
            $header.removeAttribute("style");
        }
    }

    if ($secondary && $secondary.hasAttribute("sidebar-fixed")) {
        var $main = document.getElementById("main");
        var clientHeight = document.documentElement.clientHeight;
        var headerHeight = isHeadFixed ? 0 : 41;
        if ($main.offsetHeight > $secondary.offsetHeight) {
            if ($secondary.offsetHeight > clientHeight - 71 && scrollTop > $secondary.offsetHeight + 101 - clientHeight) {
                if (scrollTop < $main.offsetHeight + 101 - clientHeight) {
                    $secondary.style.marginTop = scrollTop - $secondary.offsetHeight - 101 + clientHeight + "px";
                } else {
                    $secondary.style.marginTop = $main.offsetHeight - $secondary.offsetHeight + "px";
                }
            } else if ($secondary.offsetHeight <= clientHeight - 71 && scrollTop > 30 + headerHeight) {
                if (scrollTop < $main.offsetHeight - $secondary.offsetHeight + headerHeight) {
                    $secondary.style.marginTop = scrollTop - 30 - headerHeight + "px";
                } else {
                    $secondary.style.marginTop = $main.offsetHeight - $secondary.offsetHeight - 30 + "px";
                }
            } else {
                $secondary.removeAttribute("style");
            }
        }
    }
}

window.addEventListener("scroll", function () {
    if (!scrollFramePending) {
        scrollFramePending = true;
        requestAnimationFrame(handleScroll);
    }
});

// 初始化背景音乐
if (document.getElementById("music")) {
    (function () {
        var $audio = document.getElementById("audio");
        var $musicButton = document.getElementById("music");
        var musicSources = $audio.getAttribute("data-src").split(",");
        var volume = $audio.getAttribute("data-vol");
        if (volume && volume >= 0 && volume <= 1) {
            $audio.volume = volume;
        }
        $audio.src = musicSources.shift();
        $audio.addEventListener("play", onPlay);
        $audio.addEventListener("pause", onPause);
        $audio.addEventListener("ended", onEnded);
        $audio.addEventListener("error", onEnded);
        $audio.addEventListener("canplay", onCanPlay);

        function onEnded() {
            if (!musicSources.length) {
                $audio.removeEventListener("play", onPlay);
                $audio.removeEventListener("pause", onPause);
                $audio.removeEventListener("ended", onEnded);
                $audio.removeEventListener("error", onEnded);
                $audio.removeEventListener("canplay", onCanPlay);
                $musicButton.style.display = "none";
                alert(
                    "本站的背景音乐好像有问题了，希望您可以通过留言等方式通知管理员，谢谢您的帮助。",
                );
            } else {
                $audio.src = musicSources.shift();
                $audio.play();
            }
        }

        function onPlay() {
            $musicButton.setAttribute("class", "play");
            $audio.addEventListener("timeupdate", onTimeUpdate);
        }

        function onPause() {
            $musicButton.removeAttribute("class");
            $audio.removeEventListener("timeupdate", onTimeUpdate);
        }

        function onCanPlay() {
            musicSources.push($audio.src);
        }

        function onTimeUpdate() {
            $musicButton.getElementsByTagName("i")[0].style.width =
                ((audio.currentTime / audio.duration) * 100).toFixed(1) + "%";
        }

        $musicButton.onclick = function () {
            if (
                $audio.canPlayType("audio/mpeg") != "" ||
                $audio.canPlayType('audio/ogg;codes="vorbis"') != "" ||
                $audio.canPlayType('audio/mp4;codes="mp4a.40.5"') != ""
            ) {
                if ($audio.paused) {
                    if ($audio.error) {
                        onEnded();
                    } else {
                        $audio.play();
                    }
                } else {
                    $audio.pause();
                }
            } else {
                alert("对不起，您的浏览器不支持HTML5音频播放，请升级您的浏览器。");
            }
        };

        $musicButton.removeAttribute("class");
    })();
}

// 初始化目录
var hasCornerTool = true;

function initCatalog() {
    var $catalogCol = document.getElementById("catalog-col");
    var $catalog = document.getElementById("catalog");
    var $cornerTool = document.getElementById("cornertool");
    var $catalogLi;

    if ($catalogCol && !$catalog) {
        if ($cornerTool) {
            $cornerTool = $cornerTool.getElementsByTagName("ul")[0];
            $catalogLi = document.createElement("li");
            $catalogLi.setAttribute("id", "catalog");
            $catalogLi.setAttribute("onclick", "toggleCatalog()");
            $catalogLi.appendChild(document.createElement("span"));
            $cornerTool.appendChild($catalogLi);
        } else {
            hasCornerTool = false;
            $cornerTool = document.createElement("div");
            $cornerTool.setAttribute("id", "cornertool");
            $cornerTool.innerHTML =
                '<ul><li id="catalog" onclick="toggleCatalog()"><span></span></li></ul>';
            document.body.appendChild($cornerTool);
        }
        document.getElementById("catalog").className = $catalogCol.className;
    }

    if (!$catalogCol && $catalog) {
        hasCornerTool
            ? $cornerTool.getElementsByTagName("ul")[0].removeChild($catalog)
            : document.body.removeChild($cornerTool);
    }

    if ($catalogCol && $catalog) {
        $catalog.className = $catalogCol.className;
    }
}

initCatalog();

// 代码块复制
function addCopyButtonsToCodeblocks() {
    // 排除 Mermaid 代码块
    const codeblocks = document.querySelectorAll('pre:not(:has(.mermaid)):not(:has([class*="mermaid"]))');

    codeblocks.forEach((codeblock) => {
        // 跳过已经包含 Mermaid 内容或已初始化的代码块
        if (codeblock.querySelector('.mermaid, [class*="mermaid"], svg') || codeblock.dataset.copyButtonInitialized) {
            return;
        }

        const code = codeblock.querySelector("code");
        if (!code) {
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.className = "code-block";
        codeblock.before(wrapper);
        wrapper.appendChild(codeblock);

        const copyButton = document.createElement("button");
        copyButton.type = "button";
        copyButton.className = "copy-button";
        copyButton.textContent = "复制";
        copyButton.setAttribute("aria-label", "复制代码");
        wrapper.appendChild(copyButton);
        codeblock.dataset.copyButtonInitialized = "true";

        // 复制文本：优先异步 Clipboard API（需 HTTPS），失败时降级 execCommand
        function fallbackCopyText(text) {
            var textarea = document.createElement("textarea");
            textarea.value = text;
            textarea.setAttribute("readonly", "");
            textarea.style.position = "fixed";
            textarea.style.opacity = "0";
            document.body.appendChild(textarea);
            textarea.select();
            var ok = false;
            try {
                ok = document.execCommand("copy");
            } catch (err) {
                ok = false;
            }
            document.body.removeChild(textarea);
            return ok;
        }

        function copyCodeText(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard
                    .writeText(text)
                    .then(function () {
                        return true;
                    })
                    .catch(function () {
                        return fallbackCopyText(text);
                    });
            }
            return Promise.resolve(fallbackCopyText(text));
        }

        // 执行复制代码功能
        copyButton.addEventListener("click", function () {
            copyCodeText(code.textContent).then(function (ok) {
                copyButton.textContent = ok ? "复制成功" : "复制失败";
                if (!ok) {
                    console.error("复制失败");
                }
            });
            setTimeout(() => {
                copyButton.textContent = "复制";
            }, 1000);
        });
    });
}

// 初始化代码高亮
function initHighlight() {
    if (typeof hljs !== "undefined") {
        // 配置 hljs 忽略 Mermaid 代码块，避免与 Mermaid.js 冲突
        hljs.configure({ ignoreUnescapedHTML: true });
        document.querySelectorAll('pre code:not(.mermaid):not(.language-mermaid):not([class*="mermaid"])').forEach((el) => {
            hljs.highlightElement(el);
        });
    }
}

// 等待页面加载完成
document.addEventListener("DOMContentLoaded", function () {
    initHighlight();
});