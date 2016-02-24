<?php

class AdminImporterRunningController extends ModuleAdminController
{
	private $current_step;
	private $current_key_in_xml;
	private $xml_filename;
	private $xml_file_line_count;
	private $xml_file_content;

	// We can test if we can put an url of the feeds ddeeee

	// First we need to let the user upload the XML file. 
	// After we parse the xml file to object and we loop on it.
	// We take apart the brands and make an array with their. 
	// we need to save the products from the XML into a dedicated db, 
	// we do the same with brand, and possibily for the sub products and the categories
 	
	/*
	For the ajax it's "simple", just need to put a js script in the tpl page, who call the admincontroller for the work
	And i need to check the "DropshippingProductsImports" class from the dropshipping.
	*/

	//We can try to do the both think, try to put different form in different php function
	// and in function of it's fill we move the step by incrusting thing
	// to get a renderForm whom work we need to call it ?
	// What do you think about ?
	



	public function __construct()
	{
		parent::__construct();
		$this->bootstrap = true;

		//Check the step value;
		$this->current_step = (int)Configuration::get('IMPORTER_IMPORT_CURRENT_STEP');
		$this->current_key_in_xml = (int)Configuration::get('IMPORTER_IMPORT_CURRENT_KEY_IN_XML');
		$this->xml_filename = Configuration::get('IMPORTER_XML_FILE');
		$this->xml_file_line_count = Configuration::get('IMPORTER_XML_COUNT');
	}

	public function display()
	{
		//d($this->context->shop);
		parent::display();
	}
	// public function display()
	// {
	// 	// $xml_the_file = _PS_MODULE_DIR_.'importer/assets/'.$this->xml_filename;
	// 	// $products = simplexml_load_file($xml_the_file);
	// 	// $nproducts = count($products);
	// 	// $brands = array();
		
	// 	// //On passe les produits en boucle pour récupérer les marques
	// 	// for($i = 0; $i < $nproducts; $i++)
	// 	// {
	// 	// 	$brand_id = (int)$products->product[$i]->brand->id;
	// 	// 	$brands[$brand_id] = $products->product[$i]->brand;
	// 	// 	unset($products->product[$i]->brand);
	// 	// }
	// 	// //p($products->product[0]);
	// 	// //Tableau des marquesd
	// 	// $nbrands = count($brands);
	// 	// ksort($brands);
		
	// 	// p('Nombre de marques ' . $nbrands);
	// 	// p('Nombre total de produits ' . $nproducts);
		
	// 	// // p($brands);
	// 	// d('Bye');
	// 	$this->loadXML();
	// 	$pr = $this->xml_file_content;

	// 	$product = new Product();
	// 	d($product);

