<?php

use Phalcon\Mvc\Controller;

class SettingController extends Controller
{
    /**
     * setting for title optimization, default price and stock
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->setting = Settings::findFirst();
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $op = $escaper->sanitize($this->request->getPost('title'));
            $price = $escaper->sanitize($this->request->getPost('price'));
            $stock = $escaper->sanitize($this->request->getPost('stock'));
            $zip = $escaper->sanitize($this->request->getPost('zipcode'));
            $setting = Settings::findFirst();
            try {
                $setting->assign(
                    array(
                        'title_optimization' => $op,
                        'price' => $price,
                        'zipcode' => $zip,
                        'stock' => $stock,
                    ),
                    [
                        'title_optimization',
                        'price',
                        'zipcode',
                        'stock',
                    ]
                );
                if ($setting->save()) {
                    $this->flash->success("Settings updated successFully");
                    return;
                } else {
                    $this->flash->error("One or More field is empty.");
                }
            } catch (Exception $e) {
                $this->flash->error(json_encode($e));
            }
        }
    }
    /**
     * add new user role
     *
     * @return void
     */
    public function addRoleAction()
    {
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $newRole = $escaper->sanitize($this->request->getPost('role'));
            $role = new Roles();
            try {
                $role->assign(
                    array(
                        'role' => $newRole,
                    ),
                    [
                        'role',
                    ]
                );
                if ($role->save()) {
                    $this->flash->success("New role added successFully");
                    return;
                } else {
                    $this->flash->error("One or More field is empty.");
                }
            } catch (Exception $e) {
                $this->flash->error(json_encode($e));
            }
        }
    }
    /**
     * add new component
     *
     * @return void
     */
    public function addComponentAction()
    {
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $newComponent = $escaper->sanitize($this->request->getPost('component'));
            $newAction = $escaper->sanitize($this->request->getPost('action'));
            $component = new Components();
            try {
                $component->assign(
                    array(
                        'name' => $newComponent,
                        'actions' => $newAction
                    ),
                    [
                        'name',
                        'actions'
                    ]
                );
                if ($component->save()) {
                    $this->flash->success("New Component added successFully");
                    return;
                } else {
                    $this->flash->error("One or More field is empty.");
                }
            } catch (Exception $e) {
                $this->flash->error(json_encode($e));
            }
        }
    }
    /**
     * add new acl permission
     *
     * @return void
     */
    public function addAclAction()
    {
        $this->view->roles = Roles::find();
        $this->view->components = Components::find();
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $role = $escaper->sanitize($this->request->getPost('role'));
            $roles = Permissions::find("role='".$role."'");
            $roles->delete();
            $permission = new Permissions();
            try {
                if (!empty($_POST['check_list'])) {
                    foreach ($_POST['check_list'] as $check) {
                        $data = explode("->", $check);
                        $permission = new Permissions();
                        $permission->assign(
                            array(
                                'role' => $role,
                                'component' => $data[0],
                                'action' => $data[1]
                            ),
                            [
                                'role',
                                'component',
                                'action'
                            ]
                        );
                        $permission->save();
                    }
                    $this->di->get('EventsManager')->fire('notifications:getPermissions', $this);
                    $this->flash->success("New ACL Permission added successFully");
                }
            } catch (Exception $e) {
                $this->flash->error(json_encode($e));
            }
        }
    }
}
