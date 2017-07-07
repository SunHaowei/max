<?php
/**
 * 单页控制器
 */
namespace app\admin\controller;

use think\Db;

class Contact extends Common{
	
    public function index(){
        $list = $this->model()->order('cuid DESC')->paginate(20);
        $this->assign('page',$list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 公告管理界面
     * @return 
     */
	public function annindex()
    {
        $list = Db::name(self::$_table)->where(['status' => 0,'type' => 2])->order('id DESC')->paginate(20);
        $this->assign('page',$list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }

     /**
     * 添加联系人
     *
     */
    public function add(){
        //显示页面
        if (request()->isGet()) {
        	if (input('?type') && input('param.type') == 2){
        		return $this->fetch('annadd');
        	}
            return $this->fetch();
        } elseif (request()->isPost()) {
            $params = input('post.');

            if ($params['cuid'] == '') {
                //新增
                $da['name'] = $params['name'];
                $da['name_e'] = $params['name_e'];
                $da['name_a'] = $params['name_a'];
                $da['phone'] = $params['phone'];
                $da['email'] = $params['email'];
                $da['address'] = $params['address'];
                $da['address_e'] = $params['address_e'];
                $da['address_a'] = $params['address_a'];
                $da['create_time'] = date('Y-m-d H:i:s',time());
                $da['status'] = 1;
                
                $flag = $this->model()->insert($da);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '添加成功', 'url' => url('contact/index')]));
                } else {
                    exit(json_encode(['status' => 0, 'msg' => '添加失败', 'url' => '']));
                }
            } else {
                //更新
                $cuid = $params['cuid'];
                
                $da['region'] = $params['region'];
                $da['region_e'] = $params['region_e'];
                $da['region_a'] = $params['region_a'];
                $da['name'] = $params['name'];
                $da['name_e'] = $params['name_e'];
                $da['name_a'] = $params['name_a'];
                $da['phone'] = $params['phone'];
                $da['email'] = $params['email'];
                $da['address'] = $params['address'];
                $da['address_e'] = $params['address_e'];
                $da['address_a'] = $params['address_a'];
                $da['create_time'] = date('Y-m-d H:i:s',time());
                $da['status'] = 1;
                $flag = $this->model()->where(['cuid' => $cuid])->update($da);
                if ($flag) {
                    exit(json_encode(['status' => 1, 'msg' => '更新成功', 'url' => url('contact/index')]));
                } else {
                    exit(json_encode(['status' => 0, 'msg' => '更新失败，请稍后重试', 'url' => '']));
                }
            }
        }
    }

    /*
     * 更新友链信息
     *
     * $id 资源id
     */
    public function edit() {
    	$cuid = input('param.cuid/d',0);
        $data = $this->model()->where(['cuid' => $cuid])->find();
        $this->assign('item',$data);
		return $this->fetch();
        
    }

    /**
     * 删除友链、公告
     * @return [type] [description]
     */
    public function dele() {
        $id = input('param.cuid/d',0);
        //逻辑删除
        $flag = $this->model()->where(['cuid' => $id])->update(['status' => 0]);
        if ($flag) {
            echo '删除成功';
        } else {
            echo '删除失败';
        }
    }

    protected function model(){
        return Db::name('contact');
    }

}
