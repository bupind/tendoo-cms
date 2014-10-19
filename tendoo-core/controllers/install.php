<?php
class Install extends Libraries
{
	private $data;
	public function __construct()
	{
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		parent::__construct();
		__extends($this);
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$this->instance				=	get_instance();
		$this->load->library('form_validation');
		$this->load->library('installation');
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$this->file->css_push('font');
		$this->file->css_push('app.v2');
		$this->file->css_push('fuelux');
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$this->file->js_push('jquery');
		$this->file->js_push('app.min.vtendoo');
		$this->file->js_push('tendoo_app');
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	}
	public function index($i = 1,$e = '')
	{
		$this->step($i,$e);
	}
	public function step($i = 1,$e = '')
	{
		if($this->lang->isLangSelected())
		{
			$this->url->redirect(array('install','defineLang'));
		}
		$this->data['step']	=	$i;
		$this->data['InstallError'] = '';
		if($i == 1)
		{
			$_SESSION['secur_access'] = 1;
			if(isset($_POST['submit']))
			{
				$_SESSION['secur_access'] = 2;
				$this->url->redirect(array('install','step',2));
			}
			set_page('title',translate('install_tendoo_title',array('version : '.get('core_id'))));
			$this->load->view('header',$this->data);
			$this->load->view('install/step/1/homebody',$this->data);
		}
		else if($i == 2)
		{
			if(!isset($_SESSION['secur_access']))
			{
				$this->url->redirect(array('install','step',1));
			}
			else
			{
				if(!in_array($_SESSION['secur_access'],array(1,2)))
				{
					$this->url->redirect(array('install','step',1));
				}
			}
			if(isset($_POST['host_name'],$_POST['user_name']))
			{
				$this->form_validation->set_rules('host_name','Nom de l\'hôte','trim|required');
				$this->form_validation->set_rules('user_name','Nom de l\'utilisateur','trim|required');
				// $this->form_validation->set_rules('host_password','mot de passe','trim|required'); // to allow empty password usage.
				$this->form_validation->set_rules('db_name','nom de la base de donn&eacute;e','trim|required');
				$this->form_validation->set_rules('db_type','type du serveur','trim|required');
				$this->form_validation->set_rules('extension_name','Extension des tables','trim|required');
				if($this->form_validation->run())
				{
					$connexion	=	$this->installation->attempt_db_connection(
						$this->input->post('host_name'),
						$this->input->post('user_name'),
						$this->input->post('host_password'),
						$this->input->post('db_name'),
						$this->input->post('db_type'),
						$this->input->post('extension_name')
					);
					if($connexion === TRUE)
					{
						$_SESSION['secur_access'] = 3;
						$this->url->redirect(array('install','step',3));
					}
				}
			}
			set_page('title',translate('tendoo_install_first_step'));
			$this->load->view('header',$this->data);
			$this->load->view('install/step/2/homebody',$this->data);
		}
		else if($i == 3)
		{
			if(isset($_SESSION['db_datas']))
			{
				if(!isset($_SESSION['secur_access']))
				{
					$this->url->redirect('install/step/1/secur_access_not_defined');
				}
				else
				{
					if(!in_array($_SESSION['secur_access'],array(1,2,3)))
					{
						$this->url->redirect('install/step/1');
					}
				}
				set_page('title',translate('tendoo_install_second_step'));
				$this->load->view('header',$this->data);
				$this->load->view('install/step/3/homebody',$this->data);
			}
			else
			{
				$this->url->redirect('install/step/1/installError');
			}
		}
	}
	public function createTables()
	{
		if(isset($_POST['site_name']))
		{
			$this->form_validation->set_rules('site_name','Nom du site','trim|required|min_length[4]');
			if($this->form_validation->run())
			{
				// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
				$this->instance->db_connect(); // En utilisant les données de la session
				$this->instance		=	get_instance();
				// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
				$this->load->library('tendoo'); // Refreshing Tendoo Clss
				$this->load->library('tendoo_admin'); // loading Admin Class
				$this->load->library('options');
				$this->load->library('installation'); // Refreshing installation class
				// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
				if(!$this->installation->createTables())
				{
					echo 'false'; // Table creation failed, redirect so
					return false;
				}
				if(set_options(array(
					'SITE_NAME'				=>		$this->input->post('site_name'),	// Nom du site
					'CONNECT_TO_STORE'		=>		1 										// Par défaut se connecte au Store
				),	"from_install_interface"	))
				{
					$_SESSION['secur_access']	=	4;
					echo 'true';
				}
				else
				{
					echo 'false';
				}
			}
			else
			{
				echo 'invalidesitename';
			}
			// Execute control
		}
		else
		{
			echo 'nositename';
		}
	}
	public function app_step( $step_id = 1 )
	{
		$this->load->library('installation'); // Refreshing installation class
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		echo $this->installation->app_step( $step_id );
	}
	public function defineLang()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('lang','Selection de la langue / Lang selection','trim|required|min_length[3]');
		if($this->form_validation->run())
		{
			$this->instance->lang->defineLang('fr_FR'); // $this->input->post('lang')
			$this->url->redirect(array('install','step','1'));
		}
		set_page('title','Tendoo &raquo; Choose installation language, Choissisez la langue d\'installation');
		$this->load->view('header',$this->data);
		$this->load->view('install/lang/body',$this->data);
	}
}