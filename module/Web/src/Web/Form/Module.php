<?php
namespace Web\Form;
use Zend\Form\Form;
use Zend\Form\Element;

class Module extends Form
{
    public function __construct()
    {
        parent::__construct();
        //$this->setMethod('post');

        $id = new Element\Hidden('id_perm_module');
        $this->add($id);

        $module = new Element('module');
        $module->setLabel('Module');
        $module->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($module);


        $component = new Element('component');
        $component->setLabel('Component');
        $component->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($component);

        $name = new Element('menu_name');
        $name->setLabel('Menu name');
        $name->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($name);

        $order = new Element('menu_order');
        $order->setLabel('Menu order');
        $order->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($order);

        $send = new Element('submit');
        $send->setLabel('Submit');
        $send->setValue('Submit');
        $send->setAttributes(array(
            'type'  => 'submit'
        ));

        $this->add($send);

        $delete = new Element('delete');
        $delete->setLabel('Delete');
        $delete->setValue('Delete');
        $delete->setAttributes(array(
            'type'  => 'submit'
        ));
        $this->add($delete);

        $csrf = new Element\Csrf('csrf');
        $this->add($csrf);

    }
}
