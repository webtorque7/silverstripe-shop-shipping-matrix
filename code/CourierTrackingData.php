<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/09/14
 * Time: 10:29 AM
 */

class CourierTrackingData extends DataObject{
	private static $db = array(
		'invoice_packing_slip_no' => 'Varchar(10)',
		'pbt_account_no' => 'Varchar(100)',
		'pbt_couriers_account_no' => 'Varchar(100)',
		'date' => 'Date',
		'receivers_account_no' => 'Varchar(100)',
		'receivers_name' => 'Varchar(100)',
		'receivers_address_1' => 'Varchar(100)',
		'receivers_address_2' => 'Varchar(100)',
		'receivers_address_3' => 'Varchar(100)',
		'no_of_packages' => 'Varchar(100)',
		'descriptions_of_packages' => 'Varchar(100)',
		'Weight' => 'Varchar(100)',
		'Cubic' => 'Varchar(100)',
		'product_code' => 'Varchar(100)',
		'comments' => 'Varchar(100)',
		'receivers_phone' => 'Varchar(100)',
		'receivers_contact_name' => 'Varchar(100)',
		'receivers_email' => 'Varchar(100)'
	);

	private static $belongs_many_many = array(
		'Orders' => 'Order'
	);

	private static $summary_fields = array(
		'invoice_packing_slip_no' => 'Tracking ID',
		'receivers_name' => 'Receiver Name',
		'date' => 'Date'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Orders');
		return $fields;
	}
}

class CourierTrackingAdmin extends ModelAdmin{
	private static $managed_models = array('CourierTrackingData');
	private static $url_segment = 'courier-tracking-data';
	private static $menu_title = 'Courier Tracking';

	public function getExportFields() {
		$parent_fields = parent::getExportFields();
		$add_fields = array(
			'invoice_packing_slip_no' => 'invoice_packing_slip_no',
			'pbt_account_no' => 'pbt_account_no',
			'pbt_couriers_account_no' => 'pbt_couriers_account_no',
			'date' => 'date',
			'receivers_account_no' => 'receivers_account_no',
			'receivers_name' => 'receivers_name',
			'receivers_address_1' => 'receivers_address_1',
			'receivers_address_2' => 'receivers_address_2',
			'receivers_address_3' => 'receivers_address_3',
			'no_of_packages' => 'no_of_packages',
			'descriptions_of_packages' => 'descriptions_of_packages',
			'Weight' => 'Weight',
			'Cubic' => 'Cubic',
			'product_code' => 'product_code',
			'comments' => 'comments',
			'receivers_phone' => 'receivers_phone',
			'receivers_contact_name' => 'receivers_contact_name',
			'receivers_email' => 'receivers_email'
		);
		$fields = array_merge ( $parent_fields, $add_fields);
		return $fields;
	}
}
