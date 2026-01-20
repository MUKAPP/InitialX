// 检查是否启用了PJAX
if (document.getElementById("body").hasAttribute("in-pjax")) {
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

    // 设置PJAX
    $(document)
        .pjax('a:not(a[target="_blank"], a[no-pjax])', {
            container: "#main",
            fragment: "#main",
            timeout: 10000,
        })
        // 处理表单提交
        .on("submit", "form[id=search], form[id=comment-form]", function (event) {
            $.pjax.submit(event, {
                container: "#main",
                fragment: "#main",
                timeout: 10000,
            });
        })
        // PJAX加载开始时显示加载条
        .on("pjax:send", function () {
            $("#header").prepend("<div id='loading-bar'></div>");
        })
        // PJAX加载完成后的处理
        .on("pjax:complete", function () {
            setTimeout(function () {
                $("#loading-bar").remove();
            }, 300);
            $("#header").removeClass("on");
            $("#search-input").val("");
            $("#secondary").removeAttr("style");
            if (typeof hljs !== "undefined") {
                // 配置 hljs 忽略 Mermaid 代码块，避免与 Mermaid.js 冲突
                hljs.configure({ ignoreUnescapedHTML: true });
                document.querySelectorAll('pre code:not(.mermaid):not(.language-mermaid):not([class*="mermaid"])').forEach((el) => {
                    hljs.highlightElement(el);
                });
            }
        })
        // PJAX加载结束后的处理
        .on("pjax:end", function () {
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
                        newCommentId = $(commentListSelector, response)
                            .html()
                            .match(/id=\"?comment-\d+/g)
                            .join()
                            .match(/\d+/g)
                            .sort(function (a, b) {
                                return a - b;
                            })
                            .pop();
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
                        $(commentNumSelector).length
                            ? ((commentCount = parseInt($(commentNumSelector).text().match(/\d+/))),
                                $(commentNumSelector).html(
                                    $(commentNumSelector)
                                        .html()
                                        .replace(commentCount, commentCount + 1),
                                ))
                            : 0;
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
            $(".ajaxload").length ? submitProtectedContent() : fetchProtectionToken();
            return false;
        });
    }

    initProtectedContent();

    // 获取保护令牌
    function fetchProtectionToken() {
        var postUrl = $(".protected .post-title a").attr("href");
        if ($("h1.post-title").length) {
            protectionToken = $(".protected form").attr("action").replace(postUrl, "");
            submitProtectedContent();
        } else {
            $.ajax({
                url: window.location.href,
                success: function (response) {
                    protectionToken = $('.protected form[action^="' + postUrl + '"]', response)
                        .attr("action")
                        .replace(postUrl, "");
                    submitProtectedContent();
                },
            });
        }
    }

    // 提交受保护内容
    function submitProtectedContent() {
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
                        .text("密码正确，如果没有跳转新页面，请手动刷新本页。")
                        .css("color", "blue");
                    $("h1.post-title").length
                        ? $.pjax.reload({
                            container: "#main",
                            fragment: "#main",
                            timeout: 10000,
                        })
                        : $.pjax({
                            url: postUrl,
                            container: "#main",
                            fragment: "#main",
                            timeout: 10000,
                        });
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
                var $newPosts = $(response).find("#main .post");
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

// 处理滚动事件
window.onscroll = function () {
    var scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
    var $topButton = document.getElementById("top");
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
        $topButton.onclick = function scrollToTop() {
            var scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
            if (scrollTop > 1) {
                requestAnimationFrame(scrollToTop);
                scrollTo(0, scrollTop - scrollTop / 5);
            } else {
                cancelAnimationFrame(scrollToTop);
                scrollTo(0, 0);
            }
        };
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
};

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
        // 跳过已经包含 Mermaid 内容的代码块
        if (codeblock.querySelector('.mermaid, [class*="mermaid"], svg')) {
            return;
        }
        // 显示 复制代码 按钮
        codeblock.style.position = "relative";
        const copyButton = document.createElement("div");
        copyButton.className = "copy-button";
        copyButton.textContent = "复制";
        copyButton.style.visibility = "hidden";
        codeblock.appendChild(copyButton);

        // 鼠标移到代码块，就显示按钮
        codeblock.addEventListener("mouseover", () => {
            copyButton.style.visibility = "visible";
        });

        // 鼠标从代码块移开，则不显示复制代码按钮
        codeblock.addEventListener("mouseout", () => {
            copyButton.style.visibility = "hidden";
        });

        // 执行 复制代码 功能
        copyButton.addEventListener("click", async () => {
            try {
                const codeText = codeblock.firstChild.textContent;
                await navigator.clipboard.writeText(codeText);
                copyButton.textContent = "复制成功";
            } catch (err) {
                console.error("复制失败: ", err);
                copyButton.textContent = "复制失败";
            }
            setTimeout(() => {
                copyButton.textContent = "复制";
            }, 1000);
        });
    });
}

// 等待页面加载完成
document.addEventListener("DOMContentLoaded", function () {
    if (typeof hljs !== "undefined") {
        // 配置 hljs 忽略 Mermaid 代码块，避免与 Mermaid.js 冲突
        hljs.configure({ ignoreUnescapedHTML: true });
        document.querySelectorAll('pre code:not(.mermaid):not(.language-mermaid):not([class*="mermaid"])').forEach((el) => {
            hljs.highlightElement(el);
        });
    }
});