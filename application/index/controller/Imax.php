<?php
namespace app\index\controller;

use think\Db;
use think\Config;
use MySendMail;

class Imax extends Common{

    public function index(){
        $da = input();
        $this->successed($da);
    }

    /**
     * 引导图
     */
    public function guideimg(){
        
        $list = Db::name('banner_detail')
        ->field("title,title_e,title_a,img")
        ->order('id DESC')
        ->select();
        
        foreach($list as $k=>$v){
            $list[$k]['img'] = Config('service_url').$v['img'];
        }
        $this->successed($list);
    }
    
    
    /**
     * 私家影院（小房子）
     */
    public function indexCinema(){
        // 20170710 列表数据里增加阿拉伯图片
        $lists = Db::name('article')
            ->field("stitle,stitle_e,stitle_a,id as icid,title,title_e,title_a,litpic as img,litpic_a as img_a,description ,description_e ,description_a ")
            ->where(['status'=>0,'cid'=>50])
            ->order('publishtime DESC')
            ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            // 如果没有阿拉伯图片,则用默认图片
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        $this->successed($lists);
    }
    
    
    /**
     * 传统影院
     */
    public function traditionCinema(){
        // 20170711 列表数据里增加阿拉伯图片
        $lists = Db::name('flink')
        ->field("stitle,stitle_e,stitle_a,title,title_e,title_a,logo as img, logo_a as img_a,description,description_e,description_a,url as videourl")
        ->where(['status'=>0,'type'=>2])
        ->order('id DESC')
        ->select();
        
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            $lists[$k]['videourl'] = Config('service_url').$v['videourl'];
            // 如果没有阿拉伯图片,则用默认图片
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        $this->successed($lists);
    }
    
    
    /**
     * 私人影院顶部列表
     * 
     */
    public function privateCinemaList(){
        $lists = Db::name('article')
        ->field("id as pcid,title,title_e,title_a")
        ->where(['status'=>0,'cid'=>48])
        ->order('color DESC')
        ->select();
        $this->successed($lists);
    }
    
    /**
     * 私人影院
     * @param int $type(1:臻铂,2:臻享,3:臻睿)
     */
    public function privateCinema(){
        
        $params = input('pcid');

        // 20170711 列表数据里增加阿拉伯图片
        $lists = Db::name('all_img ai')
            ->field("ya.stitle,ya.stitle_e,ya.stitle_a,ai.description as xia_description,ai.description_e as xia_description_e,
            ai.description_a as xia_description_a, ai.descriptions as ge_description,ai.descriptions_e as ge_description_e,ai.descriptions_a as ge_description_a, 
            ai.img, ai.img_a,ya.id as pcid,ya.title,ya.title_e,ya.title_a,ya.litpic as parentimg, ya.litpic_a as parentimg_a, ya.description ,ya.description_e ,ya.description_a,ya.jumpurl as downurl ")
            ->join('yl_article ya','ai.parentid = ya.id')
            ->where(['ya.status'=>0,'ya.id'=>$params,'ya.cid'=>48])
            ->order('create_time DESC')
            ->select();
        
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];    // 20170711
            $lists[$k]['downurl'] = Config('service_url').$v['downurl'];
            $lists[$k]['parentimg'] = Config('service_url').$v['parentimg'];    // 20170524
            $lists[$k]['parentimg_a'] = $v['parentimg_a'] ? Config('service_url').$v['parentimg_a'] : Config('service_url').$v['parentimg'];    // 20170711
        }
        
