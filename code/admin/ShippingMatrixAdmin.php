<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/04/2016
 * Time: 10:48 AM
 */
class ShippingMatrixAdmin extends ModelAdmin
{
    public static $managed_models = array('ShippingMatrix', 'StoreShippingConfig');
    public static $url_segment = 'shipping-matrix-admin';
    public static $menu_title = 'Shipping';

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        foreach ($form->Fields()->dataFields() as $field) {
            if (method_exists($field, 'getModelClass') && singleton($field->getModelClass())->ClassName == 'ShippingMatrix') {
                $object = DataObject::get_one('ShippingMatrix');
                if($object->exists()){

                    $fields = $object->getCMSFields();

                    $actions = $object->getCMSActions();

                    $validator = null;
                    if ($object->hasMethod("getCMSValidator")) {
                        $validator = $object->getCMSValidator();
                    }

                    $form = CMSForm::create($this, 'ShippingMatrix', $fields, $actions, $validator);

                    $form->addExtraClass('cms-content center cms-edit-form');
                    $form->setAttribute('data-pjax-fragment', 'CurrentForm');
                    $form->setHTMLID('Form_EditForm');
                    $form->setResponseNegotiator($this->getResponseNegotiator());

                    $form->loadDataFrom($object);
                }
            }
        }

        $this->extend('updateEditForm', $form);

        return $form;
    }
}