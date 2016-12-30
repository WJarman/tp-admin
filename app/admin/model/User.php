<?php
namespace app\admin\model;

use think\Config;
use think\Db;
use think\Loader;
use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
	use SoftDelete;
    protected $deleteTime = 'delete_time';

	/**
	 *  用户登录
	 */
	public function login(array $data)
	{
		$code = 1;
		$msg = '';
		//使用验证类demo
		$userValidate = validate('User');
		if(!$userValidate->scene('login')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
		if( $code != 1 ) {
			return info($msg, $code);
		}
		$map = [
			'mobile' => $data['mobile']
		];
		$userRow = $this->where($map)->find();
		if( empty($userRow) ) {
			return info(lang('You entered the account or password is incorrect, please again'), 5001);
		}
		$md_password = mduser( $data['password'] );
		if( $userRow['password'] != $md_password ) {
			return info(lang('You entered the account or password is incorrect, please again'), 5001);
		}
		return info(lang('Login succeed'), $code, '', $userRow);
	}


	public function getList( $request )
	{
		//先致空
		$request = array();
		$data = Db::name('user')->order('create_time desc')->where( $request )->select();
		return $this->_fmtData( $data );
	}


	public function add(array $data = [])
	{	
		$user = User::get(['mobile' => $data['mobile']]);
		if (!empty($user)) {
			return info(lang('Moblie already exists'), 0);
		}
		if($data['password2'] != $data['password']){
            return info(lang('The password is not the same twice'), 0);
        }
		unset($data['password2']);
		$data['password'] = mduser($data['password']);
		$data['create_time'] = time();
		$this->allowField(true)->save($data);
		if($this->id > 0){
            return info(lang('Add succeed'), 1);
        }else{
            return info(lang('Add failed') ,0);
        }
	}

	public function edit(array $data = [])
	{	
		$user = User::get(['mobile' => $data['mobile']]);
		if (!empty($user)) {
			return info(lang('Moblie already exists'), 0);
		}

		if($data['password2'] != $data['password']){
            return info(lang('The two passwords No match!'),0);
        }
        $data['update_time'] = time();

		$data['password'] = mduser($data['password']);
		$res = $this->allowField(true)->save($data,['id'=>$data['id']]);
		if($res == 1){
            return info(lang('Edit succeed'), 1);
        }else{
            return info(lang('Edit failed'), 0);
        }
	}

	public function findUserById($id)
	{
		return User::get($id);
	}

	public function deleteById($id)
	{
		$result = User::destroy($id);
		if ($result > 0) {
            return info(lang('Delete succeed'), 1);
        }   
	}

	//格式化数据
	private function _fmtData( $data )
	{
		if(empty($data)) {
			return $data;
		}

		foreach ($data as $key => $value) {
			$data[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
			$data[$key]['status'] = $value['status'] == 1 ? lang('Start') : lang('Off');
		}

		return $data;
	}

}
