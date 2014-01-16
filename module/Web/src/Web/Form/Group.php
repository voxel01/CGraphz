<?php
namespace Web\Form;
use Zend\Form\Form;
use Zend\Form\Element;

class Group extends Form
{
    public function __construct()
    {
        parent::__construct();
        //$this->setMethod('post');

        $id = new Element\Hidden('id_auth_group');
        $this->add($id);

        $group = new Element('group');
        $group->setLabel('Group');
        $group->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($group);


        $description = new Element('group_description');
        $description->setLabel('Group Description');
        $description->setAttributes(array(
            'type'  => 'text'
        ));
        $this->add($description);

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
