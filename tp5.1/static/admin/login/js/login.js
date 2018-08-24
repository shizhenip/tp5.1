'use strict';
layui.use(['jquery', 'layer'], function () {
    window.jQuery = window.$ = layui.jquery;
});

//渲染拖动验证码
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

//加载拖动验证码
var timestamp = Math.round(new Date().getTime() / 1000);//Unix时间戳
$.ajax({
    url: '/admin/login/getverify/t/' + timestamp + '.html',
    type: "get",
    dataType: "json",
    success: function (data) {
        initGeetest({
            gt: data.gt,
            challenge: data.challenge,
            product: 'float',
            offline: !data.success,
            width: '100%',
        }, handlerEmbed);
    }
});

//登录验证
$(function () {
    //清空input
    $("#username").val("");
    $("#password").val("");
    //异步请求
    $('#doLogin').ajaxForm({beforeSubmit: checkForm, success: complete, dataType: 'json'});
    //登录验证
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
    }

    //登录成功跳转
    function complete(data) {
        if (data.code == 1) {
            layer.msg(data.msg, {icon: 6, time: 2000}, function (index) {
                layer.close(index);
                window.location.href = data.data;
            });
        } else if (data.code == -1) {
            $(".larry-login img").click();//刷新验证码
            layer.msg(data.msg, {icon: 5, time: 2000});
            return false;
        } else {
            layer.msg(data.msg, {icon: 5, time: 2000});
            return false;
        }
    }
});