	// }

	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(__PS_BASE_URI__.'modules/'.$this->module->name.'/views/css/global.css');
		$this->addJS(__PS_BASE_URI__.'modules/'.$this->module->name.'/js/importerRunning-step2.js');
	}

	public function displayAjax()
	{
		$return = array(
			'hasError' => true,
			'errors' => 'Ceci est le message'
		);
		die(Tools::jsonEncode($return));
	}

	public function displayAjaxtransfertXml()
	{
		$return = array(
			'message' => 'On run la boucle',
			'status' => 'looping',
			'current_key_in_xml' => $this->current_key_in_xml,
			'xml_file_line_count' => $this->xml_file_line_count,
		);
		if($this->current_key_in_xml < $this->xml_file_line_count)
			$return = array_merge($return,$this->loopOnXml());
		else {
			$return = array_merge($return, array('message' => 'On arrive est à la fin du fichier', 'status' => 'loop_end'));
			//Il faut faire qlq chose pour interdire le reload de la page et la ré-importation
		}
		die(json_encode($return));
	}

	private function loopOnXml()
	{
		$this->loadXML();
		//Ici on fait l'action sur les lignes XML
		$newproductreturn = $this->addProduct($this->xml_file_content->product[(int)$this->current_key_in_xml]);

		$return = array(
			'newproductreturn' => $newproductreturn,
			'product' => $this->xml_file_content->product[(int)$this->current_key_in_xml]
		);

		//if($this->productImport($return['product'])){

		++$this->current_key_in_xml;
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_KEY_IN_XML',$this->current_key_in_xml);
		return $return;
		// } else {
		// 	return array('status'=>'error','message'=>'Erreur du productImport');
		// }
	}

	// 
	private function addProduct($product_info)
	{
		//if($product_info->variants)
		//TODO need to check the size of variants, if it's only one variant, we can grab ean and other info
		// If not we must check the prestashop Product classes to see if we can add combinations
		$product = new Product();
		$product->id_shop_default = $this->context->shop->id;
		$product->id_tax_rules_group = 0;
		$product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->title);
		$product->description = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->description);
		$product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => Tools::link_rewrite($product_info->title));
		$product->reference = $product_info->artnr;
		// //$product->ean13
		$product->supplier_reference = $product_info->artnr;
		$product->id_manufacturer = 0;
		$product->id_supplier = 0;
		$product->quantity = 1;
		$product->minimal_quantity = 1;


		$product->category = array(2);
		$product->id_category_default = 2;
		// //Price
		$product->wholesale_price = $product_info->price->b2b;
		//Attention au prix b2c, il est TVAC
		//TODO il faut donc enlever la tva avant le l'inserer
		$product->price = $product_info->price->b2c;
		// // Date info
		$product->date_add = $product_info->date;
		$product->date_upd = $product_info->modifydate;
		// //Supplier
		$product->supplier_name = 'EDC';

		$product->active = false;
		$copyimg_return = AdminImportController::copyImg($product->id,null,'http://cdn.edc-internet.nl/500/'.$product_info->artnr.'.jpg','products',true);
		$product->add();




		return array('newproduct' => $product,'copyimg_return' => $copyimg_return,'id_category_default' => $product->id_category_default);
	}

	public function displayAjaxtestImport()
	{
		$this->loadXML();
		$product_info = $this->xml_file_content->product[1];

		$product = new Product();
		$product->id_shop_default = $this->context->shop->id;
		$product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->title);
		//$product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => Tools::link_rewrite($product_info->title));
		$product->add();

		die(json_encode($product));
	}

	/**
	* This function create/add attribute if it doesn't exist
	* @param array $variants
	* @return bool
	*/
	private function createAttribute($variants)
	{
		/*
		DATA SCHEME of 
		[variants] => SimpleXMLElement Object
        (
            [variant] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [id] => 34945
                            [type] => S
                            [subartnr] => 28403911040
                            [ean] => 4024144254118
                            [stock] => Y
                            [title] => Large
                        )

                    [1] => SimpleXMLElement Object
                        (
                            [id] => 34944
                            [type] => S
                            [subartnr] => 28403911030
                            [ean] => 4024144254101
                            [stock] => Y
                            [title] => Medium
                        )
                    [3] => SimpleXMLElement Object
		                (
		                    [id] => 34925
		                    [type] => SimpleXMLElement Object
		                        (
		                        )

		                    [subartnr] => usb001eu
		                    [ean] => 8714273796318
		                    [stock] => Y
		                )
                )

        )
        */
		// :TODO, if the type is different than S we need to put the product 
		// with his variants in a special array to alert the useradmin
		
		$default_language = Configuration::get('PS_LANG_DEFAULT');

		$groups = array();
		foreach (AttributeGroup::getAttributesGroups($default_language) as $group)
			$groups[$group['name']] = (int)$group['id_attribute_group'];
		p($groups);

		$attributes = array();
		foreach (Attribute::getAttributes($default_language) as $attribute)
			$attributes[$attribute['attribute_group'].'_'.$attribute['name']] = (int)$attribute['id_attribute'];
		
		p($attributes);
		//We can make a conversion table
		// It's obligatory to make that possible
		// conversion table with language
		dazdzef
		/*
			[ 
			M/L, 
			S/M, 
			Large, 
			XL, 
			Small, 
			Medium, 
			XXL, 
			Cup B, 
			Cup C, 
			S/L, 
			XL-XXL, 
			QS 46-50, 
			Maat 2, 
			Maat 3, 
			Maat 4, 
			Maat 5, 
			85 D, 
			85 C, 
			80 B, 
			75 B, 
			L/XL, 
			80 B/M, 
			85 B/L, 
			75 B/S, 
			Maat 1, 
			Maat 46, 
			Maat 45, 
			Maat 44, 
			Maat 43, 
			Maat 42, 
			Maat 41, 
			Maat 40, 
			Maat 39, 
			Maat 38, 
			Maat 37, 
			Maat 36, 
			Xs, 
			86 cm, 
			76 cm, 
			66 cm, 
			56 cm, 
			One Size, 
			90 B/XL, 
			85 C/L, 
			80 C/M, 
			Xtra Small, 
			85 B, 
			75 A, 
			Maat 7, 
			Maat 6, 
			85 D/XL, 
			80 C/L, 
			50 mm, 
			40 mm, 
			3-4X 48-54, 
			1-2X 44-48, 
			2XL 48, 
			1XL 44/46, 
			XXXL, 
			PS 44-52, 
			3XL 50/52, 
			90 D, 
			90 E, 
			95 D, 
			85 E, 
			95 E, 
			XXL/XXXL, 
			32 mm, 
			30 mm, 
			28 mm, 
			25 mm, 
			12 mm, 
			8 mm, 
			10 mm, 
			9 mm, 
			11 mm, 
			55 mm, 
			45 mm, 
			20 mm, 51 mm, 48 mm, 42 mm, 39 mm, 36 mm, 33 mm, 54 mm, 60 mm, 35 mm, 4mm, 6 mm, 7mm, 5mm, 100mm, 90mm, 80mm, 70mm, 18 cm, 16 cm, XXXXL, 75 C/S, XS/S, 90 C/XL, 75 A/S, ]
		*/
		//Cock_ring size

		d('Bye');

	}

	public function testImport()
	{
		$this->loadXML();
		$product_info = $this->xml_file_content->product[3];
		$this->createAttribute($product_info->variants);
		d($product_info);


		// We need to check the variant/déclinaisons/attribute


		$product = new Product();
		$product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => (string)$product_info->title);
		$product->manufacturer = (string)$product_info->brand->title;
		$product->supplier = 'edc';
		$product->price_tin = (int)$product_info->price->b2c;
		$product->tax_rate = 21;
		$product->category = $product_info->categories;
		$product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => Tools::link_rewrite((string)$product_info->title));
		$product->image = (array) $product_info->pics->pic;
		$ncategory = count($product->category->category);
		$new_category_tab = array();
		for ($i = 0; $i < $ncategory; ++$i)
		{
			$new_category_tab[] = (string)$product->category->category[$i]->cat[0]->title.'/'.(string)$product->category->category[$i]->cat[1]->title;
		}

		$product->category = $new_category_tab;

		$default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		$id_lang = Tools::getValue('iso_lang');
		if (!Validate::isUnsignedId($id_lang))
			$id_lang = $default_language_id;

		if (!Shop::isFeatureActive())
			$product->shop = 1;
		// elseif (!isset($product->shop) || empty($product->shop))
		// 		$product->shop = implode($this->multiple_value_separator, Shop::getContextListShopID());

		if (!Shop::isFeatureActive())
			$product->id_shop_default = 1;
		else
			$product->id_shop_default = (int)Context::getContext()->shop->id;

		$product->id_shop_list = array();
		$product->id_shop_list[] = 1;
		if (isset($product->manufacturer) && is_numeric($product->manufacturer) && Manufacturer::manufacturerExists((int)$product->manufacturer))
			$product->id_manufacturer = (int)$product->manufacturer;
		elseif (isset($product->manufacturer) && is_string($product->manufacturer) && !empty($product->manufacturer))
		{
			if ($manufacturer = Manufacturer::getIdByName($product->manufacturer))
				$product->id_manufacturer = (int)$manufacturer;
			else
			{
				$manufacturer = new Manufacturer();
				$manufacturer->name = $product->manufacturer;
				if (($field_error = $manufacturer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
					($lang_field_error = $manufacturer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $manufacturer->add())
					$product->id_manufacturer = (int)$manufacturer->id;
				else
				{
					$this->errors[] = sprintf(
						Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
						$manufacturer->name,
						(isset($manufacturer->id) && !empty($manufacturer->id))? $manufacturer->id : 'null'
					);
					$this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
						Db::getInstance()->getMsgError();
				}
			}
		}

		if (isset($product->supplier) && is_numeric($product->supplier) && Supplier::supplierExists((int)$product->supplier))
			$product->id_supplier = (int)$product->supplier;
		elseif (isset($product->supplier) && is_string($product->supplier) && !empty($product->supplier))
		{
			if ($supplier = Supplier::getIdByName($product->supplier))
				$product->id_supplier = (int)$supplier;
			else
			{
				$supplier = new Supplier();
				$supplier->name = $product->supplier;
				$supplier->active = true;
				if (($field_error = $supplier->validateFields(UNFRIENDLY_ERROR, true)) === true &&
					($lang_field_error = $supplier->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $supplier->add())
				{
					$product->id_supplier = (int)$supplier->id;
					$supplier->associateTo($product->id_shop_list);
				}
				else
				{
					$this->errors[] = sprintf(
						Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
						$supplier->name,
						(isset($supplier->id) && !empty($supplier->id))? $supplier->id : 'null'
					);
					$this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
						Db::getInstance()->getMsgError();
				}
			}
		}

		if (isset($product->price_tex) && !isset($product->price_tin))
			$product->price = $product->price_tex;
		elseif (isset($product->price_tin) && !isset($product->price_tex))
		{
			$product->price = $product->price_tin;
			// If a tax is already included in price, withdraw it from price
			if ($product->tax_rate)
				$product->price = (float)number_format($product->price / (1 + $product->tax_rate / 100), 6, '.', '');
		}
		elseif (isset($product->price_tin) && isset($product->price_tex))
			$product->price = $product->price_tex;

		if (isset($product->category) && is_array($product->category) && count($product->category))
		{
			$product->id_category = array(); // Reset default values array
			foreach ($product->category as $value) 
			{
				if (is_string($value) && !empty($value))
				{
					$category = Category::searchByPath($default_language_id, trim($value), 'AdminImportControllerCore', 'productImportCreateCat');
					if ($category['id_category'])
						$product->id_category[] = (int)$category['id_category'];
					else
						$this->errors[] = sprintf(Tools::displayError('%1$s cannot be saved'), trim($value));
				}
			}
			$product->id_category = array_values(array_unique($product->id_category));
			
		}

		if (!isset($product->id_category_default) || !$product->id_category_default)
			$product->id_category_default = isset($product->id_category[0]) ? (int)$product->id_category[0] : (int)Configuration::get('PS_HOME_CATEGORY');

		p($product->link_rewrite);
		$link_rewrite = (is_array($product->link_rewrite) && isset($product->link_rewrite[$id_lang])) ? trim($product->link_rewrite[$id_lang]) : '';
		$valid_link = Validate::isLinkRewrite($link_rewrite);

		// if ((isset($product->link_rewrite[$id_lang]) && empty($product->link_rewrite[$id_lang])) || !$valid_link)
		// {
		// 	$link_rewrite = Tools::link_rewrite($product->name[$id_lang]);
		// 	if ($link_rewrite == '')
		// 		$link_rewrite = 'friendly-url-autogeneration-failed';
		// }

		// if (!$valid_link)
		// 	$this->warnings[] = sprintf(
		// 		Tools::displayError('Rewrite link for %1$s (ID: %2$s) was re-written as %3$s.'),
		// 		$product->name[$id_lang],
		// 		(isset($info['id']) && !empty($info['id']))? $info['id'] : 'null',
		// 		$link_rewrite
		// 	);

		//$product->indexed = 0;

		$res = false;

		$product->add();


		//Category
		if (isset($product->id_category) && is_array($product->id_category))
			$product->updateCategories(array_map('intval', $product->id_category));

		//Supplier
		if (isset($product->id) && $product->id && isset($product->id_supplier) && property_exists($product, 'supplier_reference'))
		{
			$id_product_supplier = (int)ProductSupplier::getIdByProductAndSupplier((int)$product->id, 0, (int)$product->id_supplier);
			if ($id_product_supplier)
				$product_supplier = new ProductSupplier($id_product_supplier);
			else
				$product_supplier = new ProductSupplier();

			$product_supplier->id_product = (int)$product->id;
			$product_supplier->id_product_attribute = 0;
			$product_supplier->id_supplier = (int)$product->id_supplier;
			$product_supplier->product_supplier_price_te = $product->wholesale_price;
			$product_supplier->product_supplier_reference = $product->supplier_reference;
			$product_supplier->save();
		}

		//Image
		$base_url = 'http://cdn.edc-internet.nl/500/';
		if (isset($product->image) && is_array($product->image) && count($product->image))
		{
			foreach ($product->image as $key => $url)
			{
				$url = $base_url.$url;
				$url = trim($url);
				$error = false;
				if (!empty($url))
				{
					$url = str_replace(' ', '%20', $url);

					$image = new Image();
					$image->id_product = (int)$product->id;
					$image->position = Image::getHighestPosition($product->id) + 1;
					$image->cover = true;
					// file_exists doesn't work with HTTP protocol
					if ($image->add())
					{
						$image->associateTo($shops);
						if (!AdminImportController::copyImg($product->id,$image->id,$url,'products',!Tools::getValue('regenerate')))
						{
							$image->delete();
							$this->warnings[] = sprintf(Tools::displayError('Error copying image: %s'), $url);
						}
					}
					else
						$error = true;
				}
				else
					$error = true;
				if ($error)
					$this->warnings[] = sprintf(Tools::displayError('Product #%1$d: the picture (%2$s) cannot be saved.'), $image->id_product, $url);
			}
		}

		// $product->id_shop_default = $this->context->shop->id;
		// $product->supplier_reference = $product_info->artnr;
		// $product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->title);
		// $product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => Tools::link_rewrite($product_info->title));
		// $product->description = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->description);
		// $product->id_category_default = 2;
		// $product->categories = array(2);
		// $product->price = $product_info->price->b2c;
		// $product->wholesale_price = $product_info->price->b2b;
		// $product->add();
		
		//p($product);
		//p($product->getFields());
		d('die');
	}

	private function productImport($pr)
	{
		// $default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		// $id_lang = Language::getIdByIso(Tools::getValue('iso_lang'));
		// if (!Validate::isUnsignedId($id_lang))
		// 	$id_lang = $default_language_id;
		// AdminImportController::setLocale();
		// $shop_ids = Shoazdeergregtrgrtehp::getCompleteListOfShopsID();
		// $product = new Product();
		// $product->reference = $pr->artnr;
		// $product->name = $pr->title;
		// return $product->add();
	}


	public function displayAjaxresetXmlCountLine()
	{
		// Reset the line count
		$this->current_key_in_xml = 0;
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_KEY_IN_XML',$this->current_key_in_xml);
		$return = array(
			'current_key_in_xml' => $this->current_key_in_xml
		);
		die(json_encode($return));
	}

	public function renderList()
	{
		$_html = "";

		// Get the current step
		$this->checkCurrentStep();
		if(!$this->checkTheXMLExist())
		{
			if($this->xml_filename != '')
				$_html .= $this->module->displayError('The xml file doesn\'t exist. You need to re-upload it.');
		}

		if(isset($_POST["submitStep1Importer"]) && $_POST["submitStep1Importer"]==1) //Step 1 Submit
		{
			//On importe le fichier
			$result = $this->uploadXml();
			if(isset($result['file']['error']) && !empty($result['file']['error']))
				$_html .= $this->module->displayError($result['file']['error']);
			else
			{
				$_html .= $this->module->displayConfirmation($result['file']['filename']);
				$this->changeStep(1);
				$this->xml_filename = $result['file']['filename'];
				Configuration::updateValue('IMPORTER_XML_FILE',$this->xml_filename);
				Configuration::updateValue('IMPORTER_IMPORT_CURRENT_KEY_IN_XML',0);
				$this->loadXML();
				$this->countXML();
			}
		}
		elseif (isset($_POST["submitStep2Importer"]) && $_POST["submitStep2Importer"]==1)
		{
			//On va executer qlq chose
			$this->testImport();
		}

		$_html .= $this->module->display(_MODULE_DIR_.'importer', 'views/templates/admin/stepIndicator.tpl');


		if($this->current_step === 0)
			$_html .= $this->stepOne();
		elseif($this->current_step === 1)
			$_html .= $this->stepTwo();

		return $_html.parent::renderList();
	}
	
	private function uploadXml()
	{
		$filename_prefix = date('YmdHis').'-';

		if (isset($_FILES['file']) && !empty($_FILES['file']['error']))
		{
			switch ($_FILES['file']['error']) 
			{
				case UPLOAD_ERR_INI_SIZE:
					$_FILES['file']['error'] = Tools::displayError('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess.');
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$_FILES['file']['error'] = Tools::displayError('The uploaded file exceeds the post_max_size directive in php.ini.
						If your server configuration allows it, you may add a directive in your .htaccess, for example:')
					.'<br/><a href="'.$this->context->link->getAdminLink('AdminMeta').'" >
					<code>php_value post_max_size 20M</code> '.
					Tools::displayError('(click to open "Generators" page)').'</a>';
					break;
				break;
				case UPLOAD_ERR_PARTIAL:
					$_FILES['file']['error'] = Tools::displayError('The uploaded file was only partially uploaded.');
					break;
				break;
				case UPLOAD_ERR_NO_FILE:
					$_FILES['file']['error'] = Tools::displayError('No file was uploaded.');
					break;
				break;
			}
		}
		elseif (!preg_match('/.*\.xml$/i', $_FILES['file']['name']))
			$_FILES['file']['error'] = Tools::displayError('The extension of your file should be .xml.');
		elseif (!@filemtime($_FILES['file']['tmp_name']) || 
			!move_uploaded_file($_FILES['file']['tmp_name'], AdminImporterRunningController::getPath().$filename_prefix.str_replace("\0", '', $_FILES['file']['name'])))
			$_FILES['file']['error'] = $this->l('An error occurred while uploading / copying the file.');
		else
		{
			@chmod(AdminImporterRunningController::getPath().$filename_prefix.$_FILES['file']['name'], 0664);
			$_FILES['file']['filename'] = $filename_prefix.str_replace('\0', '', $_FILES['file']['name']);
		}

		return $_FILES;
	}

	public function stepOne()
	{
		//Ici on va créer une form
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type' => 'file',
					'label' => $this->l('Votre fichier'),
					'name' => 'file',
					'required' => true,
				)
				
			),
			'submit' => array(
				'title' => $this->l('	Save 	'),
				'class' => 'btn btn-default pull-right'
			)
		);
		$helper = new HelperForm();
		$helper->module = $this->module;
		$helper->token = Tools::getValue('token');
		//$helper->currentIndex = AdminController::$currentIndex;

		$helper->title = 'StepOne';
		$helper->submit_action = 'submitStep1Importer';
		$helper->fields_value['thefeedfile'] = '';
		return $helper->generateForm($fields_form);
	}
	public function stepTwo()
	{
		//Ici on va demander au client de cliquer sur un bouton pour démarrer un import vers une table intermédiaire
		/// A voir si ça peut fonctionner entre fetch et display
		$this->context->smarty->assign(array(
			'xml_file_count' => $this->xml_file_line_count,
			'xml_filename' => $this->xml_filename,
			'xml_current_key' => $this->current_key_in_xml
		));
		return $this->context->smarty->fetch(_PS_MODULE_DIR_.'importer/views/templates/admin/steptwo.tpl');
	}

	public static function getPath($file = '')
	{
		return (_PS_MODULE_DIR_.'importer'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.$file);
	}

	private function checkCurrentStep()
	{
		$this->current_step = (int)Configuration::get('IMPORTER_IMPORT_CURRENT_STEP');
	}

	private function checkTheXMLExist()
	{
		if (file_exists($this->getPath($this->xml_filename)))
		{
			return true;
		}
		else 
		{	
			$this->changeStep(0);
			return false;
		}
	}

	private function changeStep($nStep)
	{
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_STEP',(int)$nStep);
		$this->current_step = (int)$nStep;
	}
	private function countXML()
	{
		//Function to count xml
		$this->xml_file_line_count = count($this->xml_file_content);
		Configuration::updateValue('IMPORTER_XML_COUNT',(int)$this->xml_file_line_count);
	}
	private function loadXML()
	{
		$this->xml_file_content = simplexml_load_file($this->getPath($this->xml_filename));
	}
}