<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 07/11/13
 * Time: 15:40
 */

class ShippingMatrixDeliveryFormExtension extends DataExtension{

	public function updateFields($fields){}

	public function updateValidation($fields, $messages){}

	public function onBeforeWriteOrder($data, Order $order, Form $form = null) {
		if($modifier = $order->getModifier('ShippingMatrixModifier')){
			$modifier->populate($data, $order);
		}
	}

	public function deliveryValidation($data, Form $form) {
		return true;
	}
}


class CustomCheckboxSetField extends CheckboxSetField{

	public function performReadonlyTransformation() {
		$values = '';
		$data = array();
		$items = $this->value;

		if(!empty($this->source)) {
			foreach($this->source as $id => $title) {
				$sourceTitles[$id] = $title;
			}
		}

		if($items) {
			if($items instanceof SS_List) {
				foreach($items as $item) {
					$data[] = $item->Title;
				}
				if($data) $values = implode(', ', $data);
			}
			else {
				if(!is_array($items)) {
					$items = preg_split('/ *, */', trim($items));
				}
				foreach($items as $item) {
					if(is_array($item)) {
						$data[] = $item['Title'];
					}
					elseif(is_array($this->source) && !empty($this->source[$item])) {
						$data[] = $this->source[$item];
					}
					elseif (!empty($sourceTitles) && !empty($sourceTitles[$item])) {
						$data[] = $sourceTitles[$item];
					}
					else {
						$data[] = $item;
					}
				}
				$values = implode(', ', $data);
			}
		}
		$field = $this->castedCopy('ReadonlyField');
		$field->setValue($values);
		return $field;
	}
}