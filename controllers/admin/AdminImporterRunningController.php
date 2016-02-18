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

	public function displayAjaxtestImport()
	{
		$product = new Product();

		die($product);
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
		$newproduct = $this->addProduct($this->xml_file_content->product[(int)$this->current_key_in_xml]);

		$return = array(
			'product' => $this->xml_file_content->product[(int)$this->current_key_in_xml],
			'newproduct' => $newproduct
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
		$product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $product_info->title);
		$product->description = $product_info->description;
		$product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => Tools::link_rewrite($product_info->title));
		$product->reference = $product_info->artnr;
		// //$product->ean13
		$product->supplier_reference = $product_info->artnr;
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
		$product->add();

		//Marche pas
		//AdminImportController::copyImg($product->id,null,'http://cdn.edc-internet.nl/500/'.$product_info->artnr,'products');



		return true;
	}
	private function productImport($pr)
	{
		// $default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		// $id_lang = Language::getIdByIso(Tools::getValue('iso_lang'));
		// if (!Validate::isUnsignedId($id_lang))
		// 	$id_lang = $default_language_id;
		// AdminImportController::setLocale();
		// $shop_ids = Shop::getCompleteListOfShopsID();
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