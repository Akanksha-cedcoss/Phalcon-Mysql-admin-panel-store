<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * product list view
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->products = Products::find();
    }
    /**
     * add new product
     *
     * @return void
     */
    public function addAction($product_id = null)
    {
        $locale = new App\Components\Locale();
        if (is_null($product_id)) {
            $product = new Products();
            $this->view->head = $this->di->get('locale')->_('ad_product');
        } else {
            $product = Products::findFirst($product_id);
            $this->view->product = $product;
            $this->view->head = $this->di->get('locale')->_('edit_product');
        }
        if ($_POST) {
            $escaper = new \App\Components\MyEscaper();
            $name = $escaper->sanitize($this->request->getPost('product_name'));
            $description = $escaper->sanitize($this->request->getPost('product_description'));
            $tags = $escaper->sanitize($this->request->getPost('tags'));
            $price = $escaper->sanitize($this->request->getPost('price'));
            $stock = $escaper->sanitize($this->request->getPost('stock'));
            $name = $this->di->get('EventsManager')->fire(
                'notifications:titleOptimization',
                $this,
                array('title' => $name, 'tags' => $tags)
            );
            $price = $this->di->get('EventsManager')->fire('notifications:defaultPrice', $this, $price);
            $stock = $this->di->get('EventsManager')->fire('notifications:defaultStock', $this, $stock);

            // $product = new Products();
            try {
                $product->assign(
                    array(
                        'name' => $name,
                        'description' => $description,
                        'tags' => $tags,
                        'price' => $price,
                        'stock' => $stock
                    ),
                    [
                        'name',
                        'description',
                        'tags',
                        'price',
                        'stock'
                    ]
                );
                if ($product->save()) {
                    $this->flash->success("Product Added/Updated successFully");
                    return;
                } else {
                    $this->flash->error("One or More field is empty.");
                }
            } catch (Exception $e) {
                $this->flash->error($e);
            }
        }
    }
    
    public function deleteAction($product_id)
    {
        $product = Products::findFirst($product_id);
        $product->delete();
        $this->response->redirect("product/index");
    }
}
