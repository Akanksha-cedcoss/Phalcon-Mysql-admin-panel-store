<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use App\components\MyValidation as validation;


class LoginController extends Controller
{

    /**
     * log in user
     *
     * @return void
     */
    public function indexAction()
    {
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $email = $escaper->sanitize($this->request->getPost('email'));
            $password = $escaper->sanitize($this->request->getPost('password'));
            if (!empty($email) and !empty($password)) {
                $user = Users::findFirst("email='" . $email . "' and password = '" . $password . "'");
                if ($user) {
                    if ($user->role == 'admin') {
                        $session = $this->di->get('session');
                        $session->set('user', array('name' => $user->name, 'role' => $user->role));
                        $this->loginLogger->info('User logged in.user_id = ' . $user->id);
                        $this->response->redirect("index/index");
                    } else {
                        $this->loginLogger->warning('unknown user try to login user_id = ' . $user->id);
                        $this->flash->error("Only admin can login");
                    }
                } else {
                    $this->loginLogger->alert('Incorrect credentials entered by user.');
                    $this->flash->error("E-mail or password is wrong.");
                }
            } else {
                $this->flash->info("One or more field is empty.");
            }
        }
    }


    /**
     * logout user
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->di->get('session')->destroy();
        $this->cookies->get('remember-me')->delete();
        $this->response->redirect("index/index");
    }
}
