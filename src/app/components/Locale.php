<?php

namespace App\Components;

use Phalcon\Di\Injectable;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

class Locale extends Injectable
{
    /**
     * @return NativeArray
     */
    public function getTranslator(): NativeArray
    {
        $lan = $this->request->get("lan");
        if ($lan) {
            $messages = [];
            $translationFile = APP_PATH . '/languages/' . $lan . '.php';
            if (true !== file_exists($translationFile)) {
                $translationFile = APP_PATH . '/languages/en.php';
            }
        } else {
            $translationFile = APP_PATH . '/languages/en.php';
        }
        require $translationFile;

        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $cache = $this->cache;
        if ($cache->has($lan.'msg')) {
            $messages = (array)$cache->get($lan.'msg');
        } else {
            $cache->set($lan.'msg', $messages);
        }
        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        );
    }
}
