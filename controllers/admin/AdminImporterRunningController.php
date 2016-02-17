<?php

class AdminImporterRunningController extends ModuleAdminController
{
	private $current_step;
	private $current_key_in_xml;
	private $xml_file;
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
	
	// public function display()
	// {
	// 	$xml_the_file = _PS_MODULE_DIR_.'importer/assets/'.$this->xml_file;
	// 	$products = simplexml_load_file($xml_the_file);
	// 	$nproducts = count($products);
	// 	$brands = array();
		
	// 	//On passe les produits en boucle pour récupérer les marques
	// 	for($i = 0; $i < $nproducts; $i++)
	// 	{
	// 		$brand_id = (int)$products->product[$i]->brand->id;
	// 		$brands[$brand_id] = $products->product[$i]->brand;
	// 		unset($products->product[$i]->brand);
	// 	}
	// 	//p($products->product[0]);
	// 	//Tableau des marquesd
	// 	$nbrands = count($brands);
	// 	ksort($brands);
		
	// 	// p('Nombre de marques ' . $nbrands);
	// 	// p('Nombre total de produits ' . $nproducts);
		
	// 	// // p($brands);
	// 	// d('Bye');
	// }


	public function __construct()
	{
		parent::__construct();
		$this->bootstrap = true;

		//Check the step value;
		$this->current_step = (int)Configuration::get('IMPORTER_IMPORT_CURRENT_STEP');
		$this->current_key_in_xml = (int)Configuration::get('IMPORTER_IMPORT_CURRENT_KEY_IN_XML');
		$this->xml_file = Configuration::get('IMPORTER_XML_FILE');
	}

	public function display()
	{
		parent::display();
	}

	public function setMedia()
	{
		parent::setMedia();
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
			'xml_file' => $this->xml_file,
			'message' => "ici on est dans displayAjaxTest"
		);
		die(Tools::jsonEncode(array_merge($return,$this->loopOnXml())));
	}

	private function loopOnXml()
	{
		$return = array(
			'current_key_in_xml' => $this->current_key_in_xml
		);
		++$this->current_key_in_xml;
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_KEY_IN_XML',$this->current_key_in_xml);
		return $return;
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
				$this->xml_file = $result['file']['filename'];
				Configuration::updateValue('IMPORTER_XML_FILE',$this->xml_file);
				

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

		$helper->title = 'tergergre';
		$helper->submit_action = 'submitStep1Importer';
		$helper->fields_value['thefeedfile'] = '';
		return $helper->generateForm($fields_form);
	}
	public function stepTwo()
	{
		//Ici on va demander au client de cliquer sur un bouton pour démarrer un import vers une table intermédiaire
		/// A voir si ça peut fonctionner entre fetch et display
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
}