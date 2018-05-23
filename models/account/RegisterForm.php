<?php

namespace app\models\account;

use app\models\Config;
use Yii;
use app\models\Model;
use app\models\User;

class RegisterForm extends Model
{

    public $name;
    public $email;
    public $password;
    public $registerToken;
    public $verifyCode;

    public function rules()
    {

        return [
            [['name', 'email', 'verifyCode', 'registerToken'], 'filter', 'filter' => 'trim'],
            ['name', 'required', 'message' => '用户昵称不可以为空'],
            ['name', 'string', 'min' => 2, 'max' => 50, 'message' => '用户昵称至少包含2个字符，最多50个字符'],
            ['email', 'required', 'message' => '登录邮箱不能为空'],
            ['email', 'email','message' => '邮箱格式不合法'],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => '该登录邮箱已存在'],
            ['password', 'required', 'message' => '密码不可以为空'],
            ['password', 'string', 'min' => 6, 'tooShort' => '密码至少填写6位'],
            ['verifyCode', 'required', 'message' => '验证码不能为空'],
            ['verifyCode', 'captcha', 'captchaAction' => 'account/captcha'],
            ['registerToken', 'required', 'message' => '注册口令不能为空'],
            ['registerToken', 'validateToken'],
        ];
    }

    public function validateToken($attribute)
    {

        if (!$this->hasErrors()) {

            $config = Config::findOne(['type' => 'app']);

            $token  = $config->getField('register_token');

            if (!$token || $token != $this->registerToken) {
                $this->addError($attribute, '注册口令错误');
            }
        }
    }

    public function register()
    {

        if (!$this->validate()) {
            return null;
        }

        $user = new User();

        $user->name   = $this->name;
        $user->email  = $this->email;
        $user->ip     = Yii::$app->request->userIP;

        $user->setPassword($this->password);
        $user->generateAuthKey();

        // 获取IP物理地址
//        $ip_address = get_ip_address();
//        $user->address = $ip_address['country'] . ' ' . $ip_address['province'] .' ' . $ip_address['city'];

        if($user->save()){

            Yii::$app->user->login($user);

            return $user;

        }

        return null;
    }

}