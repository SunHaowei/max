<?php
/**
 *产品模型
 */

namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Db;
use think\Request;

class Article extends Common
{
    /*
     * 产品表
     */
    private static $_table = 'article';

    /*
     * 数据库配置参数
     */
    protected $db_config = [];

    /*
     * 存储表对象
     */
    private static $db = '';


    public function __construct()
    {
        parent::__construct();
        //加载数据库配置
        $this->db_config =  Config::load(APP_PATH.'/database.php');
        //分类
        $catgeroy = Db::name('category')->field('id,pid,name')->where('modelid', 1)->select();
        $all_cat = [];
        //拼接导航 一级二级
        foreach ($catgeroy as $val) {
            if ($val['pid'] == 0) {
                $all_cat[$val['id']] = $val;
            } else {
                $all_cat[$val['pid']]['children'][] = $val;
            }
        }
        //实例化表对象
        self::$db = Db::name(self::$_table);

        $this->assign('category', $all_cat);
    }

    /**
     * 显示资源列表
     *param int $id cid
     * @return \think\Response
     */
    public function index()
    {
        $id = input('param.id/d',0);
        $list = Db::field('p.id,p.title,p.publishtime,p.cid,p.click,p.flag,c.name')
            ->table($this->db_config['prefix'].'article p,'.$this->db_config['prefix'].'category c')
            ->where('p.cid = c.id')
            ->where('p.status',0)
            ->order('p.flag DESC,p.publishtime DESC');
        if($id == 0){
            $list = $list->paginate(10);
        }else{
            $list = $list->where('cid', $id)->paginate(10);
        }

        // 获取分页显示
        $page = $list->render();
        if ($list->total() < 1) {
            $this->assign('empty', "<tr><td colspan='7'>暂无数据</td></tr>");
        }
        $this->assign('page',$page);
        $this->assign("id", $id);
        $this->assign('data', $list);
        return $this->fetch();
    }

    /**
     * 添加文章
     *
     */
    public function add()
    {
        //显示页面
        if (request()->isGet()) {
            $id = input('param.caid/d',0);
            $this->assign('caid',$id);
            
            $lists = Db::name('article')
            ->field("id as sid,title")
            ->where(['status'=>0,'cid'=>51])
            ->order('publishtime DESC')
            ->select();
            $this->assign('style',$lists);
            
            
            return $this->fetch();
        } elseif (request()->isPost()) {
            $params = input('post.');
            if (isset($params['pic_url'])) {
                $params['litpic'] = implode('|',$params['pic_url']);
                unset($params['pic_url']);
            }else{
                $params['litpic'] = '';
            }

            if(!$params['cid']){
                exit(json_encode(['status' => 0, 'msg' => '请先选择分类', 'url' => '']));
            }
            
            $params['publishtime'] = strtotime($params['publishtime']);
            if (!$params['id']) {
                //新增
                unset($params['id']);
                //描述为空 则截取内容填补
                if(!$params['description']){
                    $content = strip_tags($params['content']);
                    $params['description'] = mb_substr($content,0,180,'utf-8');
                }
                
                
                //上传文件
                $file = request()->file('tech_file');
                if($file){
                    $info = $file->move(ROOT_PATH . 'public/uploads');
                    if ($info) {
                        $path = $info->getPath();
                        $filename = $info->getFilename();
                        $save_name = $info->getSaveName();
                        if(__ROOT__){
                            $realpath =  __ROOT__.'/uploads/' . $save_name;
                        }else{
                            $realpath =  '/uploads/' . $save_name;
                        }
                        $params['jumpurl'] = $realpath;
//                         dump($realpath);exit();//return $this->fetch();
                    } else {
                        $this->error('上传失败');
                        //exit(json_encode(['status' => 0, 'msg' => '上传视频失败', 'url' => '']));
                    }
                }
                
                
                
                $flag = Db::name(self::$_table)->insert($params);
                if ($flag) {
                    $this->redirect('article/index', array('id'=>$params['cid']), 1, '添加成功~');
//                     $this->success('添加成功', 'article/index');
                    //exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('article/index',['id' => $params['cid']])]));
                } else {
                    $this->error('添加失败');
                    //exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
                }
            } else {
                
                $file = request()->file('tech_file');
                if($file){
                    $info = $file->move(ROOT_PATH . 'public/uploads');
                    if ($info) {
                        $path = $info->getPath();
                        $filename = $info->getFilename();
                        $save_name = $info->getSaveName();
                        if(__ROOT__){
                            $realpath =  __ROOT__.'/uploads/' . $save_name;
                        }else{
                            $realpath =  '/uploads/' . $save_name;
                        }
                        $params['jumpurl'] = $realpath;
                        //dump($realpath);//return $this->fetch();
                    } else {
                        $this->error('上传失败');
                        //exit(json_encode(['status' => 0, 'msg' => '上传视频失败', 'url' => '']));
                    }
                }
                
                //更新
                $id = $params['id'];
                unset($params['id']);
                $params['updatetime'] = strtotime("now");
                $flag = Db::name(self::$_table)->where('id', $id)->update($params);
                if ($flag) {
                    $this->redirect('article/index', array('id'=>$params['cid']), 1, '更新成功~');
                    //exit(json_encode(['status' => 1, 'msg' => '更新成功', 'url' => url('article/index',['id' => $params['cid']])]));
                } else {
                    $this->error('更新失败，请稍后重试');
                    //exit(json_encode(['status' => 0, 'msg' => '更新失败，请稍后重试', 'url' => '']));
                }
            }
        }
    }

