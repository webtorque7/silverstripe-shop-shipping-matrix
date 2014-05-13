<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 07/11/13
 * Time: 15:40
 */

class ShippingMatrixDeliveryFormExtension extends DataExtension{
	public function updateFields($fields){

		/*$shippingExtraArray = array();
		$shippingExtras = DomesticShippingExtra::get();

		foreach($shippingExtras as $shippingExtra){
			$shippingExtraArray[$shippingExtra->ID] = $shippingExtra->Title;
		}

		$fields->push(CompositeField::create(
			HeaderField::create('DeliveryHeading', 'Delivery Options', 3),
			CompositeField::create(
				CompositeField::create(
					$internationalShippingCarrierField = OptionsetField::create('InternationalShippingCarrier', '', InternationalShippingCarrier::get()->map('ID', 'Title'))->addExtraClass('delivery-option')
				)->addExtraClass('international'),
				CompositeField::create(
					$islandField = OptionsetField::create('Island', 'Island', Config::inst()->get('ShippingMatrixModifier', 'islands'))->setHasEmptyDefault(true)->addExtraClass('delivery-option'),
					$domesticShippingExtraField = new CustomCheckboxSetField('DomesticShippingExtra', 'Shipping Additions', DomesticShippingExtra::get()->map('ID','Title'))
				)->addExtraClass('domestic')
			)->addExtraClass('col-content')
		)->addExtraClass('col col-3'));

		$domesticShippingExtraField->addExtraClass('delivery-option');

		if(($order = ShoppingCart::curr()) && ($modifier = $order->getModifier('ShippingMatrixModifier'))){
			if($modifier->IsInternational){
				$internationalShippingCarrierField->setValue($modifier->InternationalShippingCarrierID);
			} else {
				$addOnArray = array();

//				foreach($modifier->DomesticShippingExtras() as $itemValues){
//					$addOnArray[$itemValues->Title] = $itemValues->ID;
//				}

				foreach($modifier->DomesticShippingExtras() as $itemValues){
					array_push($addOnArray, $itemValues->ID);
				}

				$islandField->setValue($modifier->Island);

				//Debug::dump($addOnArray);exit;

				$domesticShippingExtraField->setValue($addOnArray);
			}
		}*/

	}

	public function deliveryValidation($data, Form $form) {

		/*$valid = true;

		if ($data['DeliveryCountry'] == 'NZ') {
			if(empty($data['Island'])){
				$form->addErrorMessage('Island', 'Please select an Island', 'bad');
				$valid = false;
			}
		} else {
			if(empty($data['InternationalShippingCarrier'])){
				$form->addErrorMessage('InternationalShippingCarrier', 'Please select a courier type', 'bad');
				$valid = false;
			}
		}*/

		return true;
	}

	public function onBeforeWriteOrder($data, Order $order, Form $form = null) {
		if($modifier = $order->getModifier('ShippingMatrixModifier')){
			$modifier->populate($data, $order);
		}
	}

	public function updateValidation(&$fields, &$messages) {

		/*$fields['Courier'] = array(
			'required' => true
		);

		$messages['Courier'] = array(
			'required' => 'Please select a Courier'
		);*/
	}
}


class CustomCheckboxSetField extends CheckboxSetField{
	public function performReadonlyTransformation() {
		$values = '';
		$data = array();

		$items = $this->value;

		if(!empty($this->source)) {
			foreach($this->source as $id => $title) {
				//if(is_object($source)) {
				$sourceTitles[$id] = $title;
				//}
			}
		}

		if($items) {
			// Items is a DO Set
			if($items instanceof SS_List) {
				foreach($items as $item) {
					$data[] = $item->Title;
				}
				if($data) $values = implode(', ', $data);

				// Items is an array or single piece of string (including comma seperated string)
			} else {
				if(!is_array($items)) {
					$items = preg_split('/ *, */', trim($items));
				}

				foreach($items as $item) {
					if(is_array($item)) {
						$data[] = $item['Title'];
					} elseif(is_array($this->source) && !empty($this->source[$item])) {
						$data[] = $this->source[$item];
					} elseif (!empty($sourceTitles) && !empty($sourceTitles[$item])) {
						$data[] = $sourceTitles[$item];
					} else {
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