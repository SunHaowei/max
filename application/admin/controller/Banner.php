<?php
/**
 * 单页控制器
 */
namespace app\admin\controller;

use app\admin\model\Category;
use think\Db;

class Banner extends Common
{

    public function index()
    {
        $banner = Db::name('banner')->where(['status' => 0])->select();
        $this->assign('banner', $banner);
        return $this->fetch();
    }

    /*
     * 添加内容
     */
    public function add(){
        if (request()->isPost()) {
            //新增处理
            $params = input('post.');
            $this->assign('pid',$params['id']);
            $flag = Db::name('banner')->insert($params);
            if ($flag) {
                exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('banner/index')]));
            }else{
                exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
            }
        }else{
            return $this->fetch();
        }
    }

    /**
     * 修改
     */
    public function edit($id){
        $data = Db::name('banner')->find($id);
        $this->assign('item',$data);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function dele($id){
        $flag = Db::name('banner')->where(['id' => $id])->update(['status' => 1]);
        if ($flag) {
            echo '删除成功！';
        }else{
            echo '删除失败！';
        }
    }

    /**
     * Banner 已添加图片列表
     * @return
     */
    public function banlist($id)
    {
        $cate = Db::name('banner')->field('title,type,id')->find($id);
        $list = Db::name('banner_detail')->where(['pid' => $id])->select();

        $this->assign([
            'list' => $list,
            'cate' => $cate
        ]);
        return $this->fetch();
    }

    /**
     * 添加banner内容
     */
    public function addDetail($id = 0){
        if (request()->isAjax()) {
            //新增处理
            $params = input('post.');
            if (isset($params['pic_url'])) {
                $params['img'] = implode('|',$params['pic_url']);
                unset($params['pic_url']);
            }else{
                $params['img'] = '';
            }
            if (!isset($params['id'])) {
                $flag = Db::name('banner_detail')->insert($params);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('banner/banlist')]));
                }else{
                    exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
                }
            }else{
                $flag = Db::name('banner_detail')->where(['id' => $params['id']])->update($params);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '修改成功', 'url' => url('banner/banlist')]));
                }else{
                    exit(json_encode(['status' => 0, 'msg' => '修改失败', 'url' => '']));
                }
            }

        }else{
            $this->assign('pid',$id);
            return $this->fetch();
        }
    }

    /**
     * 编辑大图
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function editDetail($id){
        $item = Db::name('banner_detail')->find($id);
        $this->assign('item',$item);
        return $this->fetch();
    }

    public function deleDetail($id){
        $flag = Db::name('banner_detail')->delete($id);
        if ($flag) {
            echo '删除成功！';
        }else{
            echo '删除失败！';
        }
    }


    /**
     * 添加banner内容
     *
     * @date 20170524
     */
    public function addimg(){
        if (request()->isAjax()) {

            $params = input('post.');

//             if(empty($params['title'])){
//                 exit(json_encode(['status' => 0, 'msg' => '标题为必填项', 'url' => '']));
//             }
            if(empty($params['aid'])){
                exit(json_encode(['status' => 0, 'msg' => '请选择项目分类', 'url' => '']));
            }
            if($params['aid']!=49 && empty($params['son'])){
                exit(json_encode(['status' => 0, 'msg' => '请选择内容分类', 'url' => '']));
            }
            if($params['aid']==49 && empty($params['pttype'])){
                exit(json_encode(['status' => 0, 'msg' => '请选择定制类别', 'url' => '']));
            }
            if(empty($params['pic_url'])){
                exit(json_encode(['status' => 0, 'msg' => '请添加图片', 'url' => '']));
            }

            $da['parentid'] = $params['son'];
            $da['img'] = implode('|',$params['pic_url']);
            // 阿拉伯图片
            if (isset($params['pic_url_a'])) {
                $da['img_a'] = implode('|',$params['pic_url_a']);
            } else {
                $da['img_a'] = '';
            }
            switch ($params['aid']){
                case 45 :
                    $tt = 'mt';
                    break;
                case 47 :
                    $tt = 'mt';
                    break;
                case 48 :
                    $tt = 'pc';
                    break;
                case 49 :
                    $tt = 'pt';
                    break;
                default :
                    $tt = '';
                    break;
            }
            $da['type'] = $tt;
//             $da['title'] = $params['title'];
//             $da['title_e'] = $params['title_e'];
//             $da['title_a'] = $params['title_a'];
            $da['description'] = $params['description'];
            $da['description_e'] = $params['description_e'];
            $da['description_a'] = $params['description_a'];
            $da['descriptions'] = $params['descriptions'];
            $da['descriptions_e'] = $params['descriptions_e'];
            $da['descriptions_a'] = $params['descriptions_a'];


            $da['create_time'] = time();
            $da['status'] = 0;
            if($params['aid']==49){
                $da['parentid'] = 0;
                $da['cate'] = $params['pttype'];
            }

            // 20170524修改为含有新增和更新两种方法判断
            if (!isset($params['id'])) {
                // 新增
                $flag = Db::name('all_img')->insert($da);

                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('banner/addimg')]));
                }else{
                    exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
                }
            } else {
                // 更新

                $id = $params['id'];
                unset($da['create_time']);
                unset($da['status']);
                $flag = Db::name('all_img')->where(array('id' => $id))->update($da);

                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '更新成功', 'url' => url('banner/addimg')]));
                }else{
                    exit(json_encode(['status' => 0, 'msg' => '更新失败', 'url' => '']));
                }
            }




        }else{
            return $this->fetch();
        }
    }
    public function addstimg(){
        if (request()->isAjax()) {
            //新增处理
            $params = input('post.');
            if(empty($params['title'])){
                exit(json_encode(['status' => 0, 'msg' => '标题为必填项', 'url' => '']));
            }
            if(empty($params['pic_url'])){
                exit(json_encode(['status' => 0, 'msg' => '请添加图片', 'url' => '']));
            }

            $da['img'] = implode('|',$params['pic_url']);
            $da['type'] = 'pt';
            $da['title'] = $params['title'];
//             $da['title_e'] = $params['title_e'];
//             $da['title_a'] = $params['title_a'];
            $da['create_time'] = time();
            $da['status'] = 0;
            $da['parentid'] = 0;
            $da['style'] = $params['styleid'];
            $da['cate'] = $params['pttype'];


            $flag = Db::name('all_img')->insert($da);

            if ($flag) {
                exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('banner/addstimg')]));
            }else{
                exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
            }

        }else{

            $lists = Db::name('article')
                ->field("id as sid,title")
                ->where(['status'=>0,'cid'=>51])
                ->order('publishtime DESC')
                ->select();
            $this->assign('style',$lists);
            return $this->fetch();
        }
    }

    /**
     * 编辑图片管理
     *
     * @param $id
     * @return mixed
     * @date 20170524
     */
    public function editimg($id)
    {
        $where = array();
        $where['ai.id'] = $id;
        $data = Db::name('all_img ai')
            ->field("ai.title,ai.id as picid,ai.img, ai.img_a,ai.create_time,ai.parentid,a.title as ftitle,a.cid,a.id as aid,c.name as fname, 
            ai.description, ai.description_e, ai.description_a, ai.descriptions, ai.descriptions_e, ai.descriptions_a")
            ->join("yl_article a","a.id = ai.parentid")
            ->join("yl_category c","c.id = a.cid")
            ->where($where)
            ->order('ai.create_time DESC')->find();

        $this->assign('item', $data);

        return $this->fetch();
    }

    public function imglist(){

        $cat_id = input('cat_id');
        $keyword = input('keyword');
        $cat_id_n = 0;
        $keyword_n = '';
        $where = ['ai.status'=>0,'ai.parentid'=>array('gt',0)];
        if(!empty($cat_id)){
            $where = array_merge($where,['c.id'=>$cat_id]);
            $cat_id_n = $cat_id;
        }

        if(!empty($keyword)){
            $where = array_merge($where,['a.title'=>['Like',"%$keyword%"]]);
            $keyword_n = $keyword;
        }

        $this->assign('cat_id_n',$cat_id_n);
        $this->assign('keyword_n',$keyword_n);

        $list = Db::name('all_img ai')
            ->field("ai.title,ai.id as picid,ai.img,ai.create_time,ai.parentid,a.title as ftitle,a.cid,a.id,c.id,c.name as fname")
            ->join("yl_article a","a.id = ai.parentid")
            ->join("yl_category c","c.id = a.cid")
            ->where($where)
            ->order('ai.create_time DESC')
            ->paginate(7);

        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function stimglist(){

        $cat_id = input('cat_id');
        $keyword = input('keyword');
        $cat_id_n = 'a';
        $keyword_n = 'a';
        $where = ['ai.status'=>0,'ai.parentid'=>0];
        if(!empty($cat_id)){
            $where = array_merge($where,['ai.cate'=>$cat_id]);
            $cat_id_n = $cat_id;
        }

        if(!empty($keyword)){
            $where = array_merge($where,['ar.id'=>$keyword]);
            $keyword_n = $keyword;
        }

        $style = Db::name('article')
            ->field("id as sid,title")
            ->where(['status'=>0,'cid'=>51])
            ->order('publishtime DESC')
            ->select();
        $this->assign('style',$style);


        $this->assign('cat_id_n',$cat_id_n);
        $this->assign('keyword_n',$keyword_n);



        $list = Db::name('all_img ai')
            ->field("ai.title,ai.id,ai.img,ai.create_time,ai.title,ai.cate,ar.title as atitle")
            ->join('yl_article ar','ai.style=ar.id')
            ->where($where)
            ->order('ai.create_time DESC')
            ->paginate(7);

        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    public function sonlist(){
        $params = input('post.');
        $list = Db::name('article')
            ->field("title,id")
            ->where(['status'=>0,'cid'=>$params['type']])
            ->order('publishtime DESC')
            ->select();
        exit(json_encode(['status' => $params['type'], 'data' => $list, 'url' => '']));
    }

    public function deleimg($id){
        $flag = Db::name('all_img')->delete($id);
        if ($flag) {
            echo '删除成功！';
        }else{
            echo '删除失败！';
        }
    }

}
