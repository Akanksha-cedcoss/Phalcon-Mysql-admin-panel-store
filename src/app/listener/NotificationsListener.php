<?php

namespace App\Listeners;

use Phalcon\Events\Event;
use Settings;
use Roles;
use Components;
use Permissions;
use Phalcon\Di\Injectable;
use Phalcon\Acl\Adapter\Memory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Exception;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;

/**
 * event listener class
 */
class NotificationsListener extends Injectable
{

    /**
     * title optimization  event
     *
     * @param Event $event
     * @param [type] $component
     * @param [type] $data
     * @return void
     */
    public function titleOptimization(
        Event $event,
        $component,
        $data
    ) {
        $Settings = Settings::findFirst(['columns' => 'title_optimization']);
        if ($Settings->title_optimization == 'on') {
            return $data['title'] . ' ' . $data['tags'];
        } else {
            return $data['title'];
        }
    }

    /**
     * set default price event
     *
     * @param Event $event
     * @param [type] $component
     * @param [type] $price
     * @return void
     */
    public function defaultPrice(
        Event $event,
        $component,
        $price
    ) {

        if (empty($price)) {
            $Settings = Settings::findFirst(['columns' => 'price']);
            return $Settings->price;
        } else {
            return $price;
        }
    }

    /**
     * set default stock event
     *
     * @param Event $event
     * @param [type] $component
     * @param [type] $stock
     * @return void
     */
    public function defaultStock(
        Event $event,
        $component,
        $stock
    ) {
        if (empty($stock)) {
            $Settings = Settings::findFirst(['columns' => 'stock']);
            return $Settings->stock;
        } else {
            return $stock;
        }
    }

    /**
     * set default zip code event
     *
     * @param Event $event
     * @param [type] $component
     * @param [type] $zip
     * @return void
     */
    public function defaultZipCode(
        Event $event,
        $component,
        $zip
    ) {
        // return 'test';
        if (empty($zip)) {
            $Settings = Settings::findFirst(['columns' => 'zipcode']);
            return $Settings->zipcode;
        } else {
            return $zip;
        }
    }

    /**
     * before loading url event
     *
     * @param Event $event
     * @param \Phalcon\Mvc\Application $application
     * @return void
     */
    public function beforeHandleRequest(Event $event, \Phalcon\Mvc\Application $application)
    {
        $bearer = $application->request->get("bearer");
        $session = $this->di->get('session');
        $route = $this->router->getControllerName() ?? 'index';
        $action = $this->router->getActionName() ?? 'index';
        if ($bearer) {

            $key = "example_key";
            $parser = new Parser();
            $now = $this->di->get('dateTime');
            $expires = $now->getTimestamp();
            $token = $parser->parse($bearer);
            try {
                $validator = new Validator($token, 100);
                $validator->validateExpiration($expires);
                $jwt = JWT::decode($bearer, new Key($key, 'HS256'));
                $role = $jwt->sub;
                $name = $jwt->nam;
                $aclFile = APP_PATH . '/storage/security/acl.cache';
                if (true === is_file($aclFile)) {
                    $acl = unserialize(file_get_contents($aclFile));
                    if (true !== $acl->isAllowed($role, $route, $action)) {
                        echo "<h1>" . $this->locale->_(
                            'ad_name',
                            [
                                'name' => $name,
                            ]
                        ) . "</h1>";
                        echo 'Your role is = ' . $jwt->sub;
                        echo $this->tag->linkTo([
                            "index/index",
                            "Return to Home Page",
                        ]);
                        die();
                    }
                } else {
                    $this->di->get('EventsManager')->fire('notifications:getPermissions', $this);
                }
            } catch (Exception $e) {
                echo "<h1>" . $this->locale->_('tf') . "</h1>";
                echo $this->tag->linkTo([
                    "index/index",
                    "Return to Home Page",
                ]);
                die;
            }
        } else {
            if (isset($session->user)) {
                if ($session->user['role'] !== 'admin') {
                    echo print_r($this->session->user['role']);
                    echo "<h1>" . $this->locale->_('bnf') . "</h1>";
                    die;
                }
            } elseif (($route == 'login' or $route == 'index') and ($action == 'index')) {
            } else {
                echo "<h1>" . $this->locale->_('bnf') . "</h1>";
                die;
            }
        }
    }

    /**
     * get user permissions from db event
     *
     * @param Event $event
     * @param [type] $component
     * @return void
     */
    public function getPermissions(
        Event $event,
        $component
    ) {
        $aclFile = APP_PATH . '/storage/security/acl.cache';
        if (true !== is_file($aclFile)) {
            $acl = new Memory();
        } else {
            $acl = unserialize(
                file_get_contents($aclFile)
            );
        }
        $roles = Roles::find();
        $components = Components::find();
        $permissions = Permissions::find();
        foreach ($roles as $r) {
            $acl->addRole($r->role);
        }
        foreach ($components as $com) {
            $action = explode(',', $com->actions);
            $acl->addComponent(
                $com->name,
                $action
            );
        }
        $act->allow('admin', '*', '*');
        foreach ($permissions as $per) {
            $acl->allow($per->role, $per->component, $per->action);
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        }
        file_put_contents(
            $aclFile,
            serialize($acl)
        );
    }

    /**
     * generate token
     *
     * @param Event $event
     * @param [type] $component
     * @param [type] $data
     * @return void
     */
    public function generateToken(
        Event $event,
        $component,
        $data
    ) {
        $key = "example_key";
        $now = $this->di->get('dateTime');
        // $timestap = $now->getTimestamp();
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $now->getTimeStamp(),
            "nbf" => $now->modify("-1 minute")->getTimeStamp(),
            "exp" => $now->modify("+1 day")->getTimeStamp(),
            "nam" => $data['name'],
            "sub" => $data['role']
        );
        return JWT::encode($payload, $key, 'HS256');
    }
}