    /*
     * 更新文章信息
     *
     * $id 资源id
     */
    public function edit($id = 0) {
        $data = Db::name(self::$_table)->where('id',$id)->find();
        
        $lists = Db::name('article')
        ->field("id as sid,title")
        ->where(['status'=>0,'cid'=>51])
        ->order('publishtime DESC')
        ->select();
        $this->assign('style',$lists);
        
        
        $this->assign('item',$data);
        return $this->fetch();
    }

    /*
     * 置顶文章
     *
     * $id 资源id
     */
    public function topit() {
        $id = input('param.id');
        $flag = input('param.flag');
        $flag = $flag? 0:1;

        $res = Db::name(self::$_table)->where('id',$id)->update(['flag' => $flag]);
        if($res){
            exit(json_encode(['status' => 1,'msg' => '操作成功']));
        }else{
           exit(json_encode(['status' => 0,'msg' => '操作失败'])); 
        }
    }

    /*
     * 删除资源
     * @param id int 资源id
     */
    public function dele() {
        if(input('?param.checkbox')){
            $ids = input('param.checkbox/a');
        }else{
            $ids = input('param.id/d',0);
        }
        //逻辑删除
        $flag = Db::name(self::$_table)->where('id','in',$ids)->update(['status' => 1]);
        if ($flag) {
            echo '删除成功';
        } else {
            echo '删除失败';
        }
    }

    /*
     * 移动分类
     */
   public function move(){
       $params = input('param.');
       $cid = $params['new_cat_id'];
       $ids = $params['checkbox'];

       $flag = Db::name(self::$_table)->where('id','in',$ids)->update(['cid' => $cid]);
       if ($flag) {
            echo '操作成功';
       } else {
            echo '操作失败';
       }
   }

    /**
     * 转载文章 暂只支持csdn
     */
    public function copy(){
        if(request()->isAjax()){
            $url = input('post.url');
            $cid = input('post.cid');

            if(!$url || !substr_count($url, 'csdn')){
                exit(json_encode(['status' => 0, 'msg' => '请输入csdn博客文章地址', 'url' => '']));
            }

           if(!$cid){
                exit(json_encode(['status' => 0, 'msg' => '请先选择分类', 'url' => '']));
            }
           
           
            try {
                \phpQuery::newDocumentFile($url);
                $title = pq('.link_title a')->text();
                if (!$title) {
                    $title = pq('.list_c_t a')->text();
                }
                $content = pq('#article_content')->html();
                //如果抓取不到主内容
                if(!$content){
                     throw new Exception("文章不存在或禁止爬虫");
                     
                }
                $params['cid'] = $cid;
                $params['content'] = $content;
                $params['title'] = $title;
                $params['publishtime'] = time();
                $params['description'] = mb_substr(trim(strip_tags($content)), 0, 180, 'utf-8');
                $params['copyfrom'] = $url;
                $flag = Db::name(self::$_table)->insert($params);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '转载成功', 'url' => url('article/index',['id' => $cid])]));
                } else {
                    exit(json_encode(['status' => 0, 'msg' => '转载失败', 'url' => '']));
                }
            }
            catch (Exception $e){
                exit(json_encode(['status' => 0, 'msg' => '添加失败：'.$e->getMessage(), 'url' => '']));
            }
        }else{
            return $this->fetch();
        }

    }
    
    public function ptsofa(){
        $style = input('style');
        $list = Db::name('all_img')
        ->field("id,title")
        ->where(['status'=>0,'type'=>'pt','cate'=>'ptsofa','style'=>$style])
        ->order('create_time DESC')
        ->select();
        exit(json_encode(['status' => $style, 'data' => $list, 'url' => '']));
    }
    public function ptwall(){
        $style = input('style');
        $list = Db::name('all_img')
        ->field("id,title")
        ->where(['status'=>0,'type'=>'pt','cate'=>'ptwall','style'=>$style])
        ->order('create_time DESC')
        ->select();
        exit(json_encode(['status' => $style, 'data' => $list, 'url' => '']));
    }
    public function ptfloor(){
        $style = input('style');
        $list = Db::name('all_img')
        ->field("id,title")
        ->where(['status'=>0,'type'=>'pt','cate'=>'ptfloor','style'=>$style])
        ->order('create_time DESC')
        ->select();
        exit(json_encode(['status' => $style, 'data' => $list, 'url' => '']));
    }
    public function ptlighting(){
        $style = input('style');
        $list = Db::name('all_img')
        ->field("id,title")
        ->where(['status'=>0,'type'=>'pt','cate'=>'ptlighting','style'=>$style])
        ->order('create_time DESC')
        ->select();
        exit(json_encode(['status' => $style, 'data' => $list, 'url' => '']));
    }
    
    


}
