<?php 

namespace Qs\token;

use think\Db;
use Qs\log\Log;

class Token {

    // 用户信息表
    private $userTable = 'qs_users';
    // 无操作的返回信息
    protected $data = ['status'=>0,'msg'=>'无数据'];
    // 日记实例
    protected $log;

    public function __construct(){
        // TODO: 初始化
        $this->log = Log::instance();
    }


    /**
     * Auth: Qs
     * Name: 生成token、token_time
     * Note: 
     * Time: 2018/5/28 18:10 
     **/
    private function create_token( $userId, $token, $time = 3600 ){
        $data['token_time'] = time() + $time;
        $data['token'] = md5($userId . $token . $data['token_time']);
        $data['user_id'] = $userId;
        return $data;
    }

    /**
     * Auth: Qs
     * Name: 生成refresh_token、refresh_time
     * Note: 
     * Time: 2018/5/28 18:10 
     **/
    private function create_refresh ( $userId, $token, $time = 604800 ) {
        $data['refresh_time'] = time() + $time;
        $data['refresh_token'] = md5( $userId . $token . $data['refresh_time'] );
        $data['user_id'] = $userId;
        return $data;
    }

    /**
     * Auth: Qs
     * Name: 更新该用户的token、refresh信息并返回数据
     * Note: 
     * Time: 2018/5/28 18:11 
     **/
    public function token ( $userId ) {
        // 获取用户信息
        $userTmp = Db::table($this->userTable)->where(['user_id'=>$userId])->find();
        // 判断是否有该用户
        if ( !$userTmp ) return $this->data;
        // 生成token
        $data = $this->create_token($userTmp['user_id'], $userTmp['password']);
        // 生成refresh token
        $data = array_merge( $data, $this->create_refresh( $userTmp['user_id'], $data['token'] ) );

        try {
            // 更新用户信息
            Db::table($this->userTable)->where(['user_id'=>$userId])->update($data);
            $data['status'] = 1;
            $data['msg'] = '操作成功';
            $this->data = array_merge($this->data, $data);
        } catch (\Exception $e) {
            $this->data['status'] = 0;
            $this->data['msg'] = $e->getMessage();
            $this->log->save($e->getMessage(),'token');
        }
        return $this->data;
    }

    /**
     * Auth: Qs
     * Name: 验证token
     * Note: 
     * Time: 2018/5/28 18:12 
     **/
    public function auth_token ( $userId, $token ) {
        $userTmp = Db::table($this->userTable)->where([
            ['user_id','=', $userId],
            ['token', '=', $token],
            ['token_time','>=',time()]
        ])->find();
        // 判断该用户token是否正确
        if ( $userTmp ) return true;
        return false;
    }

    /**
     * Auth: Qs
     * Name: 验证refresh信息并生成新的token、refresh信息
     * Note: 
     * Time: 2018/5/28 18:12 
     **/
    public function auth_refresh ( $userId, $refresh ) {
        $userTmp = Db::table($this->userTable)->where([
            ['user_id','=', $userId],
            ['refresh_token', '=', $refresh],
            ['refresh_time','>=',time()]
        ])->find();
        $this->data = ['status'=>0,'msg'=>'无数据'];
        if ( $userTmp ) $this->token( $userId );
        return $this->data;
    }

}