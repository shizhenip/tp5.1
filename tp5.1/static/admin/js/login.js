'use strict';
layui.use(['jquery', 'layer'], function () {
    window.jQuery = window.$ = layui.jquery;
    $(".layui-canvs").width($(window).width());
    $(".layui-canvs").height($(window).height());
});
$(function () {
    $("#username").val("");
    $("#password").val('');
    $(".layui-canvs").jParticle({
        background: "#141414",
        color: "#E6E6E6"
    });
});
var handlerEmbed = function (captchaObj) {
    $("#embed-submit").click(function (e) {
        var validate = captchaObj.getValidate();
        if (!validate) {
            $("#notice")[0].className = "show";
            setTimeout(function () {
                $("#notice")[0].className = "hide";
            }, 2000);
            e.preventDefault();
        }
    });
    captchaObj.appendTo("#embed-captcha");
    captchaObj.onReady(function () {
        $("#wait")[0].className = "hide";
    });
};

var timestamp=Math.round(new Date().getTime()/1000);//Unix时间戳
$.ajax({
    url: "admin/login/getverify/t/"+timestamp+".html",
    type: "get",
    dataType: "json",
    success: function (data) {
        initGeetest({
            gt: data.gt,
            challenge: data.challenge,
            product: "float",
            offline: !data.success
        }, handlerEmbed);
    }
});

$(function () {
    $('#doLogin').ajaxForm({beforeSubmit: checkForm, success: complete, dataType: 'json'});
    function checkForm() {
        if ($.trim($('#username').val()) == "") {
            layer.msg('请输入登录用户名', {icon: 5, time: 2000}, function (index) {
                layer.close(index);
            });
            return false;
        }
        if ($.trim($('#password').val()) == "") {
            layer.msg('请输入登录密码', {icon: 5, time: 2000}, function (index) {
                layer.close(index);
            });
            return false;
        }
        $("button").removeClass('btn-primary').addClass('btn-danger').text("登录中...");
    }
    function complete(data) {
        if (data.code == 1) {
            layer.msg(data.msg, {icon: 6, time: 2000}, function (index) {
                layer.close(index);
                window.location.href = data.data;
            });
        } else if (data.code == -1) {
            $(".larry-login img").click();
            layer.msg(data.msg, {icon: 5, time: 2000});
        }
        else {
            layer.msg(data.msg, {icon: 5, time: 2000});
            $("button").removeClass('btn-danger').addClass('btn-primary').text("登　录");
            return false;
        }
    }
});