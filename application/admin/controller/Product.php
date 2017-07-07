<?php
/**
 *产品模型
 */

namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Db;
use think\Request;

class Product extends Common
{
    /*
     * 产品表
     */
    private static $_table = 'product';


    /*
     * 存储表对象
     */
    private static $db = '';


    public function __construct()
    {
        parent::__construct();

        //分类
        $catgeroy = Db::name('category')->field('id,pid,name')->where('modelid', 3)->select();
        $all_cat = [];
        //拼接导航 一级二级
        foreach ($catgeroy as $val) {
            if ($val['pid'] == 0) {
                $all_cat[$val['id']] = $val;
            } else {
                $all_cat[$val['pid']]['children'][] = $val;
            }
        }

        $this->assign('category', $all_cat);
    }

    /**
     * 显示资源列表
     *param int $id cid
     * @return \think\Response
     */
    public function index($id = 0)
    {
        //$list = Db::name(self::$_table)->field('id,title,publishtime,cid')->order('id DESC');
        $list = Db::field('p.id,p.title,p.publishtime,p.cid,p.flag,p.click,c.name')
            ->table($this->prefix.'product p,'.$this->prefix.'category c')
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
     * 添加产品
     *
     */
    public function add()
    {
        //获取图片缩略图宽高
        $config = Db::name('system')->field('value')->where('name','in',['display_thumbw','display_thumbh'])->select();
        $thumb_width = $config[0]['value'];
        $thumb_height = $config[1]['value'];
        //显示页面
        if (request()->isGet()) {
            return $this->fetch();
        } elseif (request()->isPost()) {
            $params = input('post.');
            if (isset($params['pic_url'])) {
                $img1 = str_replace(__ROOT__,'',$params['pic_url'][0]);
                $realpath = str_replace(['/..\/','/../'],'/',ROOT_PATH.$img1);

                //第一张图生成缩略图
                $image = \think\Image::open($realpath);
                $type = $image->type();
                $thumb_path = './uploads/'.date('Ymd').'/'.date('YmdHis').'-thumb.'.$type;
                $image->thumb($thumb_width,$thumb_height)->save($thumb_path);
                if(__ROOT__){
                    $params['litpic'] = __ROOT__.ltrim($thumb_path,'.');
                }else{
                    $params['litpic'] = ltrim($thumb_path,'.');
                }

                $params['pictureurls'] = implode('|',$params['pic_url']);
                unset($params['pic_url']);
            }else{
                $params['pictureurls'] = $params['litpic'] = '';
            }
            
            if(!$params['cid']){
                exit(json_encode(['status' => 0, 'msg' => '请先选择分类', 'url' => '']));
            }
            $params['publishtime'] = strtotime($params['publishtime']);
            if (!$params['id']) {
                //新增
                unset($params['id']);
                $flag = Db::name(self::$_table)->insert($params);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('product/index',['id' => $params['cid']])]));
                    $this->success('添加成功', 'product/index?id='.$params['cid']);
                } else {
                    exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
                }
            } else {
                //更新
                $id = $params['id'];
                unset($params['id']);
                $params['updatetime'] = strtotime("now");
                $flag = Db::name(self::$_table)->where('id', $id)->update($params);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '更新成功', 'url' => url('product/index',['id' => $params['cid']])]));
                } else {
                    exit(json_encode(['status' => 0, 'msg' => '更新失败，请稍后重试', 'url' => '']));
                }
            }
        }
    }

    /*
     * 更新产品信息
     *
     * $id 资源id
     */
    public function edit($id = 0) {
        $data = Db::name(self::$_table)->where('id',$id)->find();
        $data['pic_url'] = explode('|',$data['pictureurls']);
        $this->assign('item',$data);
        return $this->fetch();
    }

    /*
     * 置顶
     *
     * $id 资源id
     */
    public function topit() {
        $id = input('param.id/d');
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

}
