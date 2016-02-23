<?php

class AdminImporterCounterController extends ModuleAdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->bootstrap = true;
	}

	public function renderList()
	{
		$_html = "";

		if (isset($_POST["submitFileCounter"]) && $_POST["submitFileCounter"]==1)
		{
			$result = $this->uploadXml();
			if(isset($result['file']['error']) && !empty($result['file']['error']))
				$_html .= $this->module->displayError($result['file']['error']);
			else
			{
				$_html .= $this->module->displayConfirmation($result['file']['filename']);
			}
		}
		
		
		$_html .= $this->counterForm();
		$_html .= $this->listXML();

		return $_html.parent::renderList();
	}

	public function counterForm()
	{
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Counter')
			),
			'input' => array(
				array(
					'type' => 'file',
					'label' => $this->l('Vos fichier'),
					'name' => 'file',
					'required' => true,
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
			)
		);
		$helper = new HelperForm();
		$helper->module = $this->module;
		$helper->token = Tools::getValue('token');

		$helper->title = 'Counter of files';
		$helper->submit_action = 'submitFileCounter';

		return $helper->generateForm($fields_form);
	}

	private function uploadXml()
	{
		$filename_prefix = '';//date('YmdHis').'-';

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
			!move_uploaded_file($_FILES['file']['tmp_name'], AdminImporterCounterController::getPath().$filename_prefix.str_replace("\0", '', $_FILES['file']['name'])))
			$_FILES['file']['error'] = $this->l('An error occurred while uploading / copying the file.');
		else
		{
			@chmod(AdminImporterCounterController::getPath().$filename_prefix.$_FILES['file']['name'], 0664);
			$_FILES['file']['filename'] = $filename_prefix.str_replace('\0', '', $_FILES['file']['name']);
		}

		return $_FILES;
	}

	public static function getPath($file = '')
	{
		return (_PS_MODULE_DIR_.'importer'.DIRECTORY_SEPARATOR.'counter'.DIRECTORY_SEPARATOR.$file);
	}

	public function listXML()
	{	
		$files = array();
		$dir = AdminImporterCounterController::getPath();
		if($handle = opendir($dir))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && $entry != ".DS_Store")
				{
					$xml_file = simplexml_load_file($this->getPath($entry));
					$nb_line = count($xml_file);
					$files[] = array(
						'filename' => $entry,
						'nb_line' => $nb_line
					);
				}
			}
			closedir($handle);
		}
		
		if(!empty($files))
		{
			$this->context->smarty->assign(array(
				'empty' => false,
				'files' => $files
			));
		} 
		else 
		{
			$this->context->smarty->assign(array(
				'empty' => true,
				'files' => ''
			));
		}
		

		return $this->context->smarty->fetch(_PS_MODULE_DIR_.'importer/views/templates/admin/counter.tpl');
	}
}