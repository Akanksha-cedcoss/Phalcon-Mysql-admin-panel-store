<?php

use Phalcon\Mvc\Controller;

class UserController extends Controller
{
    public function indexAction()
    {
        $query = $this
            ->modelsManager
            ->createQuery(
                'SELECT Roles.role as role, Users.* FROM Users join Roles on Users.role_id=Roles.id'
            );
        $this->view->users = Users::find();
    }
    public function addAction($id = null)
    {
        $locale = new App\Components\Locale();
        if (is_null($id)) {
            $user = new Users();
            $this->view->head = $this->di->get('locale')->_('ad_user');
        } else {
            $user = Users::findFirst($id);
            $this->view->user = $user;
            $this->view->head = $this->di->get('locale')->_('edit_user');
        }
        $this->view->roles = Roles::find();
        /**
         * add new Uer
         */
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $name = $escaper->sanitize($this->request->getPost('name'));
            $email = $escaper->sanitize($this->request->getPost('email'));
            $role = $escaper->sanitize($this->request->getPost('role'));

            try {
                $user->assign(
                    array(
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                    ),
                    [
                        'name',
                        'email',
                        'role'
                    ]
                );
                if ($user->save()) {
                    $this->flash->success('user Added successFully .Email Token : ' .
                    $this->di->get('EventsManager')->fire('notifications:generateToken', $this, array('name' => $name, 'role' => $role)));
                    $this->signupLogger->info('New user joined with user_id = ' . $user->id);
                    return;
                } else {
                    $this->flash->error("One or More field is empty.");
                    $this->signupLogger->error($user->getMessages());
                }
            } catch (Exception $e) {
                $this->flash->error($e);
                $this->signupLogger->alert("This E-mail is already registered with us.");
            }
        }
    }

    public function deleteAction($id)
    {
        $user = Users::findFirst($id)->delete();
        $this->response->redirect("product/index");
    }
}
