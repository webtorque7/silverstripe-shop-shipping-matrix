<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/04/2016
 * Time: 10:48 AM
 */
class ShippingMatrixAdmin extends ModelAdmin
{
    public static $managed_models = array('ShippingMatrixConfig', 'StoreShippingMatrixConfig');
    public static $url_segment = 'shipping-matrix-admin';
    public static $menu_title = 'Shipping';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        foreach ($form->Fields()->dataFields() as $field) {
            if (method_exists($field, 'getModelClass') && singleton($field->getModelClass())->ClassName == 'ShippingMatrixConfig') {
                $object = DataObject::get_one('ShippingMatrixConfig');
                if ($object && $object->exists()) {
                    $fields = $object->getCMSFields();
                    $actions = $object->getCMSActions();
                    $form = CMSForm::create($this, 'ShippingMatrixConfig', $fields, $actions);

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

    public function getManagedModels()
    {
        $models = $this->stat('managed_models');
        if (is_string($models)) {
            $models = array($models);
        }
        if (!count($models)) {
            user_error(
                'ModelAdmin::getManagedModels():
				You need to specify at least one DataObject subclass in public static $managed_models.
				Make sure that this property is defined, and that its visibility is set to "public"',
                E_USER_ERROR
            );
        }

        foreach ($models as $k => $v) {
            if (is_numeric($k)) {
                $models[$v] = array('title' => singleton($v)->i18n_singular_name());
                unset($models[$k]);
            }
        }

        if (!DataObject::get_one('StoreShippingMatrixConfig')) {
            unset($models['StoreShippingMatrixConfig']);
        } else {
            unset($models['ShippingMatrixConfig']);
        }

        return $models;
    }
}