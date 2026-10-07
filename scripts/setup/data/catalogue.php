<?php
/**
 * The store's catalogue structure, in the order it appears on the site.
 *
 * Category codes are the first part of each SKU: code, YYMM, 3-digit number,
 * for example HB-2609-001.
 *
 * @package clo-setup
 */

return array(
	'categories'        => array(
		array(
			'name' => 'Health & Beauty',
			'slug' => 'health-beauty',
			'code' => 'HB',
		),
		array(
			'name' => 'Household',
			'slug' => 'household',
			'code' => 'HH',
		),
		array(
			'name' => 'Electronics',
			'slug' => 'electronics',
			'code' => 'EL',
		),
		array(
			'name' => 'Clothing & Fashion',
			'slug' => 'clothing-fashion',
			'code' => 'CF',
		),
		array(
			'name' => 'Home & Garden',
			'slug' => 'home-garden',
			'code' => 'HG',
		),
		array(
			'name' => 'Toys & Games',
			'slug' => 'toys-games',
			'code' => 'TG',
		),
		array(
			'name' => 'Pet Supplies',
			'slug' => 'pet-supplies',
			'code' => 'PS',
		),
		array(
			'name' => 'DIY & Tools',
			'slug' => 'diy-tools',
			'code' => 'DT',
		),
		array(
			'name' => 'Kitchen',
			'slug' => 'kitchen',
			'code' => 'KT',
		),
		array(
			'name' => 'Office & Stationery',
			'slug' => 'office-stationery',
			'code' => 'OS',
		),
		array(
			'name' => 'Sports & Leisure',
			'slug' => 'sports-leisure',
			'code' => 'SL',
		),
		array(
			'name' => 'Seasonal',
			'slug' => 'seasonal',
			'code' => 'SE',
		),
		array(
			'name' => 'Mixed Products',
			'slug' => 'mixed-products',
			'code' => 'MX',
		),
		array(
			'name'     => 'Wholesale, Liquidation & Bulk Buy',
			'slug'     => 'wholesale-liquidation-bulk-buy',
			'code'     => 'WL',
			'children' => array(
				array(
					'name' => 'Pallets & Lots',
					'slug' => 'pallets-lots',
					'code' => 'PL',
				),
			),
		),
	),

	// New products go here when no category is chosen.
	'default_category'  => 'mixed-products',

	'tags'              => array(
		array(
			'name'        => 'New Stock',
			'slug'        => 'new-stock',
			'description' => 'Added in the last 30 days. The tag is removed automatically after 30 days.',
		),
		array(
			'name'        => 'Clearance',
			'slug'        => 'clearance',
			'description' => 'Clearance deals.',
		),
	),

	// Product attributes, used as shop filters. Terms are only listed where the wording is fixed.
	'attributes'        => array(
		array(
			'name'     => 'Condition',
			'slug'     => 'condition',
			'order_by' => 'menu_order',
			'terms'    => array(
				array(
					'name'        => 'New',
					'slug'        => 'new',
					'description' => 'Unused, in original sealed packaging.',
				),
				array(
					'name'        => 'New, Open Box',
					'slug'        => 'new-open-box',
					'description' => 'Unused, packaging opened or damaged.',
				),
				array(
					'name'        => 'Customer Return, Tested',
					'slug'        => 'customer-return-tested',
					'description' => 'Returned by a customer, checked and working.',
				),
				array(
					'name'        => 'Customer Return, Untested',
					'slug'        => 'customer-return-untested',
					'description' => 'Returned by a customer, not checked. Sold as seen, mainly for trade.',
				),
				array(
					'name'        => 'Used',
					'slug'        => 'used',
					'description' => 'Previously used, working, signs of wear described.',
				),
			),
		),
		array(
			'name'     => 'Brand',
			'slug'     => 'brand',
			'order_by' => 'name',
		),
		array(
			'name'     => 'Colour',
			'slug'     => 'colour',
			'order_by' => 'name',
		),
		array(
			'name'     => 'Size',
			'slug'     => 'size',
			'order_by' => 'menu_order',
		),
	),

	'shipping_classes'  => array(
		array(
			'name'        => 'Standard parcel',
			'slug'        => 'standard-parcel',
			'description' => 'Fits a standard parcel service.',
		),
		array(
			'name'        => 'Large parcel',
			'slug'        => 'large-parcel',
			'description' => 'Bulky or heavy items sent by a large parcel service.',
		),
		array(
			'name'        => 'Pallet',
			'slug'        => 'pallet',
			'description' => 'Pallet delivery, quoted after the order is placed.',
		),
	),
);