        $this->successed($lists);
        
//         if($params>0){
//             $lists = Db::name('article')
//             ->field("id as pcid,title,title_e,title_a,litpic as img,description ,description_e ,description_a ")
//             ->where(['status'=>0,'id'=>$params,'cid'=>48])
//             ->order('publishtime DESC')
//             ->select();
//         }else{
//             $lists = Db::name('article')
//             ->field("id as pcid,title,title_e,title_a,litpic as img,description ,description_e ,description_a ")
//             ->where(['status'=>0,'cid'=>48])
//             ->limit(0,1)
//             ->order('publishtime DESC')
//             ->select();
//         }
        
//         foreach($lists as $k=>$v){
//             $imgs = Db::name('all_img')
//             ->field("img")
//             ->where(['status'=>0,'type'=>'pc','parentid'=>$v['pcid']])
//             ->order('create_time DESC')
//             ->select();
            
//             $img = [];
//             if($imgs){
//                 foreach($imgs as $ik=>$iv){
//                     $img[] = $iv['img'];
//                 }
//             }
//             $lists[$k]['img'] = $img;
//         }
//         $this->successed($lists);
    }
    
    
    /**
     * 私人定制风格列表
     */
    public function personalList(){
        // 20170711 列表数据里增加阿拉伯图片
        $lists = Db::name('article')
        ->field("id as plid,title,title_e,title_a,litpic as img, litpic_a as img_a")
        ->where(['status'=>0,'cid'=>51])
        ->order('publishtime DESC')
        ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        $this->successed($lists);
    }
    
    
    
    /**
     * 私人定制4种类型
     * @param string $cate (沙发ptsofa,墙面ptwall,地板ptfloor,灯光ptlighting)
     */
    public function ptcate(){
        $plid = input('plid');
        $cate = input('cate');

        // 20170711 没有参数返回空数组, 原来没有此判断接口会报错
        if (empty($plid) || empty($cate)) {
            $this->successed(array());
        }
        
        $lists = Db::name('article')->field($cate)->where(['author'=>$plid])->group($cate)->select();
        $arr = array();
        foreach($lists as $kk=>$vv){
            $one = Db::name('article')->field('id')->where([$cate=>$vv[$cate]])->find();
            $arr[] = $one['id'];
        }
		$str = "(".implode(',',$arr).")";
        $where=" id IN ".$str;
		if($cate=='ptlighting'){$where=" status=0 and id IN ".$str;}
        // 20170711 列表数据里增加阿拉伯图片
        $fainllist = Db::name('article')->field('id as yyy,litpic as img,litpic_a as img_a,'.$cate.' as id')->where($where)->select();
        
//         $gb = $cate.',litpic';
//         $lists = Db::name('article')->field('litpic as img,'.$cate.' as id')->where(['author'=>$plid])->group($gb)->order('id DESC')->select();
//         dump($lists);exit();
        
        
//         $imgs = Db::name('all_img')
//         ->field("id,title,title_e,title_a,img")
//         ->where(['status'=>0,'type'=>'pt','cate'=>$cate,'style'=>$plid])
//         ->order('create_time DESC')
//         ->select();
        
        
        foreach($fainllist as $k=>$v){
            $fainllist[$k]['img'] = Config('service_url').$v['img'];
            $fainllist[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        $imgss['lists'] = $fainllist;
        
        
        //默认选中当前系列一条数据
        $def = Db::name('article')
        ->field("ptsofa,ptwall,ptfloor,ptlighting")
        ->where(['status'=>0,'author'=>$plid])
        ->find();
        
        $imgss['ptsofa'] = $def['ptsofa'];
        $imgss['ptwall'] = $def['ptwall'];
        $imgss['ptfloor'] = $def['ptfloor'];
        $imgss['ptlighting'] = $def['ptlighting'];
        
        $this->successed($imgss);
    }
    
    
    
    
    /**
     * 私人定制
     */
    public function personalTailor(){
        $plid = input('plid');
        $ptsofa = input('ptsofaid');
        $ptwall = input('ptwallid');
        $ptfloor = input('ptfloorid');
        $ptlighting = input('ptlightingid');

        // 20170711 列表数据里增加阿拉伯图片
        $lists = Db::name('article')
        ->field("id as ptid,title,title_e,title_a,litpic as img,litpic_a as img_a,description,description_e,description_a")
        ->where(['ptsofa'=>$ptsofa,'ptwall'=>$ptwall,'ptfloor'=>$ptfloor,'ptlighting'=>$ptlighting,'status'=>0,'author'=>$plid])
        ->order('id DESC')
        ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        $this->successed($lists);
    }
    
    
    /**
     * 私人定制详情
     */
    public function personalTailorDetail(){
        $params = input('ptid');
        
        $lists = array(
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
            'http://img04.tooopen.com/images/20130712/tooopen_17270713.jpg',
        );
        $this->successed($lists);
    }
    
    
    /**
     * 获取新媒体/新闻条数
     */
    public function newCount(){
        $maxmtid = input('maxmtid');
        $mtcount = Db::name('article')
        ->field("*")
        ->where(['status'=>0,'cid'=>45,'id'=>array('gt',$maxmtid)])
        ->count();
        
        
        $maxnewsid = input('maxnewsid');
        $newscount = Db::name('article')
        ->field("*")
        ->where(['status'=>0,'cid'=>47,'id'=>array('gt',$maxnewsid)])
        ->count();
        
        
        $arr = array(
            'mtcount'=>$mtcount,
            'newscount'=>$newscount
        );
        $this->successed($arr);
    }
    
    
    
    /**
     * 媒体介绍
     * @param int type (45媒体类型 , 47新闻)
     */
    public function mediainTroduction(){
        
        $type = input('type');

        // 20170711 列表数据里增加阿拉伯图片
        $lists = Db::name('article')
        ->field("id as mtid,title,title_e,title_a,litpic as img,litpic_a as img_a,description as absort,description_e as absort_e,description_a as absort_a")
        ->where(['status'=>0,'cid'=>$type])
        ->order('publishtime DESC')
        ->select();
        
        foreach($lists as $k=>$v){
            $lists[$k]['img'] = Config('service_url').$v['img'];
            $lists[$k]['img_a'] = $v['img_a'] ? Config('service_url').$v['img_a'] : Config('service_url').$v['img'];
        }
        
        $this->successed($lists);
    }
    
    
    /**
     * 媒体介绍详情
     */
    public function mediainDetail(){
        $params = input('mtid');
    
        $lists = Db::name('all_img')
        ->field("img")
        ->where(['status'=>0,'type'=>'mt','parentid'=>$params])
        ->order('create_time DESC')
        ->select();
        $imgs = [];
        if($lists){
            foreach($lists as $k=>$v){
                $imgs[] = Config('service_url').$v['img'];
            }
        }
        $this->successed($imgs);
    }
    
    
    /**
     * 联系我们
     */
    public function contactUs(){
        
        $list = Db::name('contact')
        ->field("cuid,region,region_e,region_a,name,name_e,name_a,phone,address,address_e,address_a,email")
        ->where(['status'=>1])
        ->order('cuid DESC')
        ->select();
        
        $this->successed($list);
        
    }

    
    /**
     * 联系我们--提交数据
     */
    public function contactSub(){
        $site_receive = Db::name('system')->field("value")->where(['name'=>'site_receive'])->find()['value'];//收件箱账号
        $cuid = input('cuid');
        $country = input('country');
        $name = input('name');
        $phone = input('phone');
        $email = input('email');
        $preferred = input('preferred');
        
        $data['title'] = $country;
        $data['username'] = $name;
        $data['tel'] = $phone;
        $data['email'] = $email;
        $data['content'] = $preferred;
        $data['create_time'] = date('Y-m-d H:i:s',time());
        
        $commentModel = Db::name('comment');
        
        $commentModel->startTrans();
        $result = $commentModel->insert($data);
        if($result){
            $commentModel->commit();

            if(preg_match("/^\d{4}\-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/i",$preferred)){
                $preferred = "预约体验日期".$preferred;
            }
            $str = "国家：".$country."<br/>"."姓名：".$name."<br/>"."电话：".$phone."<br/>"."邮箱：".$email."<br/>"."留言内容：".$preferred;
            $res = $this->send_mail(trim($site_receive),'','Imax用户留言',$str);
            $this->subReturn('200', '提交成功');
        }else{
            $commentModel->rollback();
            $this->subReturn('500', '提交失败');
        }
        
        
    }
    
    
    /**
    ** 异步获取聊天记录
    **/
    public function getMessageHis(){
        $page = input('param.page');
        $list = Db::name('chat')
                ->field("name,content,FROM_UNIXTIME(send_time,'%Y-%m-%d %H:%i') as send_date")
                ->order('send_time DESC')
                ->limit(($page-1)*10,10)
                ->select();

        exit(json_encode(array_reverse($list)));
    }

}
