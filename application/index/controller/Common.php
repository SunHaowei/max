<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Config;
use think\Exception;

class Common extends Controller
{
    /*
     * 数据表前缀
     */
    protected $prefix = '';

    public function _initialize(){

    }

    /**
     * 封装输出
     * @param unknown $code
     * @param unknown $message
     * @param unknown $data
     */
    public function send($code,$message,$data){
        if(!is_numeric($code)){
            return '';
        }
        $result=array(
            'code'=>$code,
            'message'=>$message,
            'data'=>$data
        );
        return $this->ajaxReturn($result);
        
    }
    
    public function subReturn($code,$message){
        if(!is_numeric($code)){
            $code = '500';
        }
        $result=array(
            'code'=>$code,
            'message'=>$message,
        );
        return $this->ajaxReturn($result);
    }
    
    
    public function successed($data){
        $result = array(
            'code'=>200,
            'message'=>'success',
            'data'=>$data
        );
        return $this->ajaxReturn($result);
    }
    public function failed($data){
        $result=array(
            'code'=>400,
            'message'=>'fail',
            'data'=>$data
        );
        return $this->ajaxReturn($result);
    }
    
    protected function ajaxReturn($data,$type='') {
        if(empty($type)) $type  =   Config('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[Config('VAR_JSONP_HANDLER')]) ? $_GET[Config('VAR_JSONP_HANDLER')] : Config('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
        }
    }
    
    
    /**
     * 系统邮件发送函数
     * @param string $tomail 接收邮件者邮箱
     * @param string $name 接收邮件者名称
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     * @param string $attachment 附件列表
     * @return boolean
     * @author static7 <static7@qq.com>
     */
    function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
        
        $site_email = Db::name('system')->field("value")->where(['name'=>'site_email'])->find()['value'];//发件箱邮件账号
        $site_password = Db::name('system')->field("value")->where(['name'=>'site_password'])->find()['value'];//发件箱邮件密码
        $site_port = Db::name('system')->field("value")->where(['name'=>'site_port'])->find()['value'];//SMTP服务器的端口号
        $site_server = Db::name('system')->field("value")->where(['name'=>'site_server'])->find()['value'];//SMTP 服务器
        $site_safe = Db::name('system')->field("value")->where(['name'=>'site_safe'])->find()['value'];//安全协议

        
        $mail = new \PHPMailer();           //实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                    // 设定使用SMTP服务
        $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
        $mail->SMTPSecure = trim($site_safe);          // 使用安全协议
        $mail->Host = trim($site_server); // SMTP 服务器
        $mail->Port = trim($site_port);                  // SMTP服务器的端口号
        $mail->Username = trim($site_email);    // SMTP服务器用户名
        $mail->Password = trim($site_password);     // SMTP服务器密码
        $mail->SetFrom(trim($site_email), 'imax');
        $replyEmail = '';                   //留空则为发件人EMAIL
        $replyName = '';                    //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($tomail, $name);
        
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }
    
    
    /*
     * 空操作
     */
    public function _empty()
    {
        abort(404,'页面不存在');
    }
}
