/**
 * Created by Administrator on 2016-08-10.
 */
;$(function(){
	/**登录**/
	//验证码简单处理
	$("input[name='captcha']").focus(function(){
		$(this).val('');
		$(".captcha").show();
	});

	$("#login-submit").click(function(){
			var username = $("input[name='username']").val();
			var passwd = $("input[name='password']").val();
			var captcha = $("input[name='captcha']").val();
			if(!username){
				alerts('用户名不能为空！');
				return false;
			}

			if(!passwd){
				alerts('密码不能为空！');
				return false;
			}

			if(!captcha){
				alerts('验证码不能为空！');
				return false;
			}

			$.ajax({
				url: login,
				data: {
					username: username,
					password: passwd,
					captcha: captcha
				},
				type: 'post',
				dataType: 'json',
				success:function(res){
					if(res.status){
						window.location.href = res.url;
					} else {
						alerts(res.msg);
					}
				},
				error:function(res){
					alerts('error');
				}
			});
		});
	
	//退出登录
	$("#logout").click(function(){
		$.ajax({
			url:logout,
			dataType:'json',
			success:function(res){
				if(res.status){
					alertw(res.msg);
					//location.reload();
				} else {
					alertw(res.msg);
				}
			},
			error:function(res){
				console.log(res);
				alertw('error');
			}
		});
	});

	/**清除缓存**/
	$("#clear-cache").click(function(){

		$.ajax({
			url:cache,
			dataType:'json',
			success:function(res){
				if(res.status){
					alertw(res.msg);
					//location.reload();
				} else {
					alertw(res.msg);
				}
			},
			error:function(res){
				alertw('error');
			}
		});
	});

	 /**文章置顶操作**/
    $('.topit').on('click',function(){
    	var id = $(this).data('id');
    	var flag = $(this).data('flag');
    	
        $.ajax({
			url:topit_url,
			type:'post',
			data:{
				id: id,
				flag: flag
			},
			dataType:'json',
			success:function(res){
				if(res.status){
					alertw(res.msg);
					//location.reload();
				} else {
					alertw(res.msg);
				}
			},
			error:function(res){
				alertw('error');
			}
		});
    });

    /** 文章 产品异步提交 **/
    $("span.btn").on('click',function(){
    	var $form = $(this).parents('form');
        $.ajax({
         type: "POST",
         url:art_pro_url,
         data:$form.serialize(),
         dataType:'json',
         success: function(data) {
            if(data.status == 1){
            	if(data.url == ''){
            		alerts(data.msg);
            	}else{
            		//layer层 特殊处理
            		var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            		if(index){
            			parent.layer.close(index); //再执行关闭
            			parent.window.location.reload();
            		}
            		alertw(data.msg,data.url);
            	}
            	if(typeof(data.type) != 'undefined' && data.type == 'nav') parent.reload_category();
            }else{
                alerts(data.msg);
            }
            
        },
         error: function(data){
            alerts('服务器出错，请稍后重新操作！');
         }
     });
    });


});